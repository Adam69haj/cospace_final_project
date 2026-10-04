<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CoSpace — Connexion</title>
    <link rel="stylesheet" href="public/css/style.css">
</head>
<body>

<div class="login-page">

    <!-- LOGO -->
    <div class="login-logo">
        <h1>Co<span>Space</span></h1>
        <p>Gestion de salles & réservations</p>
    </div>

    <!-- MESSAGE ERREUR -->
    <?php if (isset($erreur)): ?>
        <div class="alert alert-error">
            ❌ <?= $erreur ?>
        </div>
    <?php endif; ?>

    <!-- MESSAGE SUCCÈS (flash) -->
    <?php if (isset($message)): ?>
        <div class="alert alert-success">
            ✅ <?= $message ?>
        </div>
    <?php endif; ?>

    <!-- CHOIX DU RÔLE -->
    <p class="role-titre">Je suis...</p>

    <div class="role-cards">

        <!-- CARTE ADMIN -->
        <div class="role-card" data-role="administrateur" onclick="selectRole('administrateur', this)">
            <div class="role-icon">👑</div>
            <div class="role-nom">Administrateur</div>
        </div>

        <!-- CARTE EMPLOYÉ -->
        <div class="role-card" data-role="employe" onclick="selectRole('employe', this)">
            <div class="role-icon">💼</div>
            <div class="role-nom">Employé</div>
        </div>

        <!-- CARTE MEMBRE -->
        <div class="role-card" data-role="membre" onclick="selectRole('membre', this)">
            <div class="role-icon">🙋</div>
            <div class="role-nom">Membre</div>
        </div>

    </div>

    <!-- FORMULAIRE LOGIN -->
    <form method="POST" action="index.php?page=do-login">

        <!-- Champ caché — rôle sélectionné -->
        <input type="hidden" name="role" id="roleInput" value="">

        <div class="form-group">
            <label>Email ou login</label>
            <input
                type="text"
                name="identifier"
                placeholder="Entrez votre email ou login"
                required
            >
        </div>

        <div class="form-group">
            <label>Mot de passe</label>
            <input
                type="password"
                name="password"
                placeholder="••••••••"
                required
            >
        </div>

        <button type="submit" class="btn-login" id="btnLogin">
            Se connecter
        </button>

    </form>
        
    <!-- LIEN INSCRIPTION -->
    <p class="login-link">
        Pas de compte ?
        <a href="index.php?page=register">S'inscrire</a>
    </p>

    <!-- COMPTES DE TEST -->
<div class="test-comptes">
    <p class="test-titre">🧪 Comptes de test</p>
    <div class="test-liste">

        <div class="test-item" onclick="remplirLogin('admin', 'admin123', 'administrateur')">
            <span class="test-role admin">👑 Admin</span>
            <span class="test-creds">admin / admin123</span>
        </div>

        <div class="test-item" onclick="remplirLogin('jean.dupont', 'employe123', 'employe')">
            <span class="test-role employe">💼 Employé</span>
            <span class="test-creds">jean.dupont / employe123</span>
        </div>

        <div class="test-item" onclick="remplirLogin('marie@gmail.com', 'membre123', 'membre')">
            <span class="test-role membre">🙋 Membre</span>
            <span class="test-creds">marie@gmail.com / membre123</span>
        </div>

    </div>
</div>

<!-- SCRIPT — remplit automatiquement le formulaire -->
<script>
function remplirLogin(login, password, role) {

    // Remplit les champs
    document.querySelector('input[name="identifier"]').value = login;
    document.querySelector('input[name="password"]').value   = password;

    // Sélectionne automatiquement la bonne carte de rôle
    var carte = document.querySelector('.role-card[data-role="' + role + '"]');
    if (carte) {
        selectRole(role, carte);
    }
}
</script>

</div>

<!-- JAVASCRIPT — sélection du rôle -->
<script>
// Quand on clique sur une carte de rôle
function selectRole(role, card) {

    // Retire la sélection de toutes les cartes
    document.querySelectorAll('.role-card').forEach(function(c) {
        c.classList.remove('selected');
    });

    // Ajoute la sélection sur la carte cliquée
    card.classList.add('selected');

    // Met le rôle dans le champ caché
    document.getElementById('roleInput').value = role;

    // Change le texte du bouton
    var labels = {
        'administrateur': 'Se connecter en tant qu\'Admin',
        'employe':        'Se connecter en tant qu\'Employé',
        'membre':         'Se connecter en tant que Membre'
    };
    document.getElementById('btnLogin').textContent = labels[role];
}

// Vérifie qu'un rôle est sélectionné avant de soumettre
document.querySelector('form').addEventListener('submit', function(e) {
    if (document.getElementById('roleInput').value === '') {
        e.preventDefault();
        alert('Veuillez choisir votre rôle !');
    }
});
</script>

</body>
</html>