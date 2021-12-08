<?php

namespace Tests\Unit\Actions\Members;

use App\Models\User;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;
use Xguard\LaravelKanban\Actions\Members\DeleteMemberAction;
use Xguard\LaravelKanban\Models\Log;
use Xguard\LaravelKanban\Models\Member;

class DeleteMemberActionTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = factory(User::class)->create();
        Auth::setUser($this->user);
        session(['role' => 'admin', 'employee_id' => $this->user->id]);
    }

    public function testDeletionOfEmployee()
    {
        $member = factory(Member::class)->create();

        $payload = [
            'user_id' => Auth::user()->id,
            'log_type' => Log::TYPE_KANBAN_MEMBER_DELETED,
            'description' => 'Deleted member [' . $member->employee->user->full_name . '] from board [' . $member->board->name . ']',
            'targeted_employee_id' => $member->employee_id,
            'loggable_id' => $member->board_id,
            'loggable_type' => 'Xguard\LaravelKanban\Models\Board'
        ];
        
        app(DeleteMemberAction::class)->fill(['memberId' => $member->id])->run();

        $member->refresh();
        
        $this->assertDatabaseHas('kanban_logs', $payload);    
        $this->assertNotNull($member->deleted_at);
    }

    public function testErrorIsThrownWhenAnInvalidIdIsGiven()
    {
        $this->expectException(Exception::class);

        $member = factory(Member::class)->create();

        $countBefore = Member::all()->count();
        app(DeleteMemberAction::class)->fill(['memberId' => $countBefore + 1])->run();
        $countAfter = Member::all()->count();

        $this->assertEquals($countBefore, $countAfter);
        $this->assertDatabaseHas('kanban_members', [
            'employee_id' => $member->employee_id,
            'board_id' => $member->board_id
        ]);
        $this->assertEquals(0, Log::count());
    }
}
