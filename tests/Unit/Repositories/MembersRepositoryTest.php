<?php

namespace Tests\Unit\Repositories;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Xguard\LaravelKanban\Models\Board;
use Xguard\LaravelKanban\Models\Member;
use Xguard\LaravelKanban\Repositories\MembersRepository;

class MembersRepositoryTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->membersRepository = new MembersRepository();
        $this->board = factory(Board::class)->create();
        $this->members = factory(Member::class, 5)->create(['board_id' => $this->board->id]);
    }

    public function testGetMembersReturnsCollectionOfMatchingEntries()
    {
        $results = $this->membersRepository::getMembers($this->board->id);
        
        $this->assertEquals(count($this->members), $results->count());

        for ($i = 0; $i < 5; $i++ ) {
            $this->assertEquals($this->members[$i]->employee_id, $results[$i]->employee_id);
            $this->assertEquals($this->members[$i]->board_id, $results[$i]->board_id);
            $this->assertEquals($this->members[$i]->employee->user->id, $results[$i]->employee->user->id);
            $this->assertEquals($this->members[$i]->employee->user->first_name, $results[$i]->employee->user->first_name);
            $this->assertEquals($this->members[$i]->employee->user->last_name, $results[$i]->employee->user->last_name);
        }
    }

    public function testgetMembersFormattedForQuillReturnsCollectionOfMatchingEntries()
    {
        $results = $this->membersRepository::getMembersFormattedForQuill($this->board->id);
        
        $this->assertEquals(count($this->members), $results->count());

        for ($i = 0; $i < 5; $i++ ) {
            $this->assertEquals($this->members[$i]->employee->id, $results[$i]['id']);
            $this->assertEquals($this->members[$i]->employee->user->full_name, $results[$i]['value']);
        }
    }
}
