<?php

namespace Tests\Unit\Actions\Badges;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;
use Xguard\LaravelKanban\Actions\Badges\ListBadgesWithCountAction;
use Xguard\LaravelKanban\Models\Badge;
use Xguard\LaravelKanban\Models\Task;
use Xguard\LaravelKanban\Models\Template;

class ListBadgesWithCountActionTest extends TestCase
{

    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $user = factory(User::class)->create();
        Auth::setUser($user);
    }

    public function testAUserCanWriteCommentOnTaskIfTheyHaveBoardAccess()
    {
        $badge1 = factory(Badge::class)->create(['name' => 'A']);
        $badge2 = factory(Badge::class)->create(['name' => 'B']);
        $badge3 = factory(Badge::class)->create(['name' => 'C']);
        $badge4 = factory(Badge::class)->create(['name' => 'D']);
        
        $numberOfTemplatesBadge1 = rand(1, 10);
        $numberOfTemplatesBadge2 = rand(1, 10);
        $numberOfTasksBadge2 = rand(1, 10);
        $numberOfTasksBadge3 = rand(1, 10);
        
        factory(Template::class, $numberOfTemplatesBadge1)->create(['badge_id' => $badge1]);
        factory(Template::class, $numberOfTemplatesBadge2)->create(['badge_id' => $badge2]);

        factory(Task::class, $numberOfTasksBadge2)->create(['badge_id' => $badge2]);
        factory(Task::class, $numberOfTasksBadge3)->create(['badge_id' => $badge3]);
        
        $sortedList = app(ListBadgesWithCountAction::class)->run();

        $this->assertEquals($sortedList[0]->id, $badge1->id);
        $this->assertEquals($sortedList[1]->id, $badge2->id);
        $this->assertEquals($sortedList[2]->id, $badge3->id);
        $this->assertEquals($sortedList[3]->id, $badge4->id);
        
        $this->assertEquals($sortedList[0]->name, $badge1->name);
        $this->assertEquals($sortedList[1]->name, $badge2->name);
        $this->assertEquals($sortedList[2]->name, $badge3->name);
        $this->assertEquals($sortedList[3]->name, $badge4->name);

        $this->assertEquals($sortedList[0]->total, $numberOfTemplatesBadge1);
        $this->assertEquals($sortedList[1]->total, $numberOfTemplatesBadge2 + $numberOfTasksBadge2);
        $this->assertEquals($sortedList[2]->total, $numberOfTasksBadge3);
        $this->assertEquals($sortedList[3]->total, 0);
    }
}
