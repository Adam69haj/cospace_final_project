<?php
$pageTitle = 'Mon espace';
require 'views/layouts/header.php';
?>

<?php if (isset($message) && $message): ?>
    <div class="alert alert-success">✅ <?= $message ?></div>
<?php endif; ?>

<div class="page-header">
    <div>
        <h1>👋 Bonjour, <?= getUserNom() ?> !</h1>
        <p>Vos prochaines réservations</p>
    </div>
    <a href="index.php?page=reservation-create" class="btn btn-primary">
        ➕ Réserver une salle
    </a>
</div>

<?php if (empty($prochaines)): ?>

    <div class="empty-state">
        <div class="empty-icon">📅</div>
        <h3>Aucune réservation à venir</h3>
        <p>Explorez nos salles et faites votre première réservation !</p>
        <a href="index.php?page=member-rooms" class="btn btn-primary">
            🔍 Explorer les salles
        </a>
    </div>

<?php else: ?>

    <div class="reservations-liste">

        <?php foreach ($prochaines as $resa): ?>

        <?php
        $dateResa    = new DateTime($resa['date_reservation']);
        $maintenant  = new DateTime();
        $difference  = $maintenant->diff($dateResa);
        $creneauPasse = false;
if ($resa['date_reservation'] === date('Y-m-d')) {
    $parts        = explode(' - ', $resa['creneau_libelle']);
    $heureFin     = $parts[1] ?? '00:00';
    $creneauPasse = $heureFin . ':00' <= date('H:i:s');
}
$dateResa   = new DateTime($resa['date_reservation'] . ' 00:00:00');
$maintenant = new DateTime();
$diffHeures = ($dateResa->getTimestamp() - $maintenant->getTimestamp()) / 3600;
        $peutAnnuler = !$creneauPasse && $diffHeures >= 24;
        ?>

        <div class="resa-card">

            <div class="resa-icon">🏠</div>

            <div class="resa-infos">
                <div class="resa-salle">
                    <?= $resa['salle_nom'] ?>
                </div>
                <div class="resa-details">
                    📅 <?= date('d/m/Y', strtotime($resa['date_reservation'])) ?>
                    &nbsp;·&nbsp;
                    🕐 <?= $resa['creneau_libelle'] ?>
                </div>
            </div>

            <span class="badge badge-success">✅ Confirmée</span>

            <!-- BOUTON QR CODE ← AJOUTÉ -->
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

            <?php if ($creneauPasse): ?>
    <!-- Créneau terminé -->
    <span class="badge badge-warning">⏰ Terminé</span>
            <?php elseif ($peutAnnuler): ?>
                <a href="index.php?page=reservation-cancel&id=<?= $resa['id'] ?>"
                   class="btn btn-danger btn-sm"
                   onclick="return confirm('Annuler cette réservation ?')">
                    Annuler
                </a>
            <?php else: ?>
                <span class="badge-24h" title="Moins de 24h">
                    🔒 Moins de 24h
                </span>
            <?php endif; ?>

        </div>

        <?php endforeach; ?>

    </div>

    <div class="voir-tout">
        <a href="index.php?page=my-reservations" class="btn btn-ghost">
            Voir tout l'historique →
        </a>
    </div>

<?php endif; ?>

<!-- ACCÈS RAPIDES -->
<div class="acces-rapides" style="margin-top:30px">
    <a href="index.php?page=member-rooms" class="acces-card">
        <div class="acces-icon">🔍</div>
        <div class="acces-titre">Explorer les salles</div>
        <div class="acces-desc">Voir les salles disponibles</div>
    </a>
    <a href="index.php?page=reservation-create" class="acces-card">
        <div class="acces-icon">➕</div>
        <div class="acces-titre">Nouvelle réservation</div>
        <div class="acces-desc">Réserver une salle</div>
    </a>
    <a href="index.php?page=my-reservations" class="acces-card">
        <div class="acces-icon">📅</div>
        <div class="acces-titre">Mes réservations</div>
        <div class="acces-desc">Voir tout l'historique</div>
    </a>
</div>

<!-- MODALE QR CODE ← AJOUTÉE -->
<div class="qr-overlay" id="qrOverlay" onclick="fermerQR()">
    <div class="qr-modal" onclick="event.stopPropagation()">
        <div class="qr-header">
            <h3>🎫 QR Code de réservation</h3>
            <button class="qr-close" onclick="fermerQR()">✕</button>
        </div>
        <div class="qr-body">
            <div class="qr-canvas-wrap">
                <canvas id="qrCanvas"></canvas>
            </div>
            <div class="qr-infos" id="qrInfos"></div>
            <p class="qr-message">
                Présentez ce QR code pour accéder à la salle
            </p>
            <button class="btn btn-ghost" onclick="imprimerQR()">
                🖨️ Imprimer
            </button>
        </div>
    </div>
</div>

<!-- SCRIPT QR ← AJOUTÉ -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrious/4.0.2/qrious.min.js"></script>
<script>
function afficherQR(id, salle, date, creneau) {
    document.getElementById('qrOverlay').classList.add('open');
    var data = 'RESERVATION #' + id
             + ' | SALLE: '    + salle
             + ' | DATE: '     + date
             + ' | CRENEAU: '  + creneau;
    new QRious({
        element:    document.getElementById('qrCanvas'),
        value:      data,
        size:       200,
        background: '#1b1f35',
        foreground: '#7c6af7',
        level:      'H'
    });
    document.getElementById('qrInfos').innerHTML =
        '<div class="qr-info-salle">'   + salle   + '</div>' +
        '<div class="qr-info-details">📅 ' + date + ' &nbsp;·&nbsp; 🕐 ' + creneau + '</div>' +
        '<div class="qr-info-id">Réservation #' + id + '</div>';
}
function fermerQR() {
    document.getElementById('qrOverlay').classList.remove('open');
}
function imprimerQR() {
    window.print();
}
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') fermerQR();
});
</script>

<?php require 'views/layouts/footer.php'; ?>