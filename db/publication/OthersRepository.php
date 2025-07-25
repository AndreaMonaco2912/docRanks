<?php

class OthersRepository
{
    private mysqli $mysqli;

    public function __construct(mysqli $mysqli)
    {
        $this->mysqli = $mysqli;
    }

    public function existsByDoi(string $doi): bool
    {
        $stmt = $this->mysqli->prepare("SELECT DOI FROM OTHERS WHERE DOI = ?");
        if (!$stmt) return false;

        $stmt->bind_param("s", $doi);
        $stmt->execute();
        $result = $stmt->get_result();
        $exists = $result->num_rows > 0;
        $stmt->close();

        return $exists;
    }

    public function insert(array $data): bool
    {
        $stmt = $this->mysqli->prepare("
            INSERT INTO OTHERS (titolo, nome_autori, anno, dblp_venue, tipo, DOI) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        if (!$stmt) return false;

        $stmt->bind_param(
            "ssisss",
            $data['titolo'],
            $data['nome_autori'],
            $data['anno'],
            $data['dblp_venue'],
            $data['tipo'],
            $data['DOI']
        );

        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }

    public function getOthersDetails(string $scopusId): array
    {
        $stmt = $this->mysqli->prepare("
            SELECT 
                o.DOI,
                o.titolo,
                o.anno,
                o.nome_autori,
                o.dblp_venue,
                o.tipo
            FROM OTHERS o
            INNER JOIN PUBBLICAZIONE_ALTRO pa ON o.DOI = pa.DOI
            WHERE pa.scopus_id = ?
            ORDER BY o.anno DESC, o.titolo ASC
        ");

        if (!$stmt) return [];

        $stmt->bind_param("s", $scopusId);
        $stmt->execute();
        $result = $stmt->get_result();

        $others = [];
        while ($row = $result->fetch_assoc()) {
            $others[] = $row;
        }

        $stmt->close();
        return $others;
    }
}
