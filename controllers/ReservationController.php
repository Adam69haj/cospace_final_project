<?php

// Gère : liste, création, annulation des réservations

// Toutes les réservations — admin uniquement
function reservationListAll() {

    requireRole('administrateur');

    // Récupère toutes les réservations
    $reservations = ReservationModel::getAllPourAdmin();

    // Message flash si existe
    $message = getMessage();

    require 'views/admin/reservations/list.php';
}


// Mes réservations — membre et employé
function reservationMyList() {

    requireRole(['administrateur', 'employe', 'membre']);

    // Récupère les réservations de l'utilisateur connecté
    $reservations = ReservationModel::getByUser(getUserId());//id from session

    // Message flash si existe
    $message = getMessage();

    require 'views/member/my-reservations.php';
}


// Affiche le formulaire de réservation
function reservationCreate() {

    requireRole(['employe', 'membre']);

    // Récupère toutes les salles
    $salles = RoomModel::getAll();

    // Créneaux vides au départ
    $creneaux = [];

    // Si l'utilisateur a choisi une salle et une date
    // On charge les créneaux disponibles
    $salle_id = $_POST['salle_id']         ?? '';
    $date     = $_POST['date_reservation'] ?? '';

    if (!empty($salle_id) && !empty($date)) {
        $creneaux = CreneauxModel::getAvailable($salle_id, $date);
    }

    require 'views/member/book.php';
}


// Traite le formulaire de réservation
function reservationStore() {

    requireRole(['employe', 'membre']);

    $salle_id   = $_POST['salle_id']         ?? '';
    $creneau_id = $_POST['creneau_id']       ?? '';
    $date       = $_POST['date_reservation'] ?? '';

    // Validations
    if (empty($salle_id) || empty($creneau_id) || empty($date)) {
        $erreur   = "Veuillez remplir tous les champs !";
        $salles   = RoomModel::getAll();
        $creneaux = CreneauxModel::getAvailable($salle_id, $date);
        require 'views/member/book.php';
        return;
    }

    if ($date < date('Y-m-d')) {
        $erreur   = "La date ne peut pas être dans le passé !";
        $salles   = RoomModel::getAll();
        $creneaux = CreneauxModel::getAvailable($salle_id, $date);
        require 'views/member/book.php';
        return;
    }

    // ← NOUVEAU : vérifie l'heure si c'est aujourd'hui
    if ($date === date('Y-m-d')) {
        $pdo  = getDB();
        $stmt = $pdo->prepare(
            "SELECT heure_fin FROM creneaux WHERE id = :id"
        );
        $stmt->execute(['id' => $creneau_id]);
        $creneau = $stmt->fetch();

        if ($creneau && $creneau['heure_fin'] <= date('H:i:s')) {
            $erreur   = "Ce créneau est déjà passé !";
            $salles   = RoomModel::getAll();
            $creneaux = CreneauxModel::getAvailable($salle_id, $date);
            require 'views/member/book.php';
            return;
        }
    }
    
    // Crée la réservation
    $result = ReservationModel::create(
        $salle_id,
        getUserId(),//session
        $creneau_id,
        $date
    );

    if (!$result) {
        $erreur   = "Ce créneau est déjà réservé !";
        $salles   = RoomModel::getAll();
        $creneaux = CreneauxModel::getAvailable($salle_id, $date);
        require 'views/member/book.php';
        return;
    }

    // Succès
    setMessage("Réservation créée avec succès !");
    redirect('index.php?page=my-reservations');
}
// Annule une réservation
function reservationCancel() {
    requireRole(['administrateur', 'employe', 'membre']);

    $id = $_GET['id'] ?? '';

    // ============================================
    // ADMIN → annule sans restriction
    // ============================================
    if (getUserRole() === 'administrateur') {
        ReservationModel::cancel($id);
        setMessage("Réservation annulée !");
        redirect('index.php?page=admin-reservations');
        return;
    }

    // ============================================
    // MEMBRE / EMPLOYÉ → vérifie la règle 24h
    // ============================================

    // Cherche la réservation dans la BDD (avec l'heure de début du créneau)
    $pdo  = getDB();
    $stmt = $pdo->prepare(
        "SELECT r.date_reservation, c.heure_debut
         FROM reservations r
         JOIN creneaux c ON c.id = r.creneau_id
         WHERE r.id = :id
         AND r.utilisateur_id = :uid"
    );
    $stmt->execute([
        'id'  => $id,
        'uid' => getUserId()
    ]);
    $resa = $stmt->fetch();

    // Réservation introuvable
    if (!$resa) {
        setMessage("Réservation introuvable !");
        redirect('index.php?page=my-reservations');
        return;
    }

    // Calcule les secondes restantes avant le début réel du créneau
    $dateResa   = new DateTime($resa['date_reservation'] . ' 00:00:00');
    $maintenant = new DateTime();
    $diffHeures = ($dateResa->getTimestamp() - $maintenant->getTimestamp()) / 3600;

    if ($diffHeures < 24) {
        $erreur       = "Impossible d'annuler : moins de 24h avant la réservation !";
        $reservations = ReservationModel::getByUser(getUserId());
        $message        = null;
        require 'views/member/my-reservations.php';
        return;
    }
    // Plus de 24h → annulation possible
    ReservationModel::cancel($id);
    setMessage("Réservation annulée avec succès !");
    redirect('index.php?page=my-reservations');
}
?>
