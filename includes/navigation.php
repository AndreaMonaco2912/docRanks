<?php

function renderNavigation($scopus_id, $current_page = '')
{
?>
    <nav>
        <div class="container">
            <a href="../../home/index.php">
                Home
            </a>
            <span class="text-muted">|</span>
            <a href="../index.php">
                Cerca Autore
            </a>
            <span class="text-muted">|</span>
            <a href="index.php?scopus_id=<?php echo urlencode($scopus_id); ?>"
                <?php echo $current_page === 'profile' ? 'class="fw-bold text-primary;"' : ''; ?>>Profilo Autore</a> |
            <a href="conference_papers.php?scopus_id=<?php echo urlencode($scopus_id); ?>"
                <?php echo $current_page === 'conferences' ? 'class="fw-bold text-primary;"' : ''; ?>>Conference Papers</a> |
            <a href="journal_articles.php?scopus_id=<?php echo urlencode($scopus_id); ?>"
                <?php echo $current_page === 'journals' ? 'class="fw-bold text-primary;"' : ''; ?>>Journal Articles</a> |
            <a href="other_publications.php?scopus_id=<?php echo urlencode($scopus_id); ?>"
                <?php echo $current_page === 'others' ? 'class="fw-bold text-primary;"' : ''; ?>>Altre Pubblicazioni</a>
        </div>
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
        <table class="table table-hover">
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
        <table class="table table-hover">
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
    <aside class="card">
        <h3 class="card-header">Azioni</h3>
        <ul class="list-group list-group-flush">
            <li class="list-group-item">
                <a href="index.php?scopus_id=<?php echo urlencode($scopus_id); ?>">← Torna al Profilo Autore</a><br>
            </li>
            <?php if ($current_page !== 'conferences'): ?>
                <li class="list-group-item">
                    <a href="conference_papers.php?scopus_id=<?php echo urlencode($scopus_id); ?>">Visualizza Conference Papers</a><br>
                </li>
            <?php endif; ?>
            <?php if ($current_page !== 'journals'): ?>
                <li class="list-group-item">
                    <a href="journal_articles.php?scopus_id=<?php echo urlencode($scopus_id); ?>">Visualizza Journal Articles</a>
                </li>
            <?php endif; ?>
            <?php if ($current_page !== 'others'): ?>
                <li class="list-group-item">
                    <a href="other_publications.php?scopus_id=<?php echo urlencode($scopus_id); ?>">Visualizza Altre Pubblicazioni</a>
                </li>
            <?php endif; ?>
        </ul>
    </aside>
<?php
}
?>