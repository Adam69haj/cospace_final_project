<?php
$isEdit    = isset($employe) && $employe !== null;
$pageTitle = $isEdit ? 'Modifier un employé' : 'Ajouter un employé';
require 'views/layouts/header.php';
?>

<!-- MESSAGE ERREUR -->
<?php if (isset($erreur)): ?>
    <div class="alert alert-error">❌ <?= $erreur ?></div>
<?php endif; ?>

<!-- EN-TÊTE PAGE -->
<div class="page-header">
    <div>
        <h1><?= $isEdit ? '✏️ Modifier un employé' : '➕ Ajouter un employé' ?></h1>
        <p><?= $isEdit ? 'Modifiez les informations.' : 'Remplissez le formulaire.' ?></p>
    </div>
    <a href="index.php?page=admin-employees" class="btn btn-ghost">
        ← Retour
    </a>
</div>

<!-- FORMULAIRE -->
<div class="card form-card">
    <form
        method="POST"
        action="<?= $isEdit ? 'index.php?page=employee-update' : 'index.php?page=employee-store' ?>"
    >

        <!-- Champ caché ID — seulement en modification -->
        <?php if ($isEdit): ?>
            <input type="hidden" name="id" value="<?= $employe['id'] ?>">
        <?php endif; ?>

        <!-- NOM -->
        <div class="form-group">
            <label>Nom complet *</label>
            <input
                type="text"
                name="nom"
                placeholder="Ex: Jean Dupont"
                value="<?= $isEdit ? $employe['nom'] : ($_POST['nom'] ?? '') ?>"
                required
            >
            <small>Minimum 3 caractères</small>
        </div>

        <!-- EMAIL -->
        <div class="form-group">
            <label>Email *</label>
            <input
                type="text"
                name="email"
                placeholder="Ex: jean@entreprise.com"
                value="<?= $isEdit ? $employe['email'] : ($_POST['email'] ?? '') ?>"
                required
            >
        </div>

        <!-- LOGIN — seulement en création -->
        <?php if (!$isEdit): ?>
        <div class="form-group">
            <label>Login *</label>
            <input
                type="text"
                name="login"
                placeholder="Ex: jean.dupont"
                value="<?= $_POST['login'] ?? '' ?>"
                required
            >
            <small>Attribué par l'administrateur</small>
        </div>
        <?php endif; ?>

        <!-- MOT DE PASSE — seulement en création -->
        <?php if (!$isEdit): ?>
        <div class="form-group">
            <label>Mot de passe *</label>
            <input
                type="text"
                name="password"
                placeholder="Mot de passe initial"
                required
            >
            <small>Minimum 6 caractères</small>
        </div>
        <?php endif; ?>

        <!-- DÉPARTEMENT -->
        <div class="form-group">
            <label>Département</label>
            <input
                type="text"
                name="departement"
                placeholder="Ex: Informatique, RH, Marketing..."
                value="<?= $isEdit ? $employe['departement'] : ($_POST['departement'] ?? '') ?>"
            >
            <small>Optionnel</small>
        </div>

        <!-- BOUTONS -->
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <?= $isEdit ? '💾 Enregistrer' : '✅ Créer l\'employé' ?>
            </button>
            <a href="index.php?page=admin-employees" class="btn btn-ghost">
                Annuler
            </a>
        </div>

    </form>
</div>

<?php require 'views/layouts/footer.php'; ?>