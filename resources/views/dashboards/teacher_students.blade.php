<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>O'quvchilarim | Obsidian OS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="font-sans antialiased bg-[#0b0c10]">
<div class="h-screen flex flex-col md:flex-row bg-[#0b0c10] text-[#c5c6c7] font-sans selection:bg-[#66fcf1] selection:text-[#0b0c10] overflow-hidden">
    
    <!-- Sidebar -->
    <aside class="w-full md:w-64 bg-[#1f2833] shadow-2xl flex flex-col transition-all duration-300 z-20 cyber-border border-r border-[#66fcf1]/20">
        <div class="p-6 flex items-center justify-center border-b border-[#66fcf1]/10 bg-gradient-to-r from-[#1f2833] to-[#0b0c10]">
            <h1 class="text-2xl font-black text-transparent bg-clip-text bg-gradient-to-r from-[#66fcf1] to-[#45a29e] tracking-wider drop-shadow-[0_0_10px_rgba(102,252,241,0.5)]">OBSIDIAN</h1>
        </div>
        
        <div class="flex-1 overflow-y-auto cyber-scrollbar p-4 space-y-2">
            <a href="{{ route('teacher.dashboard') }}" class="flex items-center space-x-3 w-full p-3 rounded-lg text-gray-400 hover:text-[#45a29e] hover:bg-[#66fcf1]/5 transition-all group">
                <svg class="w-5 h-5 group-hover:drop-shadow-[0_0_5px_rgba(69,162,158,0.8)]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                <span class="font-semibold transition-colors group-hover:text-[#45a29e]">Bosh Sahifa</span>
            </a>
            
            <a href="{{ route('teacher.students') }}" class="flex items-center space-x-3 w-full p-3 rounded-lg bg-gradient-to-r from-[#66fcf1]/20 to-transparent text-[#66fcf1] border-l-2 border-[#66fcf1] transition-all">
                <svg class="w-5 h-5 drop-shadow-[0_0_5px_rgba(102,252,241,0.8)]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                <span class="font-bold tracking-wide">O'quvchilarim</span>
            </a>
        </div>
        
        <div class="p-4 border-t border-[#66fcf1]/10 bg-[#0b0c10]/50 backdrop-blur-sm">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="w-full py-2 bg-red-500/10 hover:bg-red-500/20 text-red-400 hover:text-red-300 rounded-lg transition-all font-semibold flex items-center justify-center space-x-2 border border-red-500/20 hover:border-red-500/40">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    <span>Chiqish</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col h-screen overflow-hidden relative">
        <header class="h-16 flex items-center justify-between px-6 bg-[#1f2833]/80 backdrop-blur-md border-b border-[#66fcf1]/10 z-10 sticky top-0">
            <div class="flex items-center gap-4">
                <button onclick="window.history.back()" class="w-8 h-8 rounded-full bg-white/5 border border-white/10 flex items-center justify-center text-white/50 hover:text-[#66fcf1] hover:border-[#66fcf1]/50 transition-all shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                </button>
                <h2 class="text-xl font-bold text-white uppercase tracking-widest">O'quvchilar Ro'yxati</h2>
            </div>
        </header>

        <div class="flex-1 overflow-y-auto cyber-scrollbar p-6 z-10">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @foreach($groups as $group)
                <div class="bg-[#1f2833] rounded-xl border border-[#66fcf1]/20 overflow-hidden shadow-2xl">
                    <div class="p-4 bg-[#0b0c10] border-b border-[#66fcf1]/10 flex justify-between items-center">
                        <div>
                            <span class="text-xs text-[#66fcf1] font-mono tracking-widest uppercase">Guruh:</span>
                            <h3 class="text-lg font-black text-white">{{ $group->name }}</h3>
                        </div>
                        <div class="text-[10px] bg-[#66fcf1]/10 text-[#66fcf1] px-2 py-1 rounded border border-[#66fcf1]/20">
                            {{ $group->students->count() }} o'quvchi
                        </div>
                    </div>
                    <div class="p-4">
                        <div class="space-y-3">
                            @foreach($group->students as $student)
                            <div class="flex items-center justify-between p-3 bg-[#0b0c10]/30 rounded-lg border border-white/5 hover:border-[#66fcf1]/20 transition-all">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-[#66fcf1] to-[#45a29e] flex items-center justify-center text-[#0b0c10] font-bold text-xs">
                                        {{ substr($student->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="text-sm font-bold text-white">{{ $student->name }}</div>
                                        <div class="text-[10px] text-gray-400 font-mono">{{ $student->phone }}</div>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <span class="text-[10px] {{ $student->status === 'active' ? 'text-green-400' : 'text-red-400' }} uppercase font-bold">{{ $student->status }}</span>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </main>
</div>
</body>
</html>
