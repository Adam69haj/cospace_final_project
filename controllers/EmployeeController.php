<?php

// Gère : liste, ajout, modification, suppression des employés
// Accessible uniquement par l'administrateur

// Affiche la liste des employés
function employeeList() {

    requireRole('administrateur');

    // Récupère tous les employés
    $employes = EmployeeModel::getAll();

    // Message flash si existe
    $message = getMessage();

    require 'views/admin/employees/list.php';
}


// Affiche le formulaire d'ajout
function employeeCreate() {

    requireRole('administrateur');

    require 'views/admin/employees/form.php';
}


// Traite le formulaire d'ajout
function employeeStore() {

    requireRole('administrateur');

    // Récupère les données du formulaire
    $nom          = $_POST['nom']          ?? '';
    $email        = $_POST['email']        ?? '';
    $login        = $_POST['login']        ?? '';
    $password     = $_POST['password']     ?? '';
    $departement  = $_POST['departement']  ?? '';

    // Validations
    if (empty($nom) || empty($email) || empty($login) || empty($password)) {
        $erreur = "Veuillez remplir tous les champs obligatoires !";
        require 'views/admin/employees/form.php';
        return;
    }

    if (strlen($nom) < 3) {
        $erreur = "Le nom doit avoir au moins 3 caractères !";
        require 'views/admin/employees/form.php';
        return;
    }

    if (empty($email) || strpos($email, '@') === false || strpos($email, '.') === false) {
        $erreur = "Email invalide !";
        require 'views/admin/employees/form.php';
        return;
    }

    if (UserModel::emailExiste($email)) {
        $erreur = "Cet email est déjà utilisé !";
        require 'views/admin/employees/form.php';
        return;
    }

    if (UserModel::loginExiste($login)) {
        $erreur = "Ce login est déjà utilisé !";
        require 'views/admin/employees/form.php';
        return;
    }

    if (strlen($password) < 6) {
        $erreur = "Le mot de passe doit avoir au moins 6 caractères !";
        require 'views/admin/employees/form.php';
        return;
    }

    // Crée l'employé
    EmployeeModel::creerParAdmin($nom, $email, $login, $password, $departement);

    // Succès
    setMessage("Employé '$nom' créé avec succès !");
    redirect('index.php?page=admin-employees');
}


// Affiche le formulaire de modification
function employeeEdit() {

    requireRole('administrateur');

    // Récupère l'ID dans l'URL
    $id = $_GET['id'] ?? '';

    // Cherche l'employé
    $employe = EmployeeModel::findByUserId($id);

    // Introuvable
    if (!$employe) {
        setMessage("Employé introuvable !");
        redirect('index.php?page=admin-employees');
        return;
    }

    // Affiche formulaire pré-rempli
    require 'views/admin/employees/form.php';
}


// Traite le formulaire de modification
function employeeUpdate() {

    requireRole('administrateur');

    $id          = $_POST['id']          ?? '';
    $nom         = $_POST['nom']         ?? '';
    $email       = $_POST['email']       ?? '';
    $departement = $_POST['departement'] ?? '';

    // Validations
    if (empty($nom) || empty($email)) {
        $erreur  = "Veuillez remplir tous les champs !";
        $employe = EmployeeModel::findByUserId($id);
        require 'views/admin/employees/form.php';
        return;
    }

    // Met à jour
    EmployeeModel::update($id, $nom, $email, $departement);

    // Succès
    setMessage("Employé modifié avec succès !");
    redirect('index.php?page=admin-employees');
}


// Supprime un employé
function employeeDelete() {

    requireRole('administrateur');

    $id = $_GET['id'] ?? '';

    // Vérifie si l'employé a des réservations futures
    if (EmployeeModel::reservationFuture($id)) {
        setMessage("Impossible ! Cet employé a des réservations à venir !");
        redirect('index.php?page=admin-employees');
        return;
    }

    // Supprime l'employé
    UserModel::delete($id);

    // Succès
    setMessage("Employé supprimé avec succès !");
    redirect('index.php?page=admin-employees');
}
?>