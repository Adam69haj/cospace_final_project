<?php
$pageTitle = 'Toutes les réservations';
require 'views/layouts/header.php';
?>

<!-- MESSAGE FLASH -->
<?php if (isset($message) && $message): ?>
    <div class="alert alert-success">✅ <?= $message ?></div>
<?php endif; ?>

<!-- EN-TÊTE PAGE -->
<div class="page-header">
    <div>
        <h1>📅 Toutes les réservations</h1>
        <p><?= count($reservations) ?> réservation(s) au total</p>
    </div>
</div>

<!-- TABLEAU DES RÉSERVATIONS -->
<div class="card">

    <?php if (empty($reservations)): ?>

        <!-- Aucune réservation -->
        <div class="empty-state">
            <div class="empty-icon">📅</div>
            <h3>Aucune réservation</h3>
            <p>Aucune réservation enregistrée pour le moment.</p>
        </div>

    <?php else: ?>

        <table class="tableau">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Utilisateur</th>
                    <th>Salle</th>
                    <th>Date</th>
                    <th>Créneau</th>
                    <th>Statut</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reservations as $resa): ?>
                <tr>

                    <!-- ID -->
                    <td class="text-muted">#<?= $resa['id'] ?></td>

                    <!-- UTILISATEUR -->
                    <td>
                        <div class="user-nom"><?= $resa['user_nom'] ?></div>
                        <div class="user-role"><?= $resa['user_role'] ?></div>
                    </td>

                    <!-- SALLE -->
                    <td><?= $resa['salle_nom'] ?></td>

                    <!-- DATE -->
                    <td>
                        <?= date('d/m/Y', strtotime($resa['date_reservation'])) ?>
                    </td>

                    <!-- CRÉNEAU -->
                    <td><?= $resa['creneau_libelle'] ?></td>

                    <!-- STATUT -->
                    <td>
                        <?php if ($resa['statut'] === 'confirmee'): ?>
                            <span class="badge badge-success">✅ Confirmée</span>
                        <?php else: ?>
                            <span class="badge badge-danger">❌ Annulée</span>
                        <?php endif; ?>
                    </td>

                    <!-- ACTION -->
                    <td>
                        <?php if ($resa['statut'] === 'confirmee'): ?>
                            <a href="index.php?page=reservation-cancel&id=<?= $resa['id'] ?>"
                               class="btn btn-danger btn-sm"
                               onclick="return confirm('Annuler cette réservation ?')">
                                Annuler
                            </a>
                        <?php else: ?>
                            <span class="text-muted">—</span>
                        <?php endif; ?>
                    </td>

                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    <?php endif; ?>

</div>

<?php require 'views/layouts/footer.php'; ?>