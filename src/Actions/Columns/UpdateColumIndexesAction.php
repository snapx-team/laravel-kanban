<?php

namespace Xguard\LaravelKanban\Actions\Columns;

use Lorisleiva\Actions\Action;
use Xguard\LaravelKanban\Repositories\ColumnsRepository;

class UpdateColumIndexesAction extends Action
{
    /**
     * Get the validation rules that apply to the action.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'columns' => ['present', 'array'],
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return mixed
     */
    public function handle()
    {
        $newIndex = 0;
        try {
            \DB::beginTransaction();
            foreach ($this->columns as $column) {
                ColumnsRepository::updateColumnIndex($column['id'], $newIndex);
                $newIndex++;
            }
            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollBack();
            throw $e;
        }
    }
}
