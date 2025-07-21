<?php

require_once 'CategoriesManager.php';
require_once dirname(__DIR__) . '/JournalRepository.php';

function importScimagoJR($mysqli): bool
{
    $folder = dirname(__DIR__, 2) . "/uploads/scimagojr";
    $years = range(2014, 2024);
    $journalRepository = new JournalRepository($mysqli);

    if (!is_dir($folder)) {
        echo "Cartella uploads/scimagojr non trovata<br>";
        return false;
    }
    $categoriesManager = new CategoriesManager($mysqli);

    foreach ($years as $year) {
        $filename = "$folder/$year.csv";

        if (!file_exists($filename)) {
            echo "File non trovato: $year.csv<br>";
            continue;
        }

        if (($handle = fopen($filename, 'r')) !== FALSE) {
            fgetcsv($handle, 1000, ";");

            while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
                if (count($data) < 24) continue;

                [
                    $rank,
                    $sourceid,
                    $title,
                    $type,
                    $issn,
                    $sjr_raw,
                    $best_quartile,
                    $h_index,
                    $total_docs,
                    $total_docs_3y,
                    $total_refs,
                    $total_cites,
                    $citable_docs,
                    $cites_doc,
                    $refs_doc,
                    $female,
                    $overton,
                    $sdg,
                    $country,
                    $region,
                    $publisher,
                    $coverage,
                    $categories_raw,
                    $areas_raw
                ] = $data;

                $sjr = floatval(str_replace(',', '.', $sjr_raw));
                $journal_id = trim($sourceid);

                if (empty($journal_id) || empty($title)) continue;

                $journalRepository->insertJournal($journal_id, trim($title), trim($publisher), trim($issn));

                $journalRepository->insertJournalInfo($journal_id, $year, $sjr, trim($best_quartile), (int)$rank);

                $categories = array_map('trim', explode(';', $categories_raw));
                $all_areas = [];

                foreach ($categories as $cat) {
                    if (preg_match('/^(.*?) \(Q(\d)\)$/', $cat, $matches)) {
                        $category_name = trim($matches[1]);
                        $quartile = (int)$matches[2];

                        if ($categoriesManager->categoryExists($category_name)) {
                            $new_category = $category_name;
                            $new_area = $categoriesManager->getAreaByCategory($category_name);
                        } else {
                            $new_category = 'Other';
                            $new_area = 'Other';
                        }

                        $journalRepository->insertCategorySpecialization($new_category, $journal_id);

                        $journalRepository->insertQuartile($new_category, $journal_id, $quartile, $year);

                        if (!in_array($new_area, $all_areas)) {
                            $journalRepository->insertAreaSpecialization($new_area, $journal_id);
                            $all_areas[] = $new_area;
                        }
                    }
                }
            }

            fclose($handle);
        } else {
            echo "- Errore nell'apertura del file: $year.csv<br>";
            return false;
        }
    }
    return true;
}
