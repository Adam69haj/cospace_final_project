<?php
// Ce formulaire est utilisé pour 2 cas :
//  Ajouter une salle  → $salle = null
//  Modifier une salle → $salle = tableau avec les données
$isEdit = isset($salle) && $salle !== null;
$pageTitle = $isEdit ? 'Modifier une salle' : 'Ajouter une salle';
require 'views/layouts/header.php';
?>

<!-- MESSAGE ERREUR -->
<?php if (isset($erreur)): ?>
    <div class="alert alert-error">❌ <?= $erreur ?></div>
<?php endif; ?>

<!-- EN-TÊTE PAGE -->
<div class="page-header">
    <div>
        <h1><?= $isEdit ? '✏️ Modifier la salle' : '➕ Ajouter une salle' ?></h1>
        <p><?= $isEdit ? 'Modifiez les informations.' : 'Remplissez le formulaire.' ?></p>
    </div>
    <a href="index.php?page=admin-rooms" class="btn btn-ghost">
        ← Retour
    </a>
</div>

<!-- FORMULAIRE -->
<div class="card form-card">
    <form
        method="POST"
        action="<?= $isEdit ? 'index.php?page=room-update' : 'index.php?page=room-store' ?>"
    >

        <!-- Champ caché ID — seulement en modification -->
        <?php if ($isEdit): ?>
            <input type="hidden" name="id" value="<?= $salle['id'] ?>">
        <?php endif; ?>

        <!-- NOM -->
        <div class="form-group">
            <label>Nom de la salle *</label>
            <input
                type="text"
                name="nom"
                placeholder="Ex: Salle Alpha"
                value="<?= $isEdit ? $salle['nom'] : ($_POST['nom'] ?? '') ?>"
                required
            >
            <small>Minimum 3 caractères — doit être unique</small>
        </div>

        <!-- TYPE -->
        <div class="form-group">
            <label>Type de salle *</label>
            <select name="type_salle" required>
                <?php
                $types = ['Réunion', 'Coworking', 'Conférence', 'Bureau individuel'];
                foreach ($types as $type):
                    // Sélectionne le bon type en modification
                    $selected = '';
                    if ($isEdit && $salle['type_salle'] === $type) {
                        $selected = 'selected';
                    }
                ?>
                    <option value="<?= $type ?>" <?= $selected ?>>
                        <?= $type ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- CAPACITÉ -->
        <div class="form-group">
            <label>Capacité *</label>
            <input
                type="number"
                name="capacite"
                placeholder="Ex: 20"
                value="<?= $isEdit ? $salle['capacite'] : ($_POST['capacite'] ?? '') ?>"
                min="1"
                max="500"
                required
            >
            <small>Entre 1 et 500 personnes</small>
        </div>

        <!-- DESCRIPTION -->
        <div class="form-group">
            <label>Description</label>
            <textarea
                name="description"
                placeholder="Décrivez la salle..."
                rows="3"
            ><?= $isEdit ? $salle['description'] : ($_POST['description'] ?? '') ?></textarea>
        </div>

        <!-- ÉQUIPEMENTS -->
        <div class="form-group">
            <label>Équipements</label>
            <input
                type="text"
                name="equipements"
                placeholder="Ex: Projecteur, Wifi, Tableau blanc"
                value="<?= $isEdit ? $salle['equipements'] : ($_POST['equipements'] ?? '') ?>"
            >
            <small>Séparez par des virgules</small>
        </div>

        <!-- BOUTONS -->
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <?= $isEdit ? '💾 Enregistrer' : '✅ Créer la salle' ?>
            </button>
            <a href="index.php?page=admin-rooms" class="btn btn-ghost">
                Annuler
            </a>
        </div>

    </form>
</div>

<?php require 'views/layouts/footer.php'; ?>