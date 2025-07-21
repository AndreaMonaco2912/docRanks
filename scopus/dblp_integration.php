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
        error_log("Errore query DBLP '{$query}': " . $e->getMessage());
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
