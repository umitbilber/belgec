<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

abstract class BaseRepository
{
    protected PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function beginTransaction(): void
    {
        if (!$this->db->inTransaction()) {
            $this->db->beginTransaction();
        }
    }

    public function commit(): void
    {
        if ($this->db->inTransaction()) {
            $this->db->commit();
        }
    }

    public function rollBack(): void
    {
        if ($this->db->inTransaction()) {
            $this->db->rollBack();
        }
    }
}