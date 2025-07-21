<?php

require_once dirname(__DIR__) . '/includes/env_loader.php';

define('SCOPUS_API_KEY', $_ENV['SCOPUS_API_KEY'] ?? '');

if (empty(SCOPUS_API_KEY)) {
    throw new Exception("SCOPUS_API_KEY non configurata nel file .env");
}

