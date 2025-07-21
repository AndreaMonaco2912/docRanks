<?php
set_time_limit(600);
ini_set('memory_limit', '512M');

$success = false;
$error_message = '';

try {
    require_once 'db/reset_database.php';
    require_once 'db/init_database.php';
    $success = true;
} catch (Exception $e) {
    $error_message = "Errore durante reset/init database: " . $e->getMessage();
    error_log($error_message);
}
?>
<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <title>Reset Database - DocRanks</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body>
    <main role="main">
        <h1>Reset e Inizializzazione Database</h1>

        <section aria-live="polite">
            <?php if ($success): ?>
                <p><strong>Il database è stato resettato e inizializzato correttamente.</strong></p>
            <?php else: ?>
                <p><strong>😭 Si è verificato un errore durante il reset del database.</strong></p>
                <pre><?= htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8') ?></pre>
            <?php endif; ?>
        </section>

        <p><a href="home/">← Torna alla home</a></p>
    </main>
</body>

</html>