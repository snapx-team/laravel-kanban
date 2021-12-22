<?php

Route::group(['prefix' => 'kanban',], function () {
    Route::group(['namespace' => 'Xguard\LaravelKanban\Http\Controllers',], function () {
        Route::group(['middleware' => ['web']], function () {

            // Setting sessions variables and checking if user is still logged in
            Route::get('/set-sessions', 'LaravelKanbanController@setKanbanSessionVariables');

            Route::group(['middleware' => ['laravel_kanban_role_check']], function () {

                // We'll let vue router handle 404 (it will redirect to dashboard)
                Route::fallback('LaravelKanbanController@getIndex');

                // All view routes are handled by vue router
                Route::get('/', 'LaravelKanbanController@getIndex');
                Route::get('/dashboard', 'LaravelKanbanController@getIndex');
                Route::get('/board', 'LaravelKanbanController@getIndex');
                Route::get('/metrics', 'LaravelKanbanController@getIndex');


                // Kanban App Data
                Route::get('/get-role-and-employee-id', 'LaravelKanbanController@getRoleAndEmployeeId');
                Route::get('/get-board-data/{id}', 'LaravelKanbanController@getKanbanData');
                Route::get('/get-dashboard-data', 'LaravelKanbanController@getDashboardData');
                Route::get('/get-backlog-data/{start}/{end}', 'LaravelKanbanController@getBacklogData');
                Route::get('/get-footer-info', 'LaravelKanbanController@getFooterInfo');
                Route::get('/get-user-profile', 'LaravelKanbanController@getUserProfile');

                //Notifications
                Route::get('/get-notif-data/{logType}', 'NotificationController@getNotificationData');
                Route::get('/get-notif-count', 'NotificationController@getNotificationCount');
                Route::post('/update-notif-count', 'NotificationController@updateLastNotificationCheck');
                Route::get(
                    '/get-boards-with-employee-notification-settings',
                    'NotificationController@getBoardsWithEmployeeNotificationSettings'
                );
                Route::post(
                    '/first-or-create-employee-notification-settings',
                    'NotificationController@firstOrCreateEmployeeNotificationSettings'
                );

                // Metrics
                Route::get('/get-badge-data/{start}/{end}', 'MetricsController@getBadgeData');
                Route::get(
                    '/get-tasks-created-by-employee/{start}/{end}',
                    'MetricsController@getTasksCreatedByEmployee'
                );
                Route::get('/get-peak-hours-tasks-created/{start}/{end}', 'MetricsController@getPeakHoursTasksCreated');
                Route::get('/get-contract-data/{start}/{end}', 'MetricsController@getContractData');
                Route::get('/get-closed-by-employee/{start}/{end}', 'MetricsController@getClosedTasksByAssignedTo');
                Route::get('/get-closed-by-admin/{start}/{end}', 'MetricsController@getClosedTasksByAdmin');
                Route::get(
                    '/get-average-time-to-completion-by-badge/{start}/{end}',
                    'MetricsController@getAverageTimeToCompletionByBadge'
                );
                Route::get(
                    '/get-average-time-to-completion-by-employee/{start}/{end}',
                    'MetricsController@getAverageTimeToCompletionByEmployee'
                );
                Route::get('/get-created-vs-resolved/{start}/{end}', 'MetricsController@getCreatedVsResolved');
                Route::get(
                    '/get-estimated-completed-by-employee/{start}/{end}',
                    'MetricsController@getEstimatedHoursCompletedByEmployees'
                );

                // Board
                Route::post('/create-board', 'BoardsController@createBoard');
                Route::post('/delete-board/{id}', 'BoardsController@deleteBoard');
                Route::post('/edit-board', 'BoardsController@editBoard');
                Route::get('/get-boards', 'BoardsController@getBoards');

                // Columns
                Route::post('/save-row-and-columns', 'RowAndColumnsController@createOrUpdateRowAndColumns');
                Route::get('/get-columns/{row_id}', 'RowAndColumnsController@getColumns');

                // Rows
                Route::get('/get-rows/{board_id}', 'RowAndColumnsController@getRows');
                Route::post('/delete-row/{id}', 'RowAndColumnsController@deleteRow');
                Route::post('/update-column-indexes', 'RowAndColumnsController@updateColumnIndexes');
                Route::post('/update-row-indexes', 'RowAndColumnsController@updateRowIndexes');

                // Task
                Route::post('/get-backlog-tasks', 'TaskController@getBacklogTasks');
                Route::get('/get-all-tasks', 'TaskController@getAllTasks');
                Route::get('/get-some-tasks/{searchTerm}', 'TaskController@getSomeTasks');
                Route::get('/get-task-data/{id}', 'TaskController@getTaskData');
                Route::get('/get-related-tasks/{id}', 'TaskController@getRelatedTasks');
                Route::get('/get-related-tasks-less-info/{id}', 'TaskController@getRelatedTasksLessInfo');
                Route::post('/create-task', 'TaskController@createTaskCard');
                Route::post('/update-task', 'TaskController@updateTaskCard');
                Route::post('/create-backlog-tasks', 'TaskController@createBacklogTaskCards');
                Route::post('/get-task-cards-by-column/{id}', 'TaskController@getTaskCardsByColumn');
                Route::post('/update-task-card-indexes', 'TaskController@updateTaskCardIndexes');
                Route::post(
                    '/update-task-card-row-and-column/{columnId}/{rowId}/{taskCardId}',
                    'TaskController@updateTaskCardRowAndColumnId'
                );
                Route::post('/delete-kanban-task-card/{id}', 'TaskController@deleteTaskCard');
                Route::post('/update-task-description', 'TaskController@updateDescription');
                Route::post('/set-status/{taskCardId}/{status}', 'TaskController@setStatus');
                Route::post('/place-task/{taskId}/{row_id}/{column_id}/{board_id}', 'TaskController@placeTask');
                Route::post('/update-group/{taskId}/{group}', 'TaskController@updateGroup');
                Route::post('/remove-group/{taskId}', 'TaskController@removeFromGroup');

                // Comments
                Route::get('/get-task-comments/{taskId}', 'CommentController@getAllTaskComments');
                Route::post('/create-or-update-task-comment', 'CommentController@createOrUpdateTaskComment');
                Route::post('/delete-task-comment', 'CommentController@deleteTaskComment');

                // Templates
                Route::get('/get-templates', 'TemplateController@getAllTemplates');
                Route::post('/create-template', 'TemplateController@createOrUpdateTemplate');
                Route::post('/delete-template/{templateId}', 'TemplateController@deleteTemplate');

                // Employees
                Route::post('/create-kanban-employees', 'EmployeeController@createEmployees');
                Route::post('/delete-kanban-employee/{id}', 'EmployeeController@deleteEmployee');
                Route::get('/get-kanban-employees', 'EmployeeController@getEmployees');

                // Members
                Route::post('/create-members/{id}', 'MemberController@createMembers');
                Route::post('/delete-member/{id}', 'MemberController@deleteMember');
                Route::get('/get-members/{id}', 'MemberController@getMembers');
                Route::get('/get-members-formatted-for-quill/{id}', 'MemberController@getMembersFormattedForQuill');


                // Badges
                Route::get('/get-all-badges', 'BadgeController@getAllBadges');
                Route::get('/list-badges-with-count', 'BadgeController@listBadgesWithCount');
                Route::post('/create-badge', 'BadgeController@createOrUpdateBadge');
                Route::delete('/delete-badge/{id}', 'BadgeController@deleteBadge');

                // Logs
                Route::get('/get-logs/{id}', 'LogController@getLogs');

                //ERP Data
                Route::get('/get-all-users', 'ErpController@getAllUsers');
                Route::get('/get-some-users/{searchTerm}', 'ErpController@getSomeUsers');
                Route::get('/get-all-contracts', 'ErpController@getAllContracts');
                Route::get('/get-some-contracts/{searchTerm}', 'ErpController@getSomeContracts');
            });
        });
    });
});

// API

Route::group(['namespace' => 'Xguard\LaravelKanban\Http\Controllers',], function () {
    Route::group(['prefix' => 'kanban',], function () {
        // api routes
    });
});
