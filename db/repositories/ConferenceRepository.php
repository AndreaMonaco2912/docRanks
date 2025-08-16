<?php

class ConferenceRepository
{
    private mysqli $mysqli;

    public function __construct(mysqli $mysqli)
    {
        $this->mysqli = $mysqli;
    }

    private function clearAcronym(string $venue): string
    {
        $venue = preg_replace('/\d+/', '', $venue);
        return trim($venue);
    }

    public function findConferenceByVenue(string $venue): ?string
    {
        $cleared_venue = $this->clearAcronym($venue);
        if ($cleared_venue === '') return null;

        $stmt = $this->mysqli->prepare("SELECT acronimo FROM CONFERENZE WHERE acronimo = ?");
        if (!$stmt) return null;

        $stmt->bind_param("s", $cleared_venue);
        $stmt->execute();
        $result = $stmt->get_result();
        $acronym = $result->fetch_assoc()['acronimo'] ?? null;
        $stmt->close();

        return $acronym;
    }
}
