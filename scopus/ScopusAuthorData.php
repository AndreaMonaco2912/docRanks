<?php
define('SCOPUS_RATE_LIMIT_DELAY', 1);

class ScopusAuthorData
{
    private string $api_key;
    private string $url = 'https://api.elsevier.com/content/search/scopus';
    public string $scopus_id;

    /** @var array<string, array{scopus_id: ?string, citation: ?int, source_id: ?string}> */
    private array $pub_doi = [];

    /** @var array<int, array{documents: int, citation: int}> */
    private array $year_data = [];

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
        $query = "AU-ID({$scopus_id})";
        $start = 0;
        $count = 25;

        do {
            $response = $this->fetchPublicationBatch($query, $start, $count);
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

    private function fetchPublicationBatch(string $query, int $start, int $count): false|string
    {
        $params = [
            'query' => $query,
            'start' => $start,
            'count' => $count,
            'sort' => 'pubyear',
            'view' => 'STANDARD',
            'field' => 'doi,citedby-count,source-id,eid'
        ];

        $full_url = $this->url . '?' . http_build_query($params);
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

        $doi = strtoupper($entry['prism:doi']);
        $scopus_pub_id = $entry['eid'] ?? null;

        if ($scopus_pub_id) {
            $citation = isset($entry['citedby-count'])
                ? (int)$entry['citedby-count']
                : null;

            $source_id = $entry['source-id'] ?? null;

            $this->pub_doi[$doi] = [
                'scopus_id' => $scopus_pub_id,
                'citation' => $citation,
                'source_id' => $source_id
            ];
        }
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

    public function getPublicationData(string $doi): ?array
    {
        return $this->pub_doi[$doi] ?? null;
    }

    /** @return array<int, array{documents: int, citation: int}> */
    public function getYearPub(): array
    {
        return $this->year_data;
    }
}
