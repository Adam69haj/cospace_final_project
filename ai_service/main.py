import os

from dotenv import load_dotenv
from fastapi import FastAPI, HTTPException
from google import genai
from google.genai import types #needed so we can pass configuration (GenerateContentConfig) to Gemini.
from pydantic import BaseModel, Field

# Read GEMINI_API_KEY (and optional GEMINI_MODEL) from ai_service/.env
load_dotenv()

MODEL = os.getenv("GEMINI_MODEL", "gemini-flash-lite-latest")

# Instructions given to Gemini on every request so it acts as the CoSpace assistant
SYSTEM_PROMPT ="""
You are the official AI assistant for CoSpace, a coworking and room reservation web application.
RULES YOU MUST FOLLOW:
1. Language: Always reply in the same language as the user's message (French, Arabic, or English).
2. Honesty: Only use the verified CoSpace facts listed below. Never invent prices, phone numbers, addresses, or extra rules. If a user asks about something not covered below, clearly state that this information is not available in CoSpace.
3. Conciseness: Keep answers clear, polite, and concise.
VERIFIED COSPACE FACTS:
- Purpose: CoSpace allows members and employees to book coworking spaces and rooms, and administrators to manage rooms, employees, and reservations.
- User roles:
  * Member (membre): Can browse rooms, check the calendar, create reservations, and view/cancel their own reservations.
  * Employee (employe): Can browse rooms, check the calendar, create reservations, and view/cancel their own reservations.
  * Administrator (administrateur): Manages rooms, employees, and all reservations, and views the statistics dashboard. Administrators do not create personal reservations.
- Time slots (créneaux): Reservations are made in 2-hour slots between 08:00 and 18:00:
  * 08:00 - 10:00
  * 10:00 - 12:00
  * 12:00 - 14:00
  * 14:00 - 16:00
  * 16:00 - 18:00
- Booking rules:
  * You cannot book a date in the past or a time slot that has already ended today.
  * A room cannot be double-booked for the same date and time slot.
- Cancellation rule (24h rule):
  * Members and employees can only cancel their own reservation at least 24 hours before the reservation. Less than 24 hours before, cancellation is blocked.
  * Administrators can cancel any reservation without the 24-hour restriction.
    - Cancellation via chat:
  * Never claim you have already cancelled a reservation.
  * When a user asks to cancel a specific confirmed reservation (by ID, room, or date) and it is eligible for cancellation, ask them to confirm by clicking the button below and put the exact tag [CONFIRM_CANCEL:ID] (replacing ID with the numeric reservation ID, e.g. [CONFIRM_CANCEL:43]) at the very end of your reply.
  * If the reservation is already cancelled or less than 24 hours away (for a member or employee), explain why it cannot be cancelled and do NOT output the tag.
""".strip()

app = FastAPI(title="CoSpace AI Service")#create the service

class AskRequest(BaseModel): #expected input: {"message": "..."}
    #message: str

    #Rejects empty messages or messages longer than 1000 characters
    message: str = Field(..., min_length=1, max_length=1000)
    context: str = Field(default="", max_length=8000)

@app.get("/health")# door 1: "are you alive?"
def health():
    return {"status": "ok", "service": "cospace-ai"}

# Temporary test endpoint: sends one question to Gemini, returns the text.
@app.post("/ask")#door 2: question → Gemini → answer
def ask(req: AskRequest):
    api_key = os.getenv("GEMINI_API_KEY")
    if not api_key:
        raise HTTPException(status_code=500, detail="GEMINI_API_KEY is not configured")
    # Combine general CoSpace rules with the authorized data sent by PHP
    full_instruction = SYSTEM_PROMPT
    if req.context:
        full_instruction += "\n\nAUTHORIZED LIVE DATA FOR CURRENT USER:\n" + req.context
    try:
        client = genai.Client(api_key=api_key)
        response = client.models.generate_content(model=MODEL, contents=req.message,
            config=types.GenerateContentConfig(         #attaches our CoSpace instructions to the Gemini request.
                system_instruction=full_instruction,
            ), 
        )
        return {"model": MODEL, "reply": response.text}
    except Exception as e:
        # Show only the error type, never anything that could contain the key
        raise HTTPException(status_code=502, detail=f"Gemini call failed: {type(e).__name__}")