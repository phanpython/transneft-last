<?php

namespace widgets\status;

class Status
{
    protected $pdo;

    public function __construct($db) {
        $this->pdo = $db;
    }

    public function getStatuses() {
        $query = "SELECT * FROM get_status()";
        $stmt = $this->pdo->query($query);

        return $stmt->fetchAll();
    }
}