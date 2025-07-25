<?php

class JournalRepository
{
    private mysqli $mysqli;

    public function __construct(mysqli $mysqli)
    {
        $this->mysqli = $mysqli;
    }

    private function getJournalByName(string $name): ?string
    {
        $stmt = $this->mysqli->prepare("SELECT id FROM RIVISTE WHERE nome = ?");
        if (!$stmt) return null;

        $stmt->bind_param("s", $name);
        $stmt->execute();
        $result = $stmt->get_result();
        $id = $result->fetch_assoc()['id'] ?? null;
        $stmt->close();

        return $id;
    }

    public function getJournalByVenue(string $venue): ?string
    {
        $id = $this->getJournalByName($venue);
        if ($id) return $id;

        $venue_norm = $this->normalizeVenue($venue);
        if ($venue_norm !== $venue) {
            $stmt = $this->mysqli->prepare("SELECT id FROM RIVISTE WHERE nome = ?");
            if ($stmt) {
                $stmt->bind_param("s", $venue_norm);
                $stmt->execute();
                $result = $stmt->get_result();
                $id = $result->fetch_assoc()['id'] ?? null;
                $stmt->close();
                if ($id) return $id;
            }
        }
        return null;
    }

    private function normalizeVenue(string $venue): string
    {
        $abbreviations = [
            // Riviste aggiunte manualmente
            'Commun. ACM' => 'Communications of the ACM',
            'Comput. Entertain.' => 'Computers in Entertainment',
            'Multim. Tools Appl.' => 'Multimedia Tools and Applications',
            'ACM Trans. Model. Comput. Simul.' => 'ACM Transactions on Modeling and Computer Simulation',
            'Formal Aspects Comput.' => 'Formal Aspects of Computing',
            'J. Big Data' => 'Journal of Big Data',
            'Virtual Real.' => 'Virtual Reality',
            'Int. J. Hum. Comput. Interact.' => 'International Journal of Human Computer Interaction',
            'Mob. Networks Appl.' => 'Mobile Networks and Applications',
            'IEEE Trans. Veh. Technol.' => 'IEEE Transactions on Vehicular Technology',
            'Wirel. Commun. Mob. Comput.' => 'Wireless Communications and Mobile Computing',
            'IEEE Commun. Mag.' => 'IEEE Communications Magazine',
            'J. Vis. Commun. Image Represent.' => 'Journal of Visual Communication and Image Representation',
            'Comput. Networks' => 'Computer Networks',
            'IEEE Internet Things J.' => 'IEEE Internet of Things Journal',

            // Pattern tipici di riviste
            'Trans.' => 'Transactions on',
            'Proc.' => 'Proceedings of',
            'J.' => 'Journal',
            'Int.' => 'International',
            'Comput.' => 'Computer',
            'Commun.' => 'Communications',
            'Multim.' => 'Multimedia',
            'Technol.' => 'Technology',
            'Represent.' => 'Representation',
            'Interact.' => 'Interaction',
            'Entertain.' => 'Entertainment',
            'Networks' => 'Networks',
            'Appl.' => 'Applications',
            'Mag.' => 'Magazine',
            'Veh.' => 'Vehicular',
            'Wirel.' => 'Wireless',
            'Mob.' => 'Mobile',
            'Vis.' => 'Visual',
            'Sci.' => 'Science',
            'Eng.' => 'Engineering',
            'Intell.' => 'Intelligent',
            'Anal.' => 'Analysis'
        ];

        if (isset($abbreviations[$venue])) {
            return $abbreviations[$venue];
        }

        $normalized = $venue;
        foreach ($abbreviations as $abbr => $full) {
            if (strpos($abbr, '.') !== false) {
                $normalized = str_ireplace($abbr, $full, $normalized);
            }
        }

        return trim(preg_replace('/\s+/', ' ', $normalized));
    }

