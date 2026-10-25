<?php
// Construit le contexte autorisé selon l'utilisateur connecté en session
function buildAssistantContext() {
    $userId   = getUserId();
    $userNom  = getUserNom();
    $userRole = getUserRole();
    $today    = date('Y-m-d');

    $lines   = [];
    $lines[] = "Current date: {$today}";
    $lines[] = "Authenticated user: {$userNom} (Role: {$userRole})";

     // 1. Liste des salles CoSpace (accessible à tous les utilisateurs connectés)
    $salles = RoomModel::getAll();
    $lines[] = "\nAvailable rooms in CoSpace:";
    foreach ($salles as $s) {
        $lines[] = "- {$s['nom']} | Type: {$s['type_salle']} | Capacity: {$s['capacite']} | Equipment: {$s['equipements']}";
    }

     // 2. Données selon le rôle (strictement filtrées par PHP)
    if ($userRole === 'membre' || $userRole === 'employe') {
        $resas = ReservationModel::getByUser($userId);
        $lines[] = "\nThis user's own reservations:";
        if (empty($resas)) {
            $lines[] = "- None.";
        } else {
            foreach ($resas as $r) {
                $lines[] = "- ID #{$r['id']} | Date: {$r['date_reservation']} | Slot: {$r['creneau_libelle']} | Room: {$r['salle_nom']} | Status: {$r['statut']}";
            }
        }
    }elseif ($userRole === 'administrateur') {
        $stats = ReservationModel::getStats();
        $lines[] = "\nCoSpace global statistics (admin only):";
        $lines[] = "- Total confirmed reservations: {$stats['total']}";
        $lines[] = "- Confirmed reservations today: {$stats['today']}";
        $lines[] = "- Total rooms: {$stats['salles']}";
        $lines[] = "- Total employees: {$stats['employes']}";
    }
    // 3. Historique temporaire de la session en cours (max 6 derniers échanges)
    if (!empty($_SESSION['assistant_history'])) {
        $lines[] = "\nRecent conversation in this session:";
        foreach ($_SESSION['assistant_history'] as $turn) {
            $lines[] = "User: " . $turn['user'];
            $lines[] = "Assistant: " . $turn['bot'];
        }
    }
    return implode("\n", $lines);
}

// Gère la communication entre PHP et le service FastAPI (IA)
function assistantChat() {
    // 1. Seuls les utilisateurs connectés peuvent utiliser l'assistant
    header('Content-Type: application/json; charset=utf-8');//Elle indique au navigateur ou au client que la réponse contient du JSON encodé en UTF-8. Elle doit être appelée avant toute sortie (aucun echo, HTML ou espace avant <?php), sinon tu auras l’erreur « headers already sent ».
    if (!isConnecte()) {
        http_response_code(401);
        echo json_encode(['error' => 'Non autorisé. Veuillez vous connecter.']);
        return;
    }

    // 2. Accepte uniquement les requêtes POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'Méthode non autorisée.']);
        return;
    }

    // 3. Lit le message JSON envoyé par le navigateur
    $input   = json_decode(file_get_contents('php://input'), true);
    $message = trim($input['message'] ?? '');

    if ($message === '') {
        http_response_code(400);
        echo json_encode(['error' => 'Le message ne peut pas être vide.']);
        return;
    }

    // 4. Prépare le contexte autorisé depuis la session et la BDD
    $context = buildAssistantContext();

    // 5. Envoie le message + contexte à FastAPI (127.0.0.1:8001/ask) avec cURL
    $ch = curl_init('http://127.0.0.1:8001/ask');//creates a new cURL session
    curl_setopt_array($ch, [ //Sets several options on the handle at once.
        CURLOPT_POST           => true, //Makes the request an HTTP POST instead of the default GET. POST is needed because you are sending a body.
        CURLOPT_RETURNTRANSFER => true, //Tells cURL to return the response as a string from curl_exec() instead of printing it directly. Without this, FastAPI’s answer would be output straight to the client, and you could not read it or process it first.
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],//Adds a request header telling FastAPI that the body is JSON. FastAPI uses it to parse/analyze the body correctly.
        CURLOPT_POSTFIELDS     => json_encode(['message' => $message,'context' => $context,]),//turns it into the JSON text
        CURLOPT_TIMEOUT        => 20,//Maximum time in seconds for the whole request. If FastAPI takes longer than 60 seconds, cURL aborts and curl_exec() returns false. This stops your PHP script from hanging forever.
        CURLOPT_PROXY          => '',   // ne jamais passer par un proxy (appel local)
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    // 6. Si FastAPI est éteint ou renvoie une erreur
    if ($response === false || $httpCode !== 200) {
        http_response_code(502);
        echo json_encode([
            'error'  => 'Le service IA est temporairement indisponible.',
            ]);
        return;
    }

    // Sauvegarde temporairement l'échange en session (sans base de données)
    $decoded = json_decode($response, true);
    if (!empty($decoded['reply'])) {
        $_SESSION['assistant_history'][] = [
            'user' => $message,
            'bot'  => $decoded['reply'],
        ];
        // Garde uniquement les 6 derniers échanges
        $_SESSION['assistant_history'] = array_slice($_SESSION['assistant_history'], -6);
    }

    // 7. Renvoie la réponse de FastAPI au navigateur
    echo $response;//Reaching this line means FastAPI answered with status 200. success.
}

