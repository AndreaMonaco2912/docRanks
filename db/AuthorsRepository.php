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
            FROM AUTORI
        ");

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function removeAuthorsFromDb(): bool
    {
        try {
            $this->db->query("SET FOREIGN_KEY_CHECKS = 0");

            $this->db->query("TRUNCATE TABLE AUTORI");
            $this->db->query("TRUNCATE TABLE INFORMAZIONI_AUTORI");
            $this->db->query("TRUNCATE TABLE PARTECIPAZIONE");
            $this->db->query("TRUNCATE TABLE REDAZIONE");
            $this->db->query("TRUNCATE TABLE PUBBLICAZIONE_ALTRO");

            $this->db->query("SET FOREIGN_KEY_CHECKS = 1");

            return true;
        } catch (Exception $e) {
            $this->db->query("SET FOREIGN_KEY_CHECKS = 1");
            return false;
        }
    }
}
