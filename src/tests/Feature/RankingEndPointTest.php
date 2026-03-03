<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Config\Database;
use App\Exceptions\MovementNotFoundException;
use App\Repositories\MovementRepository;
use App\Services\RankingService;
use Dotenv\Dotenv;
use PHPUnit\Framework\TestCase;

class RankingEndpointTest extends TestCase
{
    private RankingService $service;

    public static function setUpBeforeClass(): void
    {
        $dotenv = Dotenv::createImmutable(__DIR__ . "/../../", ".env.test");
        $dotenv->load();
    }

    protected function setUp(): void
    {
        Database::reset();
        $this->service = new RankingService(new MovementRepository());
    }

    public function test_returns_ranking_by_movement_id(): void
    {
        $result = $this->service->getRankingByMovement("1");

        $this->assertSame("Deadlift", $result["movement"]);
        $this->assertCount(4, $result["ranking"]);
    }

    public function test_returns_ranking_by_movement_name(): void
    {
        $result = $this->service->getRankingByMovement("Deadlift");

        $this->assertSame("Deadlift", $result["movement"]);
        $this->assertNotEmpty($result["ranking"]);
    }

    public function test_ranking_is_ordered_descending_by_personal_record(): void
    {
        $result = $this->service->getRankingByMovement("1");
        $records = array_column($result["ranking"], "personal_record");
        $sorted = $records;
        rsort($sorted);

        $this->assertSame($sorted, $records);
    }

    public function test_first_position_has_highest_personal_record(): void
    {
        $result = $this->service->getRankingByMovement("1");

        $this->assertSame(
            200.0,
            (float) $result["ranking"][0]["personal_record"],
        );
        $this->assertSame(1, (int) $result["ranking"][0]["position"]);
    }

    public function test_returns_correct_pr_date_not_latest_date(): void
    {
        $result = $this->service->getRankingByMovement("1");
        $alice = collect_by_user($result["ranking"], "Alice");

        $this->assertSame("2021-01-02 00:00:00", $alice["date"]);
    }

    public function test_tied_users_share_first_position(): void
    {
        $result = $this->service->getRankingByMovement("2");
        $alice = collect_by_user($result["ranking"], "Alice");
        $bob = collect_by_user($result["ranking"], "Bob");
        $charlie = collect_by_user($result["ranking"], "Charlie");

        $this->assertSame(1, (int) $alice["position"]);
        $this->assertSame(1, (int) $bob["position"]);
        $this->assertSame(3, (int) $charlie["position"]);
    }

    public function test_tied_users_share_middle_position(): void
    {
        $result = $this->service->getRankingByMovement("3");
        $alice = collect_by_user($result["ranking"], "Alice");
        $bob = collect_by_user($result["ranking"], "Bob");
        $charlie = collect_by_user($result["ranking"], "Charlie");
        $diana = collect_by_user($result["ranking"], "Diana");

        $this->assertSame(1, (int) $alice["position"]);
        $this->assertSame(2, (int) $bob["position"]);
        $this->assertSame(2, (int) $charlie["position"]);
        $this->assertSame(4, (int) $diana["position"]);
    }

    public function test_returns_empty_ranking_for_movement_without_records(): void
    {
        $result = $this->service->getRankingByMovement("Clean");

        $this->assertSame("Clean", $result["movement"]);
        $this->assertEmpty($result["ranking"]);
    }

    public function test_returns_ranking_by_movement_name_with_space(): void
    {
        $result = $this->service->getRankingByMovement("Back Squat");

        $this->assertSame("Back Squat", $result["movement"]);
        $this->assertNotEmpty($result["ranking"]);
    }

    public function test_throws_exception_for_nonexistent_movement(): void
    {
        $this->expectException(MovementNotFoundException::class);
        $this->service->getRankingByMovement("999");
    }

    public function test_sql_injection_via_id_returns_not_found(): void
    {
        $this->expectException(MovementNotFoundException::class);
        $this->service->getRankingByMovement("1 OR 1=1");
    }

    public function test_sql_injection_via_name_returns_not_found(): void
    {
        $this->expectException(MovementNotFoundException::class);
        $this->service->getRankingByMovement("' OR '1'='1");
    }

    public function test_sql_injection_drop_table_returns_not_found(): void
    {
        $this->expectException(MovementNotFoundException::class);
        $this->service->getRankingByMovement("'; DROP TABLE movement; --");
    }
}

function collect_by_user(array $ranking, string $name): ?array
{
    foreach ($ranking as $entry) {
        if ($entry["user"] === $name) {
            return $entry;
        }
    }
    return null;
}
