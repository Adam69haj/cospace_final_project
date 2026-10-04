<?php
// ============================================
// CALENDARCONTROLLER.PHP
// Calendrier des réservations
// ============================================

function showCalendar() {
    requireConnexion();

    // Admin → toutes les réservations
    // Autres → seulement les siennes
    if (getUserRole() === 'administrateur') {
        $reservations = ReservationModel::getAllPourAdmin();
    } else {
        $reservations = ReservationModel::getByUser(getUserId());
    }

    // Prépare les données pour le calendrier
    $events = [];
    foreach ($reservations as $resa) {
        $events[] = [
            'id'       => $resa['id'],
            'title'    => $resa['salle_nom'],
            'date'     => $resa['date_reservation'],
            'creneau'  => $resa['creneau_libelle'],
            'statut'   => $resa['statut'],
            'user'     => $resa['user_nom'] ?? getUserNom(),
        ];
    }

    $message = getMessage();
    require 'views/calendar/calendar.php';
}