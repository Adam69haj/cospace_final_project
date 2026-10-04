<?php
$pageTitle = 'Dashboard';
require 'views/layouts/header.php';
?>

<?php if (isset($message) && $message): ?>
    <div class="alert alert-success">✅ <?= $message ?></div>
<?php endif; ?>

<div class="page-header">
    <div>
        <h1>Bonjour, <?= getUserNom() ?> 👋</h1>
        <p>Tableau de bord — <?= date('d/m/Y') ?></p>
    </div>
</div>

<!-- STATS -->
<div class="stats-grid">
    <div class="stat-card stat-purple">
        <div class="stat-icon">📅</div>
        <div class="stat-info">
            <div class="stat-nombre"><?= $stats['total'] ?></div>
            <div class="stat-label">Réservations totales</div>
        </div>
    </div>
    <div class="stat-card stat-green">
        <div class="stat-icon">✅</div>
        <div class="stat-info">
            <div class="stat-nombre"><?= $stats['today'] ?></div>
            <div class="stat-label">Aujourd'hui</div>
        </div>
    </div>
    <div class="stat-card stat-blue">
        <div class="stat-icon">🏠</div>
        <div class="stat-info">
            <div class="stat-nombre"><?= $stats['salles'] ?></div>
            <div class="stat-label">Salles</div>
        </div>
    </div>
    <div class="stat-card stat-orange">
        <div class="stat-icon">👥</div>
        <div class="stat-info">
            <div class="stat-nombre"><?= $stats['employes'] ?></div>
            <div class="stat-label">Employés</div>
        </div>
    </div>
</div>

<!-- ===== GRAPHIQUES ===== -->
<div class="charts-grid">

    <!-- Graphique 1 : Barres — réservations par jour -->
    <div class="card">
        <div class="card-header">
            <h2>📈 Réservations par jour</h2>
            <span class="badge badge-info">30 derniers jours</span>
        </div>
        <div class="chart-wrap">
            <canvas id="chartJours"></canvas>
        </div>
    </div>

    <!-- Graphique 2 : Donut — réservations par type -->
    <div class="card">
        <div class="card-header">
            <h2>🍩 Par type de salle</h2>
            <span class="badge badge-info">Total</span>
        </div>
        <div class="chart-wrap">
            <canvas id="chartTypes"></canvas>
        </div>
    </div>

</div>

<!-- DERNIÈRES RÉSERVATIONS -->
<div class="card">
    <div class="card-header">
        <h2>🕐 Dernières réservations</h2>
        <a href="index.php?page=admin-reservations" class="btn btn-ghost">
            Voir tout →
        </a>
    </div>
    <?php if (empty($dernieres)): ?>
        <div class="empty-state">
            <div class="empty-icon">📅</div>
            <h3>Aucune réservation</h3>
        </div>
    <?php else: ?>
        <table class="tableau">
            <thead>
                <tr>
                    <th>Utilisateur</th>
                    <th>Salle</th>
                    <th>Date</th>
                    <th>Créneau</th>
                    <th>Statut</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($dernieres as $resa): ?>
                <tr>
                    <td>
                        <div class="user-nom"><?= $resa['user_nom'] ?></div>
                        <div class="user-role"><?= $resa['user_role'] ?></div>
                    </td>
                    <td><?= $resa['salle_nom'] ?></td>
                    <td><?= date('d/m/Y', strtotime($resa['date_reservation'])) ?></td>
                    <td><?= $resa['creneau_libelle'] ?></td>
                    <td>
                        <?php if ($resa['statut'] === 'confirmee'): ?>
                            <span class="badge badge-success">✅ Confirmée</span>
                        <?php else: ?>
                            <span class="badge badge-danger">❌ Annulée</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<!-- ACCÈS RAPIDES -->
<div class="acces-rapides" style="margin-top:20px">
    <a href="index.php?page=admin-rooms" class="acces-card">
        <div class="acces-icon">🏠</div>
        <div class="acces-titre">Gérer les salles</div>
        <div class="acces-desc">Ajouter, modifier, supprimer</div>
    </a>
    <a href="index.php?page=admin-employees" class="acces-card">
        <div class="acces-icon">👥</div>
        <div class="acces-titre">Gérer les employés</div>
        <div class="acces-desc">Comptes et accès</div>
    </a>
    <a href="index.php?page=admin-reservations" class="acces-card">
        <div class="acces-icon">📅</div>
        <div class="acces-titre">Toutes les réservations</div>
        <div class="acces-desc">Filtrer et annuler</div>
    </a>
</div>
<?php
// Récupère les feedbacks
$pdo      = getDB();
$feedbacks = $pdo->query(
    "SELECT * FROM feedbacks ORDER BY created_at DESC LIMIT 10"
)->fetchAll();

// Calcule la note moyenne
$moyenne = $pdo->query(
    "SELECT AVG(note) AS moy, COUNT(*) AS total FROM feedbacks"
)->fetch();
?>

