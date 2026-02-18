<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Dossiers-RH</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/admin.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-800 font-sans antialiased flex h-screen overflow-hidden">
        @include('admin.layout.sidebar')
        <main class="flex-1 flex flex-col h-full overflow-hidden">
            @include('admin.layout.header')
            <div class="flex-1 overflow-y-auto p-4 lg:p-8">
                @yield('content')
            </div>
        </main>
</body>

<script src="{{ asset('js/admin/script.js') }}"></script>
</html>
