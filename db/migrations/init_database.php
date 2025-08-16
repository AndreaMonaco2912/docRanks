<?php
set_time_limit(600);
ini_set('memory_limit', '512M');

require dirname(__DIR__) . '/connection.php';
require_once dirname(__DIR__) . '/importers/CategoriesManager.php';
require_once dirname(__DIR__) . '/importers/CoreImporter.php';
require_once dirname(__DIR__) . '/importers/ScimagoImporter.php';
require_once dirname(__DIR__) . '/importers/ScopusImporter.php';

$categorieManager = new CategoriesManager($mysqli);
$categorieManager->uploadDefault();

$coreImporter = new CoreImporter($mysqli);
if (!$coreImporter->importCore()) {
    echo "⚠ Importazione dati CORE non eseguita (file CSV non trovati)<br>";
}

$scimagoImporter = new ScimagoImporter($mysqli);
if (!$scimagoImporter->importScimagoJR()) {
    echo "⚠ Importazione dati SciMago JR non eseguita (file CSV non trovati)<br>";
}

$scopusImporter = new ScopusImporter($mysqli);
if (!$scopusImporter->importScopus()) {
    echo "⚠ Aggiornamento dati Scopus non eseguito (file CSV non trovati)<br>";
}

$mysqli->close();
