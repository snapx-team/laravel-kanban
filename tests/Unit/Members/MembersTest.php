<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;
use Xguard\LaravelKanban\Actions\Members\DeleteMemberAction;
use Xguard\LaravelKanban\Actions\Members\UpdateOrCreateMemberAction;
use Xguard\LaravelKanban\Models\Board;
use Xguard\LaravelKanban\Models\Employee;
use Xguard\LaravelKanban\Models\Member;
use Xguard\LaravelKanban\Models\Log;

class MemberTest extends TestCase
{

    use WithFaker, RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $user = factory(User::class)->create();
        Auth::setUser($user);
    }

    public function testAUserCanCreateSeveralMembers()
    {

        Board::create(['name' => 'test board',]);
        Employee::create(['user_id' => '1', 'role' => 'admin']);
        Employee::create(['user_id' => '2', 'role' => 'admin']);

        (new UpdateOrCreateMemberAction([
            'members' => [['id' => 1,], ['id' => 2,]],
            'board_id' => 1
        ]))->run();

        $this->assertCount(2, Member::all());
    }

    public function testAUserCanDeleteAMember()
    {
        $this->testAUserCanCreateSeveralMembers();
        $this->assertCount(2, Member::all());
        (new DeleteMemberAction(['member_id' => 1]))->run();
        $this->assertCount(1, Member::all());
    }

    public function testLogsAreCreatedWhenMemberIsCreatedAndDeleted()
    {
        $this->testAUserCanCreateSeveralMembers();
        $this->assertCount(3, Log::all());
    }
}
