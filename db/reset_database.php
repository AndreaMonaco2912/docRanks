<?php

require 'connection.php';

if (!$mysqli->query("SET FOREIGN_KEY_CHECKS = 0")) {
    die("Errore durante la disabilitazione delle foreign key: " . $mysqli->error);
}

$tables = [
    'SPECIALIZZAZIONI_CATEGORIE',
    'SPECIALIZZAZIONI_AREA',
    'REDAZIONE',
    'QUARTILI',
    'PUBBLICAZIONE_ALTRO',
    'PARTECIPAZIONE',
    'INFORMAZIONI_RIVISTE',
    'INFORMAZIONI_AUTORI',
    'INFO_CONF',
    'CONFERENZE',
    'CATEGORIE',
    'AUTORI',
    'OTHERS',
    'ATTI_DI_CONVEGNO',
    'ARTICOLI',
    'RIVISTE',
    'RANKING_1',
    'AREE'
];

foreach ($tables as $table) {
    $query = "TRUNCATE TABLE $table";
    if (!$mysqli->query($query)) {
        echo "Errore durante lo svuotamento della tabella $table: " . $mysqli->error . "<br>";
    }
}

if (!$mysqli->query("SET FOREIGN_KEY_CHECKS = 1")) {
    echo "Errore durante la riabilitazione delle foreign key: " . $mysqli->error . "<br>";
}

$mysqli->close();
