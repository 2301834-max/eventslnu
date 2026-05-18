<?php

namespace Tests;

use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        $this->ensureTestingDatabaseExists();

        parent::setUp();

        // Ensure web feature tests behave consistently across phpunit/artisan/docker runs.
        $this->withoutMiddleware(ValidateCsrfToken::class);
    }

    protected function ensureTestingDatabaseExists(): void
    {
        $databasePath = dirname(__DIR__).DIRECTORY_SEPARATOR.'database'.DIRECTORY_SEPARATOR.'testing.sqlite';

        if (! file_exists($databasePath)) {
            touch($databasePath);
        }
    }
}
