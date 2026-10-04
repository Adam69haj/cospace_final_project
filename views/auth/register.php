<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CoSpace — Inscription</title>
    <link rel="stylesheet" href="public/css/style.css">
</head>
<body>

<div class="login-page">

    <!-- LOGO -->
    <div class="login-logo">
        <h1>Co<span>Space</span></h1>
        <p>Créer un compte membre</p>
    </div>

    <!-- MESSAGE ERREUR -->
    <?php if (isset($erreur)): ?>
        <div class="alert alert-error">
            ❌ <?= $erreur ?>
        </div>
    <?php endif; ?>

    <!-- MESSAGE SUCCÈS (flash) -->
    <?php $message = getMessage(); ?>
    <?php if ($message): ?>
        <div class="alert alert-success">
            ✅ <?= $message ?>
        </div>
    <?php endif; ?>

    <!-- BADGE MEMBRE -->
    <div class="badge-membre">
        🙋 Inscription Membre
    </div>

    <!-- FORMULAIRE INSCRIPTION -->
    <form method="POST" action="index.php?page=do-register">

        <div class="form-group">
            <label>Nom complet *</label>
            <input
                type="text"
                name="nom"
                placeholder="Ex: Jean Dupont"
                value="<?= $_POST['nom'] ?? '' ?>"
                required
            >
        </div>

        <div class="form-group">
            <label>Adresse email *</label>
            <input
                type="text"
                name="email"
                placeholder="Ex: jean@gmail.com"
                value="<?= $_POST['email'] ?? '' ?>"
                required
            >
        </div>

        <div class="form-group">
            <label>Mot de passe *</label>
            <input
                type="password"
                name="password"
                placeholder="Au moins 6 caractères"
                required
            >
        </div>

        <div class="form-group">
            <label>Confirmer le mot de passe *</label>
            <input
                type="password"
                name="confirm_password"
                placeholder="••••••••"
                required
            >
        </div>

        <button type="submit" class="btn-login">
            Créer mon compte
        </button>

    </form>

    <!-- LIEN LOGIN -->
    <p class="login-link">
        Déjà inscrit ?
        <a href="index.php?page=login">Se connecter</a>
    </p>

</div>

</body>
</html>