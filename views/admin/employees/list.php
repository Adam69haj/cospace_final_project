<?php
$pageTitle = 'Gestion des employés';
require 'views/layouts/header.php';
?>

<!-- MESSAGE FLASH -->
<?php if (isset($message) && $message): ?>
    <div class="alert alert-success">✅ <?= $message ?></div>
<?php endif; ?>

<!-- EN-TÊTE PAGE -->
<div class="page-header">
    <div>
        <h1>👥 Gestion des employés</h1>
        <p><?= count($employes) ?> employé(s) au total</p>
    </div>
    <a href="index.php?page=employee-create" class="btn btn-primary">
        ➕ Ajouter un employé
    </a>
</div>

<!-- TABLEAU -->
<div class="card">

    <?php if (empty($employes)): ?>

        <div class="empty-state">
            <div class="empty-icon">👥</div>
            <h3>Aucun employé</h3>
            <p>Commencez par ajouter un employé.</p>
            <a href="index.php?page=employee-create" class="btn btn-primary">
                ➕ Ajouter un employé
            </a>
        </div>

    <?php else: ?>

        <table class="tableau">
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Email</th>
                    <th>Login</th>
                    <th>Département</th>
                    <th>Réservations</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($employes as $employe): ?>

                <?php
                // Compte les réservations futures de l'employé
                $nbReservations = EmployeeModel::countFutureReservations($employe['id']);
                $aDesReservations = $nbReservations > 0;
                ?>

                <tr>

                    <!-- NOM -->
                    <td>
                        <div class="employe-info">
                            <div class="employe-avatar">
                                <?= strtoupper(substr($employe['nom'], 0, 1)) ?>
                            </div>
                            <div class="employe-nom">
                                <?= $employe['nom'] ?>
                            </div>
                        </div>
                    </td>

                    <!-- EMAIL -->
                    <td class="text-muted"><?= $employe['email'] ?></td>

                    <!-- LOGIN -->
                    <td>
                        <span class="code-badge"><?= $employe['login'] ?></span>
                    </td>

                    <!-- DÉPARTEMENT -->
                    <td>
                        <?php if (!empty($employe['departement'])): ?>
                            <span class="badge badge-info">
                                <?= $employe['departement'] ?>
                            </span>
                        <?php else: ?>
                            <span class="text-muted">—</span>
                        <?php endif; ?>
                    </td>

                    <!-- RÉSERVATIONS FUTURES -->
                    <td>
                        <?php if ($aDesReservations): ?>
                            <!-- A des réservations futures → badge orange -->
                            <span class="badge badge-warning">
                                📅 <?= $nbReservations ?> à venir
                            </span>
                        <?php else: ?>
                            <!-- Pas de réservations futures → badge vert -->
                            <span class="badge badge-success">
                                ✅ Aucune
                            </span>
                        <?php endif; ?>
                    </td>

                    <!-- ACTIONS -->
                    <td>
                        <div class="actions">

                            <!-- Modifier — toujours possible -->
                            <a href="index.php?page=employee-edit&id=<?= $employe['id'] ?>"
                               class="btn btn-ghost btn-sm">
                                ✏️ Modifier
                            </a>

                            <?php if ($aDesReservations): ?>

                                <!-- A des réservations → bouton désactivé -->
                                <span
                                    class="btn btn-disabled btn-sm"
                                    title="Impossible : <?= $nbReservations ?> réservation(s) à venir">
                                    🔒 Supprimer
                                </span>

                            <?php else: ?>

                                <!-- Pas de réservations → peut supprimer -->
                                <a href="index.php?page=employee-delete&id=<?= $employe['id'] ?>"
                                   class="btn btn-danger btn-sm"
                                   onclick="return confirm('Supprimer cet employé ?')">
                                    🗑️ Supprimer
                                </a>

                            <?php endif; ?>

                        </div>
                    </td>

                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        

    <?php endif; ?>

</div>

<?php require 'views/layouts/footer.php'; ?>