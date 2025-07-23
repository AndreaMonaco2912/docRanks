<?php
require_once dirname(__DIR__) . '/JournalRepository.php';

function processScopus($mysqli, $file_path, $anno)
{
    if (!file_exists($file_path)) {
        echo "File non trovato: $file_path<br>";
        return 0;
    }

    $handle = fopen($file_path, 'r');

    if ($handle === FALSE) {
        echo "Errore nell'apertura del file: $file_path<br>";
        return 0;
    }

    $header = fgetcsv($handle, 0, ",", '"', "\\");
    if ($header === FALSE) {
        echo "Errore nella lettura dell'header del file: $file_path<br>";
        fclose($handle);
        return 0;
    }

    $source_index = false;
    $citescore_index = false;
    $snip_index = false;

    foreach ($header as $index => $column_name) {
        $column_name = trim($column_name);
        if (strcasecmp($column_name, 'Source Title') === 0 || strcasecmp($column_name, 'Source title') === 0) {
            $source_index = $index;
        } elseif (strcasecmp($column_name, 'CiteScore') === 0) {
            $citescore_index = $index;
        } elseif (strcasecmp($column_name, 'SNIP') === 0) {
            $snip_index = $index;
        }
    }

    if ($source_index === false || $citescore_index === false || $snip_index === false) {
        echo "Colonne mancanti nel file " . basename($file_path) . "<br>";
        echo "Header trovato: " . implode(', ', $header) . "<br>";
        echo "Indici trovati - Source: $source_index, CiteScore: $citescore_index, SNIP: $snip_index<br>";
        fclose($handle);
        return 0;
    }

    while (($row = fgetcsv($handle, 0, ",", '"', "\\")) !== FALSE) {
        $source_title = trim($row[$source_index] ?? '');
        $citescore = trim($row[$citescore_index] ?? '');
        $snip = trim($row[$snip_index] ?? '');

        if (empty($source_title) || empty($citescore) || empty($snip)) {
            continue;
        }

        $citescore_float = floatval($citescore);
        $snip_float = floatval($snip);

        $journalRepository = new JournalRepository($mysqli);
        $journal_id = $journalRepository->getJournalByVenue($source_title);

        if ($journal_id !== null) {
            $journalRepository->updateSnipCiteScore($journal_id, $anno, $citescore_float, $snip_float);
        }
    }

    fclose($handle);
}

function importScopus($mysqli): bool
{
    $folder = dirname(__DIR__, 2) . "/uploads/scopus";
    $years = range(2022, 2024);

    if (!is_dir($folder)) {
        echo "Cartella uploads/scopus non trovata<br>";
        return false;
    }

    foreach ($years as $year) {
        $filename = "$folder/cs-$year.csv";

        if (!file_exists($filename)) {
            echo "File non trovato: cs-$year.csv<br>";
            continue;
        }

        processScopus($mysqli, $filename, $year);
    }

    return true;
}
