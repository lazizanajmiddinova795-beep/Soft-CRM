<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ITCloud > Dev Access</title>
    <!-- Fonts -->
    <link href="https://fonts.bunny.net/css?family=orbitron:400,500,700,900|share-tech-mono:400&display=swap" rel="stylesheet" />
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            font-family: 'Share Tech Mono', monospace;
            background-color: #000;
            color: #FF00FF;
            margin: 0; overflow: hidden;
            background-image: repeating-linear-gradient(0deg, transparent, transparent 2px, rgba(10, 10, 10, 0.7) 2px, rgba(10, 10, 10, 0.7) 4px);
        }
        .crt::before {
            content: " "; display: block; position: absolute; top: 0; left: 0; bottom: 0; right: 0;
            background: linear-gradient(rgba(18, 16, 16, 0) 50%, rgba(0, 0, 0, 0.25) 50%), linear-gradient(90deg, rgba(255, 0, 0, 0.06), rgba(0, 255, 0, 0.02), rgba(0, 0, 255, 0.06));
            z-index: 2; background-size: 100% 2px, 3px 100%; pointer-events: none;
        }
        .cyber-panel { background: rgba(10, 10, 10, 0.85); border: 1px solid #FF00FF; position: relative; box-shadow: 0 0 15px rgba(255, 0, 255, 0.2); }
        .cyber-panel::before { content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 2px; background: #FF00FF; box-shadow: 0 0 10px #FF00FF; }
        .glitch { animation: glitch 1s linear infinite; }
        @keyframes glitch { 2%, 64% { transform: translate(2px,0) skew(0deg); } 4%, 60% { transform: translate(-2px,0) skew(0deg); } 62% { transform: translate(0,0) skew(5deg); } }
    </style>
</head>
<body class="antialiased h-screen crt flex items-center justify-center">

<div class="z-10 absolute inset-0 opacity-10 pointer-events-none" style="background-image: linear-gradient(#FF00FF 1px, transparent 1px), linear-gradient(90deg, #FF00FF 1px, transparent 1px); background-size: 50px 50px; transform: perspective(500px) rotateX(60deg) scale(2); transform-origin: top;"></div>

<div class="relative z-20 w-full max-w-md">
    <div class="text-center mb-6">
        <h1 class="text-5xl font-orbitron font-bold tracking-widest leading-none drop-shadow-[0_0_10px_#FF00FF] glitch mb-2">
            DEV_ACCESS
        </h1>
        <p class="uppercase tracking-[0.3em] opacity-80 decoration-dashed underline text-xs">Architect Override Gateway</p>
    </div>

    <form method="POST" action="{{ route('dev.login.submit') }}" class="cyber-panel p-8">
        @csrf
        
        @if($errors->any())
            <div class="border border-red-500 bg-red-900 bg-opacity-20 text-red-500 text-xs uppercase p-3 mb-6 font-bold tracking-widest shadow-[inset_0_0_10px_red]">
                >> Authentication Failed. Unauthorized Entity.
            </div>
        @endif

        <div class="space-y-6">
            <div>
                <label class="block text-xs uppercase tracking-[0.2em] font-bold mb-2 opacity-80">Root Identity (Email)</label>
                <div class="relative">
                    <span class="absolute left-3 top-3">></span>
                    <input type="email" name="email" required autofocus autocomplete="email" class="w-full bg-black bg-opacity-80 border-b-2 border-[#FF00FF] text-[#FF00FF] p-3 pl-8 focus:outline-none focus:bg-[#FF00FF] focus:bg-opacity-10 focus:shadow-[0_4px_15px_rgba(255,0,255,0.3)] transition-all uppercase tracking-widest" placeholder="_type_dev_email">
                </div>
            </div>

            <div>
                <label class="block text-xs uppercase tracking-[0.2em] font-bold mb-2 opacity-80">RSA Key (Password)</label>
                <div class="relative">
                    <span class="absolute left-3 top-3">#</span>
                    <input type="password" name="password" required autocomplete="current-password" class="w-full bg-black bg-opacity-80 border-b-2 border-[#FF00FF] text-[#FF00FF] p-3 pl-8 focus:outline-none focus:bg-[#FF00FF] focus:bg-opacity-10 focus:shadow-[0_4px_15px_rgba(255,0,255,0.3)] transition-all tracking-widest" placeholder="••••••••••••">
                </div>
            </div>

            <button type="submit" class="w-full mt-6 py-4 bg-transparent border border-[#FF00FF] text-[#FF00FF] hover:bg-[#FF00FF] hover:text-black font-orbitron font-black text-xl tracking-[0.3em] uppercase transition-all shadow-[0_0_15px_rgba(255,0,255,0.2)] hover:shadow-[0_0_30px_rgba(255,0,255,0.8)] cursor-pointer">
                [ INITIALIZE ]
            </button>
        </div>
        
        <div class="mt-8 text-center text-[0.6rem] uppercase tracking-widest border-t border-[#FF00FF] border-opacity-30 pt-4 flex justify-between">
            <span>Powered by ITCloud</span>
            <span>V_2077.0.D</span>
        </div>
    </form>
</div>

</body>
</html>
