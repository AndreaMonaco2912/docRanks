<?php
define('SCOPUS_RATE_LIMIT_DELAY', 1);

class ScopusAuthorData
{
    private string $api_key;
    private string $base_url = 'https://api.elsevier.com/content/';
    public string $scopus_id;

    /** @var array<string, array{scopus_id: ?string, citation: ?int}> */
    public array $pub_doi = [];

    /** @var array<int, array{documents: int, citation: int}> */
    public array $year_data = [];

    public function __construct(string $api_key)
    {
        $this->api_key = $api_key;
    }

    public function loadAuthorData(string $scopus_id): bool|string
    {
        $this->scopus_id = $scopus_id;
        $result = $this->loadPub($scopus_id);

        if ($result !== true) {
            return $result;
        }

        return true;
    }

    private function loadPub(string $scopus_id): bool
    {
        $url = $this->base_url . "search/scopus";
        $query = "AU-ID({$scopus_id})";
        $start = 0;
        $count = 25;

        do {
            $response = $this->fetchPublicationBatch($url, $query, $start, $count);
            if (!$response) {
                break;
            }

            $data = json_decode($response, true);
            $entries = $data['search-results']['entry'] ?? [];

            foreach ($entries as $entry) {
                $this->processPublication($entry);
            }

            $start += $count;
            $total_results = (int)($data['search-results']['opensearch:totalResults'] ?? 0);

            if ($start >= $total_results) {
                break;
            }

            sleep((int)SCOPUS_RATE_LIMIT_DELAY);
        } while (true);

        return true;
    }

    private function fetchPublicationBatch(string $url, string $query, int $start, int $count): false|string
    {
        $params = [
            'query' => $query,
            'start' => $start,
            'count' => $count,
            'sort' => 'pubyear',
            'view' => 'STANDARD',
            'field' => 'doi,citedby-count,coverDate,prism:publicationName,dc:identifier'
        ];

        $full_url = $url . '?' . http_build_query($params);
        $headers = [
            "X-ELS-APIKey: {$this->api_key}",
            "Accept: application/json",
            "User-Agent: PHP-Scopus-Client/1.0"
        ];

        return $this->makeApiCall($full_url, $headers);
    }

    /** @param array<string, mixed> $entry */
    private function processPublication(array $entry): void
    {
        if (empty($entry['prism:doi'])) {
            return;
        }

        $doi = $entry['prism:doi'];
        $citation = (int)($entry['citedby-count'] ?? 0);
        $scopus_pub_id = $entry['dc:identifier'] ?? null;

        $this->pub_doi[$doi] = [
            'scopus_id' => $scopus_pub_id ?: null,
            'citation' => $scopus_pub_id ? $citation : null
        ];

        if (!empty($entry['prism:coverDate'])) {
            $anno = (int)substr($entry['prism:coverDate'], 0, 4);
            $this->updateYearStats($anno, $citation);
        }
    }

    private function updateYearStats(int $anno, int $citation): void
    {
        if (!isset($this->year_data[$anno])) {
            $this->year_data[$anno] = ['documents' => 0, 'citation' => 0];
        }

        $this->year_data[$anno]['documents']++;
        $this->year_data[$anno]['citation'] += $citation;
    }

    /** @param string[] $headers */
    private function makeApiCall(string $url, array $headers): false|string
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        curl_close($ch);

        if ($http_code === 200 && empty($curl_error)) {
            return $response;
        }

        error_log("Scopus API Error: HTTP $http_code - $curl_error");
        return false;
    }

    public function getPub(): int
    {
        return count($this->pub_doi);
    }

    public function getCitation(): int
    {
        return array_sum(array_map(fn($d) => $d['citation'] ?? 0, $this->pub_doi));
    }

    /** @return array<int, array{documents: int, citation: int}> */
    public function getYearPub(): array
    {
        return $this->year_data;
    }

    public function debug(): void
    {
        echo "=== DEBUG DATI AUTORE ===\n";
        echo "Scopus ID: {$this->scopus_id}\n";
        echo "Totale pubblicazioni: " . $this->getPub() . "\n";
        echo "Totale citation: " . $this->getCitation() . "\n";
        echo "\nDati per anno:\n";
        echo "\n=== DEBUG CALCOLI ===\n";
        echo "Count pubblicazioni_doi: " . count($this->pub_doi) . "\n";
        echo "Somma citation calcolata: " . $this->getCitation() . "\n";
        echo "Anni con dati: " . count($this->year_data) . "\n";

        foreach ($this->year_data as $anno => $dati) {
            echo "  $anno: {$dati['documents']} documenti, {$dati['citation']} citation\n";
        }

        echo "\nPubblicazioni DOI (prime 5):\n";
        $count = 0;
        foreach ($this->pub_doi as $doi => $info) {
            if ($count++ >= 5) {
                break;
            }
            $scopus_id_display = $info['scopus_id'] ?? 'null';
            $citation_display = $info['citation'] ?? 'null';
            echo "  DOI: $doi - Scopus ID: $scopus_id_display - citation: $citation_display\n";
        }

        if (count($this->pub_doi) > 5) {
            echo "  ... e altre " . (count($this->pub_doi) - 5) . " pubblicazioni\n";
        }
        echo "========================\n";
    }
}
