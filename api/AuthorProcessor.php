<?php
require_once dirname(__DIR__) . '/scopus/ScopusAuthorData.php';
require_once dirname(__DIR__) . '/scopus/dblp_integration.php';
require_once dirname(__DIR__) . '/db/connection.php';
require_once dirname(__DIR__) . '/db/AuthorRepository.php';
require_once dirname(__DIR__) . '/db/publication/PaperRepository.php';
require_once dirname(__DIR__) . '/db/publication/ArticleRepository.php';
require_once dirname(__DIR__) . '/db/JournalRepository.php';
require_once dirname(__DIR__) . '/db/core/ConferenceRepository.php';
require_once dirname(__DIR__) . '/includes/publication_parser.php';
require_once dirname(__DIR__) . '/includes/env_loader.php';
require_once dirname(__DIR__) . '/db/publication/OthersRepository.php';

class AuthorProcessor
{
    private mysqli $mysqli;
    private ScopusAuthorData $scopusData;
    private AuthorRepository $authorRepository;
    private PaperRepository $paperRepository;
    private ArticleRepository $articleRepository;
    private ConferenceRepository $conferenceRepository;
    private JournalRepository $journalRepository;
    private OthersRepository $othersRepository;

    public function __construct(mysqli $mysqli)
    {
        $this->mysqli = $mysqli;
    }

    public function processAuthorCompleteByPid(string $scopusId, string $dblpPid): bool
    {
        try {
            $authorInfo = getAuthorNameFromPid(trim($dblpPid));

            return $this->processAuthorComplete($scopusId, $authorInfo['name'], $authorInfo['surname']);
        } catch (Exception $e) {
            return false;
        }
    }

    public function processAuthorComplete(string $scopusId, string $name, string $surname): bool
    {
        $this->authorRepository = new AuthorRepository($this->mysqli, $scopusId);
        $this->paperRepository = new PaperRepository($this->mysqli);
        $this->articleRepository = new ArticleRepository($this->mysqli);
        $this->conferenceRepository = new ConferenceRepository($this->mysqli);
        $this->journalRepository = new JournalRepository($this->mysqli);
        $this->othersRepository = new OthersRepository($this->mysqli);

        try {
            if (empty($_ENV['SCOPUS_API_KEY'])) {
                throw new Exception('API Key Scopus non configurata');
            }

            if (empty($name) || empty($surname)) {
                throw new Exception("Il name completo dell'autore non è valido.");
            }

            $this->scopusData = new ScopusAuthorData($_ENV['SCOPUS_API_KEY']);
            $result = $this->scopusData->loadAuthorData($scopusId);

            if ($result !== true) {
                throw new Exception("Errore caricamento Scopus: {$result}");
            }

            $dblpPublications = searchDBLPByAuthor($name, $surname);

            $filtered = filterDBLPPublications($dblpPublications);

            $this->insertOrUpdateAuthor($name, $surname);

            $this->insertAuthorYearlyInfo();

            $this->processPublications($filtered);

            return true;
        } catch (Exception $e) {
            return false;
        }
    }


    private function insertOrUpdateAuthor(string $name, string $surname): void
    {
        $this->authorRepository->insertOrUpdateAuthor([
            'nome' => $name,
            'cognome' => $surname,
            'scopus_id' => $this->scopusData->scopus_id,
            'numero_citazioni' => $this->scopusData->getCitation(),
            'numero_documenti' => $this->scopusData->getPub()
        ]);
    }

    private function insertAuthorYearlyInfo(): void
    {
        $year_data = $this->scopusData->getYearPub();
        foreach ($year_data as $year => $data) {
            $this->authorRepository->upsertAuthorYearInfo($year, $data['documents'], $data['citation']);
        }
    }

    private function processPublications(array $publications): void
    {
        foreach ($publications as $pub) {
            try {
                if ($pub['type'] === 'Conference and Workshop Papers') {
                    $this->processConferencePaper($pub);
                } elseif ($pub['type'] === 'Journal Articles') {
                    $this->processJournalArticle($pub);
                } else {
                    $this->processOtherPublication($pub);
                }
            } catch (Exception $e) {
            }
        }
    }

    private function processOtherPublication(array $pub): void
    {
        $doi = $pub['doi'];

        if ($this->othersRepository->existsByDoi($doi)) {
            if (!$this->authorRepository->otherPublicationExists($doi)) {
                $this->authorRepository->insertOtherPublication($doi);
            }
            return;
        }

        $authors_string = implode(', ', $pub['authors']);

        $otherData = [
            'titolo' => $pub['title'],
            'nome_autori' => $authors_string,
            'anno' => $pub['year'],
            'dblp_venue' => $pub['venue'],
            'tipo' => $pub['type'],
            'DOI' => $doi
        ];

        if (!$this->othersRepository->insert($otherData)) {
            throw new Exception("Errore inserimento altra pubblicazione");
        }

        $this->authorRepository->insertOtherPublication($doi);
    }

    private function processConferencePaper(array $pub): void
    {
        $doi = $pub['doi'];

        if ($this->paperRepository->existsByDoi($doi)) {
            if (!$this->authorRepository->participationExists($doi)) {
                $this->authorRepository->insertParticipation($doi);
            }
            return;
        }

        $acronym = $this->conferenceRepository->findConferenceByVenue($pub['venue']);
        
        $paperData = prepareConferencePaperData($pub, $this->scopusData->getPublicationData($doi) ?? []);
        $paperData['acronimo'] = $acronym;

        if (!$this->paperRepository->insert($paperData)) {
            throw new Exception("Errore inserimento atto convegno");
        }

        $this->authorRepository->insertParticipation($doi);
    }

    private function processJournalArticle(array $pub): void
    {
        $doi = $pub['doi'];

        if ($this->articleRepository->existsByDoi($doi)) {
            if (!$this->authorRepository->redactionExists($doi)) {
                $this->authorRepository->insertRedaction($doi);
            }
            return;
        }

        $scopusData = $this->scopusData->getPublicationData($doi);
        $journalId = null;
        if ($scopusData && !empty($scopusData['source_id'])) {
            $journalId = $scopusData['source_id'];
        } else {
            $journalId = $this->journalRepository->getJournalByVenue($pub['venue']);
        }

        $articleData = prepareArticleData($pub, $scopusData ?? []);
        $articleData['id'] = $journalId;

        if (!$this->articleRepository->insert($articleData)) {
            throw new Exception("Errore inserimento articolo");
        }

        $this->authorRepository->insertRedaction($doi);
    }
}
