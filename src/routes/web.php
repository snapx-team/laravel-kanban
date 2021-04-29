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
            Route::get('/get-phone-line-data/{id}', 'LaravelKanbanController@getPhoneLineData');
            Route::get('/get-dashboard-data', 'LaravelKanbanController@getDashboardData');

            // Phone Lines
            Route::post('/create-phone-line', 'PhoneLineController@createPhoneLine');
            Route::post('/delete-phone-line/{id}', 'PhoneLineController@deletePhoneLine');
            Route::get('/get-phone-lines', 'PhoneLineController@getPhoneLines');
            Route::get('/get-tags', 'PhoneLineController@getTags');

            // Columns
            Route::post('/create-columns', 'ColumnController@createOrUpdateColumns');

            // Employee Cards
            Route::post('/create-employee-cards', 'EmployeeCardController@createEmployeeCards');
            Route::post('/get-employee-cards-by-column/{id}', 'EmployeeCardController@getEmployeeCardsByColumn');
            Route::post('/update-employee-card-indexes', 'EmployeeCardController@updateEmployeeCardIndexes');
            Route::post('/update-employee-card-column/{columnId}/{employeeCardId}', 'EmployeeCardController@updateEmployeeCardColumnId');

            Route::post('/delete-employee-card/{id}', 'EmployeeCardController@deleteEmployeeCard');

            // Employees
            Route::post('/create-employee', 'EmployeeController@createEmployee');
            Route::post('/delete-employee/{id}', 'EmployeeController@deleteEmployee');
            Route::get('/get-employees', 'EmployeeController@getEmployees');

            // Phone Line Members
            Route::post('/create-members/{id}', 'MemberController@createMembers');
            Route::post('/delete-member/{id}', 'MemberController@deleteMember');
            Route::get('/get-members/{id}', 'MemberController@getMembers');

        });
    });
});

// API

Route::group(['namespace' => 'Xguard\PhoneScheduler\Http\Controllers',], function () {
    Route::group(['prefix' => 'phone-scheduler',], function () {

        Route::get('/api/formatted-phone-line-data/{id}', 'LaravelKanbanController@getFormattedData');
        Route::get('/api/get-available-agent/{id}/{level}', 'LaravelKanbanController@getAvailableAgent');
    });
});
