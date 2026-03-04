<?php

declare(strict_types=1);

namespace App\Repositories;

interface MovementRepositoryInterface
{
    public function findByIdOrName(string|int $identifier): ?array;
    public function getRanking(int $movementId): array;
}
