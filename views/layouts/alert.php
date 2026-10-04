<?php
// Affiche le message flash s'il existe
$message = getMessage();
if ($message):
?>
    <div class="alert alert-success">
        ✅ <?= $message ?>
    </div>
<?php endif; ?>