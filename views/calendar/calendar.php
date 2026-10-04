<?php
$pageTitle = 'Calendrier';
require 'views/layouts/header.php';
?>

<!-- MESSAGE FLASH -->
<?php if (isset($message) && $message): ?>
    <div class="alert alert-success">✅ <?= $message ?></div>
<?php endif; ?>

<!-- EN-TÊTE -->
<div class="page-header">
    <div>
        <h1>📅 Calendrier des réservations</h1>
        <p>
            <?php if (getUserRole() === 'administrateur'): ?>
                Toutes les réservations
            <?php else: ?>
                Vos réservations
            <?php endif; ?>
        </p>
    </div>
    <a href="index.php?page=reservation-create" class="btn btn-primary">
        ➕ Nouvelle réservation
    </a>
</div>

<!-- CALENDRIER -->
<div class="card calendar-card">

    <!-- NAVIGATION -->
    <div class="cal-nav">
        <button class="btn btn-ghost btn-sm" id="btnPrev">← Précédent</button>
        <h2 class="cal-titre" id="calTitre"></h2>
        <button class="btn btn-ghost btn-sm" id="btnNext">Suivant →</button>
    </div>

    <!-- LÉGENDE -->
    <div class="cal-legende">
        <span class="cal-leg-item">
            <span class="cal-dot dot-success"></span> Confirmée
        </span>
        <span class="cal-leg-item">
            <span class="cal-dot dot-danger"></span> Annulée
        </span>
        
    </div>

    <!-- JOURS DE LA SEMAINE -->
    <div class="cal-grid-header">
        <div>Lun</div>
        <div>Mar</div>
        <div>Mer</div>
        <div>Jeu</div>
        <div>Ven</div>
        <div>Sam</div>
        <div>Dim</div>
    </div>

    <!-- GRILLE DU CALENDRIER -->
    <div class="cal-grid" id="calGrid"></div>

</div>

<!-- MODALE DÉTAIL JOUR -->
<div class="cal-overlay" id="calOverlay" onclick="fermerDetail()">
    <div class="cal-modal" onclick="event.stopPropagation()">
        <div class="cal-modal-header">
            <h3 id="modalDate"></h3>
            <button class="qr-close" onclick="fermerDetail()">✕</button>
        </div>
        <div class="cal-modal-body" id="modalBody"></div>
        <div class="cal-modal-footer">
            <a href="index.php?page=reservation-create"
               class="btn btn-primary btn-sm">
                ➕ Réserver ce jour
            </a>
        </div>
    </div>
</div>

<!-- DONNÉES PHP → JS -->
<script>
// Toutes les réservations en JSON
var reservations = <?= json_encode($events) ?>;
var userRole     = '<?= getUserRole() ?>';
var userId       = <?= getUserId() ?>;

// Mois et année actuels
var aujourd = new Date();
var moisCourant = aujourd.getMonth();
var anneeCourante = aujourd.getFullYear();

// Noms des mois en français
var nomsMois = [
    'Janvier','Février','Mars','Avril','Mai','Juin',
    'Juillet','Août','Septembre','Octobre','Novembre','Décembre'
];

// Génère le calendrier
function genererCalendrier(mois, annee) {

    // Titre
    document.getElementById('calTitre').textContent =
        nomsMois[mois] + ' ' + annee;

    var grid = document.getElementById('calGrid');
    grid.innerHTML = '';

    // Premier jour du mois (0=dim, 1=lun...)
    var premierJour = new Date(annee, mois, 1).getDay();
    // Ajuste pour commencer lundi (0=lun)
    premierJour = premierJour === 0 ? 6 : premierJour - 1;

    // Nombre de jours dans le mois
    var nbJours = new Date(annee, mois + 1, 0).getDate();

    // Cellules vides avant le 1er
    for (var i = 0; i < premierJour; i++) {
        var vide = document.createElement('div');
        vide.className = 'cal-day cal-day-vide';
        grid.appendChild(vide);
    }

    // Jours du mois
    for (var j = 1; j <= nbJours; j++) {

        var dateStr = annee + '-'
            + String(mois + 1).padStart(2, '0') + '-'
            + String(j).padStart(2, '0');

        // Filtre les réservations de ce jour
        var resaDuJour = reservations.filter(function(r) {
            return r.date === dateStr;
        });

        var day = document.createElement('div');
        day.className = 'cal-day';

        // Aujourd'hui ?
        var auj = new Date();
        if (j === auj.getDate() &&
            mois === auj.getMonth() &&
            annee === auj.getFullYear()) {
            day.classList.add('cal-day-today');
        }

        // Numéro du jour
        var num = document.createElement('div');
        num.className = 'cal-day-num';
        num.textContent = j;
        day.appendChild(num);

        // Réservations du jour
        resaDuJour.forEach(function(r) {
            var event = document.createElement('div');
            event.className = 'cal-event';

            // Couleur selon statut
            if (r.statut === 'confirmee') {
                event.classList.add('cal-event-success');
            } else {
                event.classList.add('cal-event-danger');
            }

            event.textContent = '🏠 ' + r.title;
            day.appendChild(event);
        });

        // Clic → affiche détail
        if (resaDuJour.length > 0) {
            day.style.cursor = 'pointer';
            day.onclick = (function(date, resas) {
                return function() {
                    afficherDetail(date, resas);
                };
            })(dateStr, resaDuJour);
        }

        grid.appendChild(day);
    }
}

// Affiche le détail d'un jour
function afficherDetail(date, resas) {

    // Formate la date en français
    var d = new Date(date + 'T00:00:00');
    var options = {
        weekday: 'long',
        day:     'numeric',
        month:   'long',
        year:    'numeric'
    };
    document.getElementById('modalDate').textContent =
        d.toLocaleDateString('fr-FR', options);

    var body = document.getElementById('modalBody');
    body.innerHTML = '';

    resas.forEach(function(r) {
        var item = document.createElement('div');
        item.className = 'modal-resa-item';

        var statut = r.statut === 'confirmee'
            ? '<span class="badge-cal success">✅ Confirmée</span>'
            : '<span class="badge-cal danger">❌ Annulée</span>';

        // Admin voit le nom de l'utilisateur
        var userInfo = userRole === 'administrateur'
            ? '<div class="modal-user">👤 ' + r.user + '</div>'
            : '';

        item.innerHTML =
            '<div class="modal-salle">🏠 ' + r.title + '</div>' +
            '<div class="modal-creneau">🕐 ' + r.creneau + '</div>' +
            userInfo +
            statut;

        body.appendChild(item);
    });

    document.getElementById('calOverlay').classList.add('open');
}

// Ferme la modale
function fermerDetail() {
    document.getElementById('calOverlay').classList.remove('open');
}

// Navigation mois précédent
document.getElementById('btnPrev').onclick = function() {
    moisCourant--;
    if (moisCourant < 0) {
        moisCourant = 11;
        anneeCourante--;
    }
    genererCalendrier(moisCourant, anneeCourante);
};

// Navigation mois suivant
document.getElementById('btnNext').onclick = function() {
    moisCourant++;
    if (moisCourant > 11) {
        moisCourant = 0;
        anneeCourante++;
    }
    genererCalendrier(moisCourant, anneeCourante);
};

// Ferme avec Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') fermerDetail();
});

// Lance le calendrier
genererCalendrier(moisCourant, anneeCourante);
</script>

<?php require 'views/layouts/footer.php'; ?>