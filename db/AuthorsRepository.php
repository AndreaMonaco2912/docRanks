<?php
class AuthorsRepository
{
    private mysqli $db;

    public function __construct(mysqli $connection)
    {
        $this->db = $connection;
    }

    /**
     * @return array<int, array{nome: string, cognome: string, scopus_id: string}>
     */
    public function getAllAuthors(): array
    {
        $result = $this->db->query("
            SELECT nome, cognome, scopus_id
            FROM autori
        ");

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function removeAuthorsFromDb(): bool
    {
        try {
            $this->db->query("SET FOREIGN_KEY_CHECKS = 0");

            $this->db->query("TRUNCATE TABLE articoli");
            $this->db->query("TRUNCATE TABLE atti_di_convegno");
            $this->db->query("TRUNCATE TABLE autori");
            $this->db->query("TRUNCATE TABLE informazioni_autori");
            $this->db->query("TRUNCATE TABLE partecipazione");
            $this->db->query("TRUNCATE TABLE redazione");

            $this->db->query("SET FOREIGN_KEY_CHECKS = 1");

            return true;
        } catch (Exception $e) {
            $this->db->query("SET FOREIGN_KEY_CHECKS = 1");
            return false;
        }
    }
}
