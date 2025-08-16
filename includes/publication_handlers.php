<?php

require_once '../../db/repositories/PaperRepository.php';
require_once '../../db/repositories/ArticleRepository.php';

function handleFWCIUpdate($mysqli, $scopus_id, $publication_type = 'article')
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_fwci'])) {
        $doi = trim($_POST['doi']);
        $new_fwci = trim($_POST['new_fwci']);

        if (!empty($doi)) {
            if ($publication_type === 'article') {
                $repo = new ArticleRepository($mysqli);
                $redirect_page = 'journal_articles.php';
            } else {
                $repo = new PaperRepository($mysqli);
                $redirect_page = 'conference_papers.php';
            }

            $fwci_to_set = empty($new_fwci) ? null : floatval($new_fwci);

            if ($repo->updateFWCI($doi, $fwci_to_set)) {
                header("Location: {$redirect_page}?scopus_id=" . urlencode($scopus_id));
            }
        }
    }
    return;
}

function handleJournalIdUpdate($mysqli, $scopus_id)
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_rivista'])) {
        $doi = trim($_POST['doi']);
        $new_rivista_id = trim($_POST['new_rivista_id']);

        if (!empty($doi)) {
            $articleRepo = new ArticleRepository($mysqli);

            $rivista_id_to_set = empty($new_rivista_id) ? null : $new_rivista_id;

            if ($articleRepo->updateArticleJournalId($doi, $rivista_id_to_set)) {
                header("Location: journal_articles.php?scopus_id=" . urlencode($scopus_id));
            }
        }
    }
}

function handleAcronymUpdate($mysqli, $scopus_id)
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_acronym'])) {
        $dblp_venue = trim($_POST['dblp_venue']);
        $new_acronym = trim($_POST['new_acronym']);

        if (!empty($dblp_venue)) {
            $paperRepo = new PaperRepository($mysqli);

            $acronym_to_set = empty($new_acronym) ? null : $new_acronym;

            if ($paperRepo->updateConferenceAcronymByDblpVenue($dblp_venue, $acronym_to_set)) {
                header("Location: conference_papers.php?scopus_id=" . urlencode($scopus_id));
            }
        }
    }
}

function renderEditableFWCI($publication, $label = 'FWCI')
{
    $doi_hash = md5($publication['DOI']);
?>
    <tr>
        <td><strong><?php echo $label; ?></strong></td>
        <td>
            <div id="edit_fwci_<?php echo $doi_hash; ?>" style="display: inline;">
                <span><?php echo $publication['FWCI'] ?? 'Non impostato'; ?></span>
                <button type="button" class="btn-edit">Modifica</button>
            </div>
            <div id="form_fwci_<?php echo $doi_hash; ?>" style="display: none;">
                <form method="post" style="display: inline;">
                    <input type="hidden" name="doi" value="<?php echo htmlspecialchars($publication['DOI']); ?>">
                    <input type="number" step="0.001" name="new_fwci" value="<?php echo htmlspecialchars($publication['FWCI'] ?? ''); ?>" placeholder="FWCI">
                    <button type="submit" name="update_fwci">Salva</button>
                    <button type="button" class="btn-cancel">Annulla</button>
                </form>
            </div>
        </td>
    </tr>
<?php
}

function renderEditableAcronym($paper)
{
    $doi_hash = md5($paper['DOI']);
?>
    <tr>
        <td><strong>Acronimo Core Conferenza</strong></td>
        <td>
            <div id="edit_acronym_<?php echo $doi_hash; ?>" style="display: inline;">
                <span><?php echo htmlspecialchars($paper['acronimo'] ?? 'N/A'); ?></span>
                <button type="button" class="btn-edit">Modifica</button>
            </div>
            <div id="form_acronym_<?php echo $doi_hash; ?>" style="display: none;">
                <form method="post" style="display: inline;">
                    <input type="hidden" name="dblp_venue" value="<?php echo htmlspecialchars($paper['acronimo_dblp']); ?>">
                    <input type="text" name="new_acronym" value="<?php echo htmlspecialchars($paper['acronimo'] ?? ''); ?>" placeholder="Acronimo">
                    <button type="submit" name="update_acronym">Salva</button>
                    <button type="button" class="btn-cancel">Annulla</button>
                </form>
                <br><small>Modifica l'acronimo per tutti i paper con venue DBLP: "<?php echo htmlspecialchars($paper['acronimo_dblp']); ?>"</small>
            </div>
        </td>
    </tr>
<?php
}

function renderEditableJournalId($article)
{
    $doi_hash = md5($article['DOI']);
?>
    <tr>
        <td><strong>ID Rivista</strong></td>
        <td>
            <div id="edit_<?php echo $doi_hash; ?>" style="display: inline;">
                <span><?php echo htmlspecialchars($article['rivista_id'] ?? 'Nessuna rivista collegata'); ?></span>
                <button type="button" class="btn-edit">Modifica</button>
            </div>
            <div id="form_<?php echo $doi_hash; ?>" style="display: none;">
                <form method="post" style="display: inline;">
                    <input type="hidden" name="doi" value="<?php echo htmlspecialchars($article['DOI']); ?>">
                    <input type="text" name="new_rivista_id" value="<?php echo htmlspecialchars($article['rivista_id'] ?? ''); ?>" placeholder="ID Rivista">
                    <button type="submit" name="update_rivista">Salva</button>
                    <button type="button" class="btn-cancel">Annulla</button>
                </form>
            </div>
        </td>
    </tr>
<?php
}

function calculatePublicationStats($publications)
{
    $total = count($publications);
    $total_citations = array_sum(array_column($publications, 'citation_count'));
    $by_year = [];

    foreach ($publications as $pub) {
        $anno = $pub['anno'];
        $by_year[$anno] = ($by_year[$anno] ?? 0) + 1;
    }

    ksort($by_year);

    return [
        'total' => $total,
        'total_citations' => $total_citations,
        'average_citations' => $total > 0 ? round($total_citations / $total, 2) : 0,
        'by_year' => $by_year
    ];
}

function handleHIndexUpdate($mysqli, $scopus_id)
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_hindex'])) {
        $new_hindex = trim($_POST['new_hindex']);

        $authorRepo = new AuthorRepository($mysqli, $scopus_id);
        $hindex_to_set = empty($new_hindex) ? null : floatval($new_hindex);

        if ($authorRepo->updateHIndex($hindex_to_set)) {
            header("Location: index.php?scopus_id=" . urlencode($scopus_id));
        }
    }
}

function renderEditingJavaScript()
{
?>
    <script>
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('btn-edit')) {
                const editDiv = e.target.closest('div[id^="edit_"]');
                const editId = editDiv.id;
                const formId = editId.replace('edit_', 'form_');

                editDiv.style.display = 'none';
                document.getElementById(formId).style.display = 'inline';
            }

            if (e.target.classList.contains('btn-cancel')) {
                const formDiv = e.target.closest('div[id^="form_"]');
                const formId = formDiv.id;
                const editId = formId.replace('form_', 'edit_');

                formDiv.style.display = 'none';
                document.getElementById(editId).style.display = 'inline';
            }
        });
    </script>
<?php
}
?>