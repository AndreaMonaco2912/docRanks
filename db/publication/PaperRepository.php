<?php

require_once 'PublicationRepository.php';

class PaperRepository
{
    private PublicationRepository $publicationRepo;
    private const TABLE_NAME = 'ATTI_DI_CONVEGNO';
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

    public function insert(array $paperData): bool
    {
        return $this->publicationRepo->insertPublication($paperData, 'acronimo');
    }

    public function getConferencePapersDetails(string $scopusId): array
    {
        $stmt = $this->mysqli->prepare("
        SELECT 
            ac.DOI,
            ac.titolo,
            ac.anno,
            ac.numero_autori,
            ac.nome_autori,
            ac.EFWCI,
            ac.FWCI,
            ac.citation_count,
            ac.scopus_id as pub_scopus_id,
            ac.acronimo,
            c.titolo as titolo_conferenza,
            ic.valore as ranking_conferenza
        FROM ATTI_DI_CONVEGNO ac
        INNER JOIN PARTECIPAZIONE p ON ac.DOI = p.DOI
        LEFT JOIN CONFERENZE c ON ac.acronimo = c.acronimo
        LEFT JOIN INFO_CONF ic ON c.acronimo = ic.acronimo AND ic.anno = ac.anno
        WHERE p.scopus_id = ?
        ORDER BY ac.anno DESC, ac.citation_count DESC, ac.titolo ASC
    ");

        if (!$stmt) {
            return [];
        }

        $stmt->bind_param("s", $scopusId);
        $stmt->execute();
        $result = $stmt->get_result();

        $papers = [];
        while ($row = $result->fetch_assoc()) {
            $papers[] = $row;
        }

        $stmt->close();
        return $papers;
    }

    public function updateFWCI(string $doi, ?float $fwci): bool
    {
        return $this->publicationRepo->updateFWCI($doi, $fwci);
    }
}
