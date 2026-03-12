<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ theme: localStorage.getItem('obsidian_theme') || 'dark' }" :class="theme">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Obsidian OS') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&family=Share+Tech+Mono&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        <style>
            :root {
                --electric-blue: #00f0ff;
                --cyber-yellow: #fcee0a;
                --coal-black: #0d0d0d;
                --bg-color: var(--coal-black);
                --text-color: var(--electric-blue);
                --panel-bg: rgba(13, 13, 13, 0.85);
                --grid-color: rgba(20, 20, 20, 0.5);
                --border-color: rgba(0, 240, 255, 0.4);
            }
            .light {
                --bg-color: #f0f4f8;
                --text-color: #007acc;
                --panel-bg: rgba(255, 255, 255, 0.85);
                --grid-color: rgba(200, 200, 200, 0.5);
                --border-color: rgba(0, 122, 204, 0.4);
                --electric-blue: #007acc; /* Darker blue for visibility */
                --cyber-yellow: #e6a100; /* Darker yellow for visibility */
            }

            body {
                background-color: var(--bg-color);
                color: var(--text-color);
                transition: background-color 0.3s, color 0.3s;
                font-family: 'Share Tech Mono', monospace;
            }

            /* Minimalist Grid Background */
            .fixed-bg {
                background-image: 
                    linear-gradient(var(--panel-bg), var(--panel-bg)),
                    repeating-linear-gradient(0deg, transparent, transparent 2px, var(--grid-color) 2px, var(--grid-color) 4px);
                background-size: cover, cover;
                background-attachment: fixed;
            }

            .cyber-glitch {
                text-shadow: 2px 2px var(--cyber-yellow), -2px -2px var(--electric-blue);
                animation: glitch 3s infinite;
            }
            
            @keyframes glitch {
                0% { transform: translate(0) }
                20% { transform: translate(-1px, 1px) }
                40% { transform: translate(-1px, -1px) }
                60% { transform: translate(1px, 1px) }
                80% { transform: translate(1px, -1px) }
                100% { transform: translate(0) }
            }

            .cyber-panel {
                border: 1px solid var(--border-color);
                box-shadow: 0 0 15px rgba(0, 240, 255, 0.2), inset 0 0 20px rgba(0, 240, 255, 0.05);
                background: var(--panel-bg);
                backdrop-filter: blur(12px);
            }
            
            .cyber-input {
                background: rgba(0, 240, 255, 0.05);
                border: 1px solid rgba(0, 240, 255, 0.3);
                color: var(--electric-blue);
                transition: all 0.3s ease;
            }
            .cyber-input:focus {
                outline: none;
                border-color: var(--electric-blue);
                box-shadow: 0 0 10px rgba(0, 240, 255, 0.5);
            }
            
            ::-webkit-scrollbar { width: 4px; }
            ::-webkit-scrollbar-track { background: transparent; }
            ::-webkit-scrollbar-thumb { background: var(--electric-blue); }
            
            .theme-toggle {
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 100;
                cursor: pointer;
                border: 1px solid var(--electric-blue);
                padding: 5px 10px;
                font-size: 12px;
                text-transform: uppercase;
                background: var(--panel-bg);
                color: var(--electric-blue);
            }
        </style>
    </head>
    <body class="antialiased overflow-hidden fixed-bg" x-init="$watch('theme', val => localStorage.setItem('obsidian_theme', val))">
        
        <button @click="theme = theme === 'dark' ? 'light' : 'dark'" class="theme-toggle hover:bg-[var(--electric-blue)] hover:text-white transition-colors">
            <span x-text="theme === 'dark' ? 'SOLAR MODE' : 'NEON MODE'"></span>
        </button>

        <div class="relative z-10 min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0">
            <div>
                <a href="/" class="flex flex-col items-center">
                    <h1 class="text-5xl font-orbitron font-extrabold tracking-widest text-transparent bg-clip-text bg-gradient-to-r from-[var(--electric-blue)] to-[var(--cyber-yellow)] cyber-glitch">OBSIDIAN OS</h1>
                    <p class="text-[var(--electric-blue)] font-mono mt-2 tracking-widest text-sm uppercase opacity-70">Neural Link Gateway</p>
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-8 px-8 py-8 cyber-panel sm:rounded-sm border-l-4 border-[var(--electric-blue)] relative shadow-[0_0_20px_rgba(0,240,255,0.15)]">
                <!-- Decorative Corners -->
                <div class="absolute top-0 left-0 w-2 h-2 border-t border-l border-[var(--electric-blue)]"></div>
                <div class="absolute top-0 right-0 w-2 h-2 border-t border-r border-[var(--electric-blue)]"></div>
                <div class="absolute bottom-0 left-0 w-2 h-2 border-b border-l border-[var(--electric-blue)]"></div>
                <div class="absolute bottom-0 right-0 w-2 h-2 border-b border-r border-[var(--electric-blue)]"></div>

                {{ $slot }}
            </div>
        </div>
    </body>
</html>
