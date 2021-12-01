<?php

namespace Tests\Unit\Actions\ErpShareables;

use App\Models\Contract;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;
use Xguard\LaravelKanban\Actions\ErpShareables\UpdateErpShareablesDescriptionAction;
use Xguard\LaravelKanban\Models\Employee;
use Xguard\LaravelKanban\Models\SharedTaskData;

class UpdateErpShareablesDescriptionActionTest extends TestCase
{

    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = factory(User::class)->create();
        Auth::setUser($this->user);
        $this->sharedTaskDataToUpdate = factory(SharedTaskData::class)->create();
    }

    public function testUpdateErpShareablesDescriptionActionCreatesErpContractsAndErpEmployeeAndUpdatesDescription()
    {
        $contract = factory(Contract::class)->create();
        $employee = factory(Employee::class)->create();

        app(UpdateErpShareablesDescriptionAction::class)->fill([
            "sharedTaskDataId" => $this->sharedTaskDataToUpdate->id,
            "description" => "description",
            "erpEmployees" => [["id" => $contract->id]],
            "erpContracts" => [["id" => $employee->id]],
        ])->run();

        $this->sharedTaskDataToUpdate->refresh();

        $this->assertEquals("description", $this->sharedTaskDataToUpdate->description);
        $this->assertEquals(1, $this->sharedTaskDataToUpdate->erpContracts()->count());
        $this->assertEquals(1, $this->sharedTaskDataToUpdate->erpEmployees()->count());
    }

    public function testUpdateErpShareablesDescriptionActionCreatesNoErpContractsIfInputErpContractIsNull()
    {
        $employee = factory(Contract::class)->create();

        app(UpdateErpShareablesDescriptionAction::class)->fill([
            "sharedTaskDataId" => $this->sharedTaskDataToUpdate->id,
            "description" => "description",
            "erpEmployees" => [["id" => $employee->id]],
            "erpContracts" => [],
        ])->run();

        $this->sharedTaskDataToUpdate->refresh();

        $this->assertEquals("description", $this->sharedTaskDataToUpdate->description);
        $this->assertEquals(0, $this->sharedTaskDataToUpdate->erpContracts()->count());
        $this->assertEquals(1, $this->sharedTaskDataToUpdate->erpEmployees()->count());
    }

    public function testUpdateErpShareablesDescriptionActionCreatesNoErpEmployeesIfInputErpEmployeeIsNull()
    {
        $contract = factory(Contract::class)->create();

        app(UpdateErpShareablesDescriptionAction::class)->fill([
            "sharedTaskDataId" => $this->sharedTaskDataToUpdate->id,
            "description" => "description",
            "erpEmployees" => [],
            "erpContracts" => [["id" => $contract->id]],
        ])->run();

        $this->sharedTaskDataToUpdate->refresh();

        $this->assertEquals("description", $this->sharedTaskDataToUpdate->description);
        $this->assertEquals(1, $this->sharedTaskDataToUpdate->erpContracts()->count());
        $this->assertEquals(0, $this->sharedTaskDataToUpdate->erpEmployees()->count());
    }

    public function testUpdateErpShareablesDescriptionActionThrowsExceptionIfInputDescriptionIsNull()
    {
        $this->expectException(ValidationException::class);

        $contract = factory(Contract::class)->create();
        $employee = factory(Employee::class)->create();

        app(UpdateErpShareablesDescriptionAction::class)->fill([
            "sharedTaskDataId" => $this->sharedTaskDataToUpdate->id,
            "description" => null,
            "erpEmployees" => [["id" => $contract->id]],
            "erpContracts" => [["id" => $employee->id]],
        ])->run();
    }
}
