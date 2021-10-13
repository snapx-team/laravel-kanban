<?php


Route::group(['middleware' => ['web', 'laravel_kanban_role_check']], function () {
    Route::group(['namespace' => 'Xguard\LaravelKanban\Http\Controllers',], function () {
        Route::group(['prefix' => 'kanban',], function () {

            // We'll let vue router handle 404 (it will redirect to dashboard)
            Route::fallback('LaravelKanbanController@getIndex');

            // All view routes are handled by vue router
            Route::get('/', 'LaravelKanbanController@getIndex');
            Route::get('/dashboard', 'LaravelKanbanController@getIndex');
            Route::get('/board', 'LaravelKanbanController@getIndex');

            // Kanban App Data
            Route::get('/get-role-and-employee-id', 'LaravelKanbanController@getRoleAndEmployeeId');
            Route::get('/get-board-data/{id}', 'LaravelKanbanController@getkanbanData');
            Route::get('/get-dashboard-data', 'LaravelKanbanController@getDashboardData');
            Route::get('/get-backlog-data/{start}/{end}', 'LaravelKanbanController@getBacklogData');
            Route::get('/get-footer-info', 'LaravelKanbanController@getfooterInfo');
            Route::get('/get-user-profile', 'LaravelKanbanController@getUserProfile');

            //Notifications
            Route::get('/get-notif-data/{logType}', 'LaravelKanbanController@getNotificationData');
            Route::get('/get-notif-count', 'LaravelKanbanController@getNotificationCount');
            Route::post('/update-notif-count', 'LaravelKanbanController@updateNotificationCount');

            // Metrics

            Route::get('/get-badge-data/{start}/{end}', 'MetricsController@getBadgeData');
            Route::get('/get-tickets-by-employee/{start}/{end}', 'MetricsController@getTicketsByEmployee');
            Route::get('/get-creation-by-hour/{start}/{end}', 'MetricsController@getCreationByHour');
            Route::get('/get-contract-data/{start}/{end}', 'MetricsController@getContractData');
            Route::get('/get-closed-by-employee/{start}/{end}', 'MetricsController@getClosedTasksByEmployee');
            Route::get('/get-closed-by-admin/{start}/{end}', 'MetricsController@getClosedTasksByAdmin');
            Route::get('/get-delay-by-badge/{start}/{end}', 'MetricsController@getDelayByBadge');
            Route::get('/get-delay-by-employee/{start}/{end}', 'MetricsController@getDelayByEmployee');
            Route::get('/get-created-vs-resolved/{start}/{end}', 'MetricsController@getCreatedVsResolved');


            // Board
            Route::post('/create-board', 'BoardsController@createBoard');
            Route::post('/delete-board/{id}', 'BoardsController@deleteBoard');
            Route::get('/get-boards', 'BoardsController@getBoards');
            Route::get('/get-tags', 'BoardsController@getTags');

            // Columns
            Route::post('/save-row-and-columns', 'RowAndColumnsController@createOrUpdateRowAndColumns');
            Route::get('/get-columns/{row_id}', 'RowAndColumnsController@getColumns');

            // Rows
            Route::get('/get-rows/{board_id}', 'RowAndColumnsController@getRows');
            Route::post('/delete-row/{id}', 'RowAndColumnsController@deleteRow');

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
            Route::post('/update-column-indexes', 'RowAndColumnsController@updateColumnIndexes');
            Route::post('/update-row-indexes', 'RowAndColumnsController@updateRowIndexes');

            Route::post('/update-task-card-row-and-column/{columnId}/{rowId}/{taskCardId}', 'TaskController@updateTaskCardRowAndColumnId');

            Route::post('/delete-kanban-task-card/{id}', 'TaskController@deleteTaskCard');
            Route::post('/update-task-description', 'TaskController@updateDescription');

            Route::post('/set-status/{taskCardId}/{status}', 'TaskController@setStatus');
            Route::post('/assign-task-to-board/{taskId}/{row_id}/{column_id}/{board_id}', 'TaskController@assignTaskToBoard');
            Route::post('/update-group/{taskId}/{group}', 'TaskController@updateGroup');
            Route::post('/remove-group/{taskId}', 'TaskController@removeFromGroup');


            // Comments

            Route::get('/get-task-comments/{taskId}', 'CommentController@getAllTaskComments');
            Route::post('/create-task-comment', 'CommentController@createOrUpdateTaskComment');
            Route::post('/delete-task-comment/{taskCommentId}', 'Templ@deleteTaskComment');

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

// API

Route::group(['namespace' => 'Xguard\LaravelKanban\Http\Controllers',], function () {
    Route::group(['prefix' => 'kanban',], function () {
        // api routes
    });
});
