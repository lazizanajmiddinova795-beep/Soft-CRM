@extends('layouts.cyber')

@section('sidebar')
    @include('partials.admin_sidebar')
@endsection

@section('content')
<div x-data="{ 
    activeTab: 'roster', 
    showAddModal: false, 
    showEditModal: false, 
    showBalanceModal: false,
    balanceUser: { id: null, name: '', balance: 0, debt: 0 },
    editUser: { id: null, name: '', email: '', role: '', pin_code: '', work_start_time: '', work_end_time: '', allowed_ip: '' },
    printBadge(user) {
        let win = window.open('', '_blank', 'width=400,height=600,noopener,noreferrer');
        win.document.write(`
            <html>
            <head>
                <title>Badge Print - ${user.name}</title>
                <link href='https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&family=JetBrains+Mono:wght@400;700&display=swap' rel='stylesheet'>
                <style>
                    body { background: white; color: black; font-family: 'JetBrains Mono', monospace; padding: 20px; text-align: center; }
                    .badge { border: 2px solid black; padding: 20px; border-radius: 10px; position: relative; width: 300px; margin: 0 auto; }
                    .header { font-family: 'Orbitron', sans-serif; font-weight: bold; font-size: 18px; margin-bottom: 20px; border-bottom: 2px solid black; padding-bottom: 5px; }
                    .avatar { width: 100px; height: 100px; border: 1px solid black; margin: 0 auto 10px; display: block; object-fit: cover; }
                    .name { font-size: 20px; font-weight: bold; text-transform: uppercase; margin: 10px 0; }
                    .role { font-size: 14px; font-weight: bold; color: #555; border: 1px solid #555; display: inline-block; padding: 2px 10px; margin-bottom: 10px; }
                    .details { font-size: 10px; text-align: left; background: #f0f0f0; padding: 10px; border-radius: 5px; line-height: 1.6; }
                    .details b { color: #000; }
                    .qr { margin-top: 20px; }
                    @media print { .no-print { display: none; } }
                </style>
            </head>
            <body>
                <div class='badge'>
                    <div class='header'>DELTA SYSTEM ID</div>
                    <img src='${user.avatar ? (user.avatar.startsWith('/') ? user.avatar : '/' + user.avatar) : 'https://via.placeholder.com/100'}' class='avatar'>
                    <div class='name'>${user.name}</div>
                    <div class='role'>${user.role.toUpperCase()}</div>
                    <div class='details'>
                        <div><b>SYSTEM-ID:</b> ${user.internal_id || 'N/A'}</div>
                        <div><b>LOGIN:</b> ${user.email}</div>
                        <div><b>PIN-CODE:</b> ${user.pin_code || '****'}</div>
                        <div><b>STATUS:</b> NEURAL LINK ACTIVE</div>
                        <div><b>Ish Vaqti:</b> ${user.work_start_time || '--'} - ${user.work_end_time || '--'}</div>
                    </div>
                    <img src='https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=${user.internal_id}:${user.pin_code}' class='qr'>
                </div>
                <button onclick='window.print()' class='no-print' style='margin-top: 20px; padding: 10px 20px; cursor: pointer; background: #000; color: #fff; border: none; font-weight: bold;'>CHOP ETISH</button>
            </body>
            </html>
        `);
        win.document.close();
    }
}" class="flex flex-col h-full">

    <!-- Header Section -->
    <div class="mb-4 md:mb-6 flex flex-col md:flex-row justify-between items-start md:items-center border-b border-[var(--active-color)] pb-4 gap-4">
        <div class="w-full">
            <h1 class="text-xl md:text-3xl font-orbitron font-bold tracking-widest text-[var(--active-color)] uppercase">{{ __('messages.staff_registry_title') }}</h1>
            <div class="flex flex-wrap gap-2 md:gap-4 mt-2">
                <button @click="activeTab = 'roster'" :class="activeTab === 'roster' ? 'text-[var(--active-color)] border-b-2 border-[var(--active-color)] shadow-[0_4px_10px_-5px_var(--active-color)]' : 'text-gray-500 hover:text-gray-300'" class="flex items-center gap-2 font-mono text-sm md:text-sm uppercase tracking-widest pb-1 transition-all group">
                    <svg class="w-4 h-4 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    <span>Xodimlar</span>
                </button>
                <button @click="activeTab = 'finances'" :class="activeTab === 'finances' ? 'text-[var(--active-color)] border-b-2 border-[var(--active-color)] shadow-[0_4px_10px_-5px_var(--active-color)]' : 'text-gray-500 hover:text-gray-300'" class="flex items-center gap-2 font-mono text-sm md:text-sm uppercase tracking-widest pb-1 transition-all group">
                    <svg class="w-4 h-4 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                    <span>Balans</span>
                </button>
                <button @click="activeTab = 'badges'" :class="activeTab === 'badges' ? 'text-[var(--active-color)] border-b-2 border-[var(--active-color)] shadow-[0_4px_10px_-5px_var(--active-color)]' : 'text-gray-500 hover:text-gray-300'" class="flex items-center gap-2 font-mono text-sm md:text-sm uppercase tracking-widest pb-1 transition-all group">
                    <svg class="w-4 h-4 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.333 0 4 1 4 3"></path></svg>
                    <span>Bedjik</span>
                </button>
                <button @click="activeTab = 'activity'" :class="activeTab === 'activity' ? 'text-[var(--active-color)] border-b-2 border-[var(--active-color)] shadow-[0_4px_10px_-5px_var(--active-color)]' : 'text-gray-500 hover:text-gray-300'" class="flex items-center gap-2 font-mono text-sm md:text-sm uppercase tracking-widest pb-1 transition-all group">
                    <svg class="w-4 h-4 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                    <span>Ish Faoliyati</span>
                </button>
            </div>
        </div>
        <button @click="showAddModal = true" class="fixed bottom-6 right-6 md:static w-12 h-12 bg-[var(--active-color)] text-[var(--bg-color)] rounded-full flex items-center justify-center hover:scale-110 transition-transform shadow-[0_0_15px_var(--active-color)] z-50">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
        </button>
    </div>

    @if (session('success'))
        <div class="p-4 border border-[var(--active-color)] bg-black text-[var(--active-color)] font-mono mb-6 uppercase tracking-widest text-sm shadow-[0_0_10px_var(--active-color)]">
            > {{ session('success') }}
        </div>
    @endif

    <!-- Content Sections -->
    <div class="flex-1 overflow-auto slim-scroll">
        
        <!-- TAB 1: ROSTER -->
        <div x-show="activeTab === 'roster'" class="space-y-4">
            <div class="cyber-panel p-2 md:p-4 overflow-x-auto overflow-y-hidden slim-scroll">
                <table class="w-full text-left font-mono text-xs min-w-[600px]">
                    <thead>
                        <tr class="border-b border-gray-800 text-[var(--active-color)] opacity-70 uppercase tracking-widest">
                            <th class="pb-2">Avatar</th>
                            <th class="pb-2">F.I.O / Identity</th>
                            <th class="pb-2">Roli</th>
                            <th class="pb-2">Holati & IP</th>
                            <th class="pb-2 text-right">Amallar</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-900">
                        @foreach($users as $user)
                        <tr class="hover:bg-gray-500 hover:bg-opacity-5 transition-colors group">
                            <td class="py-3">
                                <div class="relative w-10 h-10 border border-gray-800 group-hover:border-[var(--active-color)] flex items-center justify-center overflow-hidden">
                                    @if($user->avatar)
                                        <img src="{{ asset($user->avatar) }}" class="w-full h-full object-cover">
                                    @else
                                        <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                    @endif
                                    <div class="absolute bottom-0 right-0 w-2 h-2 {{ $user->status == 'online' ? 'bg-green-500' : 'bg-red-500' }}"></div>
                                </div>
                            </td>
                            <td class="py-3">
                                <div class="font-bold text-white">{{ $user->name }}</div>
                                <div class="text-xs opacity-40 uppercase">{{ $user->internal_id }}</div>
                            </td>
                            <td class="py-3">
                                @php
                                    $roleColor = match($user->role) {
                                        'admin' => 'bg-[var(--active-color)] text-black',
                                        'cashier' => 'bg-[var(--cyber-yellow)] text-black',
                                        'operator' => 'bg-purple-500 text-white',
                                        'developer' => 'bg-green-500 text-black',
                                        default => 'bg-gray-700 text-white'
                                    };
                                @endphp
                                <span class="{{ $roleColor }} px-2 py-0.5 rounded-sm text-sm uppercase font-bold shadow-[0_0_5px_currentColor]">{{ $user->role }}</span>
                            </td>
                            <td class="py-3">
                                <div class="text-sm {{ $user->status == 'online' ? 'text-green-400' : 'text-gray-500' }} uppercase">{{ $user->status }}</div>
                                <div class="text-xs opacity-40">IP: {{ $user->allowed_ip ?? 'CHECK DISABLED' }}</div>
                            </td>
                            <td class="py-3 text-right flex justify-end gap-1">
                                <button @click="editUser = {
                                    id: {{ $user->id }},
                                    name: '{{ addslashes($user->name) }}',
                                    email: '{{ addslashes($user->email) }}',
                                    role: '{{ $user->role }}',
                                    pin_code: '{{ $user->pin_code }}',
                                    work_start_time: '{{ substr($user->work_start_time, 0, 5) }}',
                                    work_end_time: '{{ substr($user->work_end_time, 0, 5) }}',
                                    allowed_ip: '{{ $user->allowed_ip }}'
                                }; showEditModal = true;" class="text-sm bg-gray-800 hover:bg-[var(--active-color)] hover:text-black px-2 py-1 font-bold uppercase tracking-widest transition-all">Tahrirlash</button>
                                
                                @if($user->role !== 'admin' && $user->id !== auth()->id())
                                    <form method="POST" action="{{ route('admin.staff.impersonate', $user->id) }}">
                                        @csrf
                                        <button type="submit" class="text-sm bg-[var(--electric-blue)] hover:bg-[var(--active-color)] text-black px-2 py-1 font-bold uppercase tracking-widest transition-all">Kuzatish/Kirish</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.staff.toggleBlock', $user->id) }}">
                                        @csrf
                                        <button type="submit" class="text-sm {{ $user->status === 'blocked' ? 'bg-red-900 border border-red-500 hover:bg-green-600' : 'bg-red-600 hover:bg-red-800' }} text-white px-2 py-1 font-bold uppercase tracking-widest transition-all">
                                            {{ $user->status === 'blocked' ? 'Blokdan Olish' : 'Bloklash' }}
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- TAB 2: FINANCES -->
        <div x-show="activeTab === 'finances'" class="space-y-4" style="display: none;">
            <div class="cyber-panel p-2 md:p-4 overflow-x-auto overflow-y-hidden slim-scroll">
                <table class="w-full text-left font-mono text-xs min-w-[700px]">
                    <thead>
                        <tr class="border-b border-gray-800 text-[var(--cyber-yellow)] opacity-70 uppercase tracking-widest">
                            <th class="pb-2">Xodim</th>
                            <th class="pb-2">Asosiy Maosh</th>
                            <th class="pb-2 text-center">Bonus / XP</th>
                            <th class="pb-2 text-right">Joriy Balans</th>
                            <th class="pb-2 text-right text-red-500">Qarz (Loan)</th>
                            <th class="pb-2 text-right">Payroll Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-900">
                        @foreach($users as $user)
                        <tr class="hover:bg-gray-500 hover:bg-opacity-5 transition-colors">
                            <td class="py-4">
                                <div class="font-bold text-white">{{ $user->name }}</div>
                                <div class="text-xs opacity-40 uppercase">{{ $user->role }}</div>
                            </td>
                            <td class="py-4 font-bold text-[var(--active-color)]">
                                {{ number_format($user->salary, 0, ',', ' ') }} <span class="text-sm opacity-50">UZS</span>
                            </td>
                            <td class="py-4 text-center">
                                <div class="text-green-500">+{{ number_format($user->bonus, 0) }}</div>
                                <div class="text-blue-500">XP: {{ $user->xp }}</div>
                            </td>
                            <td class="py-4 text-right font-bold text-[var(--cyber-yellow)]">
                                {{ number_format($user->balance, 0, ',', ' ') }} <span class="text-sm opacity-50">UZS</span>
                                <button type="button" @click="balanceUser = {
                                    id: {{ $user->id }},
                                    name: '{{ addslashes($user->name) }}',
                                    balance: {{ $user->balance }},
                                    debt: {{ $user->debt }}
                                }; showBalanceModal = true;" class="ml-2 px-2 py-0.5 bg-[var(--cyber-yellow)] text-black text-xs uppercase tracking-widest hover:bg-black hover:text-[var(--cyber-yellow)] border border-[var(--cyber-yellow)] transition-colors"><svg class="w-3 h-3 inline pb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg></button>
                            </td>
                            <td class="py-4 text-right font-bold text-red-500">
                                {{ number_format($user->debt, 0, ',', ' ') }} <span class="text-sm opacity-50 text-white">UZS</span>
                            </td>
                            <td class="py-4 text-right">
                                <form method="POST" action="{{ route('admin.staff.payroll', $user->id) }}" class="flex justify-end gap-1 items-center">
                                    @csrf
                                    <select name="payout_type" class="bg-black border border-gray-700 text-white text-xs p-1 outline-none focus:border-[var(--active-color)]">
                                        <option value="balance">Balansdan</option>
                                        <option value="loan">Qarz qilib</option>
                                    </select>
                                    <input type="number" name="deduct_balance" placeholder="Summa" class="w-16 md:w-20 bg-black border border-gray-700 text-white text-sm p-1 text-right focus:border-[var(--active-color)] outline-none">
                                    <button type="submit" class="px-2 py-1 bg-[var(--active-color)] text-black font-bold text-sm uppercase hover:bg-transparent hover:text-[var(--active-color)] border border-transparent hover:border-[var(--active-color)] transition-all">To'lash</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- TAB 3: BADGES -->
        <div x-show="activeTab === 'badges'" class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-6" style="display: none;">
            @foreach($users as $user)
            <div class="cyber-panel p-6 flex flex-col items-center group relative overflow-hidden bg-black bg-opacity-40">
                <div class="absolute top-0 right-0 p-2 opacity-20 group-hover:opacity-100 transition-opacity">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=50x50&data={{ $user->internal_id }}:{{ $user->pin_code }}" class="bg-white p-0.5 w-12 h-12">
                </div>
                
                <div class="w-24 h-24 border-2 border-gray-800 group-hover:border-[var(--active-color)] transition-colors mb-4 p-1">
                    @if($user->avatar)
                        <img src="{{ asset($user->avatar) }}" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full flex items-center justify-center bg-gray-900">
                             <svg class="w-12 h-12 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        </div>
                    @endif
                </div>
                
                <h3 class="font-bold text-white text-lg uppercase tracking-widest text-center">{{ $user->name }}</h3>
                <p class="text-[var(--active-color)] font-mono text-sm border border-[var(--active-color)] px-2 mt-1 uppercase">{{ $user->role }}</p>
                
                <div class="mt-4 w-full grid grid-cols-1 gap-2 text-xs font-mono text-gray-400">
                    <div class="bg-gray-900 p-2 border border-gray-800 flex justify-between">
                        <span class="opacity-50 uppercase">LOGIN:</span>
                        <span class="text-white">{{ $user->email }}</span>
                    </div>
                    <div class="bg-gray-900 p-2 border border-gray-800 flex justify-between">
                        <span class="opacity-50 uppercase">PIN CRYPT:</span>
                        <span class="text-[var(--cyber-yellow)] font-bold">{{ $user->pin_code }}</span>
                    </div>
                    <div class="bg-gray-900 p-2 border border-gray-800 flex justify-between">
                        <span class="opacity-50 uppercase">SHEDULE:</span>
                        <span class="text-white">{{ substr($user->work_start_time, 0, 5) }} - {{ substr($user->work_end_time, 0, 5) }}</span>
                    </div>
                </div>

                <button @click="printBadge({{ json_encode($user) }})" class="mt-6 w-full py-2 bg-transparent border border-gray-700 hover:border-[var(--active-color)] hover:text-[var(--active-color)] text-sm font-bold uppercase transition-all tracking-widest">
                    BEDJIKNI CHOP ETISH
                </button>
            </div>
            @endforeach
        </div>

        <!-- TAB 4: ACTIVITY LOGS -->
        <div x-show="activeTab === 'activity'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4" style="display: none;">
            @foreach($users as $user)
            @php
                $monthlyWorkSeconds = 0;
                $currentMonthShifts = $user->shifts->filter(fn($s) => $s->started_at >= now()->startOfMonth());
                foreach($currentMonthShifts as $s) {
                    $sTotal = $s->ended_at ? $s->ended_at->diffInSeconds($s->started_at) : ($s->status=='active'?now()->diffInSeconds($s->started_at):0);
                    $sPause = 0;
                    foreach($s->pauses as $p) {
                        if($p->resumed_at) $sPause += $p->resumed_at->diffInSeconds($p->paused_at);
                    }
                    $monthlyWorkSeconds += max(0, $sTotal - $sPause);
                }

                $last7Days = collect();
                for($i=6; $i>=0; $i--) {
                    $date = now()->subDays($i);
                    $dayShifts = $user->shifts->filter(fn($s) => $s->started_at->format('Y-m-d') == $date->format('Y-m-d'));
                    
                    $dayWork = 0;
                    $dayPause = 0;
                    $activePeriods = [];
                    foreach($dayShifts as $ds) {
                        $dt = $ds->ended_at ? $ds->ended_at->diffInSeconds($ds->started_at) : ($ds->status=='active'?now()->diffInSeconds($ds->started_at):0);
                        $dp = 0;
                        foreach($ds->pauses as $dp_item) if($dp_item->resumed_at) $dp += $dp_item->resumed_at->diffInSeconds($dp_item->paused_at);
                        $dayWork += ($dt - $dp);
                        $dayPause += $dp;

                        $start = $ds->started_at->format('H:i');
                        $end = $ds->ended_at ? $ds->ended_at->format('H:i') : 'Hozir';
                        $activePeriods[] = "$start - $end";
                    }
                    $last7Days->push([
                        'date' => $date->format('d.m.Y'), 
                        'dayName' => $date->format('D'),
                        'work' => $dayWork, 
                        'pause' => $dayPause,
                        'periods' => implode(', ', $activePeriods)
                    ]);
                }
            @endphp
            <div class="cyber-panel p-4 flex flex-col bg-black bg-opacity-40 border border-gray-800 hover:border-[var(--active-color)] transition-all">
                <div class="flex justify-between items-center border-b border-gray-800 pb-3 mb-3 gap-3">
                    <img src="{{ asset($user->avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode($user->name)) }}" class="w-10 h-10 border border-[var(--active-color)] object-cover grayscale hover:grayscale-0 transition-all">
                    <div class="flex-1">
                        <h3 class="text-sm font-orbitron font-bold text-white uppercase truncate">{{ $user->name }}</h3>
                        <p class="text-xs text-[var(--active-color)] opacity-60 uppercase">{{ $user->role }}</p>
                    </div>
                    <div class="text-right">
                        <span class="text-xs font-mono opacity-50 block uppercase">Oy davomida</span>
                        <span class="text-sm font-bold font-orbitron text-[var(--cyber-yellow)]">{{ floor($monthlyWorkSeconds / 3600) }}s {{ floor(($monthlyWorkSeconds % 3600) / 60) }}m</span>
                    </div>
                </div>

                <div class="flex-1 overflow-y-auto slim-scroll max-h-[200px] pr-1">
                    <div class="space-y-2">
                        @foreach($last7Days->reverse() as $day)
                            <div class="bg-gray-900 border {{ $day['work'] > 0 ? 'border-gray-700' : 'border-gray-800 opacity-50' }} p-2 flex flex-col gap-1 hover:border-[var(--active-color)] transition-colors">
                                <div class="flex justify-between items-center text-sm font-mono">
                                    <span class="text-white font-bold">{{ $day['date'] }} <span class="opacity-50 text-xs ml-1">{{ $day['dayName'] }}</span></span>
                                    <span class="text-[var(--active-color)] font-bold">{{ floor($day['work']/3600) }}s {{ floor(($day['work']%3600)/60) }}m</span>
                                </div>
                                <div class="flex justify-between items-center text-xs font-mono opacity-60">
                                    <span>Vaqt: <span class="text-[var(--cyber-yellow)]">{{ $day['periods'] ?: 'Ishlamagan' }}</span></span>
                                    <span>Tanaffus: {{ floor($day['pause']/3600) }}s</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endforeach
        </div>

    </div>

    <!-- MODAL: ADD STAFF -->
    <div x-show="showAddModal" style="display: none;" class="fixed inset-0 z-[100] flex items-center justify-center bg-black bg-opacity-90 backdrop-blur-md p-4">
        <div class="cyber-panel p-4 md:p-8 w-full max-w-xl border-[var(--active-color)] bg-black relative max-h-[90vh] overflow-y-auto">
            <button @click="showAddModal = false" class="absolute top-4 right-4 text-gray-500 hover:text-white">
                 <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
            <h2 class="text-2xl font-orbitron mb-6 border-b border-[var(--active-color)] pb-2 text-[var(--active-color)] uppercase tracking-widest font-bold">Yangi Xodim Qo'shish</h2>
            
            <form method="POST" action="{{ route('admin.staff.store') }}" enctype="multipart/form-data" class="space-y-4 font-mono text-sm">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block opacity-50 mb-1">To'liq Ism (Legal Name)</label>
                        <input type="text" name="name" required class="w-full bg-black border border-gray-700 p-2 text-white focus:border-[var(--active-color)] outline-none">
                    </div>
                    <div>
                        <label class="block opacity-50 mb-1">Neural Email (Login)</label>
                        <input type="email" name="email" required class="w-full bg-black border border-gray-700 p-2 text-white focus:border-[var(--active-color)] outline-none">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block opacity-50 mb-1">Tizim Paroli</label>
                        <input type="password" name="password" required class="w-full bg-black border border-gray-700 p-2 text-white focus:border-[var(--active-color)] outline-none">
                    </div>
                    <div>
                        <label class="block opacity-50 mb-1">Xizmat Roli</label>
                        <select name="role" required class="w-full bg-black border border-gray-700 p-2 text-white focus:border-[var(--active-color)] outline-none">
                            <option value="operator">Operator</option>
                            <option value="cashier">Kassir</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block opacity-50 mb-1">Boshlang'ich Maosh</label>
                        <input type="number" name="salary" class="w-full bg-black border border-gray-700 p-2 text-white focus:border-[var(--active-color)] outline-none">
                    </div>
                    <div>
                        <label class="block opacity-50 mb-1">Maxsus PIN-KOD</label>
                        <input type="text" name="pin_code" placeholder="i.e. 7777" class="w-full bg-black border border-gray-700 p-2 text-white focus:border-[var(--active-color)] outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block opacity-50 mb-1">Ish Boshlash (Start)</label>
                        <input type="time" name="work_start_time" value="09:00" class="w-full bg-black border border-gray-700 p-2 text-white focus:border-[var(--active-color)] outline-none">
                    </div>
                    <div>
                        <label class="block opacity-50 mb-1">Ish Yakunlash (End)</label>
                        <input type="time" name="work_end_time" value="18:00" class="w-full bg-black border border-gray-700 p-2 text-white focus:border-[var(--active-color)] outline-none">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4 pt-4 border-t border-gray-800">
                    <div>
                        <label class="block opacity-50 mb-1">Avatar Rasm</label>
                        <input type="file" name="avatar" class="text-sm w-full file:bg-gray-800 file:text-white file:border-none file:px-2 file:py-1">
                    </div>
                    <div>
                         <label class="block opacity-50 mb-1">Face ID Scan</label>
                        <input type="file" name="face_id_image" class="text-sm w-full file:bg-gray-800 file:text-white file:border-none file:px-2 file:py-1">
                    </div>
                </div>
                
                <button type="submit" class="w-full mt-6 py-3 bg-[var(--active-color)] text-black font-bold uppercase tracking-widest hover:bg-transparent hover:text-[var(--active-color)] border border-transparent hover:border-[var(--active-color)] transition-all">
                    BAZAGA QO'ShISh
                </button>
            </form>
        </div>
    </div>

    <!-- MODAL: EDIT STAFF -->
    <div x-show="showEditModal" style="display: none;" class="fixed inset-0 z-[100] flex items-center justify-center bg-black bg-opacity-95 backdrop-blur-md p-4">
        <div class="cyber-panel p-4 md:p-8 w-full max-w-xl border-[var(--cyber-yellow)] bg-black relative max-h-[90vh] overflow-y-auto">
            <button @click="showEditModal = false" class="absolute top-4 right-4 text-gray-500 hover:text-white">
                 <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
            <h2 class="text-2xl font-orbitron mb-6 border-b border-[var(--cyber-yellow)] pb-2 text-[var(--cyber-yellow)] uppercase tracking-widest font-bold">Xodim Tahrirlash</h2>
            
            <form :action="'{{ url('admin/staff') }}/' + editUser?.id + '/update'" method="POST" enctype="multipart/form-data" class="space-y-4 font-mono text-sm">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block opacity-50 mb-1">F.I.O</label>
                        <input type="text" name="name" x-model="editUser.name" required class="w-full bg-black border border-gray-700 p-2 text-white focus:border-[var(--cyber-yellow)] outline-none">
                    </div>
                    <div>
                        <label class="block opacity-50 mb-1">Neural Email (Login)</label>
                        <input type="email" name="email" x-model="editUser.email" required class="w-full bg-black border border-gray-700 p-2 text-white focus:border-[var(--cyber-yellow)] outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block opacity-50 mb-1">Parolni O'zgartirish (bo'sh bo'lsa qoladi)</label>
                        <input type="password" name="password" class="w-full bg-black border border-gray-700 p-2 text-white focus:border-[var(--cyber-yellow)] outline-none">
                    </div>
                    <div>
                        <label class="block opacity-50 mb-1">Xizmat Roli</label>
                        <select name="role" x-model="editUser.role" required class="w-full bg-black border border-gray-700 p-2 text-white focus:border-[var(--cyber-yellow)] outline-none">
                            <option value="operator">Operator</option>
                            <option value="cashier">Kassir</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block opacity-50 mb-1">PIN-KOD</label>
                        <input type="text" name="pin_code" x-model="editUser.pin_code" class="w-full bg-black border border-gray-700 p-2 text-white focus:border-[var(--cyber-yellow)] outline-none">
                    </div>
                    <div>
                        <label class="block opacity-50 mb-1">Ish Boshlash</label>
                        <input type="time" name="work_start_time" x-model="editUser.work_start_time" class="w-full bg-black border border-gray-700 p-2 text-white focus:border-[var(--cyber-yellow)] outline-none">
                    </div>
                    <div>
                        <label class="block opacity-50 mb-1">Ish Yakunlash</label>
                        <input type="time" name="work_end_time" x-model="editUser.work_end_time" class="w-full bg-black border border-gray-700 p-2 text-white focus:border-[var(--cyber-yellow)] outline-none">
                    </div>
                </div>

                <div>
                    <label class="block opacity-50 mb-1">Xavfsiz Kirish: IP-adres (Ixtiyoriy)</label>
                    <input type="text" name="allowed_ip" x-model="editUser.allowed_ip" placeholder="i.e. 192.168.1.1" class="w-full bg-black border border-gray-700 p-2 text-white focus:border-[var(--cyber-yellow)] outline-none">
                </div>

                <div class="grid grid-cols-2 gap-4 pt-4 border-t border-gray-800">
                    <div>
                        <label class="block opacity-50 mb-1 text-[var(--cyber-yellow)]">Yangilash: Avatar</label>
                        <input type="file" name="avatar" class="text-sm w-full file:bg-gray-800 file:text-white file:border-none file:px-2 file:py-1">
                    </div>
                    <div>
                         <label class="block opacity-50 mb-1 text-[var(--cyber-yellow)]">Yangilash: Face ID</label>
                        <input type="file" name="face_id_image" class="text-sm w-full file:bg-gray-800 file:text-white file:border-none file:px-2 file:py-1">
                    </div>
                </div>
                <div class="grid grid-cols-1 gap-4 pt-4 border-t border-gray-800">
                    <button type="submit" class="w-full py-3 bg-[var(--cyber-yellow)] text-black font-bold uppercase tracking-widest hover:bg-transparent hover:text-[var(--cyber-yellow)] border border-transparent hover:border-[var(--cyber-yellow)] transition-all">
                      O'ZGARTIRISHLARNI SAQLASH
                    </button>
                    <button type="button" @click="if(confirm('Ushbu xodimni butkul o\'chirib tashlaysizmi?')) { document.getElementById('deleteForm').action = '{{ url('admin/staff') }}/' + editUser.id; document.getElementById('deleteForm').submit(); }" class="w-full py-2 bg-red-900 bg-opacity-30 border border-red-500 text-red-500 font-bold uppercase text-sm tracking-widest mt-2">
                        Xodimni Tizimdan O'chirish
                    </button>
                </div>
            </form>
        </div>
    </div>

    <form id="deleteForm" method="POST" class="hidden">
        @csrf
        @method('DELETE')
    </form>

    <!-- MODAL: EDIT BALANCE / DEBT -->
    <div x-show="showBalanceModal" style="display: none;" class="fixed inset-0 z-[100] flex items-center justify-center bg-black bg-opacity-95 backdrop-blur-md p-4">
        <div class="cyber-panel p-4 md:p-8 w-full max-w-xl border-[var(--cyber-yellow)] bg-black relative max-h-[90vh] overflow-y-auto shadow-[0_0_30px_var(--cyber-yellow)]">
            <button @click="showBalanceModal = false" class="absolute top-4 right-4 text-gray-500 hover:text-white">
                 <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
            <h2 class="text-xl font-orbitron mb-6 border-b border-[var(--cyber-yellow)] pb-2 text-[var(--cyber-yellow)] uppercase tracking-widest font-bold">Balans & Qarzni Tahrirlash</h2>
            
            <p class="font-mono text-xs opacity-70 mb-4 text-white">Xodim: <span class="font-bold text-[var(--cyber-yellow)]" x-text="balanceUser?.name"></span></p>

            <form :action="'{{ url('admin/staff') }}/' + balanceUser?.id + '/adjust-balance'" method="POST" class="space-y-6 font-mono text-sm">
                @csrf
                <!-- Balance Section -->
                <div class="border border-gray-800 p-4 relative">
                    <span class="absolute -top-3 left-4 bg-black px-2 text-white text-xs opacity-70">Joriy Balans: <span x-text="balanceUser?.balance + ' UZS'"></span></span>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block opacity-50 mb-1 text-xs">Amal turi</label>
                            <select name="balance_action" class="w-full bg-black border border-gray-700 p-2 text-white focus:border-[var(--active-color)] outline-none">
                                <option value="set">To'liq O'zgartirish (=)</option>
                                <option value="add">Qo'shish (+)</option>
                                <option value="subtract">Ayirish (-)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block opacity-50 mb-1 text-xs">Summa (UZS)</label>
                            <input type="number" name="balance_amount" min="0" value="0" required class="w-full bg-black border border-gray-700 p-2 text-[var(--active-color)] font-bold focus:border-[var(--active-color)] outline-none">
                        </div>
                    </div>
                </div>

                <!-- Debt Section -->
                <div class="border border-red-900 p-4 relative">
                    <span class="absolute -top-3 left-4 bg-black px-2 text-white text-xs opacity-70">Sirtqi Qarz: <span x-text="balanceUser?.debt + ' UZS'" class="text-red-500"></span></span>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block opacity-50 mb-1 text-xs text-red-500">Amal turi</label>
                            <select name="debt_action" class="w-full bg-black border border-red-900 p-2 text-white focus:border-red-500 outline-none">
                                <option value="set">To'liq O'zgartirish (=)</option>
                                <option value="add">Qo'shish (+)</option>
                                <option value="subtract">Ayirish (-)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block opacity-50 mb-1 text-xs text-red-500">Summa (UZS)</label>
                            <input type="number" name="debt_amount" min="0" value="0" required class="w-full bg-black border border-red-900 p-2 text-red-500 font-bold focus:border-red-500 outline-none">
                        </div>
                    </div>
                </div>
                
                <button type="submit" class="w-full py-3 bg-[var(--cyber-yellow)] text-black font-bold uppercase tracking-widest hover:bg-transparent hover:text-[var(--cyber-yellow)] border border-transparent hover:border-[var(--cyber-yellow)] transition-all">
                    O'ZGARISHLARNI SAQLASH
                </button>
            </form>
        </div>
    </div>

</div>
@endsection
