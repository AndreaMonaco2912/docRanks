<?php
set_time_limit(300);
ini_set('memory_limit', '512M');

require_once '../../api/AuthorProcessor.php';
require_once '../../db/connection.php';

$result = null;
$scopus_id = '';
$name = '';
$surname = '';
$processAuthor = new AuthorProcessor($mysqli);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $scopus_id = trim($_POST['scopus_id'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $surname = trim($_POST['surname'] ?? '');

    if (!empty($scopus_id) && is_numeric($scopus_id)) {
        if (!empty($name) && !empty($surname)) {
            $result = $processAuthor->processAuthorComplete($scopus_id, $name, $surname);
        } else {
            $result = false;
        }
    } else {
        $result = false;
    }
}
?>

<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DocRanks - Aggiungi Autore</title>
</head>

<body>
    <nav>
        <a href="../index.php">Home</a> |
        <a href="index.php">Cerca Autore</a> |
        <a href="aggiungi.php">Aggiungi Autore</a> |
        <a href="../../reset_and_init.php">Reset Database</a>
    </nav>

    <h1>DocRanks - Aggiungi Nuovo Autore</h1>

    <main>
        <h2>Importa Autore da Scopus e DBLP</h2>

        <form method="post">
            <label for="scopus_id">Scopus ID dell'autore (obbligatorio):</label>
            <input type="text"
                id="scopus_id"
                name="scopus_id"
                value="<?php echo htmlspecialchars($scopus_id); ?>"
                placeholder="Es: 57193867382"
                pattern="[0-9]+"
                title="Inserisci solo numeri"
                required>

            <label for="name">Nome autore (obbligatorio):</label>
            <input type="text"
                id="name"
                name="name"
                value="<?php echo htmlspecialchars($name); ?>"
                placeholder="Es: Mario"
                required>

            <label for="surname">Cognome autore (obbligatorio):</label>
            <input type="text"
                id="surname"
                name="surname"
                value="<?php echo htmlspecialchars($surname); ?>"
                placeholder="Es: Rossi"
                required>

            <button type="submit">Importa Autore</button>
        </form>
    </main>
    <?php if ($result !== null): ?>
        <section>
            <?php if ($result): ?>
                <h3>Importazione Completata</h3>

                <p>L'autore è stato importato con successo nel database!</p>

                <a href="index.php?scopus_id=<?php echo urlencode($scopus_id); ?>">
                    Visualizza Profilo Autore
                </a>

            <?php else: ?>
                <p>Errore Importazione</p>
            <?php endif; ?>
        </section>

    <?php endif; ?>

    <aside>
        <h3>Come funziona l'importazione</h3>

        <p>Il sistema importa automaticamente:</p>
        <ul>
            <li><strong>Da Scopus:</strong> Dati autore, pubblicazioni con citazioni</li>
            <li><strong>Da DBLP:</strong> Elenco completo pubblicazioni con DOI</li>
            <li><strong>Integrazione:</strong> Unisce i dati per creare un profilo completo</li>
        </ul>
    </aside>
</body>

</html>