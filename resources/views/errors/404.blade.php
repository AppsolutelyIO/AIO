<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>404 - Page Not Found</title>
    <style>
        body {
            font-family: system-ui, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
        }

        .container {
            text-align: center;
        }

        h1 {
            font-size: 4rem;
            margin: 0;
            color: #333;
        }

        p {
            color: #666;
            margin: 1rem 0;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>404</h1>
        <p>Page Not Found</p>
    </div>
</body>

</html>
