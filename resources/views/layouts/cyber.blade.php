<!DOCTYPE html>
<html lang="{{ $sysLanguage ?? app()->getLocale() }}" x-data="{ theme: localStorage.getItem('obsidian_theme') || 'dark' }" :class="theme">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

   @php
        $sysSettings = [];
        try {
            if (class_exists(\App\Models\Setting::class)) {
                $sysSettings = \App\Models\Setting::pluck('value', 'key')->toArray();
            }
        } catch(\Exception $e) {}
        
        $companyName = $sysSettings['company_name'] ?? config('app.name', 'Obsidian OS');
        $bgImage = $sysSettings['bg_image_url'] ?? '';
        $strictModeStatus = $sysSettings['strict_mode'] ?? '1';
        
        $sysLanguage = (auth()->check() && auth()->user()->ui_language) ? auth()->user()->ui_language : ($sysSettings['sys_language'] ?? app()->getLocale());
        $sysFontSize = (auth()->check() && auth()->user()->ui_font_size) ? auth()->user()->ui_font_size : ($sysSettings['sys_font_size'] ?? 'text-base');
        
        $colorAdmin = $sysSettings['color_admin'] ?? '#FFD700';
        $colorOperator = $sysSettings['color_operator'] ?? '#00E5FF';
        $colorCashier = $sysSettings['color_cashier'] ?? '#00FF00';
        $colorDev = $sysSettings['color_dev'] ?? '#FF00FF';
        $userFontColor = (auth()->check() && auth()->user()->ui_font_color) ? auth()->user()->ui_font_color : null;
    @endphp
    <title>{{ $companyName }} - {{ $title ?? 'Terminal' }}</title>

    <!-- Fonts -->
    <link href="https://fonts.bunny.net/css?family=orbitron:400,500,700,900|share-tech-mono:400&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --color-admin: {{ $colorAdmin }};
            --color-operator: {{ $colorOperator }};
            --color-cashier: {{ $colorCashier }};
            --color-dev: {{ $colorDev }};
            
            --electric-blue: #00f0ff;
            --cyber-yellow: #fcee0a;
            --coal-black: #0d0d0d;
            
            --active-color: {{ auth()->check() ? (auth()->user()->role === 'admin' ? 'var(--color-admin)' : (auth()->user()->role === 'operator' ? 'var(--color-operator)' : (auth()->user()->role === 'cashier' ? 'var(--color-cashier)' : 'var(--color-dev)'))) : 'var(--color-cashier)' }};
            
            --bg-color: var(--coal-black);
            --text-color: {{ $userFontColor ? $userFontColor : 'var(--active-color)' }};
            --panel-bg: rgba(10, 10, 10, 0.85);
            --grid-color: rgba(20, 20, 20, 0.5);
            --border-color: var(--active-color);
            --header-bg: rgba(0, 0, 0, 0.9);
            --sidebar-bg: rgba(0, 0, 0, 0.95);
            --input-bg: rgba(0, 0, 0, 0.5);
            --hover-bg: rgba(125, 125, 125, 0.15);
        }

        .light {
            --bg-color: #f0f4f8;
            --panel-bg: rgba(255, 255, 255, 0.9);
            --grid-color: rgba(200, 200, 200, 0.5);
            --header-bg: rgba(255, 255, 255, 0.95);
            --sidebar-bg: rgba(240, 245, 250, 0.95);
            --input-bg: rgba(255, 255, 255, 0.7);
            --hover-bg: rgba(0, 0, 0, 0.05);
            
            --electric-blue: #007acc;
            --cyber-yellow: #d97706;
            --color-admin: #d97706; 
            --color-operator: #007acc;
            --color-cashier: #15803d;
            
            --active-color: {{ auth()->check() ? (auth()->user()->role === 'admin' ? 'var(--color-admin)' : (auth()->user()->role === 'operator' ? 'var(--color-operator)' : (auth()->user()->role === 'cashier' ? 'var(--color-cashier)' : 'var(--color-dev)'))) : 'var(--color-cashier)' }};
            --text-color: #1a2c22ff; /* Always enforce dark text in light mode for readability */
            --border-color: var(--active-color);
        }

        body {
            font-family: 'Share Tech Mono', monospace;
            background-color: var(--bg-color);
            color: var(--text-color);
            background-image: 
                linear-gradient(var(--panel-bg), var(--panel-bg)),
                {{ $bgImage ? 'url('.$bgImage.'),' : '' }}
                repeating-linear-gradient(0deg, transparent, transparent 2px, var(--grid-color) 2px, var(--grid-color) 4px);
            background-size: cover, cover;
            background-attachment: fixed;
            margin: 0;
            overflow-x: hidden; /* Prevent horizontal scroll on body */
            overflow-y: hidden; /* Desktop remains fixed-UI, inner scroll handled by main/sidebar */
            scrollbar-width: thin;
            scrollbar-color: var(--active-color) transparent;
            transition: background-color 0.3s, color 0.3s;
        }

        @media (max-width: 768px) {
            body {
                overflow-y: auto; /* Allow natural scroll on mobile if needed */
            }
        }

        /* Webkit scrollbar for Neon Effect */
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb {
            background: var(--active-color);
            border-radius: 4px;
        }

        .crt::before {
            content: " ";
            display: block;
            position: absolute;
            top: 0; left: 0; bottom: 0; right: 0;
            background: linear-gradient(rgba(18, 16, 16, 0) 50%, rgba(0, 0, 0, 0.25) 50%), linear-gradient(90deg, rgba(255, 0, 0, 0.06), rgba(0, 255, 0, 0.02), rgba(0, 0, 255, 0.06));
            z-index: 2;
            background-size: 100% 2px, 3px 100%;
            pointer-events: none;
            mix-blend-mode: overlay;
        }

        /* Glitch warning modal */
        #warning-modal {
            background: var(--header-bg);
            border: 2px solid red;
            box-shadow: 0 0 30px red;
            color: red;
        }

        /* Panels */
        .cyber-panel {
            background: var(--panel-bg);
            border: 1px solid var(--border-color);
            box-shadow: 0 0 10px rgba(0,0,0,0.2), inset 0 0 10px rgba(0,0,0,0.1);
            position: relative;
            backdrop-filter: blur(10px);
        }
        
        .cyber-panel::before {
            content: '';
            position: absolute;
            top: 0; left: 0;
            width: 100%; height: 2px;
            background: var(--active-color);
            box-shadow: 0 0 10px var(--active-color);
        }

        .nav-button {
            border: 1px solid transparent;
            transition: all 0.2s;
            cursor: pointer;
        }
        
        .nav-button:hover, .nav-item:hover {
            border-color: var(--active-color);
            background: rgba(125, 125, 125, 0.1);
            text-shadow: 0 0 5px var(--active-color);
            box-shadow: 0 0 10px var(--active-color);
        }

        .nav-item {
            padding: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
            border-left: 3px solid transparent;
            transition: all 0.2s;
            text-decoration: none;
            color: var(--active-color);
        }
        
        .nav-item.active {
            border-left-color: var(--active-color);
            background: rgba(125, 125, 125, 0.15);
            text-shadow: 0 0 5px var(--active-color);
        }
        
        .font-orbitron { font-family: 'Orbitron', sans-serif; }
    </style>
