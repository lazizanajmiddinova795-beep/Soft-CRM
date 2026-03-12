@extends('layouts.cyber')

@section('content')
<div class="flex flex-col items-center justify-center min-h-[60vh] text-center p-4">
    <div class="relative">
        <h1 class="text-9xl font-orbitron font-bold text-red-600 animate-pulse opacity-20">404</h1>
        <div class="absolute inset-0 flex items-center justify-center">
            <h2 class="text-3xl md:text-5xl font-orbitron font-bold text-[var(--active-color)] uppercase tracking-[0.5em] cyber-glitch">
                PATH NOT FOUND
            </h2>
        </div>
    </div>
    
    <div class="mt-8 space-y-4 max-w-lg">
        <div class="cyber-panel p-4 border-red-500 bg-red-900 bg-opacity-10">
            <p class="font-mono text-xs md:text-sm text-red-500 uppercase tracking-widest leading-relaxed">
                ERROR: [CORE_SYSTEM_FAILURE] <br>
                Siz so'ragan manzil matritsada mavjud emas yoki kirish huquqi cheklangan. <br>
                Iltimos, boshqaruv paneliga qayting.
            </p>
        </div>
        
        <a href="{{ url('/dashboard') }}" class="inline-block mt-6 px-8 py-3 bg-[var(--active-color)] text-black font-bold font-orbitron uppercase tracking-widest shadow-[0_0_20px_var(--active-color)] hover:scale-105 transition-transform">
            [ TERMINALGA QAYTISH ]
        </a>
    </div>
    
    <div class="mt-12 opacity-30">
        <div class="w-64 h-1 bg-red-600 mx-auto mb-2"></div>
        <p class="text-sm uppercase tracking-[1em] text-red-500">OBSIDIAN.OS SECURITY PROTOCOL</p>
    </div>
</div>
@endsection
