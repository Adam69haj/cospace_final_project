<?php
$pageTitle = 'Salles disponibles';
require 'views/layouts/header.php';
?>

<!-- EN-TÊTE PAGE -->
<div class="page-header">
    <div>
        <h1>🔍 Salles disponibles</h1>
        <p><?= count($salles) ?> salle(s) trouvée(s)</p>
    </div>
</div>

<!-- BARRE DE RECHERCHE -->
<form method="GET" action="index.php" class="search-form">
    <input type="hidden" name="page" value="member-rooms">
    <div class="search-bar">
        <input
            type="text"
            name="search"
            placeholder="Rechercher par nom, type ou équipement..."
            value="<?= $mot ?? '' ?>"
        >
        <button type="submit" class="btn btn-ghost">
            🔍 Rechercher
        </button>
        <?php if (!empty($mot)): ?>
            <a href="index.php?page=member-rooms" class="btn btn-ghost">
                ✕ Effacer
            </a>
        <?php endif; ?>
    </div>
</form>

<!-- LISTE DES SALLES -->
<?php if (empty($salles)): ?>

    <!-- Aucune salle -->
    <div class="empty-state">
        <div class="empty-icon">🏠</div>
        <h3>Aucune salle trouvée</h3>
        <p>Essayez avec un autre mot clé.</p>
        <a href="index.php?page=member-rooms" class="btn btn-ghost">
            Voir toutes les salles
        </a>
    </div>

<?php else: ?>

    <!-- Grille des salles -->
    <div class="salles-grid">

        <?php foreach ($salles as $salle): ?>
        <div class="salle-card">

            <!-- ICÔNE SELON LE TYPE -->
            <div class="salle-type-icon">
                <?php
                switch ($salle['type_salle']) {
                    case 'Réunion':           echo '🤝'; break;
                    case 'Coworking':         echo '💻'; break;
                    case 'Conférence':        echo '🎤'; break;
                    case 'Bureau individuel': echo '🪑'; break;
                    default:                  echo '🏠'; break;
                }
                ?>
            </div>

            <!-- TYPE -->
            <span class="badge badge-info">
                <?= $salle['type_salle'] ?>
            </span>

            <!-- NOM -->
            <h3 class="salle-nom"><?= $salle['nom'] ?></h3>

            <!-- DESCRIPTION -->
            <?php if (!empty($salle['description'])): ?>
                <p class="salle-desc">
                    <?= substr($salle['description'], 0, 80) ?>...
                </p>
            <?php endif; ?>

            <!-- CAPACITÉ -->
            <div class="salle-capacite">
                👤 <?= $salle['capacite'] ?> personnes
            </div>

            <!-- ÉQUIPEMENTS -->
            <?php if (!empty($salle['equipements'])): ?>
                <div class="salle-equip">
                    ⚙️ <?= $salle['equipements'] ?>
                </div>
            <?php endif; ?>

            <!-- BOUTON RÉSERVER -->
            <a href="index.php?page=reservation-create&salle_id=<?= $salle['id'] ?>"
               class="btn btn-primary btn-block">
                📅 Réserver cette salle
            </a>

        </div>
        <?php endforeach; ?>

    </div>

<?php endif; ?>

<?php require 'views/layouts/footer.php'; ?>