// Exécute l'annulation après confirmation explicite de l'utilisateur
function assistantCancel() {
    header('Content-Type: application/json; charset=utf-8');
    if (!isConnecte()) {
        http_response_code(401);
        echo json_encode(['error' => 'Non autorisé.']);
        return;
    }
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'Méthode non autorisée.']);
        return;
    }
    $input  = json_decode(file_get_contents('php://input'), true);
    $resaId = (int)($input['reservation_id'] ?? 0);
    $reason = trim($input['reason'] ?? '');
    $role   = getUserRole();
    $userId = getUserId();
    if ($resaId <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'ID de réservation invalide.']);
        return;
    }
    // 1. Récupère la réservation et l'heure réelle de début du créneau
    $pdo  = getDB();
    $stmt = $pdo->prepare(
        "SELECT r.*, c.heure_debut, s.nom AS salle_nom
         FROM reservations r
         JOIN creneaux c ON c.id = r.creneau_id
         JOIN salles   s ON s.id = r.salle_id
         WHERE r.id = :id"
    );
    $stmt->execute(['id' => $resaId]);
    $resa = $stmt->fetch();
    if (!$resa) {
        http_response_code(404);
        echo json_encode(['error' => 'Réservation introuvable.']);
        return;
    }
    // 2. Vérifie que la réservation est bien confirmée
    if ($resa['statut'] !== 'confirmee') {
        http_response_code(400);
        echo json_encode(['error' => 'Cette réservation est déjà annulée.']);
        return;
    }
    // 3. Règles selon le rôle
    if ($role === 'administrateur') {
        if ($reason === '') {
            http_response_code(400);
            echo json_encode(['error' => 'En tant qu\'administrateur, un motif d\'annulation est obligatoire.']);
            return;
        }
        ReservationModel::cancel($resaId);
        // Journal d'audit administrateur (fichier ignoré par git : *.log)
        $logLine = sprintf(
            "[%s] ADMIN_CANCEL admin_id=%d admin_nom=\"%s\" resa_id=%d owner_id=%d reason=\"%s\"\n",
            date('Y-m-d H:i:s'),
            $userId,
            getUserNom(),
            $resaId,
            $resa['utilisateur_id'],
            str_replace(["\r", "\n"], ' ', $reason)
        );
        file_put_contents('admin_cancellations.log', $logLine, FILE_APPEND);
        echo json_encode([
            'success' => true,
            'message' => "La réservation #{$resaId} ({$resa['salle_nom']}) a été annulée (motif enregistré).",
        ]);
        return;
    }
    // Membre ou employé : doit être propriétaire + règle des 24h avant heure_debut
    if ((int)$resa['utilisateur_id'] !== (int)$userId) {
        http_response_code(403);
        echo json_encode(['error' => 'Vous ne pouvez annuler que vos propres réservations.']);
        return;
    }
    $dateResa   = new DateTime($resa['date_reservation'] . ' ' . $resa['heure_debut']);
    $maintenant = new DateTime();
    $diffHeures = ($dateResa->getTimestamp() - $maintenant->getTimestamp()) / 3600;
    if ($diffHeures < 24) {
        http_response_code(400);
        echo json_encode(['error' => 'Impossible d\'annuler : moins de 24h avant le début du créneau.']);
        return;
    }
    ReservationModel::cancel($resaId);
    $userObj = UserModel::findById($userId);
    if ($userObj) {
        sendCoSpaceEmail(
            $userObj->getEmail(),
            "Annulation de réservation — CoSpace",
            "<p>Bonjour <strong>" . htmlspecialchars($userObj->getNom()) . "</strong>,</p>
            <p>Votre réservation <strong>#{$resaId}</strong> (" . htmlspecialchars($resa['salle_nom']) . " le " . htmlspecialchars($resa['date_reservation']) . ") a bien été annulée via l'assistant CoSpace.</p>"
        );
    }
    echo json_encode([
        'success' => true,
        'message' => "Votre réservation #{$resaId} ({$resa['salle_nom']} le {$resa['date_reservation']}) a bien été annulée.",
    ]);
}