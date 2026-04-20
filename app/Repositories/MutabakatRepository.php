<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Interfaces\MutabakatRepositoryInterface;

class MutabakatRepository extends BaseRepository implements MutabakatRepositoryInterface
{
    public function findCariById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM musteriler WHERE id = ?");
        $stmt->execute([$id]);

        $row = $stmt->fetch();
        return $row ?: null;
    }
}