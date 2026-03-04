<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\MovementRepositoryInterface;
use App\Exceptions\MovementNotFoundException;

class RankingService
{
    private MovementRepositoryInterface $repository;

    public function __construct(MovementRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function getRankingByMovement(string|int $identifier): array
    {
        $movement = $this->repository->findByIdOrName((string) $identifier);

        if ($movement === null) {
            throw new MovementNotFoundException(
                "Movement '{$identifier}' not found.",
            );
        }

        $ranking = $this->repository->getRanking((int) $movement["id"]);

        return [
            "movement" => $movement["name"],
            "ranking" => array_map(
                fn(array $row) => [
                    "position" => $row["position"],
                    "user" => $row["user_name"],
                    "personal_record" => $row["personal_record"],
                    "date" => $row["record_date"],
                ],
                $ranking,
            ),
        ];
    }
}
