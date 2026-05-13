<!DOCTYPE html>
@php
    $sysSettings = \Illuminate\Support\Facades\Cache::remember('sys_settings_global', 3600, function() {
        if (class_exists(\App\Models\Setting::class)) {
            return \App\Models\Setting::pluck('value', 'key')->toArray();
        }
        return [];
    });
    $companyName = $sysSettings['company_name'] ?? 'OBSIDIAN OS';
    $companyLogo = $sysSettings['company_logo'] ?? null;
@endphp
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $companyName }} | Obsidian OS v1</title>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            /* Cyberpunk colors */
            --bg-dark: #05050a;
            --neon-cyan: #00ffcc;
            --neon-purple: #b026ff;
            --neon-pink: #ff007f;
            
            /* Glassmorphism settings */
            --glass-bg: rgba(20, 20, 35, 0.4);
            --glass-border: rgba(255, 255, 255, 0.08);
            --glass-blur: blur(24px);
            
            /* Text */
            --text-main: #ffffff;
            --text-muted: #8b9bb4;

            /* Legacy compatibility for the Blade content */
            --active-color: var(--neon-cyan);
            --electric-blue: var(--neon-cyan);
            --cyber-yellow: #fcee0a;
            --panel-bg: var(--glass-bg);
            --input-bg: rgba(0,0,0,0.3);
            --border-color: var(--glass-border);
            --text-color: var(--text-main);
        }

        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Outfit', sans-serif; }

        body {
            background-color: var(--bg-dark);
            color: var(--text-main);
            height: 100vh;
            overflow: hidden;
            display: flex;
            position: relative;
        }

        /* Ambient Background */
        .ambient-blob { position: absolute; border-radius: 50%; filter: blur(120px); z-index: -1; opacity: 0.4; }
        .blob-1 { width: 500px; height: 500px; background: var(--neon-purple); top: -100px; left: -100px; animation: float 10s infinite alternate; }
        .blob-2 { width: 400px; height: 400px; background: var(--neon-cyan); bottom: -100px; right: -50px; animation: float 15s infinite alternate-reverse; }

        @keyframes float { 0% { transform: translate(0,0); } 100% { transform: translate(50px, 50px); } }

        /* Dynamic Island */
        .dynamic-island {
            position: fixed;
            top: 15px;
            left: 50%;
            transform: translateX(-50%);
            background: #000000;
            border: 1px solid var(--glass-border);
            border-radius: 40px;
            padding: 10px 25px;
            display: flex;
            align-items: center;
            gap: 15px;
            z-index: 1000;
            box-shadow: 0 10px 30px rgba(0,0,0,0.8);
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
            overflow: hidden;
            min-width: 200px;
            justify-content: center;
        }
        .dynamic-island.active { min-width: 400px; padding: 15px 25px; border-color: var(--neon-cyan); box-shadow: 0 0 20px rgba(0, 255, 204, 0.2); }
        .island-content { font-size: 14px; font-weight: 600; color: var(--text-main); white-space: nowrap; transition: 0.3s; }
        .island-icon { color: var(--neon-cyan); display: none; }
        .dynamic-island.active .island-icon { display: block; animation: pulse 1.5s infinite; }

        @keyframes pulse { 0% { opacity: 0.5; text-shadow: 0 0 0 var(--neon-cyan); } 50% { opacity: 1; text-shadow: 0 0 15px var(--neon-cyan); } 100% { opacity: 0.5; text-shadow: 0 0 0 var(--neon-cyan); } }

        /* Glass Panel */
        .glass-panel {
            background: var(--glass-bg);
            backdrop-filter: var(--glass-blur);
            -webkit-backdrop-filter: var(--glass-blur);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
        }

        /* Sidebar */
        .sidebar { 
            width: 240px; margin: 15px; padding: 25px 15px; 
            display: flex; flex-direction: column; z-index: 100; 
            height: calc(100vh - 30px);
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .brand { font-size: 22px; font-weight: 800; margin-bottom: 40px; text-align: center; letter-spacing: 1px; }
        .brand span { color: var(--neon-cyan); text-shadow: 0 0 10px rgba(0,255,204,0.4); }
        
        .nav-item {
            padding: 12px 18px; border-radius: 14px; margin-bottom: 6px; cursor: pointer;
            display: flex; align-items: center; gap: 12px; font-size: 14px; font-weight: 600; color: var(--text-muted);
            transition: all 0.2s ease; border: 1px solid transparent;
            text-decoration: none;
        }
        .nav-item:hover { background: rgba(255,255,255,0.05); color: var(--text-main); }
        .nav-item.active { background: rgba(0, 255, 204, 0.08); border-color: rgba(0, 255, 204, 0.2); color: var(--neon-cyan); }

        /* Main Container */
        .main-container { flex: 1; padding: 60px 20px 15px 5px; display: flex; flex-direction: column; overflow-y: auto; overflow-x: hidden; z-index: 10; position: relative;}
        
        /* Stats & Widgets */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 15px; margin-bottom: 20px; }
        .stat-card { padding: 20px; border-radius: 18px; position: relative; overflow: hidden; background: var(--glass-bg); border: 1px solid var(--glass-border); }
        .stat-card::before { content: ''; position: absolute; top: 0; left: 0; width: 3px; height: 100%; background: var(--neon-cyan); }
        .stat-title { font-size: 11px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; }
        .stat-value { font-size: 26px; font-weight: 800; letter-spacing: -0.5px; }
        
        .content-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 15px; }
        .panel-title { font-size: 15px; font-weight: 700; margin-bottom: 15px; display: flex; align-items: center; gap: 8px; color: var(--text-main); letter-spacing: 0.5px; text-transform: uppercase; }
        
        /* Mobile Header */
        .mobile-header {
            display: none;
            position: fixed;
            top: 0; left: 0; right: 0;
            height: 60px;
            padding: 0 15px;
            align-items: center;
            justify-content: space-between;
            z-index: 40;
            background: rgba(5,5,10,0.8);
            backdrop-filter: blur(10px);
        }

        /* Responsive Breakpoints */
        @media (max-width: 1024px) {
            .sidebar {
                position: fixed;
                top: 0; left: 0; margin: 0;
                height: 100vh;
                border-radius: 0;
                transform: translateX(-100%);
                background: rgba(5, 5, 12, 0.98);
            }
            .sidebar.mobile-open { transform: translateX(0); }
            .mobile-header { display: flex; }
            .main-container { padding: 75px 15px 15px 15px; }
            .content-row { grid-template-columns: 1fr; }
        }

        /* Scrollbar styling */
        ::-webkit-scrollbar { width: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: var(--neon-cyan); }

        .btn-ios { padding: 8px 16px; border-radius: 12px; border: none; cursor: pointer; font-weight: 700; font-size: 12px; transition: all 0.2s ease; background: rgba(255,255,255,0.08); color: white; display: inline-flex; items-center; gap: 6px; }
        .btn-ios:hover { background: rgba(255,255,255,0.15); transform: translateY(-1px); }
        .btn-neon { background: transparent !important; color: var(--neon-cyan) !important; border: 1px solid rgba(0, 255, 204, 0.4) !important; border-radius: 12px; }
        .btn-neon:hover { background: var(--neon-cyan) !important; color: #000 !important; border-color: var(--neon-cyan) !important; box-shadow: 0 0 20px rgba(0, 255, 204, 0.3); }
        
        /* Compatibility for legacy blade elements */
        .cyber-panel { 
            background: var(--glass-bg);
            backdrop-filter: var(--glass-blur);
            border: 1px solid var(--glass-border);
            border-radius: 18px;
            padding: 15px;
        }
        .font-orbitron { font-family: 'Outfit', sans-serif; font-weight: 800; font-size: 0.95em; }
        .slim-scroll::-webkit-scrollbar { width: 3px; }
        .slim-scroll::-webkit-scrollbar-thumb { background: rgba(0, 255, 204, 0.4); border-radius: 10px; }
    </style>
</head>
<body class="antialiased" x-data="cyberSystem()">

    <div class="ambient-blob blob-1"></div>
    <div class="ambient-blob blob-2"></div>

    <div class="mobile-header">
        <button @click="sidebarOpen = !sidebarOpen" class="text-white text-2xl hover:text-cyan-400 transition-colors">
            <i class="fa-solid fa-bars"></i>
        </button>
        <div class="brand flex flex-col items-center" style="margin-bottom:0;">
            @if($companyLogo)
                <img src="{{ $companyLogo }}" alt="Logo" class="h-6 object-contain">
            @endif
            <span style="font-size: 14px;">{{ $companyName }}</span>
        </div>
        <div class="w-6"></div> <!-- Spacer for centering -->
    </div>

    <!-- Backdrop for mobile sidebar -->
    <div x-show="sidebarOpen" @click="sidebarOpen = false" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 lg:hidden" x-transition.opacity style="display: none;"></div>

    <div class="dynamic-island" id="dynamicIsland" style="display: flex; gap: 10px; align-items: center; z-index: 100;">
        <div @click="simulateAIAction()" style="cursor: pointer; display: flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-sparkles island-icon"></i>
            <span class="island-content" id="islandText">Obsidian OS v1</span>
        </div>
        <div class="border-l border-white/20 h-4 mx-2"></div>
        <form action="{{ route('locale.change') }}" method="POST" class="m-0">
            @csrf
            <select name="locale" onchange="this.form.submit()" class="bg-transparent text-xs text-white/70 outline-none cursor-pointer hover:text-white uppercase font-bold tracking-widest">
                <option value="uz" {{ app()->getLocale() == 'uz' ? 'selected' : '' }} class="bg-black text-white">UZ</option>
                <option value="en" {{ app()->getLocale() == 'en' ? 'selected' : '' }} class="bg-black text-white">EN</option>
                <option value="ru" {{ app()->getLocale() == 'ru' ? 'selected' : '' }} class="bg-black text-white">RU</option>
            </select>
        </form>
    </div>

    <!-- Sidebar Menu -->
    <aside class="sidebar glass-panel" :class="{'mobile-open': sidebarOpen}">
        <div class="brand hidden lg:flex flex-col items-center justify-center gap-2 mt-4 mb-6">
            @if($companyLogo)
                <img src="{{ $companyLogo }}" alt="Logo" class="h-10 object-contain">
            @endif
            <span class="text-sm font-bold tracking-widest">{{ $companyName }}</span>
        </div>
        <button @click="sidebarOpen = false" class="lg:hidden absolute top-4 right-4 text-white/50 hover:text-white text-xl">
            <i class="fa-solid fa-xmark"></i>
        </button>
        <div class="mt-8 lg:mt-0 flex flex-col flex-1 overflow-y-auto slim-scroll">
            @if(session()->has('impersonate_by'))
                <div class="mb-6 p-4 bg-red-500/10 border border-red-500/30 rounded-2xl">
                    <div class="text-[10px] font-bold text-red-400 uppercase tracking-widest mb-2">KUZATUV REJIMI</div>
                    <form action="{{ route('leave.impersonate') }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full py-2 bg-red-500 text-white font-black text-[10px] uppercase tracking-tighter hover:bg-red-600 transition-all rounded shadow-[0_0_15px_rgba(239,68,68,0.3)]">
                            ADMIN REJIMIGA QAYTISH
                        </button>
                    </form>
                </div>
            @endif
            @yield('sidebar')
            
            <form method="POST" action="{{ route('logout') }}" id="logout-form" class="hidden">@csrf</form>
            <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="nav-item mt-auto" style="color: var(--neon-pink);">
                <i class="fa-solid fa-power-off"></i> Chiqish
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-container">
        @yield('content')
    </main>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('cyberSystem', () => ({
                isIslandActive: false,
                sidebarOpen: false,
                init() {
                    console.log('Obsidian OS v1 Glassmorphism Loaded');
                },
                simulateAIAction() {
                    const text = document.getElementById('islandText');
                    const island = document.getElementById('dynamicIsland');
                    
                    if (!island || !text) return;

                    island.classList.add('active');
                    text.innerHTML = "Agent Gemini: Ma'lumotlarni tahlil qilmoqda...";
                    text.style.color = "var(--neon-cyan)";
                    
                    setTimeout(() => {
                        text.innerHTML = "Tizim holati: Optimal";
                        text.style.color = "var(--neon-purple)";
                        
                        setTimeout(() => {
                            island.classList.remove('active');
                            text.innerHTML = "Obsidian OS v1";
                            text.style.color = "var(--text-main)";
                        }, 2000);
                    }, 3000);
                }
            }));
        });
    </script>
</body>
</html>
