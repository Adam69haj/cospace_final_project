<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CoSpace — Votre avis</title>
    <link rel="stylesheet" href="public/css/style.css">
</head>
<body>

<div class="feedback-page">

    <!-- LOGO -->
    <div class="login-logo">
        <h1>Co<span>Space</span></h1>
    </div>

    <!-- CARTE FEEDBACK -->
    <div class="feedback-card">

        <!-- EN-TÊTE -->
        <div class="feedback-header">
            <div class="feedback-icon">👋</div>
            <h2>Avant de partir...</h2>
            <p>Votre avis nous aide à améliorer CoSpace !</p>
        </div>

        <!-- FORMULAIRE -->
        <form method="POST" action="index.php?page=submit-feedback">

            <!-- ÉTOILES -->
            <div class="stars-section">
                <label>Comment évaluez-vous votre expérience ?</label>
                <div class="stars-wrap">
                    <input type="radio" name="note" id="s5" value="5">
                    <label for="s5" class="star">⭐</label>
                    <input type="radio" name="note" id="s4" value="4">
                    <label for="s4" class="star">⭐</label>
                    <input type="radio" name="note" id="s3" value="3">
                    <label for="s3" class="star">⭐</label>
                    <input type="radio" name="note" id="s2" value="2">
                    <label for="s2" class="star">⭐</label>
                    <input type="radio" name="note" id="s1" value="1">
                    <label for="s1" class="star">⭐</label>
                </div>
                <div class="stars-label" id="starsLabel">
                    Cliquez pour noter
                </div>
            </div>

            <!-- MESSAGE -->
            <div class="form-group">
                <label>Un commentaire ou un problème ? (optionnel)</label>
                <textarea
                    name="message"
                    placeholder="Décrivez votre expérience, signalez un problème..."
                    rows="4"
                ></textarea>
            </div>

            <!-- BOUTONS -->
            <div class="feedback-actions">
                <button type="submit" class="btn btn-primary">
                    ✅ Envoyer et quitter
                </button>
                <a href="index.php?page=do-logout" class="btn btn-ghost">
                    Ignorer et quitter
                </a>
            </div>

        </form>

    </div>

</div>

<!-- JS ÉTOILES -->
<script>
var labels = {
    1: '😞 Très mauvais',
    2: '😐 Mauvais',
    3: '🙂 Correct',
    4: '😊 Bien',
    5: '🤩 Excellent !'
};

// Quand on clique sur une étoile
document.querySelectorAll('input[name="note"]').forEach(function(input) {
    input.addEventListener('change', function() {
        var note = parseInt(this.value);
        document.getElementById('starsLabel').textContent = labels[note];
        document.getElementById('starsLabel').style.color = 'var(--warning)';
    });
});
</script>

</body>
</html>