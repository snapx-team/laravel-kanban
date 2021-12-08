<?php

namespace Tests\Unit\Repositories;

use App\Models\Contract;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;
use Xguard\LaravelKanban\Entities\ErpContract;
use Xguard\LaravelKanban\Repositories\ErpContractsRepository;

class ErpContractsRepositoryTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->contractsRepository = new ErpContractsRepository();
    }

    public function testRetrieveOnValidIdReturnsErpContractEntity()
    {
        $contract = factory(Contract::class)->create();

        $result = $this->contractsRepository::retrieve($contract->id);

        $this->assertInstanceOf(ErpContract::class, $result);
        $this->assertEquals($contract->id, $result->getId());
        $this->assertEquals($contract->contract_identifier, $result->getContractIdentifier());
    }

    public function testRetrieveOnInValidIdReturnsNull()
    {
        $result = $this->contractsRepository::retrieve(rand());
        $this->assertNull($result);
    }

    public function testGetSomeContractsReturnsCollectionOfMatchingEntries()
    {
        $searchTerm = Str::random(rand(1, 3));
        $matchCount = 0;

        for ($i = 0; $i < 10 ; $i ++) {
            $identifier = Str::random(rand(1, 16));
            factory(Contract::class)->create(['contract_identifier' => $identifier]);
            if (stripos($identifier, $searchTerm) === 0 || stripos($identifier, $searchTerm)) {
                $matchCount = $matchCount + 1;
            }
        }

        $result = $this->contractsRepository::getSomeContracts($searchTerm);

        $this->assertEquals($matchCount, $result->count());
    }

    public function testGetSomeContractsReturnsCollectionOfMax10Contracts()
    {
        $searchTerm = Str::random(rand(1, 3));

        factory(Contract::class, 11)->create(['contract_identifier' => $searchTerm]);

        $result = $this->contractsRepository::getSomeContracts($searchTerm);

        $this->assertEquals(10, $result->count());
    }

    public function testGetAllContractsReturnsCollectionOfAllContracts()
    {
        $countractCount = rand(0, 10);
        factory(Contract::class, $countractCount)->create();

        $result = $this->contractsRepository::getAllContracts();

        $this->assertEquals($countractCount, $result->count());
    }

    public function testGetAllContractsReturnsCollectionOfMax10Contracts()
    {
        factory(Contract::class, 11)->create();

        $result = $this->contractsRepository::getAllContracts();

        $this->assertEquals(10, $result->count());
    }
}
