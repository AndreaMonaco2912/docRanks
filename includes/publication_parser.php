<?php
function prepareArticleData($publication, $scopus_data = [])
{
    $authors_string = implode(', ', $publication['authors']);
    $numero_autori = count($publication['authors']);

    return [
        'titolo' => $publication['title'],
        'anno' => $publication['year'],
        'numero_autori' => $numero_autori,
        'DOI' => $publication['doi'],
        'nome_autori' => $authors_string,
        'FWCI' => null,
        'citation_count' => $scopus_data['citation'] ?? null,
        'scopus_id' => $scopus_data['scopus_id'] ?? null,
        'dblpRivista' => $publication['venue'],
        'id' => null
    ];
}

function prepareConferencePaperData($publication, $scopus_data = [])
{
    $authors_string = implode(', ', $publication['authors']);
    $numero_autori = count($publication['authors']);

    return [
        'titolo' => $publication['title'],
        'anno' => $publication['year'],
        'numero_autori' => $numero_autori,
        'DOI' => $publication['doi'],
        'nome_autori' => $authors_string,
        'FWCI' => null,
        'citation_count' => $scopus_data['citation'] ?? null,
        'scopus_id' => $scopus_data['scopus_id'] ?? null,
        'acronimo' => null
    ];
}