<?php

require_once 'CategoriesManager.php';
require_once dirname(__DIR__) . '/repositories/JournalRepository.php';

class ScimagoImporter
{
    private JournalRepository $journalRepository;
    private CategoriesManager $categoriesManager;

    private const YEARS_RANGE = [2014, 2024];
    private const MIN_COLUMNS = 24;

    private const COL_RANK = 0;
    private const COL_SOURCEID = 1;
    private const COL_TITLE = 2;
    private const COL_ISSN = 4;
    private const COL_SJR = 5;
    private const COL_BEST_QUARTILE = 6;
    private const COL_PUBLISHER = 20;
    private const COL_CATEGORIES = 22;

    public function __construct(mysqli $mysqli)
    {
        $this->journalRepository = new JournalRepository($mysqli);
        $this->categoriesManager = new CategoriesManager($mysqli);
    }

    public function importScimagoJR(): bool
    {
        $folder = dirname(__DIR__, 2) . "/uploads/scimagojr";

        if (!is_dir($folder)) {
            echo "Cartella uploads/scimagojr non trovata<br>";
            return false;
        }

        $years = range(self::YEARS_RANGE[0], self::YEARS_RANGE[1]);

        foreach ($years as $year) {
            if (!$this->processYearFile($folder, $year)) {
                return false;
            }
        }

        return true;
    }

    private function processYearFile(string $folder, int $year): bool
    {
        $filename = "$folder/$year.csv";

        if (!file_exists($filename)) {
            echo "File non trovato: $year.csv<br>";
            return true;
        }

        $handle = fopen($filename, 'r');
        if ($handle === false) {
            echo "Errore nell'apertura del file: $year.csv<br>";
            return false;
        }

        fgetcsv($handle, 1000, ';', '"', "\\");

        while (($data = fgetcsv($handle, 1000, ';', '"', "\\")) !== false) {
            if (count($data) < self::MIN_COLUMNS) continue;

            $this->processJournalRow($data, $year);
        }

        fclose($handle);
        return true;
    }

    private function processJournalRow(array $data, int $year): void
    {
        $journalData = $this->extractJournalData($data);

        if (!$this->isValidJournal($journalData)) {
            return;
        }

        $this->journalRepository->insertJournal(
            $journalData['id'],
            $journalData['title'],
            $journalData['publisher'],
            $journalData['issn']
        );

        $this->journalRepository->insertJournalInfo(
            $journalData['id'],
            $year,
            $journalData['sjr'],
            $journalData['best_quartile'],
            $journalData['rank']
        );

        $this->processCategories($journalData['categories_raw'], $journalData['id'], $year);
    }

    private function extractJournalData(array $data): array
    {
        return [
            'rank' => (int)$data[self::COL_RANK],
            'id' => trim($data[self::COL_SOURCEID]),
            'title' => trim($data[self::COL_TITLE]),
            'issn' => trim($data[self::COL_ISSN]),
            'sjr' => $this->parseSjr($data[self::COL_SJR]),
            'best_quartile' => trim($data[self::COL_BEST_QUARTILE]),
            'publisher' => trim($data[self::COL_PUBLISHER]),
            'categories_raw' => $data[self::COL_CATEGORIES]
        ];
    }

    private function parseSjr(string $sjrRaw): float
    {
        return floatval(str_replace(',', '.', $sjrRaw));
    }

    private function isValidJournal(array $journalData): bool
    {
        return !empty($journalData['id']) && !empty($journalData['title']);
    }

    private function processCategories(string $categoriesRaw, string $journalId, int $year): void
    {
        $categories = array_map('trim', explode(';', $categoriesRaw));
        $processedAreas = [];

        foreach ($categories as $categoryString) {
            $categoryData = $this->parseCategoryString($categoryString);

            if (!$categoryData) continue;

            [$categoryName, $quartile] = $categoryData;

            if ($this->categoriesManager->categoryExists($categoryName)) {
                $effectiveCategory = $categoryName;
                $effectiveArea = $this->categoriesManager->getAreaByCategory($categoryName);
            } else {
                $effectiveCategory = 'Other';
                $effectiveArea = 'Other';
            }

            $this->journalRepository->insertCategorySpecialization($effectiveCategory, $journalId);
            $this->journalRepository->insertQuartile($effectiveCategory, $journalId, $quartile, $year);

            if (!in_array($effectiveArea, $processedAreas)) {
                $this->journalRepository->insertAreaSpecialization($effectiveArea, $journalId);
                $processedAreas[] = $effectiveArea;
            }
        }
    }

    private function parseCategoryString(string $categoryString): ?array
    {
        if (preg_match('/^(.*?) \(Q(\d)\)$/', $categoryString, $matches)) {
            $categoryName = trim($matches[1]);
            $quartile = (int)$matches[2];
            return [$categoryName, $quartile];
        }

        return null;
    }
}
