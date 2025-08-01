<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DocRanks - Inizializzazione</title>
</head>

<body>
    <main>
        <h1>DocRanks - Sistema di Gestione</h1>

        <section aria-labelledby="reset-section">
            <h2 id="reset-section">Inizializzazione del sistema</h2>
            <p>Questa azione cancellerà tutti i dati esistenti e reimposterà il sito. Procedere solo se necessario. L'operazione dura vari minuti</p>
            <a href="reset_and_init.php">
                <button type="submit">Resetta e Inizializza il Sito</button>
            </a>
        </section>

        <section aria-labelledby="access-section">
            <h2 id="access-section">Accedi al sito</h2>
            <p>Vai direttamente all’interfaccia principale del sito senza modificare nulla.</p>
            <a href="home/">
                <button type="button">Vai al Sito</button>
            </a>
        </section>
        <section aria-labelledby="update-section">
            <h2 id="update-section">Aggiornamento del sistema</h2>
            <p>Questa azione aggiornerà tutti gli autori presenti nel database ricaricando i loro dati da Scopus e DBLP. L'operazione dura vari minuti</p>
            <a href="update_database.php">
                <button type="submit">Aggiorna Database</button>
            </a>
        </section>
    </main>
</body>

</html>