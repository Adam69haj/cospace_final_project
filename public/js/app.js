// =============================================
// APP.JS — JavaScript global CoSpace
// =============================================


// =============================================
// 1. LIEN ACTIF DANS LA SIDEBAR
// Met en surbrillance le lien de la page actuelle
// =============================================
document.addEventListener('DOMContentLoaded', function() {

    // Récupère la page actuelle dans l'URL
    // Exemple : index.php?page=admin-rooms → 'admin-rooms'
    var urlParams = new URLSearchParams(window.location.search);
    var pageCourante = urlParams.get('page');

    // Cherche tous les liens de la sidebar
    var liens = document.querySelectorAll('.nav-item');

    liens.forEach(function(lien) {
        // Récupère la page du lien
        var lienUrl    = new URLSearchParams(lien.getAttribute('href').split('?')[1]);
        var lienPage   = lienUrl.get('page');

        // Si c'est la même page → ajoute la classe active
        if (lienPage === pageCourante) {
            lien.classList.add('active');
        }
    });
});


// =============================================
// 2. AUTO-HIDE DES ALERTES
// Les alertes disparaissent après 4 secondes
// =============================================
document.addEventListener('DOMContentLoaded', function() {

    var alertes = document.querySelectorAll('.alert');

    alertes.forEach(function(alerte) {
        // Attend 4 secondes puis fait disparaître l'alerte
        setTimeout(function() {
            alerte.style.transition = 'opacity 0.5s';
            alerte.style.opacity    = '0';

            // Supprime l'alerte après la transition
            setTimeout(function() {
                alerte.remove();
            }, 500);
        }, 4000);
    });
});


// =============================================
// 3. CONFIRMATION AVANT SUPPRESSION
// Remplace le confirm() natif par une version
// plus jolie si on veut (optionnel)
// =============================================

// On utilise onclick="return confirm('...')"
// directement dans le HTML — simple et efficace


// =============================================
// 4. FORMULAIRE DE RÉSERVATION
// Empêche de soumettre si pas de créneau choisi
// =============================================
document.addEventListener('DOMContentLoaded', function() {

    // Cherche le formulaire de confirmation (étape 2)
    var formReservation = document.querySelector('form[action*="reservation-store"]');

    if (formReservation) {
        formReservation.addEventListener('submit', function(e) {

            // Vérifie qu'un créneau radio est sélectionné
            var creneauChoisi = document.querySelector('input[name="creneau_id"]:checked');

            if (!creneauChoisi) {
                e.preventDefault(); // bloque la soumission
                alert('Veuillez choisir un créneau !');
            }
        });
    }
});


// =============================================
// 5. RECHERCHE EN TEMPS RÉEL (optionnel)
// Filtre le tableau pendant que l'utilisateur tape
// =============================================
document.addEventListener('DOMContentLoaded', function() {

    var inputRecherche = document.querySelector('.search-bar input');
    var tableau        = document.querySelector('.tableau tbody');

    // Si pas de recherche ou de tableau → on arrête
    if (!inputRecherche || !tableau) return;

    inputRecherche.addEventListener('input', function() {

        var motCle = this.value.toLowerCase().trim();
        var lignes = tableau.querySelectorAll('tr');

        lignes.forEach(function(ligne) {
            var texte = ligne.textContent.toLowerCase();

            // Si le texte contient le mot clé → affiche la ligne
            // Sinon → cache la ligne
            if (texte.includes(motCle)) {
                ligne.style.display = '';
            } else {
                ligne.style.display = 'none';
            }
        });
    });
});
// ===== DARK / LIGHT MODE =====

// 1. Initialise le thème au chargement
function initTheme() {
    var theme = localStorage.getItem('cs-theme') || 'dark';
    appliquerTheme(theme);
}

// 2. Bascule entre dark et light
function toggleTheme() {
    var themeActuel  = localStorage.getItem('cs-theme') || 'dark';
    var nouveauTheme = themeActuel === 'dark' ? 'light' : 'dark';
    appliquerTheme(nouveauTheme);
    localStorage.setItem('cs-theme', nouveauTheme);
}

// 3. Applique le thème
function appliquerTheme(theme) {
    if (theme === 'light') {
        document.documentElement.setAttribute('data-theme', 'light');
        document.getElementById('themeToggle').textContent = '🌙';
    } else {
        document.documentElement.removeAttribute('data-theme');
        document.getElementById('themeToggle').textContent = '☀️';
    }
}

