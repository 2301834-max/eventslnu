<?php

namespace Tests\Unit\Models;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_record_stores_actor_organization_and_properties(): void
    {
        $actor = User::factory()->create([
            'role' => 'super_admin',
            'organization_type' => 'System',
            'organization_name' => 'LNU Smart Events',
        ]);

        $log = ActivityLog::record(
            'auth.login',
            'Signed in to the Super Admin console.',
            null,
            $actor,
            ['guard' => 'web']
        );

        $this->assertSame($actor->id, $log->user_id);
        $this->assertSame('auth.login', $log->action);
        $this->assertSame('Signed in to the Super Admin console.', $log->description);
        $this->assertSame('System', $log->organization_type);
        $this->assertSame('LNU Smart Events', $log->organization_name);
        $this->assertSame(['guard' => 'web'], $log->properties);
    }
}
