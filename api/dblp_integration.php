<?php

function searchDBLPByAuthor(string $name, string $surname)
{
    $all_publications = [];

    $query = "{$name} {$surname}";

    try {
        $results = makeDBLPRequest($query);
        $formatted = formatDBLPResults($results);

        if (!empty($formatted)) {
            $all_publications = array_merge($all_publications, $formatted);
        }
    } catch (Exception $e) {
    }

    return $all_publications;
}

function makeDBLPRequest($query, $max_results = 200)
{
    $url = 'https://dblp.org/search/publ/api?' . http_build_query([
        'q' => $query,
        'format' => 'json',
        'h' => $max_results
    ]);

    $response = file_get_contents($url);
    if ($response === false) {
        throw new Exception('Errore chiamata DBLP');
    }

    $data = json_decode($response, true);
    if (!$data) {
        throw new Exception('Errore parsing JSON DBLP');
    }

    return $data;
}

function filterDBLPPublications($publications)
{
    return array_filter($publications, fn($pub) => !empty($pub['doi']));
}

function formatDBLPResults($results)
{
    $hits = $results['result']['hits']['hit'] ?? [];
    if (isset($hits['info'])) $hits = [$hits];

    return array_map(function ($hit) {
        $info = $hit['info'];
        return [
            'title' => $info['title'] ?? '',
            'authors' => getAuthorsArray($info['authors']['author'] ?? []),
            'venue' => is_array($info['venue'] ?? '') ? implode(' ', $info['venue']) : ($info['venue'] ?? ''),
            'year' => (int)($info['year'] ?? 0),
            'type' => is_array($info['type'] ?? '') ? implode(' ', $info['type']) : ($info['type'] ?? ''),
            'doi' => $info['doi'] ?? null,
        ];
    }, $hits);
}

function getAuthorsArray($authors)
{
    if (empty($authors)) return [];
    if (is_string($authors)) return [$authors];
    if (is_array($authors) && isset($authors[0])) {
        return array_map(fn($a) => is_array($a) ? ($a['text'] ?? '') : $a, $authors);
    }
    return is_array($authors) ? [is_array($authors) ? ($authors['text'] ?? '') : $authors] : [];
}

function getAuthorNameFromPid(string $pid): array
{
    $url = "https://dblp.org/pid/{$pid}.xml";

    $response = file_get_contents($url);
    if ($response === false) {
        throw new Exception('Errore chiamata DBLP PID');
    }

    $xml = simplexml_load_string($response);
    if (!$xml) {
        throw new Exception('Errore parsing XML DBLP');
    }

    $fullName = (string)$xml['name'];
    if (empty($fullName)) {
        throw new Exception('Nome non trovato nell\'attributo name');
    }

    $parts = explode(' ', $fullName);
    $surname = array_pop($parts);
    $name = implode(' ', $parts);

    return [
        'name' => $name,
        'surname' => $surname
    ];
}
