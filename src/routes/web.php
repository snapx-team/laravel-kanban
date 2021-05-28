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
            Route::get('/get-phone-line-data/{id}', 'LaravelKanbanController@getkanbanData');
            Route::get('/get-dashboard-data', 'LaravelKanbanController@getDashboardData');

            // Board
            Route::post('/create-board', 'BoardsController@createBoard');
            Route::post('/delete-board/{id}', 'BoardsController@deleteBoard');
            Route::get('/get-phone-lines', 'BoardsController@getBoards');
            Route::get('/get-tags', 'BoardsController@getTags');

            // Columns
            Route::post('/save-row-and-columns', 'RowAndColumnsController@createOrUpdateRowAndColumns');

            // Employee Cards
            Route::post('/create-kanban-task-cards', 'TaskCardController@createTaskCards');
            Route::post('/get-task-cards-by-column/{id}', 'TaskCardController@getTaskCardsByColumn');
            Route::post('/update-task-card-indexes', 'TaskCardController@updateTaskCardIndexes');
            Route::post('/update-column-indexes', 'RowAndColumnsController@updateColumnIndexes');
            Route::post('/update-row-indexes', 'RowAndColumnsController@updateRowIndexes');

            Route::post('/update-task-card-column/{columnId}/{taskCardId}', 'TaskCardController@updateTaskCardColumnId');
            Route::post('/delete-kanban-task-card/{id}', 'TaskCardController@deleteTaskCard');

            // Employees
            Route::get('/get-all-users', 'EmployeeController@getAllUsers');
            Route::post('/create-kanban-employees', 'EmployeeController@createEmployees');
            Route::post('/delete-kanban-employee/{id}', 'EmployeeController@deleteEmployee');
            Route::get('/get-kanban-employees', 'EmployeeController@getEmployees');

            // Phone Line Members
            Route::post('/create-members/{id}', 'MemberController@createMembers');
            Route::post('/delete-member/{id}', 'MemberController@deleteMember');
            Route::get('/get-members/{id}', 'MemberController@getMembers');

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
