<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OPMAS — Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        html {
            font-size: 18px;
        }
        body {
            font-size: 1.125rem;
            background:
                linear-gradient(rgba(7, 21, 39, 0.28), rgba(7, 21, 39, 0.42)),
                url("{{ asset('images/oxygen-plant-bg.jpg') }}") center / cover no-repeat fixed;
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center px-4 py-10">
    <div class="w-full max-w-md">
        <div class="rounded-xl px-8 py-9 shadow-2xl border border-white/15" style="background:linear-gradient(180deg, rgba(59,174,209,0.96), rgba(35,151,190,0.96));">
            <div class="text-center mb-7">
                <div class="inline-flex items-center justify-center w-20 h-16 mb-4">
                    <img src="{{ asset('images/Kijabe-logo.png') }}" alt="AIC Kijabe Hospital" class="max-h-16 max-w-40 object-contain">
                </div>
                <h1 class="text-3xl font-light tracking-wide text-white">AIC KIJABE HOSPITAL</h1>
                <p class="text-sm mt-2 text-white/90">Oxygen Plant Monitoring System</p>
                <p class="text-xs mt-0.5 font-mono text-white/80">OPMAS-001 · OX-PLANT-01</p>
            </div>

            @if($errors->any())
                <div class="rounded-lg px-4 py-3 mb-5 border text-sm"
                     style="background:#FEE2E2; border-color:#FCA5A5; color:#B91C1C;">
                    {{ $errors->first() }}
                </div>
            @endif
            <form method="POST" action="/login" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-semibold mb-1.5 text-white">Email Address</label>
                    <input type="email" name="email" required value="{{ old('email') }}"
                        placeholder="Email Address"
                        class="w-full rounded-md px-4 py-3 text-base border-0 focus:outline-none focus:ring-2"
                        style="background:#EAF3FA; color:#1A2A3A; --tw-ring-color:#FFFFFF;">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1.5 text-white">Password</label>
                    <input type="password" name="password" required
                        placeholder="Password"
                        class="w-full rounded-md px-4 py-3 text-base border-0 focus:outline-none focus:ring-2"
                        style="background:#EAF3FA; color:#1A2A3A; --tw-ring-color:#FFFFFF;">
                </div>
                <button type="submit"
                    class="w-full text-sm font-bold py-3 rounded-md mt-2 shadow transition-opacity hover:opacity-90"
                    style="background-color:#FFFFFF; color:#0988C8;">
                    Sign In
                </button>
            </form>

            <p class="text-center text-sm mt-7 text-white">
                © {{ date('Y') }} Kijabe Hospital · Confidential System
            </p>
        </div>
    </div>
</body>
</html>
