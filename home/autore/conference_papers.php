<?php

require_once '../../db/connection.php';
require_once '../../db/publication/PaperRepository.php';
require_once '../../db/AuthorRepository.php';
require_once '../../includes/publication_handlers.php';
require_once '../../includes/navigation.php';
require_once '../../includes/bootstrap.php';

$scopus_id = isset($_GET['scopus_id']) ? trim($_GET['scopus_id']) : '';

if (empty($scopus_id)) {
    header('Location: index.php');
    exit;
}

handleFWCIUpdate($mysqli, $scopus_id, 'paper');
handleAcronymUpdate($mysqli, $scopus_id);

$authorRepo = new AuthorRepository($mysqli, $scopus_id);
$paperRepo = new PaperRepository($mysqli);
$author = $authorRepo->getProfile();

if (!$author) {
    header('Location: index.php');
    exit;
}

$conference_papers = $paperRepo->getConferencePapersDetails($scopus_id);

$stats = calculatePublicationStats($conference_papers);
$ranking_distribution = [];

foreach ($conference_papers as $paper) {
    $ranking = $paper['ranking_conferenza'] ?? 'F';
    $ranking_distribution[$ranking] = ($ranking_distribution[$ranking] ?? 0) + 1;
}

arsort($ranking_distribution);
?>

<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DocRanks - Conference Papers di <?php echo htmlspecialchars($author['nome'] . ' ' . $author['cognome']); ?></title>
    <?php echo $bootstrap_css;
    echo $bootstrap_icons; ?>
    <link rel="stylesheet" href="../../docranks.css">
</head>

<body>
    <?php renderNavigation($scopus_id, 'conferences'); ?>

    <?php renderPublicationHeader($author, $scopus_id, 'Conference Papers', '📄'); ?>

    <?php renderStatsTable($stats); ?>

    <?php renderYearDistributionTable($stats['by_year']); ?>
    <section>
        <h4>Distribuzione per Ranking Conferenze</h4>

        <table class="table table-hover">
            <tr>
                <th>Ranking</th>
                <th>Numero Papers</th>
                <th>Percentuale</th>
            </tr>
            <?php foreach ($ranking_distribution as $ranking => $count): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($ranking); ?></strong></td>
                    <td><?php echo $count; ?></td>
                    <td><?php echo round(($count / $stats['total']) * 100, 1); ?>%</td>
                </tr>
            <?php endforeach; ?>
        </table>
    </section>

    <main>
        <h3>Elenco Completo Conference Papers</h3>

        <?php if (empty($conference_papers)): ?>
            <p><em>Nessun conference paper trovato per questo autore.</em></p>
        <?php else: ?>

            <?php foreach ($conference_papers as $paper): ?>
                <article class="card mb-3">
                    <h4 class="card-header"><?php echo htmlspecialchars($paper['titolo']); ?></h4>

                    <table class="table table-hover card-body">
                        <tr>
                            <td><strong>DOI</strong></td>
                            <td><?php echo htmlspecialchars($paper['DOI']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Anno</strong></td>
                            <td><?php echo $paper['anno']; ?></td>
                        </tr>
                        <?php renderEditableAcronym($paper); ?>
                        <tr>
                            <td><strong>Nome Conferenza</strong></td>
                            <td>
                                <?php
                                if ($paper['titolo_conferenza']) {
                                    echo htmlspecialchars($paper['titolo_conferenza']);
                                } else {
                                    echo htmlspecialchars($paper['acronimo_dblp'] ?? 'N/A');
                                }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Venue DBLP Originale</strong></td>
                            <td><?php echo htmlspecialchars($paper['acronimo_dblp'] ?? 'N/A'); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Ranking Conferenza</strong></td>
                            <td>
                                <?php
                                $ranking = $paper['ranking_conferenza'];
                                if ($ranking) {
                                    $color = '';
                                    switch ($ranking) {
                                        case 'A*':
                                            $color = 'color: green; font-weight: bold;';
                                            break;
                                        case 'A':
                                            $color = 'color: blue; font-weight: bold;';
                                            break;
                                        case 'B':
                                            $color = 'color: orange; font-weight: bold;';
                                            break;
                                        case 'C':
                                            $color = 'color: red;';
                                            break;
                                        case 'F':
                                            $color = 'color: gray;';
                                            break;
                                    }
                                    echo "<span style='{$color}'>{$ranking}</span>";
                                } else {
                                    echo "<span style='color: gray;'>F</span>";
                                }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Citazioni</strong></td>
                            <td><?php echo $paper['citation_count'] ?? '<em>N/A</em>'; ?></td>
                        </tr>
                        <tr>
                            <td><strong>EFWCI</strong></td>
                            <td>
                                <?php
                                if ($paper['EFWCI'] !== null) {
                                    echo number_format($paper['EFWCI'], 3);
                                } else {
                                    echo 'Non disponibile';
                                }
                                ?>
                            </td>
                        </tr>

                        <?php renderEditableFWCI($paper); ?>

                        <tr>
                            <td><strong>Scopus ID</strong></td>
                            <td><?php echo $paper['pub_scopus_id'] ? htmlspecialchars($paper['pub_scopus_id']) : '<em>N/A</em>'; ?></td>
                        </tr>
                        <tr>
                            <td><strong>Numero Autori</strong></td>
                            <td><?php echo $paper['numero_autori']; ?></td>
                        </tr>
                        <tr>
                            <td><strong>Autori</strong></td>
                            <td><?php echo htmlspecialchars($paper['nome_autori']); ?></td>
                        </tr>
                    </table>
                </article>
            <?php endforeach; ?>

        <?php endif; ?>

    </main>

    <?php renderActions($scopus_id, 'conferences'); ?>

    <?php renderEditingJavaScript(); ?>

    <?php echo $bootstrap_js; ?>
</body>


</html>

<?php
$mysqli->close();
?>