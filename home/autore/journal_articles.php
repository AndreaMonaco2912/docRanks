<?php
require_once '../../db/connection.php';
require_once '../../db/repositories/ArticleRepository.php';
require_once '../../db/repositories/AuthorRepository.php';
require_once '../../db/repositories/JournalRepository.php';
require_once '../../includes/publication_handlers.php';
require_once '../../includes/navigation.php';
require_once '../../includes/bootstrap.php';

$scopus_id = isset($_GET['scopus_id']) ? trim($_GET['scopus_id']) : '';

if (empty($scopus_id)) {
    header('Location: index.php');
    exit;
}

handleJournalIdUpdate($mysqli, $scopus_id) ?? handleFWCIUpdate($mysqli, $scopus_id, 'article');

$authorRepo = new AuthorRepository($mysqli, $scopus_id);
$author = $authorRepo->getProfile();

if (!$author) {
    header('Location: index.php?error=author_not_found');
    exit;
}

$articleRepo = new ArticleRepository($mysqli);
$journal_articles = $articleRepo->getJournalArticlesDetails($scopus_id);

$stats = calculatePublicationStats($journal_articles);
$journals_count = [];
$quartile_distribution = [];

foreach ($journal_articles as $article) {
    $journal = $article['nome_rivista'] ?? 'Rivista non identificata';
    $miglior_quartile = $article['miglior_quartile'] ?? 'N/A';
    $journals_count[$journal] = ($journals_count[$journal] ?? 0) + 1;
    $quartile_distribution[$miglior_quartile] = ($quartile_distribution[$miglior_quartile] ?? 0) + 1;
}

arsort($quartile_distribution);
arsort($journals_count);
?>

<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DocRanks - Journal Articles di <?php echo htmlspecialchars($author['nome'] . ' ' . $author['cognome']); ?></title>
    <?php echo $bootstrap_css;
    echo $bootstrap_icons; ?>
    <link rel="stylesheet" href="../../docranks.css">
</head>

