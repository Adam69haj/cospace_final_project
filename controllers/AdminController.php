<?php
// Gère : dashboard administrateur

// Affiche le dashboard admin avec les statistiques
function adminDashboard() {

    // Protection : seul l'admin peut accéder
    requireRole('administrateur');//function in index

    // Récupère les statistiques
    $stats = ReservationModel::getStats();

    // Récupère les 5 dernières réservations
    $reservations = ReservationModel::getAllPourAdmin();
    $dernieres    = array_slice($reservations, 0, 5);
                                       //debut:0   5 éléments
    // Récupère toutes les salles
    $salles = RoomModel::getAll();

    // Récupère tous les employés
    $employes = EmployeeModel::getAll();

    // Récupère le message flash si existe
    $message = getMessage();

    // Données pour les graphiques
    $dataJours = ReservationModel::getByDayOfWeek();
    $dataTypes = ReservationModel::getByTypeRoom();

    // Affiche le dashboard
    require 'views/admin/dashboard.php';
}
