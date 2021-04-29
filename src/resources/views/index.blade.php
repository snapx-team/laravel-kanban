<!DOCTYPE html>
<html>
<head>
    <title>Laravel Kanban</title>
    <link href="{{ asset(mix("app.css", 'vendor/laravel-kanban')) }}?v={{config('app.version')}}" rel="stylesheet" type="text/css">
</head>
<body>
<div id="app"></div>
<script src="{{ asset(mix('app.js', 'vendor/laravel-kanban')) }}?v={{config('app.version')}}"></script>
</body>
</html>
