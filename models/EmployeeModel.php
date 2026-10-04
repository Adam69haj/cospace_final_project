<?php


class EmployeeModel {

    
    private $id;
    private $utilisateur_id;
    private $departement;

    public function __construct($id, $utilisateur_id, $departement) {
        $this->id             = $id;
        $this->utilisateur_id = $utilisateur_id;
        $this->departement    = $departement;
    }

    public function getId()            { return $this->id; }
    public function getUtilisateurId() { return $this->utilisateur_id; }
    public function getDepartement()   { return $this->departement; }


    // Retourne tous les employés
    // avec leurs infos de la table utilisateurs
    public static function getAll() {
        $pdo  = getDB();
        $stmt = $pdo->prepare(
            "SELECT u.*, e.departement, e.id AS employe_id
             FROM utilisateurs u
             JOIN employes e ON e.utilisateur_id = u.id
             WHERE u.role = 'employe' "
        );
        $stmt->execute();
        return $stmt->fetchAll();//→ retourne TOUTES les lignes //// fetch() → retourne UNE SEULE ligne
    }

    // Cherche un employé par son utilisateur_id
    public static function findByUserId($userId) {
        $pdo  = getDB();
        $stmt = $pdo->prepare(
            "SELECT u.*, e.departement, e.id AS employe_id
             FROM utilisateurs u
             JOIN employes e ON e.utilisateur_id = u.id
             WHERE u.id = :id"
        );
        $stmt->execute(['id' => $userId]);
        return $stmt->fetch();//→ retourne UNE SEULE ligne
    }

    // Crée un employé — fait par l'admin
    public static function creerParAdmin($nom, $email, $login, $password, $departement) {
        $pdo  = getDB();
        $hash = password_hash($password, PASSWORD_DEFAULT);

        // Étape 1 : crée le compte dans utilisateurs
        $stmt = $pdo->prepare(
            "INSERT INTO utilisateurs
             (nom, email, login, mot_de_passe, role)
             VALUES (:nom, :email, :login, :hash, 'employe')"
        );
        $stmt->execute([
            'nom'   => $nom,
            'email' => $email,
            'login' => $login,
            'hash'  => $hash
        ]);

        // Étape 2 : récupère l'ID créé
        $userId = $pdo->lastInsertId();

        // Étape 3 : ajoute dans employes
        $stmt2 = $pdo->prepare(
            "INSERT INTO employes (utilisateur_id, departement)
             VALUES (:uid, :dep)"
        );
        $stmt2->execute([
            'uid' => $userId,
            'dep' => $departement
        ]);
    }

    // Met à jour un employé
    public static function update($id, $nom, $email, $departement) {
        $pdo = getDB();
        // Met à jour la table utilisateurs
        $stmt = $pdo->prepare(
            "UPDATE utilisateurs
             SET nom = :nom, email = :email
             WHERE id = :id"
        );
        $stmt->execute([
            'nom'   => $nom,
            'email' => $email,
            'id'    => $id
        ]);

        // Met à jour la table employes
        $stmt2 = $pdo->prepare(
            "UPDATE employes
             SET departement = :dep
             WHERE utilisateur_id = :id"
        );
        $stmt2->execute([
            'dep' => $departement,
            'id'  => $id
        ]);
    }

    // Vérifie si l'employé a des réservations futures
    // Utilisé avant de supprimer un employé
    public static function reservationFuture($userId) {
        $pdo  = getDB();
        $stmt = $pdo->prepare(
            "SELECT COUNT(*) AS total
            FROM reservations
            WHERE utilisateur_id = :uid
            AND date_reservation >= CURDATE()
            AND statut = 'confirmee'"
        );
        $stmt->execute(['uid' => $userId]);
        $result = $stmt->fetch();
        return $result['total'] > 0;
        // true  = a des réservations futures → pas de suppression
        // false = pas de réservations → suppression possible
    }
    // Retourne le nombre de réservations futures
public static function countFutureReservations($userId) {
    $pdo  = getDB();
    $stmt = $pdo->prepare(
        "SELECT COUNT(*) AS total
         FROM reservations
         WHERE utilisateur_id  = :uid
         AND date_reservation  >= CURDATE()
         AND statut            = 'confirmee'"
    );
    $stmt->execute(['uid' => $userId]);
    $result = $stmt->fetch();
    return $result['total'];
}
}
?>