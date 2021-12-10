<?php

namespace Tests\Unit\Repositories;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Xguard\LaravelKanban\Entities\ErpUser;
use Xguard\LaravelKanban\Repositories\ErpUsersRepository;
use Illuminate\Support\Str;

class ErpUsersRepositoryTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->usersRepository = new ErpUsersRepository();
    }

    public function testRetrieveUserOnValidIdReturnsErpUserEntity()
    {
        $user = factory(User::class)->create();

        $result = $this->usersRepository::retrieve($user->id);

        $this->assertInstanceOf(ErpUser::class, $result);
        $this->assertEquals($user->id, $result->getId());
        $this->assertEquals($user->full_name, $result->getFullName());
    }

    public function testRetrieveUserOnInValidIdReturnsNull()
    {
        $result = $this->usersRepository::retrieve(rand());
        $this->assertNull($result);    
    }

    public function testGetSomeUsersReturnsCollectionOfMatchingEntries()
    {
        $searchTerm = Str::random(rand(1, 3));
        $matchCount = 0;

        $connection = DB::connection();
        $dbHandle = $connection->getPdo();

        $dbHandle->sqliteCreateFunction(
            'concat',
            function (...$input) {
                return implode('', $input);
            }
        );

        for ($i = 0; $i < 10 ; $i ++) {
            $firstName = Str::random(rand(1, 8));
            $lastName = Str::random(rand(1, 8));
            $fullName = $firstName.' '.$lastName;
            factory(User::class)->create(['first_name' => $firstName, 'last_name' => $lastName]);
            if (stripos($fullName, $searchTerm) === 0 || stripos($fullName, $searchTerm)) {
                $matchCount = $matchCount + 1;
            }
        }

        $result = $this->usersRepository::getSomeUsers($searchTerm);

        $this->assertEquals($matchCount, $result->count());
    }

    public function testGetSomeUsersReturnsCollectionOfMax10Users()
    {
        $searchTerm = Str::random(rand(1, 3));

        factory(User::class, 11)->create(['first_name' => $searchTerm, 'last_name' => $searchTerm]);

        $result = $this->usersRepository::getAllUsers($searchTerm);

        $this->assertEquals(10, $result->count());
    }

    public function testGetAllUsersReturnsCollectionOfAllUsers()
    {
        $userCount = rand(0, 10);
        factory(User::class, $userCount)->create();

        $result = $this->usersRepository::getAllUsers();

        $this->assertEquals($userCount, $result->count());
    }

    public function testGetAllUsersReturnsCollectionOfMax10Users()
    {
        factory(User::class, 11)->create();

        $result = $this->usersRepository::getAllUsers();

        $this->assertEquals(10, $result->count());
    }
}
