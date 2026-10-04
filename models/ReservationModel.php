    <?php

class ReservationModel {

    private $id;
    private $salle_id;
    private $utilisateur_id;
    private $creneau_id;
    private $date_reservation;
    private $statut;

    public function __construct($id, $salle_id, $utilisateur_id, $creneau_id, $date_reservation, $statut) {
        $this->id               = $id;
        $this->salle_id         = $salle_id;
        $this->utilisateur_id   = $utilisateur_id;
        $this->creneau_id       = $creneau_id;
        $this->date_reservation = $date_reservation;
        $this->statut           = $statut;
    }

    public function getId()              { return $this->id; }
    public function getSalleId()         { return $this->salle_id; }
    public function getUtilisateurId()   { return $this->utilisateur_id; }
    public function getCreneauId()       { return $this->creneau_id; }
    public function getDateReservation() { return $this->date_reservation; }
    public function getStatut()          { return $this->statut; }

    // Toutes les réservations — pour l'admin
    public static function getAllPourAdmin() {
        $pdo  = getDB();
        $stmt = $pdo->prepare(
            "SELECT r.*,
                    s.nom       AS salle_nom,
                    u.nom       AS user_nom,
                    u.role      AS user_role,
                    c.libelle   AS creneau_libelle
             FROM reservations r
             JOIN salles       s ON s.id = r.salle_id
             JOIN utilisateurs u ON u.id = r.utilisateur_id
             JOIN creneaux     c ON c.id = r.creneau_id
             ORDER BY r.date_reservation DESC"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Réservations d'un seul utilisateur
    // pour membre et employé
    public static function getByUser($userId) {
        $pdo  = getDB();
        $stmt = $pdo->prepare(
            "SELECT r.*,
                    s.nom     AS salle_nom,
                    c.libelle AS creneau_libelle
             FROM reservations r
             JOIN salles   s ON s.id = r.salle_id
             JOIN creneaux c ON c.id = r.creneau_id
             WHERE r.utilisateur_id = :uid
             ORDER BY r.date_reservation DESC"
        );
        $stmt->execute(['uid' => $userId]);
        return $stmt->fetchAll();
    }

    // Crée une réservation
    // retourne false si créneau déjà pris
    public static function create($salleId, $userId, $creneauId, $date) {
        $pdo = getDB();

        // Vérifie si le créneau est déjà pris
        $stmt = $pdo->prepare(
            "SELECT id FROM reservations
             WHERE salle_id          = :salle
             AND creneau_id          = :creneau
             AND date_reservation    = :date
             AND statut              = 'confirmee'"
        );
        $stmt->execute([
            'salle'   => $salleId,
            'creneau' => $creneauId,
            'date'    => $date
        ]);

        // Créneau déjà pris → retourne false
        if ($stmt->fetch()) {
            return false;
        }

        // Créneau disponible → crée la réservation
        $stmt2 = $pdo->prepare(
            "INSERT INTO reservations
             (salle_id, utilisateur_id, creneau_id, date_reservation, statut)
             VALUES (:salle, :user, :creneau, :date, 'confirmee')"
        );
        $stmt2->execute([
            'salle'   => $salleId,
            'user'    => $userId,
            'creneau' => $creneauId,
            'date'    => $date
        ]);
        return true;
    }

    // Annule une réservation
    public static function cancel($id) {
        $pdo  = getDB();
        $stmt = $pdo->prepare(
            "UPDATE reservations
             SET statut = 'annulee'
             WHERE id = :id"
        );
        $stmt->execute(['id' => $id]);
    }

    // Stats pour le dashboard admin
   public static function getStats() {
    $pdo = getDB();

    // Total réservations confirmées
    $total = $pdo->query(
        "SELECT COUNT(*) FROM reservations WHERE statut = 'confirmee'"
    )->fetchColumn();//retourne UNE SEULE valeur et simple

    // Réservations aujourd'hui
    $today = $pdo->query(
        "SELECT COUNT(*) FROM reservations 
         WHERE date_reservation = CURDATE() 
         AND statut = 'confirmee'"
    )->fetchColumn();

    // Nombre de salles
    $salles = $pdo->query(
        "SELECT COUNT(*) FROM salles"
    )->fetchColumn();

    // Nombre d'employés
    $employes = $pdo->query(
        "SELECT COUNT(*) FROM utilisateurs WHERE role = 'employe'"
    )->fetchColumn();

    // Retourne toutes les stats dans un tableau
    return [
        'total'    => $total,
        'today'    => $today,
        'salles'   => $salles,
        'employes' => $employes
    ];
}
/////////////////////////
// for the graphic chart
// Réservations par jour de la semaine
public static function getByDayOfWeek() {
    $pdo  = getDB();
    $stmt = $pdo->prepare(
        "SELECT 
            DAYNAME(date_reservation) AS jour,
            DAYOFWEEK(date_reservation) AS num_jour,
            COUNT(*) AS total
         FROM reservations
         WHERE statut = 'confirmee'
         AND date_reservation >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
         GROUP BY jour, num_jour
         ORDER BY num_jour ASC"
    );
    $stmt->execute();
    return $stmt->fetchAll();
}

// Réservations par type de salle
public static function getByTypeRoom() {
    $pdo  = getDB();
    $stmt = $pdo->prepare(
        "SELECT 
            s.type_salle AS type,
            COUNT(*) AS total
         FROM reservations r
         JOIN salles s ON s.id = r.salle_id
         WHERE r.statut = 'confirmee'
         GROUP BY s.type_salle
         ORDER BY total DESC"
    );
    $stmt->execute();
    return $stmt->fetchAll();
}
}
?>