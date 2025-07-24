<?php

require_once '../../db/connection.php';
require_once '../../db/AuthorRepository.php';
require_once '../../db/AuthorsRepository.php';
require_once '../../includes/publication_handlers.php';

$scopus_id = isset($_GET['scopus_id']) ? trim($_GET['scopus_id']) : '';
$dati_autore_completi = null;
$author_exists = false;

if (!empty($scopus_id) && is_numeric($scopus_id)) {
    $authorRepository = new AuthorRepository($mysqli, $scopus_id);
    $author_exists = $authorRepository->exists();
}

handleHIndexUpdate($mysqli, $scopus_id);
?>

<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DocRanks - Profilo Autore</title>
</head>

<body>
    <nav>
        <a href="../">Home</a> |
        <a href="">Cerca Autore</a> |
        <a href="aggiungi.php">Aggiungi Autore</a> |
        <a href="../../reset_and_init.php">Reset Database</a>
    </nav>

    <h1>DocRanks - Profilo Autore</h1>

    <?php include '../../includes/search_bar.php'; ?>

    <?php if ($author_exists): ?>
        <?php $author = $authorRepository->getProfile(); ?>
        <main>
            <h2>Profilo Completo</h2>

            <section>
                <h3>Informazioni Generali</h3>
                <table border="1">
                    <tr>
                        <th>Campo</th>
                        <th>Valore</th>
                    </tr>
                    <tr>
                        <td><strong>Nome Completo</strong></td>
                        <td><?php echo htmlspecialchars($author['nome'] . ' ' . $author['cognome']); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Scopus ID</strong></td>
                        <td><?php echo htmlspecialchars($author['scopus_id']); ?></td>
                    </tr>
                    <tr>
                        <td><strong>H-Index</strong></td>
                        <td>
                            <div id="edit_hindex_<?php echo md5($author['scopus_id']); ?>" style="display: inline;">
                                <span><?php echo $author['h_index'] ?? 'Non impostato'; ?></span>
                                <button type="button" class="btn-edit">Modifica</button>
                            </div>

                            <div id="form_hindex_<?php echo md5($author['scopus_id']); ?>" style="display: none;">
                                <form method="post" style="display: inline;">
                                    <label for="hindex_input_<?php echo md5($author['scopus_id']); ?>">H-Index</label>
                                    <input type="number" step="0.1" name="new_hindex"
                                        id="hindex_input_<?php echo $id_suffix; ?>"
                                        value="<?php echo htmlspecialchars($author['h_index'] ?? ''); ?>"
                                        placeholder="H-Index">
                                    <button type="submit" name="update_hindex">Salva</button>
                                    <button type="button" class="btn-cancel">Annulla</button>
                                </form>
                                <br><small>Inserisci il valore H-Index o lascia vuoto per rimuovere</small>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Totale Documenti</strong></td>
                        <td><?php echo htmlspecialchars($author['numero_documenti']); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Totale Citazioni</strong></td>
                        <td><?php echo htmlspecialchars($author['numero_citazioni']); ?></td>
                    </tr>
                </table>
            </section>

            <section>
                <h3>Panoramica Pubblicazioni</h3>

                <?php
                $articles_stats = $authorRepository->getArticlesStats();
                $papers_stats = $authorRepository->getConferencePapersStats();
                $total_articles = $articles_stats['total'];
                $total_papers = $papers_stats['total'];
                $total_cit_articles = $articles_stats['total_citations'];
                $total_cit_papers = $papers_stats['total_citations'];
                ?>

                <table border="1">
                    <tr>
                        <th>Tipo Pubblicazione</th>
                        <th>Numero</th>
                        <th>Citazioni Totali</th>
                        <th>Media Citazioni</th>
                        <th>Azioni</th>
                    </tr>
                    <tr>
                        <td><strong>Journal Articles</strong></td>
                        <td><?php echo $total_articles; ?></td>
                        <td><?php echo $total_cit_articles; ?></td>
                        <td><?php echo $total_articles > 0 ? round($total_cit_articles / $total_articles, 2) : 0; ?></td>
                        <td>
                            <?php if ($total_articles > 0): ?>
                                <a href="journal_articles.php?scopus_id=<?php echo urlencode($scopus_id); ?>">Visualizza Dettagli</a>
                            <?php else: ?>
                                <em>Nessun articolo</em>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Conference Papers</strong></td>
                        <td><?php echo $total_papers; ?></td>
                        <td><?php echo $total_cit_papers; ?></td>
                        <td><?php echo $total_papers > 0 ? round($total_cit_papers / $total_papers, 2) : 0; ?></td>
                        <td>
                            <?php if ($total_papers > 0): ?>
                                <a href="conference_papers.php?scopus_id=<?php echo urlencode($scopus_id); ?>">Visualizza Dettagli</a>
                            <?php else: ?>
                                <em>Nessun conference paper</em>
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
            </section>

            <?php $year_information = $authorRepository->getYearlyStats(); ?>
            <?php if (!empty($year_information)): ?>
                <section>
                    <h3>Informazioni per Anno</h3>

                    <table border="1">
                        <tr>
                            <th>Anno</th>
                            <th>Documenti</th>
                            <th>Citazioni</th>
                            <th>Media Citazioni per Documento</th>
                        </tr>
                        <?php foreach ($year_information as $info): ?>
                            <tr>
                                <td><?php echo $info['anno']; ?></td>
                                <td><?php echo $info['documenti']; ?></td>
                                <td><?php echo $info['citazioni']; ?></td>
                                <td><?php echo $info['documenti'] > 0 ? round($info['citazioni'] / $info['documenti'], 2) : 0; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                </section>
            <?php endif; ?>
        </main>
        <footer>
            <h3>Azioni</h3>
            <p>
                <a href="conference_papers.php?scopus_id=<?php echo urlencode($scopus_id); ?>">Visualizza tutti i Conference Papers</a><br>
                <a href="journal_articles.php?scopus_id=<?php echo urlencode($scopus_id); ?>">Visualizza tutti i Journal Articles</a><br>
                <a href="other_publications.php?scopus_id=<?php echo urlencode($scopus_id); ?>">Visualizza tutte le Altre Pubblicazioni</a><br>
                <a href="aggiungi.php">Aggiungi altro autore</a>
            </p>
        </footer>

    <?php elseif (!empty($scopus_id)): ?>
        <h3>Autore Non Trovato</h3>
        <p>L'autore con Scopus ID <strong><?php echo htmlspecialchars($scopus_id); ?></strong> non è presente nel database.</p>
        <p>
            <a href="aggiungi.php?scopus_id=<?php echo urlencode($scopus_id); ?>">Aggiungi questo autore al database</a>
        </p>
    <?php else: ?>
        <main>
            <h2>Tutti gli Autori</h2>

            <?php
            $authorsRepo = new AuthorsRepository($mysqli);
            $authors = $authorsRepo->getAllAuthors();
            ?>
            <table border="1">
                <caption>Elenco completo degli autori presenti nel database</caption>
                <thead>
                    <tr>
                        <th scope="col">Nome</th>
                        <th scope="col">Cognome</th>
                        <th scope="col">Scopus ID</th>
                        <th scope="col">Azioni</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($authors as $author): ?>
                        <tr>
                            <td><?= htmlspecialchars($author['nome']) ?></td>
                            <td><?= htmlspecialchars($author['cognome']) ?></td>
                            <td><?= htmlspecialchars($author['scopus_id']) ?></td>
                            <td>
                                <a href="?scopus_id=<?= urlencode($author['scopus_id']) ?>">Visualizza Profilo</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </main>
    <?php endif; ?>


    <?php renderEditingJavaScript(); ?>

</body>

</html>