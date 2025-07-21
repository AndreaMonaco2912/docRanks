<?php
$current_scopus_id = isset($_GET['scopus_id']) ? htmlspecialchars($_GET['scopus_id']) : '';
?>

<section>
    <h2>Ricerca Autore</h2>

    <form action="index.php" method="get">
        <label for="scopus_id">Inserisci Scopus ID dell'autore:</label>
        <input type="text"
            id="scopus_id"
            name="scopus_id"
            value="<?php echo $current_scopus_id; ?>"
            placeholder="Es: 57193867382"
            pattern="[0-9]+"
            title="Inserisci solo numeri"
            required>
        <button type="submit">Cerca Autore</button>

        <?php if (!empty($current_scopus_id)): ?>
            <a href="index.php">Pulisci</a>
        <?php endif; ?>
    </form>
</section>