<!-- SECTION FEEDBACKS -->
<div class="card" style="margin-top:20px">

    <div class="card-header">
        <h2>⭐ Avis des utilisateurs</h2>
        <div class="feedback-stats">
            <?php if ($moyenne['total'] > 0): ?>
                <span class="feedback-moy">
                    <?= number_format($moyenne['moy'], 1) ?>/5
                </span>
                <span class="text-muted">
                    (<?= $moyenne['total'] ?> avis)
                </span>
            <?php endif; ?>
        </div>
    </div>

    <?php if (empty($feedbacks)): ?>
        <div class="empty-state">
            <div class="empty-icon">⭐</div>
            <h3>Aucun avis pour le moment</h3>
        </div>

    <?php else: ?>

        <!-- BARRE MOYENNE -->
        <div class="feedback-moyenne-bar">
            <?php
            $moy = round($moyenne['moy']);
            for ($i = 1; $i <= 5; $i++):
            ?>
                <span style="font-size:1.5rem">
                    <?= $i <= $moy ? '⭐' : '☆' ?>
                </span>
            <?php endfor; ?>
            <strong style="margin-left:8px;color:var(--warning)">
                <?= number_format($moyenne['moy'], 1) ?>
            </strong>
        </div>

        <!-- LISTE DES FEEDBACKS -->
        <div class="feedbacks-liste">
            <?php foreach ($feedbacks as $fb): ?>
            <div class="feedback-item">

                <!-- AVATAR + NOM -->
                <div class="feedback-user">
                    <div class="employe-avatar">
                        <?= strtoupper(substr($fb['user_nom'], 0, 1)) ?>
                    </div>
                    <div>
                        <div class="feedback-nom"><?= $fb['user_nom'] ?></div>
                        <div class="feedback-role"><?= $fb['user_role'] ?></div>
                    </div>
                </div>

                <!-- ÉTOILES -->
                <div class="feedback-stars">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <?= $i <= $fb['note'] ? '⭐' : '☆' ?>
                    <?php endfor; ?>
                </div>

                <!-- MESSAGE -->
                <?php if (!empty($fb['message'])): ?>
                    <div class="feedback-msg">
                        "<?= htmlspecialchars($fb['message']) ?>"
                    </div>
                <?php endif; ?>

                <!-- DATE -->
                <div class="feedback-date text-muted">
                    <?= date('d/m/Y H:i', strtotime($fb['created_at'])) ?>
                </div>

            </div>
            <?php endforeach; ?>
        </div>

    <?php endif; ?>

</div>
<?php
// Prépare les données JSON pour Chart.js
$joursLabels = [];
$joursData   = [];
$joursMap    = [
    'Sunday'    => 'Dim',
    'Monday'    => 'Lun',
    'Tuesday'   => 'Mar',
    'Wednesday' => 'Mer',
    'Thursday'  => 'Jeu',
    'Friday'    => 'Ven',
    'Saturday'  => 'Sam',
];
foreach ($dataJours as $j) {
    $joursLabels[] = $joursMap[$j['jour']] ?? $j['jour'];
    $joursData[]   = (int) $j['total'];
}

$typesLabels = array_column($dataTypes, 'type');
$typesData   = array_map('intval', array_column($dataTypes, 'total'));
?>

<!-- CHART.JS -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// Couleurs
var colors = ['#7c6af7','#22d3a5','#f97316','#60a5fa','#f5a623'];

// ===== GRAPHIQUE 1 : BARRES =====
var ctxJours = document.getElementById('chartJours').getContext('2d');
new Chart(ctxJours, {
    type: 'bar',
    data: {
        labels:   <?= json_encode($joursLabels) ?>,
        datasets: [{
            label:           'Réservations',
            data:            <?= json_encode($joursData) ?>,
            backgroundColor: 'rgba(124,106,247,0.7)',
            borderColor:     '#7c6af7',
            borderWidth:     1,
            borderRadius:    8
        }]
    },
    options: {
        responsive:          true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false }
        },
        scales: {
            x: {
                grid:  { color: 'rgba(255,255,255,0.05)' },
                ticks: { color: '#8b92b3' }
            },
            y: {
                grid:      { color: 'rgba(255,255,255,0.05)' },
                ticks:     { color: '#8b92b3', stepSize: 1 },
                beginAtZero: true
            }
        }
    }
});

// ===== GRAPHIQUE 2 : DONUT =====
var ctxTypes = document.getElementById('chartTypes').getContext('2d');
new Chart(ctxTypes, {
    type: 'doughnut',
    data: {
        labels:   <?= json_encode($typesLabels) ?>,
        datasets: [{
            data:            <?= json_encode($typesData) ?>,
            backgroundColor: colors,
            borderWidth:     0,
            hoverOffset:     8
        }]
    },
    options: {
        responsive:          true,
        maintainAspectRatio: false,
        cutout:              '65%',
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    color:   '#8b92b3',
                    padding: 16,
                    font:    { size: 12 }
                }
            }
        }
    }
});
</script>

<?php require 'views/layouts/footer.php'; ?>