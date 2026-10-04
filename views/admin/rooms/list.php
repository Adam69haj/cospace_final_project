<?php
$pageTitle = 'Gestion des salles';
require 'views/layouts/header.php';
?>

<!-- MESSAGE FLASH -->
<?php if (isset($message) && $message): ?>
    <div class="alert alert-success">✅ <?= $message ?></div>
<?php endif; ?>

<!-- EN-TÊTE PAGE -->
<div class="page-header">
    <div>
        <h1>🏠 Gestion des salles</h1>
        <p><?= count($salles) ?> salle(s) au total</p>
    </div>
    <a href="index.php?page=room-create" class="btn btn-primary">
        ➕ Ajouter une salle
    </a>
</div>

<!-- BARRE DE RECHERCHE -->
<form method="GET" action="index.php" class="search-form">
    <input type="hidden" name="page" value="admin-rooms">
    <div class="search-bar">
        <input
            type="text"
            name="search"
            placeholder="Rechercher par nom, type ou équipement..."
            value="<?= $mot ?? '' ?>"
        >
        <button type="submit" class="btn btn-ghost">🔍 Rechercher</button>
        <?php if (!empty($mot)): ?>
            <a href="index.php?page=admin-rooms" class="btn btn-ghost">✕ Effacer</a>
        <?php endif; ?>
    </div>
</form>

<!-- TABLEAU DES SALLES -->
<div class="card">

    <?php if (empty($salles)): ?>

        <!-- Aucune salle -->
        <div class="empty-state">
            <div class="empty-icon">🏠</div>
            <h3>Aucune salle trouvée</h3>
            <p>Commencez par ajouter une salle.</p>
            <a href="index.php?page=room-create" class="btn btn-primary">
                ➕ Ajouter une salle
            </a>
        </div>

    <?php else: ?>

        <table class="tableau">
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Type</th>
                    <th>Capacité</th>
                    <th>Équipements</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($salles as $salle): ?>
                <tr>
                    <td>
                        <div class="salle-nom"><?= $salle['nom'] ?></div>
                        <?php if (!empty($salle['description'])): ?>
                            <div class="salle-desc">
                                <?= substr($salle['description'], 0, 50) ?>...
                            </div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="badge badge-info">
                            <?= $salle['type_salle'] ?>
                        </span>
                    </td>
                    <td>👤 <?= $salle['capacite'] ?> pers.</td>
                    <td class="text-muted">
                        <?= $salle['equipements'] ?: '—' ?>
                    </td>
                    <td>
                        <div class="actions">
                            <!-- Bouton Modifier -->
                            <a href="index.php?page=room-edit&id=<?= $salle['id'] ?>"
                               class="btn btn-ghost btn-sm">
                                ✏️ Modifier
                            </a>

                            <!-- Bouton Supprimer -->
                            <a href="index.php?page=room-delete&id=<?= $salle['id'] ?>"
                               class="btn btn-danger btn-sm"
                               onclick="return confirm('Supprimer cette salle ?')">
                                🗑️ Supprimer
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    <?php endif; ?>

</div>

<?php require 'views/layouts/footer.php'; ?>