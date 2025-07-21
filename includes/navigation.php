<?php

function renderNavigation($scopus_id, $current_page = '')
{
?>
    <nav>
        <a href="../../home/index.php">Home</a> |
        <a href="../index.php">Cerca Autore</a> |
        <a href="index.php?scopus_id=<?php echo urlencode($scopus_id); ?>"
            <?php echo $current_page === 'profile' ? 'style="font-weight: bold;"' : ''; ?>>Profilo Autore</a> |
        <a href="conference_papers.php?scopus_id=<?php echo urlencode($scopus_id); ?>"
            <?php echo $current_page === 'conferences' ? 'style="font-weight: bold;"' : ''; ?>>Conference Papers</a> |
        <a href="journal_articles.php?scopus_id=<?php echo urlencode($scopus_id); ?>"
            <?php echo $current_page === 'journals' ? 'style="font-weight: bold;"' : ''; ?>>Journal Articles</a>
    </nav>
<?php
}

function renderPublicationHeader($author, $scopus_id, $title, $icon)
{
?>
    <h1><?php echo $icon; ?> <?php echo $title; ?></h1>

    <h2> <?php echo htmlspecialchars($author['nome'] . ' ' . $author['cognome']); ?></h2>
    <p><strong>Scopus ID:</strong> <?php echo htmlspecialchars($scopus_id); ?></p>
<?php
}

function renderStatsTable($stats, $extra_rows = [])
{
?>
    <section>
        <h3>Statistiche</h3>
        <table border="1">
            <tr>
                <th>Metrica</th>
                <th>Valore</th>
            </tr>
            <tr>
                <td>Totale Pubblicazioni</td>
                <td><?php echo $stats['total']; ?></td>
            </tr>
            <tr>
                <td>Totale Citazioni</td>
                <td><?php echo $stats['total_citations']; ?></td>
            </tr>
            <tr>
                <td>Citazioni Medie per Pubblicazione</td>
                <td><?php echo $stats['average_citations']; ?></td>
            </tr>
            <?php foreach ($extra_rows as $label => $value): ?>
                <tr>
                    <td><?php echo $label; ?></td>
                    <td><?php echo $value; ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    </section>
<?php
}

function renderYearDistributionTable($by_year_data)
{
?>
    <section>
        <h4>Distribuzione per Anno</h4>
        <table border="1">
            <tr>
                <th>Anno</th>
                <th>Numero Pubblicazioni</th>
            </tr>
            <?php foreach ($by_year_data as $anno => $count): ?>
                <tr>
                    <td><?php echo $anno; ?></td>
                    <td><?php echo $count; ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    </section>
<?php
}

function renderActions($scopus_id, $current_page)
{
?>
    <footer>
        <h3>Azioni</h3>
        <p>
            <a href="index.php?scopus_id=<?php echo urlencode($scopus_id); ?>">← Torna al Profilo Autore</a><br>
            <?php if ($current_page !== 'conferences'): ?>
                <a href="conference_papers.php?scopus_id=<?php echo urlencode($scopus_id); ?>">📄 Visualizza Conference Papers</a><br>
            <?php endif; ?>
            <?php if ($current_page !== 'journals'): ?>
                <a href="journal_articles.php?scopus_id=<?php echo urlencode($scopus_id); ?>">📑 Visualizza Journal Articles</a>
            <?php endif; ?>
        </p>
    </footer>
<?php
}
?>