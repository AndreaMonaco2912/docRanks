<?php

require_once '../../db/connection.php';
require_once '../../db/publication/OthersRepository.php';
require_once '../../db/AuthorRepository.php';
require_once '../../includes/navigation.php';
require_once '../../includes/bootstrap.php';

$scopus_id = isset($_GET['scopus_id']) ? trim($_GET['scopus_id']) : '';

if (empty($scopus_id)) {
    header('Location: index.php');
    exit;
}

$authorRepo = new AuthorRepository($mysqli, $scopus_id);
$othersRepo = new OthersRepository($mysqli);
$author = $authorRepo->getProfile();

if (!$author) {
    header('Location: index.php');
    exit;
}

$other_publications = $othersRepo->getOthersDetails($scopus_id);
?>

<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DocRanks - Altre Pubblicazioni di <?php echo htmlspecialchars($author['nome'] . ' ' . $author['cognome']); ?></title>
    <?php echo $bootstrap_css;
    echo $bootstrap_icons; ?>
    <link rel="stylesheet" href="../../docranks.css">
</head>

<body>
    <?php renderNavigation($scopus_id, 'others'); ?>

    <?php renderPublicationHeader($author, $scopus_id, 'Altre Pubblicazioni', '📚'); ?>

    <main>
        <h3>Elenco Altre Pubblicazioni</h3>

        <?php if (empty($other_publications)): ?>
            <p><em>Nessuna altra pubblicazione trovata per questo autore.</em></p>
        <?php else: ?>

            <?php foreach ($other_publications as $pub): ?>
                <section class="card mb-3">
                    <h4 class="card-header"><?php echo htmlspecialchars($pub['titolo']); ?></h4>

                    <table class="table table-hover card-body">
                        <tr>
                            <td><strong>DOI</strong></td>
                            <td><?php echo htmlspecialchars($pub['DOI']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Anno</strong></td>
                            <td><?php echo $pub['anno']; ?></td>
                        </tr>
                        <tr>
                            <td><strong>Tipo</strong></td>
                            <td><?php echo htmlspecialchars($pub['tipo']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Venue DBLP</strong></td>
                            <td><?php echo htmlspecialchars($pub['dblp_venue']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Autori</strong></td>
                            <td><?php echo htmlspecialchars($pub['nome_autori']); ?></td>
                        </tr>
                    </table>
                </section>
            <?php endforeach; ?>

        <?php endif; ?>

    </main>

    <?php renderActions($scopus_id, 'others'); ?>

    <?php echo $bootstrap_js; ?>
</body>

</html>

<?php
$mysqli->close();
?>