</head>

<body class="antialiased min-h-screen crt {{ $sysFontSize ?? '' }}" x-data="cyberSystem()" x-init="$watch('theme', val => localStorage.setItem('obsidian_theme', val))">

    <!-- Top Navigation Mainframe -->
    <header class="border-b border-[var(--border-color)] p-4 sticky top-0 z-50 flex justify-between items-center shadow-[0_0_15px_var(--border-color)] backdrop-blur-md transition-colors" style="background-color: var(--header-bg);">
        <div class="flex items-center gap-2 md:gap-4">
            <!-- Mobile Menu Toggle -->
            <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden p-1 sm:p-2 text-[var(--active-color)] border border-[var(--active-color)] hover:bg-[var(--active-color)] hover:text-[var(--bg-color)] transition-colors focus:outline-none">
                <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
            </button>
            
            <h1 class="text-xl md:text-3xl font-orbitron font-bold tracking-widest leading-none drop-shadow-md flex items-center gap-2 md:gap-4">
                @if(isset($sysSettings['company_logo']) && $sysSettings['company_logo'])
                    <img src="{{ $sysSettings['company_logo'] }}" class="h-6 md:h-8 max-w-[50px] md:max-w-[80px] object-contain" alt="Logo">
                @endif
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-[var(--active-color)] to-[var(--cyber-yellow)] cyber-glitch" style="text-shadow: none;">
                    @php $parts = explode('.', $companyName ?? 'OBSIDIAN.OS', 2); @endphp
                    {{ $parts[0] }}@if(count($parts)>1)<span class="opacity-50 text-xs md:text-xl md:block md:-mt-1 md:inline-block text-[var(--active-color)]">.{{ $parts[1] }}</span>@endif
                </span>
            </h1>
            <span class="hidden md:inline-block px-3 py-1 text-sm border border-[var(--active-color)] bg-[var(--active-color)] text-[var(--bg-color)] font-bold uppercase shadow-[0_0_10px_var(--active-color)] tracking-wider">
                {{ auth()->user()->role ?? 'AGENT' }} TERMINAL
            </span>

            @if(session()->has('impersonate_by'))
            <form method="POST" action="{{ route('leave.impersonate') }}" class="inline">
                @csrf
                <button type="submit" class="hidden md:inline-block px-3 py-1 text-sm border border-red-500 bg-red-600 text-white font-bold uppercase shadow-[0_0_15px_rgba(255,0,0,0.5)] blink-animation tracking-wider hover:bg-red-800 transition-colors">
                    <svg class="w-4 h-4 inline-block mr-1 -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 15l-3-3m0 0l3-3m-3 3h8M3 12a9 9 0 1118 0 9 9 0 01-18 0z"></path></svg>
                    ADMINGA QAYTISH
                </button>
            </form>
            <style>
                @keyframes blink { 50% { opacity: 0.8; box-shadow: 0 0 5px red; } }
                .blink-animation { animation: blink 1.5s infinite; }
            </style>
            @endif
        </div>

        <div class="flex items-center gap-2 md:gap-6">
            <!-- Global Notification Indicator -->
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" class="p-1 md:p-2 text-[var(--active-color)] hover:bg-[var(--active-color)] hover:text-black transition-all border border-transparent hover:border-[var(--active-color)] relative">
                    <svg class="w-5 h-5 md:w-6 md:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                    </svg>
                    <span class="absolute top-0 right-0 w-2 h-2 bg-red-500 rounded-full animate-ping"></span>
                </button>
                <div x-show="open" @click.away="open = false" style="display: none;" class="absolute right-0 mt-2 w-64 cyber-panel border-t-4 border-[var(--active-color)] p-4 shadow-2xl z-[60]">
                    <h3 class="text-sm font-orbitron mb-2 uppercase tracking-widest text-[var(--active-color)]">Tizim Bildirishnomalari</h3>
                    <div class="space-y-2 max-h-48 overflow-y-auto font-mono text-xs uppercase">
                        <div class="p-2 bg-black bg-opacity-40 border-l border-[var(--active-color)]">
                            <p class="text-[var(--active-color)]">Yangi shartnoma kutilmoqda [FCC]</p>
                            <span class="opacity-50">Hozirgina</span>
                        </div>
                        <div class="p-2 bg-black bg-opacity-40 border-l border-[var(--cyber-yellow)] opacity-70">
                            <p class="text-[var(--cyber-yellow)]">G'azna hisoboti tayyor</p>
                            <span class="opacity-50">15 daqiqa oldin</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Accessibility Button -->
            <button @click="accessMenuOpen = true" class="flex items-center gap-2 px-2 md:px-3 py-1 border border-[var(--active-color)] text-[var(--active-color)] hover:bg-[var(--active-color)] hover:text-white transition-colors text-sm font-orbitron tracking-widest shadow-[0_0_5px_var(--active-color)] hover:shadow-[0_0_15px_var(--active-color)]" title="Vizual Sozlamalar" @mouseenter="if(window.speakUzbekGlobal) window.speakUzbekGlobal('Vizual sozlamalar')">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                <span class="hidden sm:inline">KO'RISH</span>
            </button>

            <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 px-2 md:px-3 py-1 border border-[var(--active-color)] text-[var(--active-color)] hover:bg-[var(--active-color)] hover:text-white transition-colors text-sm font-orbitron tracking-widest shadow-[0_0_5px_var(--active-color)] hover:shadow-[0_0_15px_var(--active-color)]" @mouseenter="if(window.speakUzbekGlobal) window.speakUzbekGlobal('Profil sozlamalari')">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                <span class="hidden sm:inline">PROFIL</span>
            </a>
            <button @click="theme = theme === 'dark' ? 'light' : 'dark'" class="items-center gap-2 px-3 py-1 border border-[var(--active-color)] text-[var(--active-color)] hover:bg-[var(--active-color)] hover:text-white transition-colors text-xs font-orbitron tracking-widest hidden md:flex">
                <svg x-show="theme === 'dark'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                <svg x-show="theme === 'light'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
                <span x-text="theme === 'dark' ? 'SOLAR' : 'NEON'"></span>
            </button>
            <div class="hidden lg:flex text-right flex-col items-end border-r border-[var(--active-color)] pr-6">
                <span class="text-xs opacity-70 tracking-widest text-[var(--active-color)] font-mono">NEURAL-ID: {{ auth()->user()->internal_id }}</span>
                <span class="font-bold tracking-widest text-sm text-[var(--active-color)]">{{ strtoupper(auth()->user()->name) }}</span>
            </div>
            
            <form method="POST" action="{{ route('logout') }}" id="logout-form" class="hidden">
                @csrf
            </form>
            <button type="submit" form="logout-form" @mouseenter="if(window.speakUzbekGlobal) window.speakUzbekGlobal('Tizimdan chiqish')" class="px-2 md:px-4 py-1.5 border border-red-500 text-red-500 hover:bg-red-500 hover:text-white font-bold uppercase tracking-widest flex items-center gap-2 nav-button shadow-[0_0_10px_rgba(255,0,0,0.3)] transition-all">
                <svg class="w-4 h-4 md:w-5 md:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                <span class="hidden md:inline text-sm">CHIQISH</span>
            </button>

        </div>
    </header>

    <div class="flex h-[calc(100vh-80px)] relative w-full overflow-hidden">
        <!-- Sidebar Menu -->
        <aside :class="{'translate-x-0': mobileMenuOpen, '-translate-x-full': !mobileMenuOpen}" class="w-64 border-r border-[var(--active-color)] flex flex-col gap-2 overflow-y-auto cyber-panel border-t-0 absolute md:relative z-40 h-full transition-all duration-300 md:translate-x-0 inset-y-0 left-0 pt-4 md:pt-0" style="background-color: var(--sidebar-bg);">
            <!-- Sidebar Header / Avatar Placeholder -->
            <div class="p-6 border-b border-[var(--active-color)] border-opacity-30 text-center">
                <div class="w-24 h-24 rounded outline outline-offset-2 outline-[var(--active-color)] mx-auto overflow-hidden bg-gray-900 flex items-center justify-center">
                    @if(auth()->user()->avatar)
                        <img src="{{ auth()->user()->avatar }}" alt="Avatar" class="w-full h-full object-cover opacity-80 mix-blend-screen mix-blend-color-dodge">
                    @else
                        <svg class="w-12 h-12 opacity-50 text-[var(--active-color)]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    @endif
                </div>
                <div class="mt-4 break-words">
                    <p class="text-xs uppercase opacity-70">XP: {{ auth()->user()->xp }}</p>
                </div>
            </div>

            <!-- Dashboard specific sidebar navigation slots -->
            <nav class="flex-1 p-4 flex flex-col gap-3 font-orbitron tracking-widest text-sm uppercase">
                @yield('sidebar')
            </nav>
            
            <div class="p-4 border-t border-[var(--active-color)] text-xs text-center opacity-50 border-opacity-30">
                <div class="flex items-center justify-center gap-2 mb-1">
                    <div class="w-2 h-2 rounded-full bg-[var(--active-color)] animate-pulse"></div>
                    <span>TIZIM ONLAYN</span>
                </div>
                V_2077.0.1 ALPHA
            </div>
        </aside>

        <!-- Main Content (The Grid) -->
        <main class="flex-1 p-4 md:p-6 overflow-y-auto relative z-20 flex flex-col min-h-0">
            <!-- Grid Lines Background behind content -->
            <div class="absolute inset-0 z-0 opacity-10 pointer-events-none" style="background-image: linear-gradient(var(--active-color) 1px, transparent 1px), linear-gradient(90deg, var(--active-color) 1px, transparent 1px); background-size: 50px 50px; transform: perspective(1000px) rotateX(60deg) scale(2); transform-origin: top;"></div>
            
            <div class="relative z-10 w-full max-w-7xl mx-auto h-full space-y-6">
                @yield('content')
            </div>
        </main>
    </div>

    <!-- STRICT MODE / INACTIVITY WARNING MODAL -->
    <div x-show="showWarning" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-95 backdrop-blur-md">
        <div id="warning-modal" class="p-10 text-center max-w-2xl bg-black crt border-8 border-red-600">
            <h2 class="text-5xl font-orbitron font-bold mb-6 animate-ping" style="color: #ff0000; text-shadow: 0 0 20px #ff0000, 0 0 40px #ff0000;">KRITIK XATOLIK</h2>
            <div class="h-1 w-full bg-red-600 mb-6 shadow-[0_0_10px_red]"></div>
            <p class="text-2xl mb-4 font-bold text-red-500">OPERATORDAN HARAKAT KUZATILMAYAPTI.</p>
            <p class="text-xl opacity-80 mb-2 text-red-400">TIZIM QULFLANIShIGA QOLDI:</p>
            <div class="text-8xl font-orbitron font-bold my-8 text-white tabular-nums drop-shadow-[0_0_15px_red] animate-pulse" x-text="countdown">10</div>
            
            <button @click="abortDisconnect" class="px-10 py-4 mt-4 bg-transparent border-4 border-red-500 text-red-500 font-bold tracking-[0.5em] hover:bg-red-600 hover:text-black transition-all cursor-pointer w-full text-2xl shadow-[inset_0_0_20px_rgba(255,0,0,0.5)]">
                [ TIZIMDA QOLISH ]
            </button>
        </div>
        <audio x-ref="alarm" src="https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3" preload="auto" loop></audio>
    </div>

    <!-- ACCESSIBILITY SETTINGS MODAL -->
    <div x-show="accessMenuOpen" style="display: none;" class="fixed inset-0 z-[100] flex items-center justify-center bg-black bg-opacity-90 backdrop-blur-md p-4">
        <div class="cyber-panel p-6 md:p-8 w-full max-w-md border-[var(--active-color)] bg-black relative max-h-[90vh] overflow-y-auto">
            <button @click="accessMenuOpen = false" class="absolute top-4 right-4 text-gray-500 hover:text-white">
                 <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
            <h2 class="text-xl font-orbitron mb-6 border-b border-[var(--active-color)] pb-2 text-[var(--active-color)] uppercase tracking-widest font-bold">Vizual Sozlamalar</h2>
            
            <div class="space-y-6 font-mono text-sm">
                <!-- Font Scale -->
                <div>
                    <label class="block opacity-70 mb-2 font-bold uppercase text-white">Shrift O'lchami</label>
                    <div class="grid grid-cols-4 gap-2">
                        <button @click="accessScale = '14'" :class="accessScale === '14' ? 'bg-[var(--active-color)] text-black' : 'bg-transparent text-white border-gray-700'" class="p-2 border hover:border-[var(--active-color)] transition-colors">A-</button>
                        <button @click="accessScale = '16'" :class="accessScale === '16' ? 'bg-[var(--active-color)] text-black' : 'bg-transparent text-white border-gray-700'" class="p-2 border hover:border-[var(--active-color)] transition-colors">A</button>
                        <button @click="accessScale = '18'" :class="accessScale === '18' ? 'bg-[var(--active-color)] text-black' : 'bg-transparent text-white border-gray-700'" class="p-2 border hover:border-[var(--active-color)] transition-colors">A+</button>
                        <button @click="accessScale = '22'" :class="accessScale === '22' ? 'bg-[var(--active-color)] text-black' : 'bg-transparent text-white border-gray-700'" class="p-2 border hover:border-[var(--active-color)] transition-colors">A++</button>
                    </div>
                    <p class="text-xs mt-2 opacity-50">Tizimdagi barcha matnlar tanlangan hajmga moslashadi.</p>
                </div>

                <!-- Theme Accent Color -->
                <div>
                    <label class="block opacity-70 mb-2 font-bold uppercase text-white">Asosiy Rang</label>
                    <div class="grid grid-cols-5 gap-2">
                        <button @click="accessColor = ''" class="h-10 border transition-all" :class="!accessColor ? 'border-white scale-110 shadow-[0_0_10px_white]' : 'border-gray-800'" style="background: linear-gradient(45deg, #FFD700, #00E5FF, #00FF00);" title="Standart Rol rangi"></button>
                        <button @click="accessColor = '#00f0ff'" class="h-10 bg-[#00f0ff] border transition-all" :class="accessColor === '#00f0ff' ? 'border-white scale-110 shadow-[0_0_10px_#00f0ff]' : 'border-transparent'"></button>
                        <button @click="accessColor = '#fcee0a'" class="h-10 bg-[#fcee0a] border transition-all" :class="accessColor === '#fcee0a' ? 'border-white scale-110 shadow-[0_0_10px_#fcee0a]' : 'border-transparent'"></button>
                        <button @click="accessColor = '#ff00ff'" class="h-10 bg-[#ff00ff] border transition-all" :class="accessColor === '#ff00ff' ? 'border-white scale-110 shadow-[0_0_10px_#ff00ff]' : 'border-transparent'"></button>
                        <button @click="accessColor = '#39ff14'" class="h-10 bg-[#39ff14] border transition-all" :class="accessColor === '#39ff14' ? 'border-white scale-110 shadow-[0_0_10px_#39ff14]' : 'border-transparent'"></button>
                    </div>
                    <p class="text-xs mt-2 opacity-50">Rolga tegishli yoki o'zingiz tanlagan asosiy rangni belgilang.</p>
                </div>
            </div>
            
            <button @click="accessMenuOpen = false" class="w-full mt-8 py-3 bg-[var(--active-color)] text-black font-bold uppercase tracking-widest hover:bg-transparent hover:text-[var(--active-color)] border border-transparent hover:border-[var(--active-color)] transition-all drop-shadow-[0_0_5px_var(--active-color)]">
                YOPISH
            </button>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('cyberSystem', () => ({
                mobileMenuOpen: false,
                accessMenuOpen: false,
                accessScale: localStorage.getItem('obsidian_scale') || '16',
                accessColor: localStorage.getItem('obsidian_color') || '',
                lastActivityTime: Date.now(),
                showWarning: false,
                countdown: 10,
                countdownTimer: null,
                idleLimitMs: 120000,
                heartbeatInterval: null,
                isStrictMode: {{ $strictModeStatus }},
                
                init() {
                    this.bindWatchers();
                    this.applyAccessSettings();
                    
                    this.$watch('accessScale', val => { 
                        localStorage.setItem('obsidian_scale', val); 
                        this.applyAccessSettings(); 
                    });
                    this.$watch('accessColor', val => { 
                        localStorage.setItem('obsidian_color', val); 
                        this.applyAccessSettings(); 
                    });

                    setInterval(() => this.checkInactivity(), 2000);
                    setInterval(() => this.sendHeartbeat(), 30000);
                    
                    window.speakUzbekGlobal = this.speakUzbek.bind(this);
                },

                applyAccessSettings() {
                    document.documentElement.style.fontSize = this.accessScale + 'px';
                    if (this.accessColor) {
                        document.documentElement.style.setProperty('--active-color', this.accessColor);
                    } else {
                        document.documentElement.style.removeProperty('--active-color');
                    }
                },

                speakUzbek(text) {
                    if (!text) return;
                    try {
                        const url = 'https://translate.google.com/translate_tts?ie=UTF-8&tl=uz&client=tw-ob&q=' + encodeURIComponent(text);
                        new Audio(url).play().catch(e => {
                            if ('speechSynthesis' in window) {
                                const utterance = new SpeechSynthesisUtterance(text);
                                utterance.lang = 'uz-UZ';
                                window.speechSynthesis.speak(utterance);
                            }
                        });
                    } catch(err) { }
                },

                bindWatchers() {
                    const updateActivity = () => {
                        if (!this.showWarning) {
                            this.lastActivityTime = Date.now();
                        }
                    };
                    window.addEventListener('mousemove', updateActivity, {passive: true});
                    window.addEventListener('keydown', updateActivity, {passive: true});
                    window.addEventListener('scroll', updateActivity, {passive: true});
                    window.addEventListener('click', updateActivity, {passive: true});
                },

                checkInactivity() {
                    if (!this.isStrictMode || this.showWarning) return;
                    
                    const timeIdle = Date.now() - this.lastActivityTime;
                    if (timeIdle >= this.idleLimitMs) {
                        this.triggerDisconnectSequence();
                    }
                },

                triggerDisconnectSequence() {
                    this.showWarning = true;
                    this.countdown = 10;
                    
                    this.speakUzbek("Diqqat! Operatordan harakat kuzatilmayapti. Tizim bloklanmoqda.");
                    
                    if (this.$refs.alarm) {
                        this.$refs.alarm.play().catch(e => console.log('Audio disabled'));
                    }

                    this.countdownTimer = setInterval(() => {
                        this.countdown--;
                        if (this.countdown <= 0) {
                            clearInterval(this.countdownTimer);
                            this.forceLogout();
                        }
                    }, 1000);
                },

                abortDisconnect() {
                    this.showWarning = false;
                    clearInterval(this.countdownTimer);
                    this.lastActivityTime = Date.now();
                    if (this.$refs.alarm) {
                        this.$refs.alarm.pause();
                        this.$refs.alarm.currentTime = 0;
                    }
                },

                async sendHeartbeat() {
                    try {
                        let response = await fetch('{{ route('system.heartbeat') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        });
                        
                        if (!response.ok) {
                           // If server rejected heartbeat (e.g. logged out elsewhere)
                           this.forceLogout();
                        }
                    } catch (error) {
                        console.error('Heartbeat failed:', error);
                    }
                },

                forceLogout() {
                    let form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '{{ route("logout") }}';
                    let csrf = document.createElement('input');
                    csrf.type = 'hidden';
                    csrf.name = '_token';
                    csrf.value = '{{ csrf_token() }}';
                    form.appendChild(csrf);
                    document.body.appendChild(form);
                    form.submit();
                }
            }))
        })
    </script>
</body>
</html>
