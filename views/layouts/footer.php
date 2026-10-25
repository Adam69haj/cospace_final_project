</main>
        <!-- FIN ZONE CONTENU -->

    </div>
    <!-- FIN MAIN -->

</div>
<!-- FIN APP -->

<!-- ===== WIDGET ASSISTANT IA COSPACE ===== -->
<button class="cs-chat-fab" id="csChatFab" onclick="toggleCsChat()" title="Assistant CoSpace">
    💬
</button>
<div class="cs-chat-panel" id="csChatPanel" aria-hidden="true">
    <div class="cs-chat-header">
        <div>
            <strong>🤖 Assistant CoSpace</strong>
            <div class="cs-chat-sub">FR · العربية · EN</div>
        </div>
        <button class="cs-chat-close" onclick="toggleCsChat()" title="Fermer">✕</button>
    </div>
    <div class="cs-chat-messages" id="csChatMessages">
        <div class="cs-msg cs-msg-bot" dir="auto">
            Bonjour <?= htmlspecialchars(getUserNom() ?? '') ?> ! 👋
Je suis l'assistant CoSpace. Posez-moi vos questions sur vos réservations, les salles ou les règles (en français, بالعربية, ou in English).
        </div>
    </div>
    <form class="cs-chat-form" id="csChatForm" onsubmit="sendCsChatMessage(event)">
        <input
            type="text"
            id="csChatInput"
            class="cs-chat-input"
            placeholder="Posez votre question..."
            dir="auto"
            maxlength="1000"
            autocomplete="off"
        >
        <button type="submit" class="cs-chat-send" id="csChatSend">➤</button>
    </form>
</div>

<script src="public/js/app.js?v=2"></script>

</body>
</html>