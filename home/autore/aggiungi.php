<?php
set_time_limit(300);
ini_set('memory_limit', '512M');

require_once '../../api/AuthorProcessor.php';
require_once '../../db/connection.php';

$result = null;
$scopus_id = '';
$name = '';
$surname = '';
$dblp_pid = '';
$search_method = '';
$processAuthor = new AuthorProcessor($mysqli);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $scopus_id = trim($_POST['scopus_id'] ?? '');
    $search_method = $_POST['search_method'] ?? 'name';
    $result = false;
    if (!empty($scopus_id) && is_numeric($scopus_id)) {
        if ($search_method === 'name') {
            $name = trim($_POST['name'] ?? '');
            $surname = trim($_POST['surname'] ?? '');
            if (!empty($name) && !empty($surname)) {
                $result = $processAuthor->processAuthorComplete($scopus_id, $name, $surname);
            }
        } else if ($search_method === 'pid') {
            $dblp_pid = trim($_POST['dblp_pid'] ?? '');
            if (!empty($dblp_pid)) {
                $result = $processAuthor->processAuthorCompleteByPid($scopus_id, $dblp_pid);
            }
        }
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
            <div id="name-fields">
                <label for="name">Nome autore:</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" placeholder="Es: Mario">

                <label for="surname">Cognome autore:</label>
                <input type="text" id="surname" name="surname" value="<?php echo htmlspecialchars($surname); ?>" placeholder="Es: Rossi">
            </div>

            <div id="pid-field" style="display: none;">
                <label for="dblp_pid">DBLP PID:</label>
                <input type="text" id="dblp_pid" name="dblp_pid" value="<?php echo htmlspecialchars($dblp_pid); ?>" placeholder="Es: 199/0048">
            </div>

            <label>
                <input type="radio" name="search_method" value="name" checked onchange="toggleFields()"> Usa Nome e Cognome
            </label>
            <label>
                <input type="radio" name="search_method" value="pid" onchange="toggleFields()"> Usa DBLP PID
            </label>

            <script>
                function toggleFields() {
                    const method = document.querySelector('input[name="search_method"]:checked').value;
                    const nameFields = document.getElementById('name-fields');
                    const pidField = document.getElementById('pid-field');

                    if (method === 'name') {
                        nameFields.style.display = 'block';
                        pidField.style.display = 'none';
                        document.getElementById('name').required = true;
                        document.getElementById('surname').required = true;
                        document.getElementById('dblp_pid').required = false;
                    } else {
                        nameFields.style.display = 'none';
                        pidField.style.display = 'block';
                        document.getElementById('name').required = false;
                        document.getElementById('surname').required = false;
                        document.getElementById('dblp_pid').required = true;
                    }
                }
                toggleFields();
            </script>

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