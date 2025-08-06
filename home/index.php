<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DocRanks - Home</title>
    <?php
    require_once '../includes/bootstrap.php';
    echo $bootstrap_css;
    echo $bootstrap_css;
    ?>
    <link rel="stylesheet" href="../docranks.css">
</head>

<body>
    <nav>
        <div class="container">
            <a href="" class="fw-bold text-primary">
                Home
            </a>
            <span class="text-muted">|</span>
            <a href="autore/">
                Cerca Autore
            </a>
            <span class="text-muted">|</span>
            <a href="autore/aggiungi.php">
                Aggiungi Autore
            </a>
            <span class="text-muted">|</span>
            <a href="../update_database.php">
                Aggiorna Database
            </a>
            <span class="text-muted">|</span>
            <a href="../reset_and_init.php">
                Reset Database
            </a>
        </div>
    </nav>

    <h1>DocRanks - Sistema di Gestione Documenti Accademici</h1>

    <section>
        <h2>Benvenuto nel sistema DocRanks</h2>

        <p>DocRanks è un sistema completo per la gestione e l'analisi di pubblicazioni.
            Permette di importare dati da Scopus e DBLP per ottenere informazioni dettagliate su autori,
            pubblicazioni e metriche, poi prende i dati da Core e Scimagojr per valutare il valore di conferenze e giornali su cui sono avvenute le pubblicazioni.</p>

        <h3>Funzionalità principali</h3>
        <ul>
            <li><strong>Ricerca Autori:</strong> Cerca autori esistenti nel database</li>
            <li><strong>Importazione Dati:</strong> Aggiungi nuovi autori con tutte le loro pubblicazioni da Scopus e DBLP</li>
            <li><strong>Analisi Pubblicazioni:</strong> Visualizza articoli e atti di convegno e i dati su conferenze e riviste corrispettive</li>
        </ul>
    </section>

    <main>
        <h2>Inizia</h2>
        <p>
            <a href="autore/">Cerca un autore esistente</a><br>
            <a href="autore/aggiungi.php">Aggiungi un nuovo autore</a>
        </p>
    </main>
    <?php echo $bootstrap_js; ?>
</body>

</html>