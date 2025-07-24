<?php
set_time_limit(600);
ini_set('memory_limit', '512M');

require_once 'api/AuthorProcessor.php';
require_once 'db/connection.php';
require_once 'db/AuthorsRepository.php';

$success = false;
$error_message = '';

try {
    $authorsRepo = new AuthorsRepository($mysqli);

    $authors = $authorsRepo->getAllAuthors();

    if (empty($authors)) {
        throw new Exception("Nessun autore nel database");
    }

    if (!$authorsRepo->removeAuthorsFromDb()) {
        throw new Exception("Errore durante la pulizia delle tabelle autori");
    }

    $processAuthor = new AuthorProcessor($mysqli);

    foreach ($authors as $author) {
        $scopus_id = trim($author['scopus_id']);
        $name = trim($author['nome']);
        $surname = trim($author['cognome']);

        $processAuthor->processAuthorComplete($scopus_id, $name, $surname);
    }

    $success = true;
} catch (Exception $e) {
    $error_message = "Errore durante l'aggiornamento database: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <title>Aggiorna Database - DocRanks</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body>
    <main>
        <h1>Aggiornamento Database</h1>

        <section>
            <?php if ($success): ?>
                <p><strong>Database aggiornato</strong></p>
            <?php else: ?>
                <p><strong>😭 Si è verificato un errore durante l'aggiornamento del database.</strong></p>
                <pre><?= htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8') ?></pre>
            <?php endif; ?>
        </section>

        <p><a href="home/">← Torna alla home</a></p>
    </main>
</body>

</html>