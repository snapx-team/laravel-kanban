<!DOCTYPE html>
<html>
<head>
    <title>Kanban</title>
    <link href="{{ asset(mix("app.css", 'vendor/laravel-kanban')) }}?v={{config('laravel_kanban.version')}}" rel="stylesheet" type="text/css">
</head>
<body>
<div id="app"></div>
<script src="{{ asset(mix('app.js', 'vendor/laravel-kanban')) }}?v={{config('laravel_kanban.version')}}"></script>
</body>
</html>
