<?php
// Gère : espace membre ET employé
// Les deux ont les mêmes pages

// Dashboard membre — page d'accueil
function memberDashboard() {

    requireRole(['membre', 'employe']);

    // Récupère les prochaines réservations
    // de l'utilisateur connecté
    $reservations = ReservationModel::getByUser(getUserId());

    // Filtre seulement les réservations futures
    $prochaines = [];
    foreach ($reservations as $resa) {
        if (
            $resa['date_reservation'] >= date('Y-m-d') &&
            $resa['statut'] == 'confirmee'
        ) {
            $prochaines[] = $resa;
        }
    }

    // Message flash si existe
    $message = getMessage();

    require 'views/member/dashboard.php';
}


// Liste des salles disponibles
function memberRooms() {

    requireRole(['membre', 'employe']);

    // Récupère le mot clé de recherche si existe
    $mot = $_GET['search'] ?? '';

    // Si recherche → filtre les salles
    // Sinon → toutes les salles
    if (!empty($mot)) {
        $salles = RoomModel::cherche($mot);
    } else {
        $salles = RoomModel::getAll();
    }

    require 'views/member/rooms.php';
}
?>