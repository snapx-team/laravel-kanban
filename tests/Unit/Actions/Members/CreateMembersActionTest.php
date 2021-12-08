<?php

namespace Tests\Unit\Actions\Members;

use App\Models\User;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;
use Xguard\LaravelKanban\Actions\Members\CreateMembersAction;
use Xguard\LaravelKanban\Models\Board;
use Xguard\LaravelKanban\Models\Employee;
use Xguard\LaravelKanban\Models\Log;
use Xguard\LaravelKanban\Models\Member;

class CreateMembersActionTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = factory(User::class)->create();
        Auth::setUser($this->user);
        session(['role' => 'admin', 'employee_id' => $this->user->id]);
    }

    public function testCreateMembersActionCreatesBoardMembersGivenValidBoardAndValidEmployeeIds()
    { 
        $employees = factory(Employee::class, 2)->create();
        $board = factory(Board::class)->create();

        app(CreateMembersAction::class)->fill(['employees' => [['id' => $employees[0]->id], ['id' => $employees[1]->id]], 'boardId' => $board->id])->run();

        $members = Member::all();
        foreach ($members as $member) {
            $payload = [
                'user_id' => Auth::user()->id,
                'log_type' => Log::TYPE_KANBAN_MEMBER_CREATED,
                'description' => 'Added a new member [' . $member->employee->user->full_name . '] to board [' . $member->board->name . ']',
                'targeted_employee_id' => $member->employee_id,
                'loggable_id' => $member->board_id,
                'loggable_type' => 'Xguard\LaravelKanban\Models\Board'
            ];

            $this->assertDatabaseHas('kanban_logs', $payload);
        }

        $this->assertEquals(2, $members->count());
        $this->assertEquals($members->count(), Log::count());
    }

    public function testCreateMembersActionGivenEmployeeThatHasMemberThenDoesntCreateMember()
    {
        $employee = factory(Employee::class)->create();
        $board = factory(Board::class)->create();
        factory(Member::class)->create(['employee_id' => $employee->id, 'board_id' => $board->id]);

        app(CreateMembersAction::class)->fill(['employees' => [['id' => $employee->id]], 'boardId' => $board->id])->run();

        $this->assertEquals(1, Member::count());
        $this->assertEquals(0, Log::count());
    }
    public function testCreateMembersActionThrowsExceptionIfInvalidBoardIdIsGiven()
    {
        $this->expectException(Exception::class);

        $employee = factory(Employee::class)->create();

        app(CreateMembersAction::class)->fill(['employees' => ['id' => $employee->id], 'boardId' => 1])->run();

        $this->assertEquals(0, Member::count());
        $this->assertEquals(0, Log::count());
    }

    public function testCreateMembersActionThrowsExceptionIfInvalidEmployeeIdIsGiven()
    {
        $this->expectException(Exception::class);

        $board = factory(Board::class)->create();

        app(CreateMembersAction::class)->fill(['employees' => ['id' => 1], 'boardId' => $board->id])->run();

        $this->assertEquals(0, Member::count());
        $this->assertEquals(0, Log::count());
    }
}
