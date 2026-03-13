<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Models;

use App\Models\Team;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Appsolutely\AIO\Tests\TestCase;

final class TeamTest extends TestCase
{
    use RefreshDatabase;

    // --- findOrFail ---

    public function test_find_or_fail_finds_team_by_id(): void
    {
        $team = Team::factory()->create();

        $found = (new Team())->findOrFail($team->id);

        $this->assertTrue($found->is($team));
    }

    public function test_find_or_fail_finds_team_by_reference(): void
    {
        $team = Team::factory()->create(['reference' => 'ref-abc']);

        $found = (new Team())->findOrFail('ref-abc');

        $this->assertTrue($found->is($team));
    }

    public function test_find_or_fail_throws_model_not_found_exception(): void
    {
        $this->expectException(ModelNotFoundException::class);

        (new Team())->findOrFail('nonexistent-id-99999');
    }
}
