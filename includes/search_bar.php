<?php
$current_scopus_id = isset($_GET['scopus_id']) ? htmlspecialchars($_GET['scopus_id']) : '';
?>

<section class="card">
    <h2 class="card-header">Ricerca Autore</h2>

    <form class="card-body" action="index.php" method="get">
        <div class="form-group row mb-3">
            <div class="col-sm-2">
                <label class="col-form-label mb-2" for="scopus_id">Inserisci Scopus ID dell'autore:</label>
            </div>
            <div class="col-sm-3">
                <input type="text"
                    class="form-control"
                    id=" scopus_id"
                    name="scopus_id"
                    value="<?php echo $current_scopus_id; ?>"
                    placeholder="Es: 57193867382"
                    pattern="[0-9]+"
                    title="Inserisci solo numeri"
                    required>
            </div>
        <div class="col-sm-2">
        <button type="submit" class="btn btn-primary mb-2">Cerca Autore</button>
        </div>
</div>
        <?php if (!empty($current_scopus_id)): ?>
            <a href="index.php">Pulisci</a>
        <?php endif; ?>
    </form>


</section>