<?php
$pageTitle = 'Mon profil';
require 'views/layouts/header.php';
?>

<!-- MESSAGE FLASH -->
<?php if (isset($message) && $message): ?>
    <div class="alert alert-success">✅ <?= $message ?></div>
<?php endif; ?>

<!-- MESSAGE ERREUR -->
<?php if (isset($erreur)): ?>
    <div class="alert alert-error">❌ <?= $erreur ?></div>
<?php endif; ?>

<!-- EN-TÊTE -->
<div class="page-header">
    <div>
        <h1>👤 Mon profil</h1>
        <p>Gérez vos informations personnelles</p>
    </div>
</div>

<div class="profile-grid">

    <!-- ===== COLONNE GAUCHE — PHOTO ===== -->
    <div class="profile-left">

        <!-- PHOTO -->
        <div class="card profile-photo-card">

            <!-- Avatar -->
            <div class="profile-avatar-big">
                <?php
                $photo = $user->getPhoto() ?? null;
                if ($photo && file_exists('public/uploads/profiles/' . $photo)):
                ?>
                    <!-- Photo uploadée -->
                    <img
                        src="public/uploads/profiles/<?= $photo ?>"
                        alt="Photo de profil"
                        class="profile-img"
                    >
                <?php else: ?>
                    <!-- Initiales par défaut -->
                    <div class="profile-initiales">
                        <?= strtoupper(substr($user->getNom(), 0, 1)) ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Nom et rôle -->
            <div class="profile-nom"><?= $user->getNom() ?></div>
            <div class="profile-role-badge role-<?= $user->getRole() ?>">
                <?php
                switch ($user->getRole()) {
                    case 'administrateur': echo '👑 Administrateur'; break;
                    case 'employe':        echo '💼 Employé';        break;
                    case 'membre':         echo '🙋 Membre';         break;
                }
                ?>
            </div>

            <!-- Formulaire upload photo -->
            <form
                method="POST"
                action="index.php?page=profile-update"
                enctype="multipart/form-data"
                class="photo-form"
            >
                <input type="hidden" name="action" value="photo">

                <label class="upload-label" for="photoInput">
                    📷 Changer la photo
                </label>
                <input
                    type="file"
                    id="photoInput"
                    name="photo"
                    accept="image/*"
                    onchange="previewPhoto(this)"
                    style="display:none"
                >
                <button type="submit" class="btn btn-primary btn-sm" id="btnUpload" style="display:none">
                    ✅ Enregistrer
                </button>

                <p class="upload-info">JPG, PNG ou GIF — max 2MB</p>
            </form>

        </div>

    </div>

    <!-- ===== COLONNE DROITE — FORMULAIRES ===== -->
    <div class="profile-right">

        <!-- FORMULAIRE 1 : Infos personnelles -->
        <div class="card">
            <div class="card-header">
                <h2>✏️ Informations personnelles</h2>
            </div>

            <form method="POST" action="index.php?page=profile-update">
                <input type="hidden" name="action" value="infos">

                <div class="form-group">
                    <label>Nom complet *</label>
                    <input
                        type="text"
                        name="nom"
                        value="<?= $user->getNom() ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label>Email *</label>
                    <input
                        type="text"
                        name="email"
                        value="<?= $user->getEmail() ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label>Login</label>
                    <!-- Login non modifiable -->
                    <input
                        type="text"
                        value="<?= $user->getLogin() ?>"
                        disabled
                        style="opacity:0.5;cursor:not-allowed"
                    >
                    <small>Le login ne peut pas être modifié</small>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        💾 Enregistrer
                    </button>
                </div>
            </form>
        </div>

        <!-- FORMULAIRE 2 : Changer mot de passe -->
        <div class="card">
            <div class="card-header">
                <h2>🔒 Changer le mot de passe</h2>
            </div>

            <form method="POST" action="index.php?page=profile-update">
                <input type="hidden" name="action" value="password">

                <div class="form-group">
                    <label>Mot de passe actuel *</label>
                    <input
                        type="password"
                        name="password_actuel"
                        placeholder="••••••••"
                        required
                    >
                </div>

                <div class="form-group">
                    <label>Nouveau mot de passe *</label>
                    <input
                        type="password"
                        name="password_nouveau"
                        placeholder="Au moins 6 caractères"
                        required
                    >
                </div>

                <div class="form-group">
                    <label>Confirmer le nouveau mot de passe *</label>
                    <input
                        type="password"
                        name="password_confirm"
                        placeholder="••••••••"
                        required
                    >
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        🔒 Changer le mot de passe
                    </button>
                </div>
            </form>
        </div>

    </div>

</div>

<!-- SCRIPT PREVIEW PHOTO -->
<script>
function previewPhoto(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            // Remplace l'avatar par la preview
            var avatar = document.querySelector('.profile-avatar-big');
            avatar.innerHTML = '<img src="' + e.target.result + '" class="profile-img" alt="Preview">';
        };
        reader.readAsDataURL(input.files[0]);

        // Affiche le bouton enregistrer
        document.getElementById('btnUpload').style.display = 'block';
    }
}
</script>

<?php require 'views/layouts/footer.php'; ?>