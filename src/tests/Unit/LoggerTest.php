<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Config\Logger;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class LoggerTest extends TestCase
{
    private string $logFile;

    protected function setUp(): void
    {
        $this->logFile = sys_get_temp_dir() . "/app_test.log";
        Logger::setLogFile($this->logFile);

        if (file_exists($this->logFile)) {
            file_put_contents($this->logFile, "");
        }
    }

    protected function tearDown(): void
    {
        if (file_exists($this->logFile)) {
            unlink($this->logFile);
        }
    }

    public function test_logs_error_message_to_file(): void
    {
        $exception = new RuntimeException("Something went wrong", 500);

        Logger::error("Test error occurred", $exception);

        $this->assertFileExists($this->logFile);

        $content = file_get_contents($this->logFile);

        $this->assertStringContainsString("ERROR", $content);
        $this->assertStringContainsString("Test error occurred", $content);
        $this->assertStringContainsString("Something went wrong", $content);
        $this->assertStringContainsString("RuntimeException", $content);
    }

    public function test_log_entry_contains_timestamp(): void
    {
        $exception = new RuntimeException("Timed error");

        Logger::error("Timestamp test", $exception);

        $content = file_get_contents($this->logFile);
        $date = date("Y-m-d");

        $this->assertStringContainsString($date, $content);
    }

    public function test_sensitive_info_not_exposed_to_client(): void
    {
        $sensitiveMessage = "SQLSTATE[HY000]: database credentials exposed";
        $exception = new RuntimeException($sensitiveMessage);

        Logger::error("DB error", $exception);

        $clientResponse = "Internal server error.";

        $this->assertStringNotContainsString(
            $sensitiveMessage,
            $clientResponse,
        );
        $this->assertStringContainsString(
            $sensitiveMessage,
            file_get_contents($this->logFile),
        );
    }
}
