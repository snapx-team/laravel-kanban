<?php

namespace Tests\Unit\Repositories;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Xguard\LaravelKanban\Models\Employee;
use Xguard\LaravelKanban\Models\Log;
use Xguard\LaravelKanban\Models\TaskVersion;
use Xguard\LaravelKanban\Repositories\NotificationsRepository;

class NotificationsRepositoryTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->notificationsRepository = new NotificationsRepository();
        $this->user = factory(User::class)->create();
        $this->employee = factory(Employee::class)->create(['user_id' => $this->user->id]);
        $this->log = factory(Log::class)->create();
        $this->log->notifications()->attach($this->employee->id);
        factory(TaskVersion::class)->create(['log_id' => $this->log->id]);
    }

    public function testGetNotificationDataReturnsAllRelevantData()
    {
        $result = $this->notificationsRepository::getNotificationData($this->user->id, $this->log->log_type);

        $this->assertEquals(count($result), 1);
        $this->assertEquals($result[0]['user']['id'], $this->log->user_id);
        $this->assertEquals($result[0]['log_type'], $this->log->log_type);
        $this->assertEquals($result[0]['loggable_id'], $this->log->loggable_id);
        $this->assertEquals($result[0]['loggable_type'], $this->log->loggable_type);
        $this->assertEquals($result[0]['loggable']['name'], $this->log->loggable->name);
    }

    public function testGetNotificationDataDoesntReturnLogOfWrongType()
    {
        $result = $this->notificationsRepository::getNotificationData($this->user->id, Log::TYPE_BADGE_DELETED); //not the expected type which is the one defined as default in factory

        $this->assertEquals(count($result), 0);
    }
}
