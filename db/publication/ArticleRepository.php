<?php

require_once 'PublicationRepository.php';

class ArticleRepository
{
    private PublicationRepository $publicationRepo;
    private const TABLE_NAME = 'ARTICOLI';
    private mysqli $mysqli;

    public function __construct(mysqli $mysqli)
    {
        $this->mysqli = $mysqli;
        $this->publicationRepo = new PublicationRepository($mysqli, self::TABLE_NAME);
    }

    public function existsByDoi(string $doi): bool
    {
        return $this->publicationRepo->existsByDoi($doi);
    }

    public function insert(array $articleData): bool
    {
        return $this->publicationRepo->insertPublication($articleData, 'id');
    }

    public function getJournalArticlesDetails(string $scopusId): array
    {
        $stmt = $this->mysqli->prepare("
        SELECT 
            a.DOI,
            a.titolo,
            a.anno,
            a.numero_autori,
            a.nome_autori,
            a.EFWCI,
            a.FWCI,
            a.citation_count,
            a.scopus_id as pub_scopus_id,
            a.dblpRivista,
            a.id as rivista_id,
            r.nome as nome_rivista,
            r.publisher,
            r.issn,
            ir.SJR,
            ir.SNIP,
            ir.miglior_quartile,
            ir.classifica,
            ir.CiteScore
        FROM ARTICOLI a
        INNER JOIN REDAZIONE red ON a.DOI = red.DOI
        LEFT JOIN RIVISTE r ON a.id = r.id
        LEFT JOIN INFORMAZIONI_RIVISTE ir ON r.id = ir.id AND ir.anno = a.anno
        WHERE red.scopus_id = ?
        ORDER BY a.anno DESC, a.citation_count DESC, a.titolo ASC
    ");

        if (!$stmt) {
            return [];
        }

        $stmt->bind_param("s", $scopusId);
        $stmt->execute();
        $result = $stmt->get_result();

        $articles = [];
        while ($row = $result->fetch_assoc()) {
            $articles[] = $row;
        }

        $stmt->close();
        return $articles;
    }

    public function updateArticleJournalId(string $doi, ?string $journal_id): bool
    {
        if ($journal_id !== null) {
            $stmt = $this->mysqli->prepare("SELECT id FROM RIVISTE WHERE id = ?");
            if ($stmt) {
                $stmt->bind_param("s", $journal_id);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result->num_rows === 0) {
                    $journal_id = null;
                }
            }
        }

        $stmt = $this->mysqli->prepare("UPDATE ARTICOLI SET id = ? WHERE DOI = ?");
        if (!$stmt) return false;

        $stmt->bind_param("ss", $journal_id, $doi);
        $success = $stmt->execute();
        $stmt->close();

        return $success;
    }

    public function updateFWCI(string $doi, ?float $fwci): bool
    {
        return $this->publicationRepo->updateFWCI($doi, $fwci);
    }
}
