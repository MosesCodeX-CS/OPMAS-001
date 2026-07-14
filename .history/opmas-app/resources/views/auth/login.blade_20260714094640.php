<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OPMAS — Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-950 min-h-screen flex items-center justify-center">
    <div class="w-full max-w-sm">
        <div class="text-center mb-8">
            <p class="text-xs text-gray-500 font-mono mb-1">OPMAS-001</p>
            <h1 class="text-2xl font-semibold text-white">OX-PLANT-01</h1>
            <p class="text-sm text-gray-500 mt-1">Monitoring System</p>
        </div>
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-8">
            @if($errors->any())
                <div class="bg-red-900/50 border border-red-700 text-red-300 text-sm rounded-lg px-4 py-3 mb-5">
                    {{ $errors->first() }}
                </div>
            @endif
            <form method="POST" action="/login" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs text-gray-400 mb-1.5">Email</label>
                    <input type="email" name="email" required
                        class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-sm text-white focus:outline-none focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-xs text-gray-400 mb-1.5">Password</label>
                    <input type="password" name="password" required
                        class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-sm text-white focus:outline-none focus:border-blue-500">
                </div>
                <button type="submit"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium py-2.5 rounded-lg mt-2 transition-colors">
                    Sign In
                </button>
            </form>
        </div>
    </div>
</body>
</html>