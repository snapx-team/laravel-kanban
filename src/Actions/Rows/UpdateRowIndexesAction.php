<?php

namespace Xguard\LaravelKanban\Actions\Rows;

use Lorisleiva\Actions\Action;
use Xguard\LaravelKanban\Models\Row;
use Xguard\LaravelKanban\Repositories\RowsRepository;

class UpdateRowIndexesAction extends Action
{
    const ROWS = 'rows';

    /**
     * @return array
     */
    public function rules()
    {
        return [
            self::ROWS => ['present', 'array'],
        ];
    }

    /**
     * @return mixed
     */
    public function handle()
    {
        $newIndex = 0;
        try {
            \DB::beginTransaction();
            foreach ($this->rows as $row) {
                RowsRepository::updateRowIndex($row[Row::ID], $newIndex);
                $newIndex++;
            }
            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollBack();
            throw $e;
        }
    }
}
