<?php
$pageTitle = 'Nouvelle réservation';
require 'views/layouts/header.php';
?>

<!-- MESSAGE ERREUR -->
<?php if (isset($erreur)): ?>
    <div class="alert alert-error">❌ <?= $erreur ?></div>
<?php endif; ?>

<!-- EN-TÊTE PAGE -->
<div class="page-header">
    <div>
        <h1>📅 Nouvelle réservation</h1>
        <p>Choisissez une salle, une date et un créneau</p>
    </div>
    <a href="index.php?page=member-rooms" class="btn btn-ghost">
        ← Retour
    </a>
</div>

<div class="book-container">

    <!-- ===== ÉTAPE 1 : CHOISIR SALLE + DATE ===== -->
    <div class="card">
        <h2>Étape 1 — Choisir une salle et une date</h2>

        <form method="POST" action="index.php?page=reservation-create">

            <!-- SALLE -->
            <div class="form-group">
                <label>Salle *</label>
                <select name="salle_id" required>
                    <option value="">-- Choisir une salle --</option>
                    <?php foreach ($salles as $salle): ?>
                        <option
                            value="<?= $salle['id'] ?>"
                            <?= (isset($salle_id) && $salle_id == $salle['id']) ? 'selected' : '' ?>
                        >
                            <?= $salle['nom'] ?>
                            (<?= $salle['capacite'] ?> pers.)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- DATE -->
            <div class="form-group">
                <label>Date *</label>
                <input
                    type="date"
                    name="date_reservation"
                    value="<?= $date ?? '' ?>"
                    min="<?= date('Y-m-d') ?>"
                    required
                >
                <small>La date doit être aujourd'hui ou dans le futur</small>
            </div>

            <button type="submit" class="btn btn-primary">
                🔍 Voir les créneaux disponibles
            </button>

        </form>
    </div>

    <!-- ===== ÉTAPE 2 : CHOISIR CRÉNEAU ===== -->
    <!-- Affiché seulement si des créneaux existent -->
    <?php if (!empty($creneaux)): ?>

    <div class="card">
        <h2>Étape 2 — Choisir un créneau</h2>

        <form method="POST" action="index.php?page=reservation-store">

            <!-- Champs cachés — garde salle et date -->
            <input type="hidden" name="salle_id"         value="<?= $salle_id ?>">
            <input type="hidden" name="date_reservation"  value="<?= $date ?>">

            <!-- LISTE DES CRÉNEAUX -->
            <div class="creneaux-liste">

                <?php foreach ($creneaux as $creneau): ?>

                    <?php if ($creneau['disponibilite'] === 'disponible'): ?>

                        <!-- CRÉNEAU DISPONIBLE -->
                        <label class="creneau-item disponible">
                            <input
                                type="radio"
                                name="creneau_id"
                                value="<?= $creneau['id'] ?>"
                                required
                            >
                            <span class="creneau-heure">
                                🕐 <?= $creneau['libelle'] ?>
                            </span>
                            <span class="badge badge-success">
                                ✅ Disponible
                            </span>
                        </label>

                        <?php elseif ($creneau['disponibilite'] === 'passe'): ?>

        <!-- PASSÉ → grisé avec message -->
        <div class="creneau-item passe">
            <span class="creneau-heure">
                🕐 <?= $creneau['libelle'] ?>
            </span>
            <span class="badge badge-warning">⏰ Passé</span>
        </div>
        
                    <?php else: ?>

                        <!-- CRÉNEAU DÉJÀ PRIS -->
                        <div class="creneau-item pris">
                            <span class="creneau-heure">
                                🕐 <?= $creneau['libelle'] ?>
                            </span>
                            <span class="badge badge-danger">
                                ❌ Déjà réservé
                            </span>
                        </div>

                    <?php endif; ?>

                <?php endforeach; ?>

            </div>

            <!-- BOUTON CONFIRMER -->
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    ✅ Confirmer la réservation
                </button>
            </div>

        </form>
    </div>

    <?php endif; ?>

</div>

<?php require 'views/layouts/footer.php'; ?>