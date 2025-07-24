<?php

class ConferenceRepository
{
    private mysqli $mysqli;

    public function __construct(mysqli $mysqli)
    {
        $this->mysqli = $mysqli;
    }

    private function clearAcronym(string $venue): string
    {
        $venue = preg_replace('/\d+/', '', $venue);
        return trim($venue);
    }

    public function findConferenceByVenue(string $venue): ?string
    {
        $cleared_venue = $this->clearAcronym($venue);
        if ($cleared_venue === '') return null;

        $stmt = $this->mysqli->prepare("SELECT acronimo FROM CONFERENZE WHERE acronimo = ?");
        if (!$stmt) return null;

        $stmt->bind_param("s", $cleared_venue);
        $stmt->execute();
        $result = $stmt->get_result();
        $acronym = $result->fetch_assoc()['acronimo'] ?? null;
        $stmt->close();

        return $acronym;
    }

    // public function insertConferenceIfNotExists(string $venue, ?int $year = null): ?string
    // {
    //     $cleared_acronym = $this->clearAcronym($venue);
    //     if ($cleared_acronym === '') return null;

    //     $original = $this->findConferenceByVenue($venue);
    //     if ($original) return $original;

    //     $year = $year ?? (int)date('Y');
    //     try {
    //         $this->mysqli->begin_transaction();
    //         $this->insertRanking('F');
    //         $this->insertConference($cleared_acronym, $venue);
    //         $this->insertConferenceInfo($cleared_acronym, $year, 'F');
    //         $this->mysqli->commit();
    //         return $cleared_acronym;
    //     } catch (Exception $e) {
    //         $this->mysqli->rollback();
    //         return null;
    //     }
    // }

    private function insertRanking(string $value): void
    {
        $stmt = $this->mysqli->prepare("INSERT IGNORE INTO RANKING_1 (valore) VALUES (?)");
        if (!$stmt) return ;

        $stmt->bind_param("s", $value);
        $stmt->execute();
        $stmt->close();
    }

    private function insertConference(string $acronym, string $title): void
    {
        $stmt = $this->mysqli->prepare("INSERT IGNORE INTO CONFERENZE (acronimo, titolo) VALUES (?, ?)");
        if (!$stmt) return;

        $stmt->bind_param("ss", $acronym, $title);
        $stmt->execute();
        $stmt->close();
    }

    private function insertConferenceInfo(string $acronym, int $year, string $value): void
    {
        $stmt = $this->mysqli->prepare("INSERT IGNORE INTO INFO_CONF (acronimo, anno, valore) VALUES (?, ?, ?)");
        if (!$stmt) return ;

        $stmt->bind_param("sis", $acronym, $year, $value);
        $stmt->execute();
        $stmt->close();
    }

    public function importCore(): bool
    {
        $core_dir = dirname(__DIR__, 2) . "/uploads/core";
        $files = glob("$core_dir/*.csv");

        if (empty($files)) {
            echo "Nessun file CSV trovato nella cartella uploads/core/<br>";
            return false;
        }

        foreach ($files as $filepath) {
            $year = (int)basename($filepath, ".csv");

            if (($handle = fopen($filepath, "r")) !== false) {
                $processed_rows = 0;

                while (($data = fgetcsv($handle, 0, ",", '"', "\\")) !== false) {
                    if (count($data) < 5) continue;

                    $title = trim($data[1]);
                    $acronym_raw = trim($data[2]);
                    $rank_raw = trim($data[4]);
                    $acronym = $this->clearAcronym($acronym_raw);

                    if ($acronym === "" || $title === "") continue;

                    $rank = ($rank_raw === "Unranked") ? "?" : strtoupper($rank_raw);

                    $this->insertRanking($rank);
                    $this->insertConference($acronym, $title);
                    $this->insertConferenceInfo($acronym, $year, $rank);

                    $processed_rows++;
                }

                fclose($handle);
            } else {
                echo "- Errore nell'apertura del file: " . basename($filepath) . "<br>";
            }
        }

        return true;
    }
}
