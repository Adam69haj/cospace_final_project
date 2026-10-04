<?php

// Gère : liste, ajout, modification, suppression des salles
// Accessible uniquement par l'administrateur

// Affiche la liste des salles
function roomList() {

    // Protection : seul l'admin
    requireRole('administrateur');

    // Récupère le mot clé de recherche si existe
    $mot = $_GET['search'] ?? '';

    // Si recherche → filtre les salles
    // Sinon → retourne toutes les salles
    if (!empty($mot)) {
        $salles = RoomModel::cherche($mot);
    } else {
        $salles = RoomModel::getAll();
    }

    // Message flash si existe
    $message = getMessage();

    // Affiche la liste
    require 'views/admin/rooms/list.php';
}


// Affiche le formulaire d'ajout
function roomCreate() {

    requireRole('administrateur');

    // Affiche formulaire vide
    require 'views/admin/rooms/form.php';
}


// Traite le formulaire d'ajout
function roomStore() {

    requireRole('administrateur');

    // Récupère les données du formulaire
    $nom          = $_POST['nom']          ?? '';
    $description  = $_POST['description']  ?? '';
    $capacite     = $_POST['capacite']     ?? '';
    $type_salle   = $_POST['type_salle']   ?? '';
    $equipements  = $_POST['equipements']  ?? '';

    // Validations
    if (empty($nom)) {
        $erreur = "Le nom est obligatoire !";
        require 'views/admin/rooms/form.php';
        return;
    }

    if (strlen($nom) < 3) {
        $erreur = "Le nom doit avoir au moins 3 caractères !";
        require 'views/admin/rooms/form.php';
        return;
    }

    if (empty($capacite) || $capacite < 1 || $capacite > 500) {
        $erreur = "La capacité doit être entre 1 et 500 !";
        require 'views/admin/rooms/form.php';
        return;
    }

    // Crée la salle
    // create() retourne false si nom déjà pris
    $result = RoomModel::create($nom, $description, $capacite, $type_salle, $equipements);

    if (!$result) {
        $erreur = "Ce nom de salle existe déjà !";
        require 'views/admin/rooms/form.php';
        return;
    }

    // Succès
    setMessage("Salle '$nom' créée avec succès !");
    redirect('index.php?page=admin-rooms');
}


// Affiche le formulaire de modification
function roomEdit() {

    requireRole('administrateur');

    // Récupère l'ID de la salle dans l'URL
    $id = $_GET['id'] ?? '';

    // Cherche la salle
    $salle = RoomModel::findById($id);

    // Salle introuvable
    if (!$salle) {
        setMessage("Salle introuvable !");
        redirect('index.php?page=admin-rooms');
        return;
    }

    // Affiche le formulaire pré-rempli
    require 'views/admin/rooms/form.php';
}


// Traite le formulaire de modification
function roomUpdate() {

    requireRole('administrateur');

    // Récupère les données
    $id          = $_POST['id']          ?? '';
    $nom         = $_POST['nom']         ?? '';
    $description = $_POST['description'] ?? '';
    $capacite    = $_POST['capacite']    ?? '';
    $type_salle  = $_POST['type_salle']  ?? '';
    $equipements = $_POST['equipements'] ?? '';

    // Validations
    if (empty($nom)) {
        $erreur = "Le nom est obligatoire !";
        $salle  = RoomModel::findById($id);/* On a besoin de $salle pour réafficher le formulaire pré-rempli */
        require 'views/admin/rooms/form.php';
        return;
    }

    if (empty($capacite) || $capacite < 1 || $capacite > 500) {
        $erreur = "La capacité doit être entre 1 et 500 !";
        $salle  = RoomModel::findById($id);
        require 'views/admin/rooms/form.php';
        return;
    }

    // Met à jour la salle
    $result = RoomModel::update($id, $nom, $description, $capacite, $type_salle, $equipements);

    if (!$result) {
        $erreur = "Ce nom de salle existe déjà !";
        $salle  = RoomModel::findById($id);
        require 'views/admin/rooms/form.php';
        return;
    }

    // Succès
    setMessage("Salle modifiée avec succès !");
    redirect('index.php?page=admin-rooms');
}


// Supprime une salle
function roomDelete() {

    requireRole('administrateur');

    // Récupère l'ID dans l'URL
    $id = $_GET['id'] ?? '';
//index.php?page=room-edit&id=3
//                              ↑
//                         $_GET['id'] = 3
if (RoomModel::hasFutureReservations($id)) {
        setMessage("Impossible ! Cette salle a des réservations à venir !");
        redirect('index.php?page=admin-rooms');
        return;
    }
    // Supprime la salle
    RoomModel::delete($id);

    // Succès
    setMessage("Salle supprimée avec succès !");
    redirect('index.php?page=admin-rooms');
}
?>