<body>
    <?php renderNavigation($scopus_id, 'journals'); ?>
    <div class="container">
        <?php renderPublicationHeader($author, $scopus_id, 'Journal Articles'); ?>

        <?php renderStatsTable($stats, ['Riviste Diverse' => count($journals_count)]); ?>

        <?php renderYearDistributionTable($stats['by_year']); ?>
        <section>
            <h4>Distribuzione per Quartile (miglior quartile rivista)</h4>
            <table class="table table-hover">
                <tr>
                    <th>Quartile</th>
                    <th>Numero Articles</th>
                    <th>Percentuale</th>
                </tr>
                <?php foreach ($quartile_distribution as $quartile => $count): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($quartile); ?></strong></td>
                        <td><?php echo $count; ?></td>
                        <td><?php echo round(($count / $stats['total']) * 100, 1); ?>%</td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </section>

        <main>
            <h3>Elenco Completo Journal Articles</h3>

            <?php if (empty($journal_articles)): ?>
                <p><em>Nessun journal article trovato per questo autore.</em></p>
            <?php else: ?>

                <?php foreach ($journal_articles as $article): ?>
                    <article class="card mb-3">
                        <h4 class="card-header"><?php echo htmlspecialchars($article['titolo']); ?></h4>

                        <table class="table table-hover card-body">
                            <tr>
                                <td><strong>DOI</strong></td>
                                <td><?php echo htmlspecialchars($article['DOI']); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Anno</strong></td>
                                <td><?php echo $article['anno']; ?></td>
                            </tr>
                            <tr>
                                <td><strong>Rivista</strong></td>
                                <td>
                                    <?php if ($article['nome_rivista']): ?>
                                        <strong><?php echo htmlspecialchars($article['nome_rivista']); ?></strong>
                                        <?php if ($article['publisher']): ?>
                                            <br><small>Publisher: <?php echo htmlspecialchars($article['publisher']); ?></small>
                                        <?php endif; ?>
                                        <?php if ($article['issn']): ?>
                                            <br><small>ISSN: <?php echo htmlspecialchars($article['issn']); ?></small>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <em>Rivista non identificata nel database</em>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Nome rivista DBLP</strong></td>
                                <td><?php echo htmlspecialchars($article['dblpRivista']); ?></td>
                            </tr>

                            <?php renderEditableJournalId($article); ?>

                            <?php if ($article['rivista_id']): ?>
                                <?php
                                $rivistaRepo = new JournalRepository($mysqli);
                                $aree = $rivistaRepo->getJournalArea($article['rivista_id']);
                                $categorie_quartili = $rivistaRepo->getCategoriesAndQuartiles($article['rivista_id'], $article['anno']);
                                ?>

                                <?php if (!empty($aree)): ?>
                                    <tr>
                                        <td><strong>Aree di Ricerca</strong></td>
                                        <td><?php echo implode(', ', array_map('htmlspecialchars', $aree)); ?></td>
                                    </tr>
                                <?php endif; ?>

                                <?php if (!empty($categorie_quartili)): ?>
                                    <tr>
                                        <td><strong>Categorie e Quartili (<?php echo $article['anno']; ?>)</strong></td>
                                        <td>
                                            <ul>
                                                <?php foreach ($categorie_quartili as $cat): ?>
                                                    <li>
                                                        <strong><?php echo htmlspecialchars($cat['nome_categoria']); ?></strong>
                                                        <?php if ($cat['nome_area']): ?>
                                                            <small>(<?php echo htmlspecialchars($cat['nome_area']); ?>)</small>
                                                        <?php endif; ?>
                                                        <?php if ($cat['quartile']): ?>
                                                            <?php
                                                            $quartile_color = '';
                                                            switch ($cat['quartile']) {
                                                                case 1:
                                                                    $quartile_color = 'color: green; font-weight: bold;';
                                                                    break;
                                                                case 2:
                                                                    $quartile_color = 'color: blue; font-weight: bold;';
                                                                    break;
                                                                case 3:
                                                                    $quartile_color = 'color: orange;';
                                                                    break;
                                                                case 4:
                                                                    $quartile_color = 'color: red;';
                                                                    break;
                                                            }
                                                            ?>
                                                            - <span style="<?php echo $quartile_color; ?>">Q<?php echo $cat['quartile']; ?></span>
                                                        <?php else: ?>
                                                            - <em>Quartile N/A</em>
                                                        <?php endif; ?>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            <?php endif; ?>

                            <tr>
                                <td><strong>Metriche Rivista (<?php echo $article['anno']; ?>)</strong></td>
                                <td>
                                    <ul>
                                        <?php if ($article['SJR']): ?>
                                            <li><strong>SJR:</strong> <?php echo $article['SJR']; ?></li>
                                        <?php endif; ?>
                                        <?php if ($article['SNIP']): ?>
                                            <li><strong>SNIP:</strong> <?php echo $article['SNIP']; ?></li>
                                        <?php endif; ?>
                                        <?php if ($article['CiteScore']): ?>
                                            <li><strong>CiteScore:</strong> <?php echo $article['CiteScore']; ?></li>
                                        <?php endif; ?>
                                        <?php if ($article['miglior_quartile']): ?>
                                            <li>
                                                <strong>Miglior Quartile:</strong>
                                                <?php
                                                $quartile_color = '';
                                                switch ($article['miglior_quartile']) {
                                                    case 'Q1':
                                                        $quartile_color = 'color: green; font-weight: bold;';
                                                        break;
                                                    case 'Q2':
                                                        $quartile_color = 'color: blue; font-weight: bold;';
                                                        break;
                                                    case 'Q3':
                                                        $quartile_color = 'color: orange;';
                                                        break;
                                                    case 'Q4':
                                                        $quartile_color = 'color: red;';
                                                        break;
                                                }
                                                ?>
                                                <span style="<?php echo $quartile_color; ?>"><?php echo $article['miglior_quartile']; ?></span>
                                            </li>
                                        <?php endif; ?>
                                        <?php if ($article['classifica']): ?>
                                            <li><strong>Classifica SJR:</strong> #<?php echo $article['classifica']; ?></li>
                                        <?php endif; ?>
                                        <?php if (!$article['SJR'] && !$article['SNIP'] && !$article['CiteScore']): ?>
                                            <li><em>Metriche non disponibili</em></li>
                                        <?php endif; ?>
                                    </ul>
                                </td>
                            </tr>

                            <tr>
                                <td><strong>Citazioni</strong></td>
                                <td><?php echo $article['citation_count'] ?? '<em>N/A</em>'; ?></td>
                            </tr>
                            <tr>
                                <td><strong>EFWCI</strong></td>
                                <td>
                                    <?php
                                    if ($article['EFWCI'] !== null) {
                                        echo number_format($article['EFWCI'], 3);
                                    } else {
                                        echo 'Non disponibile';
                                    }
                                    ?>
                                </td>
                            </tr>

                            <?php renderEditableFWCI($article); ?>

                            <tr>
                                <td><strong>Scopus ID</strong></td>
                                <td><?php echo $article['pub_scopus_id'] ? htmlspecialchars($article['pub_scopus_id']) : '<em>N/A</em>'; ?></td>
                            </tr>
                            <tr>
                                <td><strong>Numero Autori</strong></td>
                                <td><?php echo $article['numero_autori']; ?></td>
                            </tr>
                            <tr>
                                <td><strong>Autori</strong></td>
                                <td><?php echo htmlspecialchars($article['nome_autori']); ?></td>
                            </tr>
                        </table>
                    </article>
                <?php endforeach; ?>

            <?php endif; ?>

        </main>

        <?php renderActions($scopus_id, 'journals'); ?>
    </div>

    <?php renderEditingJavaScript(); ?>

    <?php echo $bootstrap_js; ?>
</body>

</html>

<?php
$mysqli->close();
?>