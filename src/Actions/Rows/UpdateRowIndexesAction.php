<?php

namespace Xguard\LaravelKanban\Actions\Rows;

use Lorisleiva\Actions\Action;
use Xguard\LaravelKanban\Repositories\RowsRepository;

class UpdateRowIndexesAction extends Action
{
    /**
     * Get the validation rules that apply to the action.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'rows' => ['present', 'array'],
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
            foreach ($this->rows as $row) {
                RowsRepository::updateRowIndex($row['id'], $newIndex);
                $newIndex++;
            }
            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollBack();
            throw $e;
        }
    }
}
