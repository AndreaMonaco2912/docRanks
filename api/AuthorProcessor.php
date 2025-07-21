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

class AuthorProcessor
{
    private mysqli $mysqli;
    private ScopusAuthorData $scopusData;
    private AuthorRepository $authorRepository;
    private PaperRepository $paperRepository;
    private ArticleRepository $articleRepository;
    private ConferenceRepository $conferenceRepository;
    private JournalRepository $journalRepository;

    public function __construct(mysqli $mysqli)
    {
        $this->mysqli = $mysqli;
    }

    public function processAuthorComplete(string $scopusId, string $name, string $surname): bool
    {
        $this->authorRepository = new AuthorRepository($this->mysqli, $scopusId);
        $this->paperRepository = new PaperRepository($this->mysqli);
        $this->articleRepository = new ArticleRepository($this->mysqli);
        $this->conferenceRepository = new ConferenceRepository($this->mysqli);
        $this->journalRepository = new JournalRepository($this->mysqli);

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
            'numero_riviste' => 0,
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
                }
            } catch (Exception $e) {
                error_log("Errore pubblicazione '{$pub['title']}': " . $e->getMessage());
            }
        }
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

        $acronym = $this->conferenceRepository->insertConferenceIfNotExists($pub['venue']);
        if (!$acronym) {
            throw new Exception("Impossibile creare conferenza per: {$pub['venue']}");
        }
        $paperData = prepareConferencePaperData($pub, $this->scopusData->pub_doi[$doi] ?? []);
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

        $journalId = $this->journalRepository->getJournalByVenue($pub['venue']);
        
        $articleData = prepareArticleData($pub, $this->scopusData->pub_doi[$doi] ?? []);
        $articleData['id'] = $journalId;

        if (!$this->articleRepository->insert($articleData)) {
            throw new Exception("Errore inserimento articolo");
        }

        $this->authorRepository->insertRedaction($doi);
    }
}
