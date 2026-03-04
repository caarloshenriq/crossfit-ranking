<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\RankingService;
use App\Exceptions\MovementNotFoundException;
use Throwable;

class RankingController
{
    private RankingService $service;

    public function __construct(RankingService $service)
    {
        $this->service = $service;
    }

    public function ranking(string|int $identifier): void
    {
        try {
            $data = $this->service->getRankingByMovement($identifier);
            $this->json($data, 200);
        } catch (MovementNotFoundException $e) {
            $this->json(["error" => $e->getMessage()], 404);
        } } catch (Throwable $e) {
            Logger::error('Unexpected error on ranking endpoint', $e);
            $this->json(['error' => 'Internal server error.'], 500);
        }
    }

    private function json(array $data, int $status): void
    {
        http_response_code($status);
        header("Content-Type: application/json; charset=utf-8");
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}
