<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Config\Database;
use PDO;

class MovementRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findByIdOrName(string|int $identifier): ?array
    {
        if (ctype_digit((string) $identifier)) {
            $sql = "SELECT id, name FROM movement WHERE id = :id LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(["id" => (int) $identifier]);
        } else {
            $sql = "SELECT id, name FROM movement WHERE name = :name LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(["name" => $identifier]);
        }

        $result = $stmt->fetch();

        return $result ?: null;
    }

    public function getRanking(int $movementId): array
    {
        $sql = <<<SQL
        SELECT
            u.name                                                                              AS user_name,
            CAST(MAX(pr.value) AS DECIMAL(10,2))                                                AS personal_record,
            SUBSTRING_INDEX(GROUP_CONCAT(pr.date ORDER BY pr.value DESC, pr.date DESC), ',', 1) AS record_date,
            CAST(RANK() OVER (ORDER BY MAX(pr.value) DESC) AS UNSIGNED)                         AS position
        FROM personal_record pr
        INNER JOIN user u ON u.id = pr.user_id
        WHERE pr.movement_id = :movement_id
        GROUP BY pr.user_id, u.name
        ORDER BY position ASC
        SQL;

        $stmt = $this->db->prepare($sql);
        $stmt->execute(["movement_id" => $movementId]);

        return $stmt->fetchAll();
    }
}