// 4. Lance au chargement
document.addEventListener('DOMContentLoaded', function() {
    initTheme();
});

// ===== WIDGET ASSISTANT IA COSPACE =====
function toggleCsChat() {
    var panel = document.getElementById('csChatPanel');
    if (!panel) return;
    panel.classList.toggle('open');
    if (panel.classList.contains('open')) {
        var input = document.getElementById('csChatInput');
        if (input) input.focus();
    }
}
function appendCsChatBubble(text, sender) {
    var box = document.getElementById('csChatMessages');
    if (!box) return null;
    var div = document.createElement('div');
    div.className = 'cs-msg ' + (sender === 'user' ? 'cs-msg-user' : 'cs-msg-bot');
    div.setAttribute('dir', 'auto'); // RTL automatique pour l'arabe
    div.textContent = text;          // textContent protège contre le XSS
    box.appendChild(div);
    box.scrollTop = box.scrollHeight;
    return div;
}
// Affiche la réponse du bot et ajoute un bouton si [CONFIRM_CANCEL:ID] est présent
function renderCsBotReply(bubble, rawText) {
    var match     = rawText.match(/\[CONFIRM_CANCEL:(\d+)\]/);
    var cleanText = rawText.replace(/\[CONFIRM_CANCEL:\d+\]/g, '').trim();
    bubble.textContent = cleanText;
    if (match) {
        var resaId = parseInt(match[1], 10);
        var btn    = document.createElement('button');
        btn.type      = 'button';
        btn.className = 'btn btn-danger btn-sm';
        btn.style.marginTop = '10px';
        btn.style.display   = 'block';
        btn.textContent     = '❌ Confirmer l\'annulation (#' + resaId + ')';
        btn.onclick = function() {
            confirmCsCancel(resaId, btn);
        };
        bubble.appendChild(btn);
    }
}
// Appelle la route PHP d'annulation après clic sur le bouton de confirmation
function confirmCsCancel(resaId, btn) {
    var roleEl = document.querySelector('.profil-role');
    var role   = roleEl ? roleEl.textContent.trim() : '';
    var reason = '';
    // Si administrateur, demande le motif obligatoire
    if (role === 'administrateur') {
        reason = prompt('Motif d\'annulation (obligatoire pour l\'administrateur) :');
        if (reason === null) return; // Annulé par l'admin
    }
    btn.disabled    = true;
    btn.textContent = '⏳ Vérification...';
    fetch('index.php?page=assistant-cancel', {
        method:  'POST',
        headers: { 'Content-Type': 'application/json' },
        body:    JSON.stringify({ reservation_id: resaId, reason: reason })
    })
    .then(function(res) { return res.json(); })
    .then(function(data) {
        if (data.success) {
            btn.textContent = '✅ Annulée';
            appendCsChatBubble(data.message, 'bot');
        } else {
            btn.textContent = '⚠️ Refusée';
            appendCsChatBubble(data.error || 'Annulation refusée.', 'bot');
        }
    })
    .catch(function() {
        btn.disabled    = false;
        btn.textContent = '❌ Réessayer (#' + resaId + ')';
        appendCsChatBubble('Erreur de connexion au serveur.', 'bot');
    });
}
function sendCsChatMessage(e) {
    e.preventDefault();
    var input = document.getElementById('csChatInput');
    var btn   = document.getElementById('csChatSend');
    if (!input || !btn) return;
    var message = input.value.trim();
    if (!message) return;
    // 1. Affiche le message de l'utilisateur
    appendCsChatBubble(message, 'user');
    input.value  = '';
    btn.disabled = true;
    // 2. Affiche un indicateur de chargement
    var loadingBubble = appendCsChatBubble('...', 'bot');
    // 3. Appelle la route PHP sécurisée
    fetch('index.php?page=assistant-chat', {
        method:  'POST',
        headers: { 'Content-Type': 'application/json' },
        body:    JSON.stringify({ message: message })
    })
    .then(function(res) { return res.json(); })
    .then(function(data) {
        if (loadingBubble) {
            renderCsBotReply(loadingBubble, data.reply || data.error || 'Aucune réponse.');
        }
    })
    .catch(function() {
        if (loadingBubble) {
            loadingBubble.textContent = 'Erreur de connexion au serveur.';
        }
    })
    .finally(function() {
        btn.disabled = false;
        input.focus();
    });
}