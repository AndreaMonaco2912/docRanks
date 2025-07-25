<?php

$servername = getenv('DB_HOST') ?: "localhost";
$username = "root";
$password = "";
$database = "docranks";

$mysqli = new mysqli($servername, $username, $password, $database);

if ($mysqli->connect_error) {
    die("Errore di connessione al database: " . $mysqli->connect_error);
}

$mysqli->set_charset("utf8");
