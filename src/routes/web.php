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
            Route::get('/get-board-data/{id}', 'LaravelKanbanController@getkanbanData');
            Route::get('/get-dashboard-data', 'LaravelKanbanController@getDashboardData');
            Route::get('/get-backlog-data/{start}/{end}', 'LaravelKanbanController@getBacklogData');

            // Metrics

            Route::get('/get-badge-data/{start}/{end}', 'MetricsController@getBadgeData');
            Route::get('/get-tickets-by-employee/{start}/{end}', 'MetricsController@getTicketsByEmployee');
            Route::get('/get-creation-by-hour/{start}/{end}', 'MetricsController@getCreationByHour');
            Route::get('/get-jobsite-data/{start}/{end}', 'MetricsController@getJobSiteData');
            Route::get('/get-closed-by-employee/{start}/{end}', 'MetricsController@getClosedTasksByEmployee');
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

            // Task
            Route::get('/get-all-tasks', 'TaskController@getAllTasks');
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
            Route::post('/assign-task-to-board/{taskId}/{row_id}/{column_id}', 'TaskController@assignTaskToBoard');
            Route::post('/update-group/{taskId}/{group}', 'TaskController@updateGroup');


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

            // Badges
            Route::get('/get-all-badges', 'BadgeController@getAllBadges');

            // Logs

            Route::get('/get-logs/{id}', 'LogController@getLogs');

            //ERP Data
            Route::get('/get-all-users', 'ErpController@getAllUsers');
            Route::get('/get-some-users/{searchTerm}', 'ErpController@getSomeUsers');
            Route::get('/get-all-job-sites', 'ErpController@getAllJobSites');
            Route::get('/get-some-job-sites/{searchTerm}', 'ErpController@getSomeJobSites');

        });
    });
});

// API

Route::group(['namespace' => 'Xguard\LaravelKanban\Http\Controllers',], function () {
    Route::group(['prefix' => 'kanban',], function () {

        Route::get('/api/formatted-phone-line-data/{id}', 'LaravelKanbanController@getFormattedData');
        Route::get('/api/get-available-agent/{id}/{level}', 'LaravelKanbanController@getAvailableAgent');
    });
});
