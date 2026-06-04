<!DOCTYPE html>
<html lang="uz">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Kirish — UySotish Pro</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gradient-to-br from-emerald-50 to-gray-100 flex items-center justify-center p-4">

<div class="w-full max-w-sm">
    {{-- Logo --}}
    <div class="text-center mb-8">
        <div class="w-14 h-14 bg-emerald-600 rounded-2xl flex items-center justify-center mx-auto mb-3 shadow-lg">
            <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5
                         M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
        </div>
        <h1 class="text-2xl font-bold text-gray-900">UySotish Pro</h1>
        <p class="text-sm text-gray-500 mt-1">Ko'chmas mulk sotish tizimi</p>
    </div>

    {{-- Forma --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 text-sm mb-5 flex items-start gap-2.5">
            <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            {{ $errors->first() }}
        </div>
        @endif

        <form action="{{ route('login') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label for="email"
                       class="block text-sm font-medium text-gray-700 mb-1.5">
                    Email manzil
                </label>
                <input id="email" type="email" name="email"
                       value="{{ old('email') }}"
                       required autofocus autocomplete="email"
                       class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm
                              focus:outline-none focus:ring-2 focus:ring-emerald-400
                              placeholder-gray-400"
                       placeholder="admin@uysotish.uz">
            </div>

            <div>
                <label for="password"
                       class="block text-sm font-medium text-gray-700 mb-1.5">
                    Parol
                </label>
                <input id="password" type="password" name="password"
                       required autocomplete="current-password"
                       class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm
                              focus:outline-none focus:ring-2 focus:ring-emerald-400"
                       placeholder="••••••••">
            </div>

            <div class="flex items-center gap-2">
                <input type="checkbox" id="remember" name="remember"
                       class="w-4 h-4 rounded border-gray-300 text-emerald-600
                              focus:ring-emerald-500 cursor-pointer">
                <label for="remember" class="text-sm text-gray-600 cursor-pointer">
                    Eslab qolish
                </label>
            </div>

            <button type="submit"
                    class="w-full bg-emerald-600 text-white py-2.5 rounded-xl text-sm font-medium
                           hover:bg-emerald-700 active:scale-95 transition mt-2">
                Tizimga kirish
            </button>
        </form>
    </div>

    {{-- Demo ma'lumotlar --}}
    <div class="mt-4 bg-blue-50 border border-blue-100 rounded-xl p-4 text-xs text-blue-700">
        <p class="font-semibold mb-2">Demo ma'lumotlar:</p>
        <div class="space-y-1">
            <div class="flex justify-between">
                <span>Admin:</span>
                <span class="font-mono">admin@uysotish.uz / Admin1234!</span>
            </div>
            <div class="flex justify-between">
                <span>Menejer:</span>
                <span class="font-mono">sardor@uysotish.uz / Manager123!</span>
            </div>
            <div class="flex justify-between">
                <span>Hisobchi:</span>
                <span class="font-mono">zafar@uysotish.uz / Accountant123!</span>
            </div>
        </div>
    </div>
</div>

</body>
</html>
