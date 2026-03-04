<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Exceptions\MovementNotFoundException;
use App\Repositories\MovementRepositoryInterface;
use App\Services\RankingService;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class RankingServiceTest extends TestCase
{
    private MovementRepositoryInterface&MockObject $repository;
    private RankingService $service;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(
            MovementRepositoryInterface::class,
        );
        $this->service = new RankingService($this->repository);
    }

    public function test_throws_exception_when_movement_not_found(): void
    {
        $this->repository
            ->expects($this->once())
            ->method("findByIdOrName")
            ->with("999")
            ->willReturn(null);

        $this->expectException(MovementNotFoundException::class);
        $this->expectExceptionMessage("Movement '999' not found.");

        $this->service->getRankingByMovement("999");
    }

    public function test_returns_correct_ranking_structure(): void
    {
        $this->repository
            ->expects($this->once())
            ->method("findByIdOrName")
            ->willReturn(["id" => 1, "name" => "Deadlift"]);

        $this->repository
            ->expects($this->once())
            ->method("getRanking")
            ->with(1)
            ->willReturn([
                [
                    "user_name" => "Jose",
                    "personal_record" => 190.0,
                    "record_date" => "2021-01-06 00:00:00",
                    "position" => 1,
                ],
                [
                    "user_name" => "Joao",
                    "personal_record" => 180.0,
                    "record_date" => "2021-01-02 00:00:00",
                    "position" => 2,
                ],
            ]);

        $result = $this->service->getRankingByMovement("1");

        $this->assertSame("Deadlift", $result["movement"]);
        $this->assertCount(2, $result["ranking"]);
        $this->assertSame(1, $result["ranking"][0]["position"]);
        $this->assertSame("Jose", $result["ranking"][0]["user"]);
        $this->assertSame(190.0, $result["ranking"][0]["personal_record"]);
    }

    public function test_tied_users_share_same_position(): void
    {
        $this->repository
            ->expects($this->once())
            ->method("findByIdOrName")
            ->willReturn(["id" => 2, "name" => "Back Squat"]);

        $this->repository
            ->expects($this->once())
            ->method("getRanking")
            ->willReturn([
                [
                    "user_name" => "Joao",
                    "personal_record" => 130.0,
                    "record_date" => "2021-01-03 00:00:00",
                    "position" => 1,
                ],
                [
                    "user_name" => "Jose",
                    "personal_record" => 130.0,
                    "record_date" => "2021-01-03 00:00:00",
                    "position" => 1,
                ],
                [
                    "user_name" => "Paulo",
                    "personal_record" => 125.0,
                    "record_date" => "2021-01-03 00:00:00",
                    "position" => 3,
                ],
            ]);

        $result = $this->service->getRankingByMovement("2");

        $this->assertSame(1, $result["ranking"][0]["position"]);
        $this->assertSame(1, $result["ranking"][1]["position"]);
        $this->assertSame(3, $result["ranking"][2]["position"]);
    }

    public function test_returns_empty_ranking_when_no_records(): void
    {
        $this->repository
            ->expects($this->once())
            ->method("findByIdOrName")
            ->willReturn(["id" => 3, "name" => "Bench Press"]);

        $this->repository
            ->expects($this->once())
            ->method("getRanking")
            ->willReturn([]);

        $result = $this->service->getRankingByMovement("3");

        $this->assertSame("Bench Press", $result["movement"]);
        $this->assertEmpty($result["ranking"]);
    }

    public function test_controller_returns_500_without_exposing_error_details(): void
    {
        $this->repository
            ->method("findByIdOrName")
            ->willThrowException(
                new \RuntimeException("Sensitive database error"),
            );

        try {
            $this->service->getRankingByMovement("1");
            $this->fail("Expected exception was not thrown");
        } catch (\RuntimeException $e) {
            $this->assertSame("Sensitive database error", $e->getMessage());
            $this->assertStringNotContainsString(
                "Sensitive database error",
                "Internal server error.",
            );
        }
    }
}
