<?php

class AuthorRepository
{
    private mysqli $mysqli;
    private string $scopusId;

    public function __construct(mysqli $mysqli, string $scopusId)
    {
        $this->mysqli = $mysqli;
        $this->scopusId = $scopusId;
    }

    public function exists(): bool
    {
        $stmt = $this->mysqli->prepare("SELECT COUNT(*) as count FROM AUTORI WHERE scopus_id = ?");
        $stmt->bind_param("s", $this->scopusId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return ($row['count'] ?? 0) > 0;
    }

    public function getProfile(): ?array
    {
        $stmt = $this->mysqli->prepare("SELECT * FROM AUTORI WHERE scopus_id = ?");
        $stmt->bind_param("s", $this->scopusId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc() ?: null;
    }

    public function getArticlesStats(): array
    {
        return $this->getPublicationStats('ARTICOLI', 'REDAZIONE');
    }

    public function getConferencePapersStats(): array
    {
        return $this->getPublicationStats('ATTI_DI_CONVEGNO', 'PARTECIPAZIONE');
    }

    private function getPublicationStats(string $publicationTable, string $relationTable): array
    {
        $stmt = $this->mysqli->prepare("
        SELECT 
            COUNT(*) as total,
            COALESCE(SUM(a.citation_count), 0) as total_citations
        FROM {$publicationTable} a
        INNER JOIN {$relationTable} r ON a.DOI = r.DOI 
        WHERE r.scopus_id = ?
    ");
        $stmt->bind_param("s", $this->scopusId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc() ?: ['total' => 0, 'total_citations' => 0];
    }

    public function getYearlyStats(): array
    {
        $stmt = $this->mysqli->prepare("
            SELECT * FROM INFORMAZIONI_AUTORI 
            WHERE scopus_id = ? 
            ORDER BY anno DESC
        ");
        $stmt->bind_param("s", $this->scopusId);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function insertParticipation(string $doi): void
    {
        $stmt = $this->mysqli->prepare("INSERT IGNORE INTO PARTECIPAZIONE (DOI, scopus_id) VALUES (?, ?)");
        if (!$stmt) return;

        $stmt->bind_param("ss", $doi, $this->scopusId);
        $stmt->execute();
        $stmt->close();
    }

    public function insertRedaction(string $doi): void
    {
        $stmt = $this->mysqli->prepare("INSERT IGNORE INTO REDAZIONE (DOI, scopus_id) VALUES (?, ?)");
        if (!$stmt) return;

        $stmt->bind_param("ss", $doi, $this->scopusId);
        $stmt->execute();
        $stmt->close();
    }

    public function participationExists(string $doi): bool
    {
        $stmt = $this->mysqli->prepare("SELECT 1 FROM PARTECIPAZIONE WHERE DOI = ? AND scopus_id = ?");
        if (!$stmt) return false;

        $stmt->bind_param("ss", $doi, $this->scopusId);
        $stmt->execute();
        $result = $stmt->get_result();
        $exists = $result->num_rows > 0;
        $stmt->close();

        return $exists;
    }

    public function redactionExists(string $doi): bool
    {
        $stmt = $this->mysqli->prepare("SELECT 1 FROM REDAZIONE WHERE DOI = ? AND scopus_id = ?");
        if (!$stmt) return false;

        $stmt->bind_param("ss", $doi, $this->scopusId);
        $stmt->execute();
        $result = $stmt->get_result();
        $exists = $result->num_rows > 0;
        $stmt->close();

        return $exists;
    }

    public function insertOrUpdateAuthor(array $dati): bool
    {
        if ($this->exists()) {
            return $this->updateAuthor($dati);
        } else {
            return $this->insertAuthor($dati);
        }
    }

    private function insertAuthor(array $dati): bool
    {
        $query = "INSERT INTO AUTORI (nome, cognome, scopus_id, h_index, numero_riviste, numero_citazioni, numero_documenti) 
              VALUES (?, ?, ?, null, ?, ?, ?)";

        $stmt = $this->mysqli->prepare($query);
        if (!$stmt) return false;

        $stmt->bind_param(
            "sssiii",
            $dati['nome'],
            $dati['cognome'],
            $dati['scopus_id'],
            $dati['numero_riviste'],
            $dati['numero_citazioni'],
            $dati['numero_documenti']
        );

        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }

    private function updateAuthor(array $dati): bool
    {
        $query = "UPDATE AUTORI 
              SET nome = ?, cognome = ?, numero_riviste = ?, 
                  numero_citazioni = ?, numero_documenti = ? 
              WHERE scopus_id = ?";

        $stmt = $this->mysqli->prepare($query);
        if (!$stmt) return false;

        $stmt->bind_param(
            "ssiiis",
            $dati['nome'],
            $dati['cognome'],
            $dati['numero_riviste'],
            $dati['numero_citazioni'],
            $dati['numero_documenti'],
            $dati['scopus_id']
        );

        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }

    public function authorYearInfoExists(int $anno): bool
    {
        $query = "SELECT COUNT(*) as count FROM INFORMAZIONI_AUTORI WHERE scopus_id = ? AND anno = ?";
        $stmt = $this->mysqli->prepare($query);
        if (!$stmt) return false;

        $stmt->bind_param("si", $this->scopusId, $anno);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        return $row['count'] > 0;
    }

    public function upsertAuthorYearInfo(int $anno, int $documenti, int $citazioni): bool
    {
        $query = "INSERT INTO INFORMAZIONI_AUTORI (scopus_id, anno, documenti, citazioni) 
              VALUES (?, ?, ?, ?) 
              ON DUPLICATE KEY UPDATE 
              documenti = VALUES(documenti), 
              citazioni = VALUES(citazioni)";

        $stmt = $this->mysqli->prepare($query);
        if (!$stmt) return false;

        $stmt->bind_param("siii", $this->scopusId, $anno, $documenti, $citazioni);
        $success = $stmt->execute();

        $stmt->close();

        return $success;
    }

    public function updateHIndex(?float $hIndex): bool
    {
        $query = "UPDATE AUTORI SET h_index = ? WHERE scopus_id = ?";
        $stmt = $this->mysqli->prepare($query);
        if (!$stmt) return false;

        $stmt->bind_param("ds", $hIndex, $this->scopusId);
        $success = $stmt->execute();
        $stmt->close();

        return $success;
    }
}
