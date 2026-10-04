<?php
// Gère le profil utilisateur

// Affiche la page profil
function showProfile() {
    requireConnexion();

    // Récupère les infos de l'utilisateur connecté
    $user  = UserModel::findById(getUserId());
    $message = getMessage();

    require 'views/profile/profile.php';
}


// Met à jour le profil
function updateProfile() {
    requireConnexion();

    $action = $_POST['action'] ?? '';

    // ============================================
    // ACTION 1 — Modifier nom et email
    // ============================================
    if ($action === 'infos') {

        $nom   = $_POST['nom']   ?? '';
        $email = $_POST['email'] ?? '';

        // Validations
        if (empty($nom) || empty($email)) {
            $erreur = "Veuillez remplir tous les champs !";
            $user   = UserModel::findById(getUserId());
            require 'views/profile/profile.php';
            return;
        }

        if (strlen($nom) < 3) {
            $erreur = "Le nom doit avoir au moins 3 caractères !";
            $user   = UserModel::findById(getUserId());
            require 'views/profile/profile.php';
            return;
        }

        if (strpos($email, '@') === false || strpos($email, '.') === false) {
            $erreur = "Email invalide !";
            $user   = UserModel::findById(getUserId());
            require 'views/profile/profile.php';
            return;
        }

        // Met à jour dans la BDD
        UserModel::updateProfile(getUserId(), $nom, $email);

        // Met à jour la session
        $_SESSION['user_nom'] = $nom;

        setMessage("Profil mis à jour avec succès !");
        redirect('index.php?page=profile');
    }

    // ============================================
    // ACTION 2 — Changer le mot de passe
    // ============================================
    if ($action === 'password') {

        $actuel   = $_POST['password_actuel']    ?? '';
        $nouveau  = $_POST['password_nouveau']   ?? '';
        $confirm  = $_POST['password_confirm']   ?? '';

        $user = UserModel::findById(getUserId());

        // Vérifie l'ancien mot de passe
        if (!password_verify($actuel, $user->getMotDePasse())) {
            $erreur = "Mot de passe actuel incorrect !";
            require 'views/profile/profile.php';
            return;
        }

        if (strlen($nouveau) < 6) {
            $erreur = "Le nouveau mot de passe doit avoir au moins 6 caractères !";
            require 'views/profile/profile.php';
            return;
        }

        if ($nouveau !== $confirm) {
            $erreur = "Les mots de passe ne correspondent pas !";
            require 'views/profile/profile.php';
            return;
        }

        // Met à jour le mot de passe
        UserModel::updatePassword(getUserId(), $nouveau);

        setMessage("Mot de passe changé avec succès !");
        redirect('index.php?page=profile');
    }

    // ============================================
    // ACTION 3 — Upload photo de profil
    // ============================================
    if ($action === 'photo') {

        // Vérifie qu'un fichier a été envoyé
        if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== 0) {
            $erreur = "Veuillez choisir une image !";
            $user   = UserModel::findById(getUserId());
            require 'views/profile/profile.php';
            return;
        }

        $fichier = $_FILES['photo'];

        // Vérifie le type — seulement images
        $typesAutorises = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($fichier['type'], $typesAutorises)) {
            $erreur = "Format invalide ! Utilisez JPG, PNG ou GIF.";
            $user   = UserModel::findById(getUserId());
            require 'views/profile/profile.php';
            return;
        }

        // Vérifie la taille — max 2MB
        if ($fichier['size'] > 2 * 1024 * 1024) {
            $erreur = "Image trop grande ! Maximum 2MB.";
            $user   = UserModel::findById(getUserId());
            require 'views/profile/profile.php';
            return;
        }

        // Génère un nom unique pour le fichier
        $extension  = pathinfo($fichier['name'], PATHINFO_EXTENSION);
        $nomFichier = 'user_' . getUserId() . '_' . time() . '.' . $extension;
        $destination = 'public/uploads/profiles/' . $nomFichier;

        // Déplace le fichier
        if (move_uploaded_file($fichier['tmp_name'], $destination)) {
            // Sauvegarde le nom dans la BDD
            UserModel::updatePhoto(getUserId(), $nomFichier);
            $_SESSION['user_photo'] = $nomFichier;
            setMessage("Photo de profil mise à jour !");
        } else {
            $erreur = "Erreur lors de l'upload !";
            $user   = UserModel::findById(getUserId());
            require 'views/profile/profile.php';
            return;
        }

        redirect('index.php?page=profile');
    }
}