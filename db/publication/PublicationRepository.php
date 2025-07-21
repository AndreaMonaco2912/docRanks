<?php

class PublicationRepository
{
    private mysqli $mysqli;
    private string $tableName;

    public function __construct(mysqli $mysqli, string $tableName)
    {
        $this->mysqli = $mysqli;
        $this->tableName = $tableName;
    }

    public function existsByDoi(string $doi): bool
    {
        $stmt = $this->mysqli->prepare("SELECT DOI FROM {$this->tableName} WHERE DOI = ?");
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("s", $doi);
        $stmt->execute();
        $result = $stmt->get_result();
        $exists = $result->num_rows > 0;
        $stmt->close();

        return $exists;
    }

    public function calculateEfwci(?int $citationCount): ?float
    {
        return $citationCount !== null ? floatval($citationCount) / 25.0 : null;
    }

    public function insertPublication(array $data, string $lastColumnName): bool
    {
        $efwci = $this->calculateEfwci($data['citation_count']);

        $isArticle = ($this->tableName === 'ARTICOLI');

        $baseFields = "titolo, anno, numero_autori, DOI, nome_autori, EFWCI, FWCI, citation_count, scopus_id";
        $basePlaceholders = "?, ?, ?, ?, ?, ?, ?, ?, ?";
        $baseTypes = "siissddis";

        if ($isArticle) {
            $fields = "{$baseFields}, dblpRivista, {$lastColumnName}";
            $placeholders = "{$basePlaceholders}, ?, ?";
            $types = "{$baseTypes}ss";
        } else {
            $fields = "{$baseFields}, {$lastColumnName}";
            $placeholders = "{$basePlaceholders}, ?";
            $types = "{$baseTypes}s";
        }

        $stmt = $this->mysqli->prepare("
        INSERT INTO {$this->tableName} ({$fields}) VALUES ({$placeholders})
    ");

        if (!$stmt) return false;

        $values = [
            $data['titolo'],
            $data['anno'],
            $data['numero_autori'],
            $data['DOI'],
            $data['nome_autori'],
            $efwci,
            $data['FWCI'],
            $data['citation_count'],
            $data['scopus_id']
        ];

        if ($isArticle) {
            $values[] = $data['dblpRivista'];
        }
        $values[] = $data[$lastColumnName];

        $stmt->bind_param($types, ...$values);
        $success = $stmt->execute();
        $stmt->close();

        return $success;
    }

    public function updateFWCI(string $doi, ?float $fwci): bool
    {
        $stmt = $this->mysqli->prepare("UPDATE {$this->tableName} SET FWCI = ? WHERE DOI = ?");
        if (!$stmt) return false;

        $stmt->bind_param("ds", $fwci, $doi);
        $success = $stmt->execute();
        $stmt->close();

        return $success;
    }
}
