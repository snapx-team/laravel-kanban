<?php

namespace Xguard\LaravelKanban\Actions\Tasks;

use Illuminate\Support\Facades\Auth;
use Lorisleiva\Actions\Action;
use Throwable;
use Xguard\LaravelKanban\Models\Log;
use Xguard\LaravelKanban\Models\Task;
use Xguard\LaravelKanban\Http\Helper\AccessManager;
use Xguard\LaravelKanban\Repositories\TasksRepository;

class UpdateTaskCardIndexesAction extends Action
{
    const DATA = 'data';
    const TASK_CARDS = 'taskCards';
    const SELECTED_SORT_METHOD = 'selectedSortMethod';
    const TARGET_TASK_ID = 'targetTaskId';
    const TYPE = 'type';
    const ADDED = 'added';
    const REMOVED = 'removed';
    const ID = 'id';
    const INDEX = 'index';

    /**
     * @throws Throwable
     */
    public function handle()
    {
        try {
            \DB::beginTransaction();

            $newIndex = 0;
            $taskCards = $this->data[self::TASK_CARDS];

            /*
            Set index to be last if kanban frontend sort is set as anything other than index
            This is to avoid re-indexing based on the sorted cards and keep original indexing
            */
            if ($this->data[self::SELECTED_SORT_METHOD] !== self::INDEX) {
                switch ($this->data[self::TYPE]) {
                    case self::ADDED:
                        $this->findAndSetTaskCardIndex($this->data[self::TARGET_TASK_ID], count($taskCards)-1);
                        break;
                    case self::REMOVED:
                        usort($taskCards, function ($a, $b) {
                            if ($a[self::INDEX] == $b[self::INDEX]) {
                                return (0);
                            }
                            return (($a[self::INDEX] < $b[self::INDEX]) ? -1 : 1);
                        });

                        foreach ($taskCards as $taskCard) {
                            $this->findAndSetTaskCardIndex($taskCard[self::ID], $newIndex);
                            $newIndex++;
                        }
                        break;
                    default:
                        break;
                }
            } else {
                foreach ($taskCards as $taskCard) {
                    $this->findAndSetTaskCardIndex($taskCard[self::ID], $newIndex);
                    $newIndex++;
                }
            }

            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollBack();
            throw $e;
        }
    }

    public function findAndSetTaskCardIndex($taskCardId, $index)
    {
        $task = Task::find($taskCardId);
        $task->update([self::INDEX => $index]);
        $task->refresh();
    }
}
