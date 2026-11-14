<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class EnvironmentTest extends TestCase
{
    /**
     * Test application config and SQLite database connection.
     */
    public function test_application_boots_and_sqlite_connected(): void
    {
        $this->assertEquals('CBT School', config('app.name'));
        $this->assertEquals('Asia/Jakarta', config('app.timezone'));
        
        $dbName = DB::connection()->getDatabaseName();
        $this->assertNotEmpty($dbName);
    }
}
