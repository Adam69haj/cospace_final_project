<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CoSpace — <?= $pageTitle ?? 'Accueil' ?></title>
    <link rel="stylesheet" href="public/css/style.css">
</head>
<body>

<div class="app">

    <!-- ===== SIDEBAR ===== -->
    <aside class="sidebar">

        <!-- LOGO -->
        <div class="sidebar-logo">
            <h2>Co<span>Space</span></h2>
        </div>

        <!-- MENU SELON LE RÔLE -->
        <nav class="sidebar-nav">

            <?php if (getUserRole() === 'administrateur'): ?>

                <!-- MENU ADMIN -->
                <p class="nav-section">Principal</p>

                <a href="index.php?page=admin-dashboard"
                   class="nav-item <?= ($_GET['page'] ?? '') === 'admin-dashboard' ? 'active' : '' ?>">
                    📊 Dashboard
                </a>

                <a href="index.php?page=calendar"
    class="nav-item <?= ($_GET['page'] ?? '') === 'calendar' ? 'active' : '' ?>">
    📅 Calendrier
</a>

                <p class="nav-section">Gestion</p>

                <a href="index.php?page=admin-rooms"
                   class="nav-item <?= ($_GET['page'] ?? '') === 'admin-rooms' ? 'active' : '' ?>">
                    🏠 Salles
                </a>

                <a href="index.php?page=admin-employees"
                   class="nav-item <?= ($_GET['page'] ?? '') === 'admin-employees' ? 'active' : '' ?>">
                    👥 Employés
                </a>

                <a href="index.php?page=admin-reservations"
                   class="nav-item <?= ($_GET['page'] ?? '') === 'admin-reservations' ? 'active' : '' ?>">
                    📅 Réservations
                </a>
                
            <?php elseif (getUserRole() === 'employe'): ?>

                <!-- MENU EMPLOYÉ -->
                <p class="nav-section">Mon espace</p>

                
                <a href="index.php?page=my-reservations"
                   class="nav-item <?= ($_GET['page'] ?? '') === 'my-reservations' ? 'active' : '' ?>">
                    📅 Mes réservations
                </a>

                <a href="index.php?page=member-rooms"
                   class="nav-item <?= ($_GET['page'] ?? '') === 'member-rooms' ? 'active' : '' ?>">
                    🏠 Salles disponibles
                </a>

                <a href="index.php?page=reservation-create"
                   class="nav-item <?= ($_GET['page'] ?? '') === 'reservation-create' ? 'active' : '' ?>">
                    ➕ Nouvelle réservation
                </a>
                <a href="index.php?page=calendar"
   class="nav-item <?= ($_GET['page'] ?? '') === 'calendar' ? 'active' : '' ?>">
    📅 Calendrier
</a>
            <?php elseif (getUserRole() === 'membre'): ?>

                <!-- MENU MEMBRE -->
                <p class="nav-section">Mon espace</p>

                <a href="index.php?page=member-dashboard"
                   class="nav-item <?= ($_GET['page'] ?? '') === 'member-dashboard' ? 'active' : '' ?>">
                    🏠 Accueil
                </a>
                <a href="index.php?page=calendar"
   class="nav-item <?= ($_GET['page'] ?? '') === 'calendar' ? 'active' : '' ?>">
    📅 Calendrier
</a>
                <a href="index.php?page=member-rooms"
                   class="nav-item <?= ($_GET['page'] ?? '') === 'member-rooms' ? 'active' : '' ?>">
                    🔍 Explorer les salles
                </a>

                <a href="index.php?page=my-reservations"
                   class="nav-item <?= ($_GET['page'] ?? '') === 'my-reservations' ? 'active' : '' ?>">
                    📅 Mes réservations
                </a>

                <a href="index.php?page=reservation-create"
                   class="nav-item <?= ($_GET['page'] ?? '') === 'reservation-create' ? 'active' : '' ?>">
                    ➕ Réserver une salle
                </a>

            <?php endif; ?>

        </nav>

        <!-- Dans le profil sidebar — cliquer pour aller au profil -->
<a href="index.php?page=profile" class="sidebar-profil">

    <!-- AVATAR -->
    <div class="profil-avatar">
        <?php
        $photo = getUserPhoto();
        if ($photo && file_exists('public/uploads/profiles/' . $photo)):
        ?>
            <!-- Photo uploadée -->
            <img
                src="public/uploads/profiles/<?= $photo ?>"
                alt="photo"
                style="width:100%;height:100%;object-fit:cover;border-radius:50%"
            >
        <?php else: ?>
            <!-- Initiales -->
            <?= strtoupper(substr(getUserNom(), 0, 1)) ?>
        <?php endif; ?>
    </div>

    <!-- INFOS -->
    <div class="profil-info">
        <div class="profil-nom"><?= getUserNom() ?></div>
        <div class="profil-role"><?= getUserRole() ?></div>
    </div>

    <!-- ICONE EDIT -->
    <div style="color:var(--text-muted);font-size:0.75rem">✏️</div>

</a>

    </aside>

    <!-- ===== CONTENU PRINCIPAL ===== -->
    <div class="main">

        <!-- TOPBAR -->
        <header class="topbar">
            <div class="topbar-titre">
                <?= $pageTitle ?? 'Accueil' ?>
            </div>
            <div class="topbar-actions">
                <div class="topbar-right">
                <!-- BOUTON DARK/LIGHT -->
    <button
        class="theme-toggle"
        id="themeToggle"
        onclick="toggleTheme()"
        title="Changer le thème">
        ☀️
    </button>
                <!-- Bouton déconnexion -->
                <a href="index.php?page=logout" class="btn-logout"
   onclick="ouvrirFeedback(event)">
    🚶‍♂️ Déconnexion
</a>
                </div>
            </div>
        </header>
        

        <!-- ZONE CONTENU -->
        <main class="contenu">