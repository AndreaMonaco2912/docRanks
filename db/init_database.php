<?php
set_time_limit(600);
ini_set('memory_limit', '512M');

require 'connection.php';
require_once 'queries/CategoriesManager.php';
require_once 'core/ConferenceRepository.php';
require_once 'queries/scimagojr_queries.php';
require_once 'queries/scopus_riviste_queries.php';

$categorieManager = new CategoriesManager($mysqli);
$categorieManager->uploadDefault();

$conferenceRepo = new ConferenceRepository($mysqli);
if (!$conferenceRepo->importCore()) {
    echo "⚠ Importazione dati CORE non eseguita (file CSV non trovati)<br>";
}

if (!importScimagoJR($mysqli)) {
    echo "⚠ Importazione dati SciMago JR non eseguita (file CSV non trovati)<br>";
}

if (!importScopus($mysqli)) {
    echo "⚠ Aggiornamento dati Scopus non eseguito (file CSV non trovati)<br>";
}

$mysqli->close();