    public function getJournalArea(string $journalId): array
    {
        $stmt = $this->mysqli->prepare("
            SELECT nome_area
            FROM SPECIALIZZAZIONI_AREA
            WHERE id = ?
        ");
        if (!$stmt) return [];

        $stmt->bind_param("s", $journalId);
        $stmt->execute();
        $result = $stmt->get_result();

        $areas = [];
        while ($row = $result->fetch_assoc()) {
            $areas[] = $row['nome_area'];
        }

        $stmt->close();
        return $areas;
    }

    public function getCategoriesAndQuartiles(string $journalId, int $year): array
    {
        if ($journalId === '') return [];

        $stmt = $this->mysqli->prepare("
            SELECT 
                sc.nome_categoria,
                c.nome_area,
                q.valore as quartile
            FROM SPECIALIZZAZIONI_CATEGORIE sc
            LEFT JOIN CATEGORIE c ON sc.nome_categoria = c.nome_categoria
            LEFT JOIN QUARTILI q ON sc.nome_categoria = q.nome_categoria AND sc.id = q.id AND q.anno = ?
            WHERE sc.id = ?
            ORDER BY c.nome_area, sc.nome_categoria
        ");
        if (!$stmt) return [];

        $stmt->bind_param("is", $year, $journalId);
        $stmt->execute();
        $result = $stmt->get_result();

        $categories = [];
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row;
        }

        $stmt->close();
        return $categories;
    }

    public function updateSnipCiteScore(string $journal_id, int $year, float $citescore, float $snip): void
    {
        $stmt = $this->mysqli->prepare("
            UPDATE INFORMAZIONI_RIVISTE 
            SET CiteScore = ?, SNIP = ? 
            WHERE id = ? AND anno = ?
        ");

        $stmt->bind_param("ddsi", $citescore, $snip, $journal_id, $year);
        $stmt->execute();
        $stmt->affected_rows;
        $stmt->close();
    }

    public function insertJournal(string $id, string $name, string $publisher, string $issn)
    {
        $stmt = $this->mysqli->prepare("
        INSERT IGNORE INTO RIVISTE (id, nome, publisher, issn)
        VALUES (?, ?, ?, ?)
    ");
        if (!$stmt) return false;

        $stmt->bind_param("ssss", $id, $name, $publisher, $issn);
        $stmt->execute();
        $stmt->close();
    }

    public function insertJournalInfo(
        string $id,
        int $year,
        float $sjr,
        string $best_quartile,
        int $rank
    ) {
        $stmt = $this->mysqli->prepare("
        INSERT IGNORE INTO INFORMAZIONI_RIVISTE
        (id, anno, SJR, SNIP, miglior_quartile, classifica, CiteScore)
        VALUES (?, ?, ?, NULL, ?, ?, NULL)
    ");
        if (!$stmt) return false;

        $stmt->bind_param("sidsi", $id, $year, $sjr, $best_quartile, $rank);
        $stmt->execute();
        $stmt->close();
    }

    public function insertCategorySpecialization(string $category_name, string $id): void
    {
        $stmt = $this->mysqli->prepare("
        INSERT IGNORE INTO SPECIALIZZAZIONI_CATEGORIE (nome_categoria, id)
        VALUES (?, ?)
    ");
        if (!$stmt) return;

        $stmt->bind_param("ss", $category_name, $id);
        $stmt->execute();
        $stmt->close();
    }

    public function insertAreaSpecialization(string $area_name, string $id): void
    {
        $stmt = $this->mysqli->prepare("
        INSERT IGNORE INTO SPECIALIZZAZIONI_AREA (nome_area, id)
        VALUES (?, ?)
    ");
        if (!$stmt) return;

        $stmt->bind_param("ss", $area_name, $id);
        $stmt->execute();
        $stmt->close();
    }

    public function insertQuartile(string $category_name, string $id, int $value, int $year): void
    {
        $stmt = $this->mysqli->prepare("
        INSERT INTO QUARTILI (nome_categoria, id, valore, anno)
        VALUES (?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE valore = LEAST(valore, VALUES(valore))
    ");
        if (!$stmt) return;

        $stmt->bind_param("ssii", $category_name, $id, $value, $year);
        $stmt->execute();
        $stmt->close();
    }

    public function journalExists(string $journalId): bool
    {
        $stmt = $this->mysqli->prepare("SELECT 1 FROM RIVISTE WHERE id = ? LIMIT 1");
        if (!$stmt) return false;

        $stmt->bind_param("s", $journalId);
        $stmt->execute();
        $result = $stmt->get_result();
        $exists = $result->num_rows > 0;
        $stmt->close();

        return $exists;
    }
}
