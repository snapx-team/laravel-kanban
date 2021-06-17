<?php


Route::group(['middleware' => ['web', 'laravel_kanban_role_check']], function () {
    Route::group(['namespace' => 'Xguard\LaravelKanban\Http\Controllers',], function () {
        Route::group(['prefix' => 'kanban',], function () {

            // We'll let vue router handle 404 (it will redirect to dashboard)
            Route::fallback('LaravelKanbanController@getIndex');

            // All view routes are handled by vue router
            Route::get('/', 'LaravelKanbanController@getIndex');
            Route::get('/dashboard', 'LaravelKanbanController@getIndex');
            Route::get('/phoneline', 'LaravelKanbanController@getIndex');

            // Phone Schedule App Data
            Route::get('/get-board-data/{id}', 'LaravelKanbanController@getkanbanData');
            Route::get('/get-dashboard-data', 'LaravelKanbanController@getDashboardData');
            
            // Metrics

            Route::get('/get-badge-data/{start}/{end}', 'MetricsController@getBadgeData');
            Route::get('/get-tickets-by-employee/{start}/{end}', 'MetricsController@getTicketsByEmployee');
            Route::get('/get-creation-by-hour/{start}/{end}', 'MetricsController@getCreationByHour');
            Route::get('/get-jobsite-data/{start}/{end}', 'MetricsController@getJobSiteData');
            Route::get('/get-closed-by-employee/{start}/{end}', 'MetricsController@getClosedTasksByEmployee');
            Route::get('/get-delay-by-badge/{start}/{end}', 'MetricsController@getDelayByBadge');
            Route::get('/get-delay-by-employee/{start}/{end}', 'MetricsController@getDelayByEmployee');


            // Board
            Route::post('/create-board', 'BoardsController@createBoard');
            Route::post('/delete-board/{id}', 'BoardsController@deleteBoard');
            Route::get('/get-boards', 'BoardsController@getBoards');
            Route::get('/get-tags', 'BoardsController@getTags');

            // Columns
            Route::post('/save-row-and-columns', 'RowAndColumnsController@createOrUpdateRowAndColumns');

            // Task
            Route::get('/get-all-tasks', 'TaskController@getAllTasks');

            Route::post('/create-task', 'TaskController@createTaskCards');
            Route::post('/create-backlog-tasks', 'TaskController@createBacklogTaskCards');

            Route::post('/get-task-cards-by-column/{id}', 'TaskController@getTaskCardsByColumn');
            Route::post('/update-task-card-indexes', 'TaskController@updateTaskCardIndexes');
            Route::post('/update-column-indexes', 'RowAndColumnsController@updateColumnIndexes');
            Route::post('/update-row-indexes', 'RowAndColumnsController@updateRowIndexes');

            Route::post('/update-task-card-column/{columnId}/{taskCardId}', 'TaskController@updateTaskCardColumnId');
            Route::post('/delete-kanban-task-card/{id}', 'TaskController@deleteTaskCard');

            // Comments

            Route::get('/get-task-comments/{taskId}', 'CommentController@getAllTaskComments');
            Route::post('/create-task-comment', 'CommentController@createOrUpdateTaskComment');
            Route::post('/delete-task-comment/{taskCommentId}', 'CommentController@deleteTaskComment');

            // Employees
            Route::post('/create-kanban-employees', 'EmployeeController@createEmployees');
            Route::post('/delete-kanban-employee/{id}', 'EmployeeController@deleteEmployee');
            Route::get('/get-kanban-employees', 'EmployeeController@getEmployees');

            // Phone Line Members
            Route::post('/create-members/{id}', 'MemberController@createMembers');
            Route::post('/delete-member/{id}', 'MemberController@deleteMember');
            Route::get('/get-members/{id}', 'MemberController@getMembers');

            // Badges
            Route::get('/get-all-badges', 'BadgeController@getAllBadges');


            //ERP Data
            Route::get('/get-all-users', 'ErpController@getAllUsers');
            Route::get('/get-some-users/{searchTerm}', 'ErpController@getSomeUsers');
            Route::get('/get-all-job-sites', 'ErpController@getAllJobSites');

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
