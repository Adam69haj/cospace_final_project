<?php
$pageTitle = 'Mes réservations';
require 'views/layouts/header.php';
?>

<!-- MESSAGE FLASH succès -->
<?php if (isset($message) && $message): ?>
    <div class="alert alert-success">✅ <?= $message ?></div>
<?php endif; ?>

<!-- MESSAGE ERREUR 24h -->
<?php if (isset($erreur)): ?>
    <div class="alert alert-error">❌ <?= $erreur ?></div>
<?php endif; ?>

<!-- EN-TÊTE PAGE -->
<div class="page-header">
    <div>
        <h1>📅 Mes réservations</h1>
        <p><?= count($reservations) ?> réservation(s) au total</p>
    </div>
    <a href="index.php?page=reservation-create" class="btn btn-primary">
        ➕ Nouvelle réservation
    </a>
</div>

<!-- TABLEAU DES RÉSERVATIONS -->
<div class="card">

    <?php if (empty($reservations)): ?>

        <div class="empty-state">
            <div class="empty-icon">📅</div>
            <h3>Aucune réservation</h3>
            <p>Vous n'avez pas encore fait de réservation.</p>
            <a href="index.php?page=reservation-create" class="btn btn-primary">
                ➕ Faire une réservation
            </a>
        </div>

    <?php else: ?>

        <table class="tableau">
            <thead>
                <tr>
                    <th>Salle</th>
                    <th>Date</th>
                    <th>Créneau</th>
                    <th>Statut</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reservations as $resa): ?>

                <?php
                // Calcule si la réservation est future
                $isFuture = $resa['date_reservation'] >= date('Y-m-d');
                $isActive = $resa['statut'] === 'confirmee';

                // Calcule si on peut encore annuler (règle 24h)
                $dateResa      = new DateTime($resa['date_reservation']);
                $maintenant    = new DateTime();
                $difference    = $maintenant->diff($dateResa);
                // Vérifie si le créneau est passé aujourd'hui
$creneauPasse = false;
if ($resa['date_reservation'] === date('Y-m-d')) {
    // Extrait l'heure de fin du créneau
    // libelle = "08:00 - 10:00"
    $parts    = explode(' - ', $resa['creneau_libelle']);
    $heureFin = $parts[1] ?? '00:00';
    $creneauPasse = $heureFin . ':00' <= date('H:i:s');
}
// pas en jours
$dateResa   = new DateTime($resa['date_reservation'] . ' 00:00:00');
$maintenant = new DateTime();

// Différence en heures
$diffHeures = ($dateResa->getTimestamp() - $maintenant->getTimestamp()) / 3600;
                $peutAnnuler   = $isActive
                                 && $isFuture
                                  && !$creneauPasse
            && $diffHeures >= 24
                ?>


                <tr>

                    <!-- SALLE -->
                    <td>🏠 <?= $resa['salle_nom'] ?></td>

                    <!-- DATE -->
                    <td>
                        <?= date('d/m/Y', strtotime($resa['date_reservation'])) ?>
                        <?php if (!$isFuture): ?>
                            <span class="badge badge-warning">Passée</span>
                        <?php endif; ?>
                    </td>

                    <!-- CRÉNEAU -->
                    <td>🕐 <?= $resa['creneau_libelle'] ?></td>

                    <!-- STATUT -->
                    <td>
                        <?php if ($isActive): ?>
                            <span class="badge badge-success">✅ Confirmée</span>
                        <?php else: ?>
                            <span class="badge badge-danger">❌ Annulée</span>
                        <?php endif; ?>
                    </td>

                   <!-- ACTION -->
<td>
    <div class="actions">

        <?php if ($resa['statut'] === 'confirmee'): ?>

            <!-- BOUTON QR CODE -->
            <button
                class="btn btn-info btn-sm"
                onclick="afficherQR(
                    '<?= $resa['id'] ?>',
                    '<?= addslashes($resa['salle_nom']) ?>',
                    '<?= date('d/m/Y', strtotime($resa['date_reservation'])) ?>',
                    '<?= addslashes($resa['creneau_libelle']) ?>'
                )">
                🎫 QR
            </button>

        <?php endif; ?>
            <?php if ($creneauPasse && $isActive): ?>
        <!-- Créneau passé aujourd'hui -->
        <span class="badge badge-warning">⏰ Terminé</span>
        <?php elseif ($peutAnnuler): ?>
            <a href="index.php?page=reservation-cancel&id=<?= $resa['id'] ?>"
               class="btn btn-danger btn-sm"
               onclick="return confirm('Annuler cette réservation ?')">
                Annuler
            </a>
        <?php elseif ($isActive && $isFuture && $difference->days < 1): ?>
            <span class="badge-24h" title="Annulation impossible : moins de 24h">
                🔒 Moins de 24h
            </span>
        <?php else: ?>
            <span class="text-muted">—</span>
        <?php endif; ?>

    </div>
</td>

                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    

    <?php endif; ?>

</div>
<!-- MODALE QR CODE -->
<div class="qr-overlay" id="qrOverlay" onclick="fermerQR()">
    <div class="qr-modal" onclick="event.stopPropagation()">

        <!-- En-tête -->
        <div class="qr-header">
            <h3>🎫 QR Code de réservation</h3>
            <button class="qr-close" onclick="fermerQR()">✕</button>
        </div>

        <!-- Contenu -->
        <div class="qr-body">

            <!-- Canvas QR Code -->
            <div class="qr-canvas-wrap">
                <canvas id="qrCanvas"></canvas>
            </div>

            <!-- Infos réservation -->
            <div class="qr-infos" id="qrInfos"></div>

            <!-- Message -->
            <p class="qr-message">
                Présentez ce QR code pour accéder à la salle
            </p>

            <!-- Bouton imprimer -->
            <button class="btn btn-ghost" onclick="imprimerQR()">
                🖨️ Imprimer
            </button>

        </div>

    </div>
</div>

<!-- SCRIPT QR CODE -->
<!-- Charge la librairie QRious depuis CDN -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrious/4.0.2/qrious.min.js"></script>

<script>
// Affiche la modale avec le QR code
function afficherQR(id, salle, date, creneau) {

    // Affiche la modale
    document.getElementById('qrOverlay').classList.add('open');

    // Données encodées dans le QR code
    var data = 'RESERVATION #' + id
             + ' | SALLE: '    + salle
             + ' | DATE: '     + date
             + ' | CRENEAU: '  + creneau;

    // Génère le QR code dans le canvas
    new QRious({
        element:    document.getElementById('qrCanvas'),
        value:      data,
        size:       200,
        background: '#1b1f35',   // fond sombre
        foreground: '#7c6af7',   // violet
        level:      'H'          // haute correction d'erreur
    });

    // Affiche les infos sous le QR code
    document.getElementById('qrInfos').innerHTML =
        '<div class="qr-info-salle">' + salle + '</div>' +
        '<div class="qr-info-details">📅 ' + date + ' &nbsp;·&nbsp; 🕐 ' + creneau + '</div>' +
        '<div class="qr-info-id">Réservation #' + id + '</div>';
}

// Ferme la modale
function fermerQR() {
    document.getElementById('qrOverlay').classList.remove('open');
}

// Imprime le QR code
function imprimerQR() {
    window.print();
}

// Ferme avec la touche Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') fermerQR();
});
</script>
<?php require 'views/layouts/footer.php'; ?>