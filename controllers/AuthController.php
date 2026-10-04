<?php

// Gère : login, logout, inscription

// Affiche la page login
function showLogin() {

    // Si déjà connecté → redirige vers son dashboard
    if (isConnecte()) {
        redirectByRole();
        return;
    }

    $message = getMessage();
    require 'views/auth/login.php';
}


// Traite le formulaire de login
function doLogin() {

    // Récupère les données du formulaire
    $identifier = $_POST['identifier'] ?? '';
    $password   = $_POST['password']   ?? '';
    $role       = $_POST['role']       ?? '';

    // Vérifie que tous les champs sont remplis
    if (empty($identifier) || empty($password) || empty($role)) {
        $erreur = "Veuillez remplir tous les champs !";
        require 'views/auth/login.php';
        return;
    }

    // Cherche l'utilisateur dans la BDD
    $user = UserModel::findByLogin($identifier);

    // Vérifie :
    // 1. L'utilisateur existe
    // 2. Le mot de passe est correct
    // 3. Le rôle correspond
    if (
        $user === null ||
        !password_verify($password, $user->getMotDePasse()/* getter */) ||
        $user->getRole() !== $role
    ) {
        $erreur = "Identifiants incorrects ou rôle invalide !";
        require 'views/auth/login.php';
        return;
    }

    // Connexion réussie → sauvegarde en session
    setUser([
        'id'    => $user->getId(),
        'nom'   => $user->getNom(),
        'role'  => $user->getRole(),
        'login' => $user->getLogin(),
        'photo' => $user->getPhoto()
    ]);// setUser fel index pour la session

    // Message de succès
    setMessage("Bienvenue " . $user->getNom() . " !");

    // Redirige vers le bon dashboard
    redirectByRole();
}


// Affiche la page inscription (membres uniquement)
function showRegister() {

    if (isConnecte()) {
        redirectByRole();
        return;
    }

    require 'views/auth/register.php';
}


// Traite le formulaire d'inscription
function doRegister() {

    $nom      = $_POST['nom']              ?? '';
    $email    = $_POST['email']            ?? '';
    $password = $_POST['password']         ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    // Validations
    if (empty($nom) || empty($email) || empty($password)) {
        $erreur = "Veuillez remplir tous les champs !";
        require 'views/auth/register.php';
        return;
    }

    if (strlen($nom) < 3) {
        $erreur = "Le nom doit avoir au moins 3 caractères !";
        require 'views/auth/register.php';
        return;
    }

    if (empty($email) || strpos($email, '@') === false || strpos($email, '.') === false) {
    $erreur = "Email invalide !";
    require 'views/auth/register.php';
    return;
}

    if (UserModel::emailExiste($email)) {
        $erreur = "Cet email est déjà utilisé !";
        require 'views/auth/register.php';
        return;
    }

    if (strlen($password) < 6) {
        $erreur = "Le mot de passe doit avoir au moins 6 caractères !";
        require 'views/auth/register.php';
        return;
    }

    if ($password !== $confirm) {
        $erreur = "Les mots de passe ne correspondent pas !";
        require 'views/auth/register.php';
        return;
    }

    // Tout est bon → crée le compte
    UserModel::creerMembre($nom, $email, $password);

    // Message succès + redirection
    setMessage("Compte créé avec succès ! Connectez-vous.");
    redirect('index.php?page=login');
}





// Page accès refusé
function showAccessDenied() {
    require 'views/auth/access-denied.php';
}

// Affiche la page feedback avant déconnexion
function showLogoutFeedback() {
    requireConnexion();
    // Membre → affiche le feedback
    if (getUserRole() === 'membre') {
        require 'views/auth/feedback.php';
        return;
    }
    // Admin et Employé → déconnexion directe
    deconnecter();
    redirect('index.php?page=login');
}

// Traite le feedback et déconnecte
function submitFeedback() {
    requireConnexion();

    $note    = $_POST['note']    ?? 0;
    $message = $_POST['message'] ?? '';

    // Sauvegarde le feedback si note donnée
    if ($note >= 1 && $note <= 5) {
        $pdo  = getDB();
        $stmt = $pdo->prepare(
            "INSERT INTO feedbacks
             (user_id, user_nom, user_role, note, message)
             VALUES (:uid, :nom, :role, :note, :msg)"
        );
        $stmt->execute([
            'uid'  => getUserId(),
            'nom'  => getUserNom(),
            'role' => getUserRole(),
            'note' => $note,
            'msg'  => $message
        ]);
    }

    // Déconnecte après feedback
    deconnecter();
    redirect('index.php?page=login');
}

// Déconnexion sans feedback (bouton ignorer)
function doLogout() {
    deconnecter();
    redirect('index.php?page=login');
}
// Redirige vers le bon dashboard selon le rôle
function redirectByRole() {
    switch (getUserRole()) {/* role from session*/
        case 'administrateur':
            redirect('index.php?page=admin-dashboard');
            break;
        case 'employe':
            redirect('index.php?page=my-reservations');
            break;
        case 'membre':
            redirect('index.php?page=member-dashboard');
            break;
        default:
            redirect('index.php?page=login');
    }
}
?>