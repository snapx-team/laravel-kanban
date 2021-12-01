<?php

namespace Tests\Unit\Actions\ErpShareables;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;
use Xguard\LaravelKanban\Actions\ErpShareables\CreateErpShareablesAction;
use App\Models\Contract;
use Illuminate\Validation\ValidationException;
use Xguard\LaravelKanban\Models\Employee;

class CreateErpShareablesActionTest extends TestCase
{

    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = factory(User::class)->create();
        Auth::setUser($this->user);
    }

    public function testCreateErpShareablesActionCreatesErpContractsAndErpEmployee()
    {
        $contract = factory(Contract::class)->create();
        $employee = factory(Employee::class)->create();

        $sharedTaskData = app(CreateErpShareablesAction::class)->fill([
            "description" => "description",
            "erpEmployees" => [["id" => $contract->id]],
            "erpContracts" => [["id" => $employee->id]],
        ])->run();

        $this->assertNotNull($sharedTaskData);
        $this->assertEquals(1, $sharedTaskData->erpContracts()->count());
        $this->assertEquals(1, $sharedTaskData->erpEmployees()->count());
    }

    public function testCreateErpShareablesActionCreatesNoErpContractsIfInputErpContractIsNull()
    {
        $employee = factory(Employee::class)->create();

        $sharedTaskData = app(CreateErpShareablesAction::class)->fill([
            "description" => "description",
            "erpEmployees" => [["id" => $employee->id]],
            "erpContracts" => [],
        ])->run();

        $this->assertNotNull($sharedTaskData);
        $this->assertEquals(0, $sharedTaskData->erpContracts()->count());
        $this->assertEquals(1, $sharedTaskData->erpEmployees()->count());
    }

    public function testCreateErpShareablesActionCreatesNoErpEmployeesIfInputErpEmployeeIsNull()
    {
        $contract = factory(Contract::class)->create();

        $sharedTaskData = app(CreateErpShareablesAction::class)->fill([
            "description" => "description",
            "erpEmployees" => [],
            "erpContracts" => [["id" => $contract->id]],
        ])->run();

        $this->assertNotNull($sharedTaskData);
        $this->assertEquals(1, $sharedTaskData->erpContracts()->count());
        $this->assertEquals(0, $sharedTaskData->erpEmployees()->count());
    }

    public function testCreateErpShareablesActionThrowsExceptionIfInputDescriptionIsNull()
    {
        $this->expectException(ValidationException::class);

        $contract = factory(Contract::class)->create();
        $employee = factory(Employee::class)->create();

        app(CreateErpShareablesAction::class)->fill([
            "description" => null,
            "erpEmployees" => [["id" => $contract->id]],
            "erpContracts" => [["id" => $employee->id]],
        ])->run();
    }
}
