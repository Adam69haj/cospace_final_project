<?php

class RoomModel {


    private $id;
    private $nom;
    private $description;
    private $capacite;
    private $type_salle;
    private $equipements;

    public function __construct($id, $nom, $description, $capacite, $type_salle, $equipements) {
        $this->id          = $id;
        $this->nom         = $nom;
        $this->description = $description;
        $this->capacite    = $capacite;
        $this->type_salle  = $type_salle;
        $this->equipements = $equipements;
    }


    public function getId()          { return $this->id; }
    public function getNom()         { return $this->nom; }
    public function getDescription() { return $this->description; }
    public function getCapacite()    { return $this->capacite; }
    public function getTypeSalle()   { return $this->type_salle; }
    public function getEquipements() { return $this->equipements; }

    // Retourne toutes les salles
    public static function getAll() {
        $pdo  = getDB();
        $stmt = $pdo->prepare(
            "SELECT * FROM salles"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Cherche une salle par son ID
    public static function findById($id) {
        $pdo  = getDB();
        $stmt = $pdo->prepare(
            "SELECT * FROM salles WHERE id = :id"
        );
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    // Recherche par mot clé
    // cherche dans nom, type et equipements
    public static function cherche($mot) {
        $pdo  = getDB();
        $like = '%' . $mot . '%';
        $stmt = $pdo->prepare(
            "SELECT * FROM salles
             WHERE nom        LIKE :m
             OR type_salle    LIKE :m2
             OR equipements   LIKE :m3"
        );
        $stmt->execute([
            'm'  => $like,
            'm2' => $like,
            'm3' => $like
        ]);
        return $stmt->fetchAll();
    }

     // Crée une salle — MySQL bloque si nom existe déjà
    public static function create($nom, $description, $capacite, $type, $equipements) {
        try {
            $pdo  = getDB();
            $stmt = $pdo->prepare(
                "INSERT INTO salles
                 (nom, description, capacite, type_salle, equipements)
                 VALUES (:nom, :desc, :cap, :type, :equi)"
            );
            $stmt->execute([
                'nom'  => $nom,
                'desc' => $description,
                'cap'  => $capacite,
                'type' => $type,
                'equi' => $equipements
            ]);
            return true; // succès

        } catch (PDOException $e) {
            return false; // nom déjà pris
        }
    }

    public static function update($id, $nom, $description, $capacite, $type, $equipements) {
        try {
            $pdo  = getDB();
            $stmt = $pdo->prepare(
                "UPDATE salles
                 SET nom         = :nom,
                     description = :desc,
                     capacite    = :cap,
                     type_salle  = :type,
                     equipements = :equi
                 WHERE id = :id"
            );
            $stmt->execute([
                'nom'  => $nom,
                'desc' => $description,
                'cap'  => $capacite,
                'type' => $type,
                'equi' => $equipements,
                'id'   => $id
            ]);
            return true; // succès

        } catch (PDOException $e) {
            return false; // nom déjà pris
        }
    }

    // Supprime une salle
    public static function delete($id) {
        $pdo  = getDB();
        $stmt = $pdo->prepare(
            "DELETE FROM salles WHERE id = :id"
        );
        $stmt->execute(['id' => $id]);
    }

    // Vérifie si la salle a des réservations futures
public static function hasFutureReservations($id) {
    $pdo  = getDB();
    $stmt = $pdo->prepare(
        "SELECT COUNT(*) AS total
         FROM reservations
         WHERE salle_id          = :id
         AND date_reservation    >= CURDATE()
         AND statut              = 'confirmee'"
    );
    $stmt->execute(['id' => $id]);
    $result = $stmt->fetch();
    return $result['total'] > 0;
    // true  = a des réservations → pas de suppression
    // false = pas de réservations → suppression ok
}

}
?>