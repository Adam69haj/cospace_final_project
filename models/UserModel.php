<?php

class UserModel {


    private $id;
    private $nom;
    private $email;
    private $login;
    private $mot_de_passe;
    private $role;
    private $photo;

    public function __construct($id, $nom, $email, $login, $mot_de_passe, $role, $photo) {
        $this->id           = $id;
        $this->nom          = $nom;
        $this->email        = $email;
        $this->login        = $login;
        $this->mot_de_passe = $mot_de_passe;
        $this->role         = $role;
        $this->photo        = $photo;
    }

    public function getId()          { return $this->id; }
    public function getNom()         { return $this->nom; }
    public function getEmail()       { return $this->email; }
    public function getLogin()       { return $this->login; }
    public function getMotDePasse()  { return $this->mot_de_passe; }
    public function getRole()        { return $this->role; }
    public function getPhoto()       { return $this->photo; }

    // Cherche un utilisateur par login ou email
    public static function findByLogin($identifier) {
        $pdo  = getDB();
        $stmt = $pdo->prepare(
            "SELECT * FROM utilisateurs 
            WHERE login = :login OR email = :email"
        );
        $stmt->execute([
            'login'  => $identifier,
            'email' => $identifier
        ]);
        $row = $stmt->fetch();

        // Si trouvé → retourne un objet UserModel
        if ($row) {
            return new UserModel(
                $row['id'],
                $row['nom'],
                $row['email'],
                $row['login'],
                $row['mot_de_passe'],
                $row['role'],
                $row['photo'] ?? null
            );
        }
        return null; // pas trouvé
    }

    // Cherche par ID
    /*pour Cas 1 — Modifier un employé
    Cas 2 — Vérifier qui est connecté
    */
    public static function findById($id) {
        $pdo  = getDB();
        $stmt = $pdo->prepare(
            "SELECT * FROM utilisateurs WHERE id = :id"
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        if ($row) {
            return new UserModel(
                $row['id'],
                $row['nom'],
                $row['email'],
                $row['login'],
                $row['mot_de_passe'],
                $row['role'],
                $row['photo'] ?? null
            );
        }
        return null;
    }

    // Vérifie si email existe déjà
    public static function emailExiste($email) {
        $pdo  = getDB();
        $stmt = $pdo->prepare(
            "SELECT id FROM utilisateurs WHERE email = :email"
        );
        $stmt->execute(['email' => $email]);
        return $stmt->fetch() !== false;
        //// Si un enregistrement est trouvé :
         // retourne un tableau
         // Si rien n'est trouvé :
         // retourne false
    }

    // Vérifie si login existe déjà
    public static function loginExiste($login) {
        $pdo  = getDB();
        $stmt = $pdo->prepare(
            "SELECT id FROM utilisateurs WHERE login = :login"
        );
        $stmt->execute(['login' => $login]);
        return $stmt->fetch() !== false;
    }

    // Crée un compte membre
    public static function creerMembre($nom, $email, $password) {
        $pdo  = getDB();
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare(
            "INSERT INTO utilisateurs 
             (nom, email, login, mot_de_passe, role)
             VALUES (:nom, :email, :login, :hash, 'membre')"
        );
        $stmt->execute([
            'nom'   => $nom,
            'email' => $email,
            'login' => $email,
            'hash'  => $hash
        ]);
    }

    // Supprime un utilisateur
    public static function delete($id) {
        $pdo  = getDB();
        // 1. Supprime d'abord les réservations de l'utilisateur
    $stmt = $pdo->prepare(
        "DELETE FROM reservations WHERE utilisateur_id = :id"
    );
    $stmt->execute(['id' => $id]);
        $stmt = $pdo->prepare(
            "DELETE FROM utilisateurs WHERE id = :id"
        );
        $stmt->execute(['id' => $id]);
    }

    // Met à jour le profil
public static function updateProfile($id, $nom, $email) {
    $pdo  = getDB();
    $stmt = $pdo->prepare(
        "UPDATE utilisateurs
         SET nom   = :nom,
             email = :email
         WHERE id  = :id"
    );
    $stmt->execute([
        'nom'   => $nom,
        'email' => $email,
        'id'    => $id
    ]);
}

// Met à jour le mot de passe
public static function updatePassword($id, $newPassword) {
    $pdo  = getDB();
    $hash = password_hash($newPassword, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare(
        "UPDATE utilisateurs
         SET mot_de_passe = :hash
         WHERE id         = :id"
    );
    $stmt->execute([
        'hash' => $hash,
        'id'   => $id
    ]);
}

// Met à jour la photo
public static function updatePhoto($id, $photo) {
    $pdo  = getDB();
    $stmt = $pdo->prepare(
        "UPDATE utilisateurs
         SET photo = :photo
         WHERE id  = :id"
    );
    $stmt->execute([
        'photo' => $photo,
        'id'    => $id
    ]);
}
}
?>