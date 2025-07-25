# DocRanks

DocRanks è un sistema per l'analisi delle pubblicazioni accademiche che inegra i dati da Scopus, DBLP, CORE e ScimagoJR al fine di garantire una vista unificata per permettere il confronto tra le varie pubblicaizioni.

## Setup

### Database

Utilizza il file [dockranks.sql](sql/docranks.sql) per creare un database con le tabelle necessarie al funzionamento.

Il software assume:

```php
$servername = "localhost";
$username = "root";
$password = "";
$database = "docranks";
```

come configurazione quindi se necessario modificala in [connection.php](db/connection.php).

### Api Key

Questa guida è necessaria per usare il sito localmente.

**Per ottenere una apiKey:**

- registrati su [elsevier](https://dev.elsevier.com/)
- clicca sul pulsante `I want an API key`.
- se richiesto attraverso una organizzazione (es unibo)
- clicca su create api key.
- inserisci una label a piacere, l'url del sito e poi accetta le condizioni di uso.

Adesso sulla base del file [.env.example](.env.example) crea il tuo file `.env` con la tua chiave.

!!!Attenzione!!! l'api key a volte potrebbe richiedere qualche ora prima di funzionare.

## Deployment

This project uses Docker Compose to manage the services. To deploy the application, run:

```bash
docker compose up -d
```

To clean up the services, you can run:

```bash
docker compose down --volumes
```
