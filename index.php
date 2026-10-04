<?php



session_start();


require_once 'connexion/database.php';



function getDB() {
    $dsn = "mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=".DB_CHARSET;
    try{
        $pdo = new PDO($dsn, DB_USER, DB_PASS);
    }catch(PDOException $e){
        die('Erreur : ' . $e->getMessage());
    }
    return $pdo;
}
// Sauvegarde l'utilisateur après connexion
function setUser($user) {
    $_SESSION['user_id']    = $user['id'];
    $_SESSION['user_nom']   = $user['nom'];
    $_SESSION['user_role']  = $user['role'];
    $_SESSION['user_login'] = $user['login'];
    $_SESSION['user_photo'] = $user['photo'] ?? null;
}
// Retourne l'ID de l'utilisateur connecté
function getUserId() {
    return $_SESSION['user_id'] ?? null;
}

// Retourne le nom de l'utilisateur connecté
function getUserNom() {
    return $_SESSION['user_nom'] ?? null;
}

// Retourne le rôle : administrateur / employe / membre
function getUserRole() {
    return $_SESSION['user_role'] ?? null;
}
// Vérifie si quelqu'un est connecté
function isConnecte() {
    return isset($_SESSION['user_id']);
}
//Retourne true ou false — est-ce que quelqu'un est connecté ?
function getUserPhoto() {
    return $_SESSION['user_photo'] ?? null;
}
// Déconnexion
function deconnecter() {
    session_unset();    // vide toutes les variables $_SESSION
    session_destroy();  // détruit complètement la session
}




// 5. FLASH MESSAGE
// Utilisé SEULEMENT pour les messages de succès
// Exemple : "Salle créée !", "Employé supprimé !"
function setMessage($message) {
    $_SESSION['flash'] =$message;
}

// Retourne le message et le supprime

function getMessage() {
    if (isset($_SESSION['flash'])) {
        $message = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $message;
    }
    return null;
}
//Lit le message ET le supprime en même temps.

// 6. PROTECTION DES PAGES
// Vérifie que l'utilisateur est connecté
// Sinon → redirige vers login
function requireConnexion() {
    if (!isConnecte()) {
        header('Location: index.php?page=login');
        exit();
    }
}


// Vérifie que l'utilisateur a le bon rôle

function requireRole($roles) {
    requireConnexion();
    // Si c'est un seul rôle → le mettre dans un tableau
    if (is_string($roles)) {
        $roles = [$roles];
    }
    //"Le rôle de l'utilisateur est-il dans la liste des rôles autorisés ?"
    if (!in_array(getUserRole(), $roles)) {
        header('Location: index.php?page=access-denied');
        exit();
    }
}

// Redirige vers une autre page
function redirect($url) {
    header('Location: ' . $url);
    exit();
}


// Models (requêtes SQL)
require_once 'models/UserModel.php';
require_once 'models/EmployeeModel.php';
require_once 'models/RoomModel.php';
require_once 'models/CreneauxModel.php';
require_once 'models/ReservationModel.php';

// Controllers (logique)
require_once 'controllers/AuthController.php';
require_once 'controllers/AdminController.php';
require_once 'controllers/RoomController.php';
require_once 'controllers/EmployeeController.php';
require_once 'controllers/ReservationController.php';
require_once 'controllers/MemberController.php';

require_once 'controllers/ProfileController.php';

require_once 'controllers/CalendarController.php';
// ============================================
// 9. ROUTEUR
// Lit ?page= dans l'URL
// Appelle la bonne fonction
// ============================================
$page = isset($_GET['page']) ? $_GET['page'] : 'login';

switch ($page) {

    // ---- AUTHENTIFICATION ----
    case 'login':         showLogin();       break;// pour n'esxecute pas le rest
    case 'do-login':      doLogin();         break;
    case 'register':      showRegister();    break;
    case 'do-register':   doRegister();      break;
    case 'logout':          showLogoutFeedback(); break;
    case 'do-logout':       doLogout();           break;
    case 'submit-feedback': submitFeedback();     break;
    case 'access-denied': showAccessDenied(); break;

    // ---- ADMIN ----
    case 'admin-dashboard': adminDashboard(); break;

    // ---- SALLES ----
    case 'admin-rooms':  roomList();    break;
    case 'room-create':  roomCreate();  break;
    case 'room-store':   roomStore();   break;
    case 'room-edit':    roomEdit();    break;
    case 'room-update':  roomUpdate();  break;
    case 'room-delete':  roomDelete();  break;

    // ---- EMPLOYÉS ----
    case 'admin-employees':  employeeList();    break;
    case 'employee-create':  employeeCreate();  break;
    case 'employee-store':   employeeStore();   break;
    case 'employee-edit':    employeeEdit();    break;
    case 'employee-update':  employeeUpdate();  break;
    case 'employee-delete':  employeeDelete();  break;

    // ---- RÉSERVATIONS ----
    case 'admin-reservations': reservationListAll();  break;
    case 'my-reservations':    reservationMyList();   break;
    case 'reservation-create': reservationCreate();   break;
    case 'reservation-store':  reservationStore();    break;
    case 'reservation-cancel': reservationCancel();   break;

    // ---- MEMBRE ET EMPLOYÉ ----
    case 'member-dashboard':  memberDashboard();  break;
    case 'member-rooms':      memberRooms();      break;

    
    case 'profile':        showProfile();   break;
    case 'profile-update': updateProfile(); break;

    case 'calendar': showCalendar(); break;

    


    // Page inconnue → login
    default:
        redirect('index.php?page=login');
}
?>