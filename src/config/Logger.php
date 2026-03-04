<?php

declare(strict_types=1);

namespace App\Config;

class Logger
{
    private static string $logFile = "/var/log/app/app.log";

    public static function setLogFile(string $path): void
    {
        self::$logFile = $path;
    }

    public static function error(string $message, \Throwable $e): void
    {
        $dir = dirname(self::$logFile);

        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $date = date("Y-m-d H:i:s");
        $class = get_class($e);
        $line = $e->getLine();
        $file = $e->getFile();
        $trace = $e->getTraceAsString();

        $entry = "[{$date}] ERROR: {$message} | {$class}: {$e->getMessage()} in {$file}:{$line}\n{$trace}\n";

        error_log($entry, 3, self::$logFile);
    }
}
