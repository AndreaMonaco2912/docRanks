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
    <nav class="navbar navbar-expand-lg bg-body-tertiary">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary nav-tabs nav-link active" href="">
                DocRanks
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav nav-tabs">
                    <li class="nav-item">
                        <a class="nav-link" href="autore/">
                            Cerca Autore
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="autore/aggiungi.php">
                            Aggiungi Autore
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../update_database.php">
                            Aggiorna Database
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../reset_and_init.php">
                            Reset Database
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container">
        <h1>DocRanks - Sistema di Gestione Documenti Accademici</h1>

        <section class="container">
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

        <main class="card">
            <h2 class="card-header">Inizia</h2>
            <ul class="list-group list-group-flush">
                <li class="list-group-item">
                    <a href="autore/">Cerca un autore esistente</a><br>
                </li>
                <li class="list-group-item">
                    <a href="autore/aggiungi.php">Aggiungi un nuovo autore</a>
                </li>
            </ul>
        </main>
    </div>
    <?php echo $bootstrap_js; ?>
</body>

</html>