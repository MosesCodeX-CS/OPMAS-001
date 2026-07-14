<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OPMAS — Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen flex items-center justify-center" style="background-color:#F4F6F9;">
    <div class="w-full max-w-sm">
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full mb-4 shadow"
                 style="background-color:#1B3A6B;">
                <span class="text-white text-xl font-bold">AICKH</span>
            </div>
            <h1 class="text-2xl font-bold" style="color:#1B3A6B;">AicKijabe Hospital</h1>
            <p class="text-sm mt-1" style="color:#6B7A90;">Oxygen Plant Monitoring System</p>
            <p class="text-xs mt-0.5 font-mono" style="color:#2B8AC6;">OPMAS-001 · OX-PLANT-01</p>
        </div>

        <div class="rounded-2xl p-8 shadow-sm border" style="background:#FFFFFF; border-color:#DDE3EE;">
            @if($errors->any())
                <div class="rounded-lg px-4 py-3 mb-5 border text-sm"
                     style="background:#FEE2E2; border-color:#FCA5A5; color:#B91C1C;">
                    {{ $errors->first() }}
                </div>
            @endif
            <form method="POST" action="/login" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold mb-1.5" style="color:#1B3A6B;">Email Address</label>
                    <input type="email" name="email" required value="{{ old('email') }}"
                        class="w-full rounded-lg px-4 py-2.5 text-sm border focus:outline-none"
                        style="background:#F4F6F9; border-color:#DDE3EE; color:#1A2A3A;">
                </div>
                <div>
                    <label class="block text-xs font-semibold mb-1.5" style="color:#1B3A6B;">Password</label>
                    <input type="password" name="password" required
                        class="w-full rounded-lg px-4 py-2.5 text-sm border focus:outline-none"
                        style="background:#F4F6F9; border-color:#DDE3EE; color:#1A2A3A;">
                </div>
                <button type="submit"
                    class="w-full text-white text-sm font-semibold py-2.5 rounded-lg mt-2 transition-opacity hover:opacity-90"
                    style="background-color:#1B3A6B;">
                    Sign In
                </button>
            </form>
        </div>

        <p class="text-center text-xs mt-6" style="color:#6B7A90;">
            © {{ date('Y') }} Kijabe Hospital · Confidential System
        </p>
    </div>
</body>
</html>