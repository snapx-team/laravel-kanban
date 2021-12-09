<!DOCTYPE html>
<html>
<head>
    <title>Kanban</title>
    <link href="{{ secure_asset(mix("app.css", 'vendor/laravel-kanban')) }}?v={{config('laravel_kanban.version')}}" rel="stylesheet" type="text/css">
</head>
<body>
<div id="app"></div>
<script src="{{ secure_asset(mix('app.js', 'vendor/laravel-kanban')) }}?v={{config('laravel_kanban.version')}}"></script>
</body>
</html>
