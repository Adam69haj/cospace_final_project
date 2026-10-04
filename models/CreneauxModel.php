<?php

class CreneauxModel {

    private $id;
    private $heure_debut;
    private $heure_fin;
    private $libelle;

    public function __construct($id, $heure_debut, $heure_fin, $libelle) {
        $this->id          = $id;
        $this->heure_debut = $heure_debut;
        $this->heure_fin   = $heure_fin;
        $this->libelle     = $libelle;
    }

    public function getId()         { return $this->id; }
    public function getHeureDebut() { return $this->heure_debut; }
    public function getHeureFin()   { return $this->heure_fin; }
    public function getLibelle()    { return $this->libelle; }

    // Retourne tous les créneaux
    // Utilisé pour afficher les créneaux dans le formulaire
    public static function getAll() {
        $pdo  = getDB();
        $stmt = $pdo->prepare(
            "SELECT * FROM creneaux 
             ORDER BY heure_debut ASC"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Retourne les créneaux avec leur disponibilité
    // pour une salle et une date précise
    // Utilisé dans la page de réservation
public static function getAvailable($salleId, $date) {
    $pdo = getDB();

    // Étape 1 : récupère tous les créneaux
    $stmt = $pdo->prepare(
        "SELECT * FROM creneaux ORDER BY heure_debut ASC"
    );
    $stmt->execute();
    $creneaux = $stmt->fetchAll();

    // Étape 2 : récupère les créneaux déjà réservés
    // pour cette salle et cette date
    $stmt2 = $pdo->prepare(
        "SELECT creneau_id FROM reservations
         WHERE salle_id        = :salle
         AND date_reservation  = :date
         AND statut            = 'confirmee'"
    );
    $stmt2->execute([
        'salle' => $salleId,
        'date'  => $date
    ]);
    $pris = $stmt2->fetchAll(PDO::FETCH_COLUMN, 0);

    // Heure actuelle
    $heureActuelle = date('H:i:s');
    $dateAujourdhui = date('Y-m-d');
    // Ajoute disponibilite à chaque créneau
    foreach ($creneaux as &$c) {

        // Créneau déjà réservé ?
        if (in_array($c['id'], $pris)) {
            $c['disponibilite'] = 'pris';
            continue;
        }

        // Si c'est aujourd'hui → vérifie l'heure
        if ($date === $dateAujourdhui) {
            if ($c['heure_fin'] <= $heureActuelle) {
                // Créneau passé → non disponible
                $c['disponibilite'] = 'passe';
                continue;
            }
        }

        // Disponible !
        $c['disponibilite'] = 'disponible';}
    

    return $creneaux;
}
}
?>