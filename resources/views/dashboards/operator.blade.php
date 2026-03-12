@extends('layouts.cyber')

@section('sidebar')
    @include('partials.operator_sidebar')
@endsection

@section('content')
<style>
    /* Neon Minimalism Styles */
    .glass-card {
        background: rgba(13, 13, 13, 0.6);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(0, 240, 255, 0.2);
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
    }
    .glass-card:hover, .glass-card:focus-within {
        border-color: var(--electric-blue);
        box-shadow: 0 0 15px rgba(0, 240, 255, 0.15), inset 0 0 20px rgba(0, 240, 255, 0.05);
    }
    .neon-btn-yellow {
        background: transparent;
        color: var(--cyber-yellow);
        border: 1px solid var(--cyber-yellow);
        transition: all 0.3s ease;
    }
    .neon-btn-yellow:hover {
        background: var(--cyber-yellow);
        color: var(--coal-black);
        box-shadow: 0 0 20px rgba(252, 238, 10, 0.6);
    }
    .neon-text-blue {
        color: var(--electric-blue);
        text-shadow: 0 0 10px rgba(0, 240, 255, 0.5);
    }
    .glitch-anim {
        animation: glitch 3s infinite;
    }
    @keyframes glitch {
        0% { transform: translate(0) }
        20% { transform: translate(-2px, 2px) }
        40% { transform: translate(-2px, -2px) }
        60% { transform: translate(2px, 2px) }
        80% { transform: translate(2px, -2px) }
        100% { transform: translate(0) }
    }
    .gemini-orb {
        width: 60px; height: 60px;
        border-radius: 50%;
        background: radial-gradient(circle, var(--electric-blue) 0%, transparent 70%);
        box-shadow: 0 0 30px var(--electric-blue), inset 0 0 20px #fff;
        animation: pulse-orb 2s infinite alternate;
        cursor: pointer;
    }
    @keyframes pulse-orb {
        0% { transform: scale(0.95); opacity: 0.8; box-shadow: 0 0 20px var(--electric-blue); }
        100% { transform: scale(1.05); opacity: 1; box-shadow: 0 0 50px var(--electric-blue), 0 0 10px #fff; }
    }
    .slim-scroll::-webkit-scrollbar { width: 3px; }
    .slim-scroll::-webkit-scrollbar-track { background: transparent; }
    .slim-scroll::-webkit-scrollbar-thumb { background: var(--electric-blue); }
    
    .laser-path {
        position: relative;
    }
    .laser-path::before {
        content: '';
        position: absolute;
        left: 11px;
        top: 30px;
        bottom: 0;
        width: 2px;
        background: linear-gradient(to bottom, var(--electric-blue), transparent);
        opacity: 0.5;
    }
</style>

@if (session('success'))
    <div class="fixed top-20 right-6 z-50 p-3 bg-gray-500 bg-opacity-10 border border-[var(--electric-blue)] text-[var(--electric-blue)] font-bold uppercase tracking-widest text-xs shadow-[0_0_15px_var(--electric-blue)] backdrop-blur-md">
        >> {{ session('success') }}
    </div>
@endif
@if ($errors->any())
    <div class="fixed top-20 right-6 z-50 p-3 bg-red-900 bg-opacity-80 border border-red-500 text-red-100 font-bold uppercase tracking-widest text-xs shadow-[0_0_15px_red] backdrop-blur-md">
        >> CRITICAL ERROR: {{ $errors->first() }}
    </div>
@endif

<!-- The entire view fits within the screen. overflow-hidden handles scroll prevention. -->
<div x-data="operatorDashboard()" class="flex-1 min-h-0 flex flex-col gap-4 overflow-hidden text-[var(--bg-color)] font-mono text-sm">

    <!-- HUD (Heads-Up Display) Top Bar -->
    <div class="flex flex-col md:flex-row justify-between items-center glass-card p-4 shrink-0 rounded-sm gap-4">
        <div class="flex items-center gap-4 md:gap-6 w-full md:w-auto justify-between md:justify-start">
            <template x-if="!stats.hasActiveShift">
                <button @click="initiateFaceID()" class="neon-btn-yellow px-4 md:px-6 py-2 font-bold uppercase tracking-widest flex items-center gap-2 text-xs md:text-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 008 4.07M3 15.364c.64-1.319 1-2.8 1-4.364 0-1.457.39-2.823 1.07-4"></path></svg>
                    IShNI BOShLASh
                </button>
            </template>
            <template x-if="stats.hasActiveShift">
                <div class="flex items-center gap-2 md:gap-4">
                    <div class="flex flex-col">
                        <span class="text-xs uppercase opacity-50 text-[var(--electric-blue)] tracking-[0.2em]">Ish Soati</span>
                        <span class="text-xl md:text-2xl font-bold font-orbitron neon-text-blue" x-text="stats.shiftDuration">00:00:00</span>
                    </div>

                    <div class="flex gap-1 md:gap-2">
                        <template x-if="stats.isPaused">
                            <button @click="togglePause()" class="px-2 md:px-3 py-1 text-sm md:text-xs border border-[var(--cyber-yellow)] text-[var(--cyber-yellow)] hover:bg-[var(--cyber-yellow)] hover:text-black uppercase transition-all shadow-[0_0_10px_rgba(252,238,10,0.3)]">DAVOM ETISH</button>
                        </template>
                        <template x-if="!stats.isPaused">
                            <button @click="togglePause()" class="px-2 md:px-3 py-1 text-sm md:text-xs border border-white text-white hover:bg-white hover:text-black uppercase transition-all">TANAFFUS</button>
                        </template>

                        <form method="POST" action="{{ route('shift.stop') }}">
                            @csrf
                            <button type="submit" class="px-2 md:px-3 py-1 text-sm md:text-xs border border-red-500 text-red-500 hover:bg-red-500 hover:text-black uppercase transition-all shadow-[0_0_10px_rgba(255,0,0,0.3)]">Stop</button>
                        </form>
                    </div>
                </div>
            </template>
        </div>

        <div class="flex items-center gap-4 md:gap-8 justify-center flex-1 order-last md:order-none">
            <!-- Voice AI Button -->
            <div class="flex flex-col items-center justify-center relative group block aspect-square rounded-full cursor-pointer" @click="activateVoiceAI()">
                <div class="gemini-orb w-10 h-10 md:w-[60px] md:h-[60px]"></div>
                <div class="absolute top-full mt-2 text-xs md:text-sm uppercase tracking-widest text-[var(--electric-blue)] opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap">Voice AI Faol</div>
            </div>
        </div>

        <div class="flex items-center gap-4 md:gap-6 text-right w-full md:w-auto justify-between md:justify-end border-t border-gray-800 md:border-none pt-4 md:pt-0">
            <div class="flex flex-col">
                <span class="text-xs uppercase opacity-50 tracking-[0.2em]">Balans</span>
                <span class="text-lg md:text-2xl font-bold font-orbitron text-[var(--electric-blue)]"><span x-text="stats.balance"></span> <span class="text-sm md:text-xs">UZS</span></span>
            </div>
            <div class="flex flex-col border-l border-gray-800 pl-4">
                <span class="text-xs uppercase opacity-50 tracking-[0.2em]">Navbat</span>
                <span class="text-lg md:text-2xl font-bold font-orbitron text-[var(--cyber-yellow)]" x-text="stats.contracts.filter(c => c.status=='pending').length">0</span>
            </div>
            <div class="flex flex-col border-l border-gray-800 pl-4 hidden sm:flex">
                <span class="text-xs uppercase opacity-50 tracking-[0.2em]">Oylik</span>
                <span class="text-xs md:text-sm font-bold font-orbitron text-gray-300">
                    <span x-text="stats.monthlyHours">0</span>s
                </span>
            </div>
        </div>
    </div>

    <!-- Active Shift Gate -->
    <template x-if="!stats.hasActiveShift">
        <div class="flex-1 flex flex-col items-center justify-center glass-card relative">
            <div class="absolute inset-0 bg-black bg-opacity-50"></div>
            <div class="z-10 text-center">
                <svg class="w-24 h-24 mx-auto text-gray-700 mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                <h2 class="text-3xl font-orbitron font-bold text-gray-500 tracking-[0.3em] uppercase mb-2">Tizim Qulflangan</h2>
                <p class="text-gray-600 uppercase tracking-widest text-xs">Markaziy matritsaga kirish uchun Face ID biometrik tekshiruvidan o'ting.</p>
            </div>
        </div>
    </template>

    <template x-if="stats.hasActiveShift">
        <div class="flex-1 flex flex-col min-h-0">
            <!-- TABS -->
            <div class="flex gap-6 border-b border-[var(--electric-blue)] border-opacity-30 mb-4 px-2 pt-2">
                <button @click="activeTab = 'console'; speakUzbek('Asosiy boshqaruv paneli');" :class="activeTab === 'console' ? 'text-[var(--electric-blue)] border-b-2 border-[var(--electric-blue)]' : 'text-gray-500 hover:text-gray-300'" class="font-orbitron tracking-widest uppercase text-xs font-bold pb-2 transition-colors">Boshqaruv Paneli</button>
                <button @click="activeTab = 'archive'; speakUzbek('Shartnomalar arxivi');" :class="activeTab === 'archive' ? 'text-[var(--electric-blue)] border-b-2 border-[var(--electric-blue)]' : 'text-gray-500 hover:text-gray-300'" class="font-orbitron tracking-widest uppercase text-xs font-bold pb-2 transition-colors">Shartnomalar Arxivi</button>
                <button @click="activeTab = 'settings'; speakUzbek('Tizim sozlamalari');" :class="activeTab === 'settings' ? 'text-[var(--electric-blue)] border-b-2 border-[var(--electric-blue)]' : 'text-gray-500 hover:text-gray-300'" class="font-orbitron tracking-widest uppercase text-xs font-bold pb-2 transition-colors">Sozlamalar</button>
            </div>

            <!-- MAIN CONSOLE TAB -->
            <div x-show="activeTab === 'console'" class="flex-1 flex gap-4 min-h-0">
                
                <!-- Left Panel: Client Matrix & Deal Generation -->
                <div class="w-1/2 flex flex-col gap-4">
                    <div class="glass-card flex-1 p-5 rounded-sm flex flex-col min-h-0">
                        <div class="flex justify-between items-center mb-4 border-b border-[var(--electric-blue)] border-opacity-30 pb-2">
                            <h2 class="font-orbitron tracking-widest text-[var(--electric-blue)] uppercase text-sm">Xizmat Kiritish (Service Core)</h2>
                            <span class="w-2 h-2 bg-[var(--electric-blue)] shrink-0 rounded-full animate-pulse shadow-[0_0_8px_var(--electric-blue)]"></span>
                        </div>

                    <form id="contractForm" @submit.prevent="submitContract" enctype="multipart/form-data" class="flex flex-col h-full gap-4 slim-scroll overflow-y-auto pr-2">
                        @csrf
                        
                        <!-- Client Search / Create Wrapper -->
                        <div x-data="{
                            searchQuery: '', clients: [], showDropdown: false, clientName: '', clientPhone: '', clientAddress: '', clientId: '',
                            searchClient() {
                                if(this.searchQuery.length < 2) { this.clients = []; this.showDropdown = false; return; }
                                fetch('{{ route('operator.clients.search') }}?q=' + this.searchQuery, {
                                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                                })
                                    .then(res => {
                                        if (res.status === 401) { window.location.href = '/'; return []; }
                                        return res.json()
                                    })
                                    .then(data => { this.clients = data; this.showDropdown = data.length > 0; });
                            },
                            selectClient(c) {
                                this.clientName = c.name; this.clientPhone = c.phone; this.clientAddress = c.address; this.clientId = c.id;
                                this.showDropdown = false; this.searchQuery = '';
                            }
                        }" class="bg-black bg-opacity-40 border border-gray-800 p-3 rounded-sm space-y-3 relative focus-within:border-[var(--electric-blue)] transition-colors">
                            <input type="hidden" name="client_id" :value="clientId">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-500 uppercase tracking-widest">Mijoz Ma'lumotlari</span>
                                <input type="text" x-model="searchQuery" @input.debounce.500ms="searchClient" placeholder="Bazada qidirish..." class="w-1/2 bg-transparent border-b border-gray-700 text-[var(--electric-blue)] placeholder-gray-700 text-xs px-1 py-0.5 outline-none focus:border-[var(--electric-blue)]">
                                
                                <div x-show="showDropdown" @click.away="showDropdown = false" class="absolute top-8 right-3 z-50 w-1/2 bg-[var(--coal-black)] border border-[var(--electric-blue)] shadow-[0_4px_15px_rgba(0,240,255,0.2)] max-h-40 overflow-y-auto slim-scroll" style="display: none;">
                                    <template x-for="c in clients" :key="c.id">
                                        <div @click="selectClient(c)" class="p-2 border-b border-gray-800 hover:bg-gray-500 hover:bg-opacity-10 cursor-pointer text-sm text-gray-300">
                                            <div class="font-bold text-[var(--electric-blue)]" x-text="c.name"></div>
                                            <div x-text="c.phone"></div>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-3 text-xs">
                                <div>
                                    <input type="text" name="client_name" x-model="clientName" class="w-full bg-transparent border-b border-gray-700 outline-none pb-1 text-white focus:border-[var(--electric-blue)] transition-colors" required placeholder="Ism va Familiya">
                                </div>
                                <div>
                                    <input type="text" name="client_phone" x-model="clientPhone" class="w-full bg-transparent border-b border-gray-700 outline-none pb-1 text-white focus:border-[var(--electric-blue)] transition-colors" required placeholder="Telefon Raqami">
                                </div>
                                <div class="col-span-2">
                                    <input type="text" name="client_address" x-model="clientAddress" class="w-full bg-transparent border-b border-gray-700 outline-none pb-1 text-white focus:border-[var(--electric-blue)] transition-colors" required placeholder="Manzil / Yashash joyi">
                                </div>
                            </div>
                        </div>

                        <!-- Service Details -->
                        <div class="bg-black bg-opacity-40 border border-gray-800 p-3 rounded-sm space-y-3 focus-within:border-[var(--electric-blue)] transition-colors">
                            <span class="text-sm text-gray-500 uppercase tracking-widest block mb-2">Xizmat va Narxlar</span>
                            <div class="grid grid-cols-2 gap-3 text-xs">
                                <div class="col-span-2">
                                    <input type="text" name="service_name" class="w-full bg-transparent border-b border-gray-700 outline-none pb-1 text-[var(--electric-blue)] focus:border-[var(--electric-blue)]" required placeholder="Xizmat Nomi / Tavsifi">
                                </div>
                                <div>
                                    <input type="text" name="custom_type" x-model="serviceType" list="serviceTypesList" class="w-full bg-transparent border-b border-gray-700 outline-none pb-1 text-[var(--electric-blue)] focus:border-[var(--electric-blue)]" required placeholder="Tarif/Xizmat Turi (Masalan: E-imzo)">
                                    <datalist id="serviceTypesList">
                                        <option value="Konsultatsiya">
                                        <option value="E-imzo">
                                        <option value="Dasturlash">
                                        <option value="Boshqa xizmat">
                                    </datalist>
                                </div>
                                <div>
                                    <input type="number" name="operator_share_percentage" class="w-full bg-transparent border-b border-gray-700 outline-none pb-1 text-[var(--cyber-yellow)] focus:border-[var(--cyber-yellow)]" required placeholder="Operator Ulushi (%)">
                                </div>
                                <div>
                                    <input type="number" name="cost_price" class="w-full bg-transparent border-b border-gray-700 outline-none pb-1 text-red-500 focus:border-red-500" required placeholder="Xizmat Tan Narxi (UZS)">
                                </div>
                                <div>
                                    <input type="number" name="amount" class="w-full bg-transparent border-b border-gray-700 outline-none pb-1 text-green-400 focus:border-green-400" required placeholder="Mijoz Narxi (Total UZS)">
                                </div>
                            </div>
                        </div>

                        <div class="border border-dashed border-gray-700 p-3 flex flex-col gap-2" :class="serviceType.toLowerCase().includes('imzo') ? 'border-[var(--electric-blue)] bg-gray-500 bg-opacity-10' : ''">
                            <span class="text-sm uppercase text-gray-500">Ilova / Shartnoma Fayli <span x-show="serviceType.toLowerCase().includes('imzo')" class="text-red-500 font-bold">*</span></span>
                            <input type="file" name="pfc_file" class="text-sm w-full file:bg-gray-800 file:text-gray-300 file:border-0 file:py-1 file:px-3 file:rounded cursor-pointer hover:file:bg-[var(--electric-blue)] hover:file:text-black transition-colors" :required="serviceType.toLowerCase().includes('imzo')">
                        </div>

                        <div class="mt-auto">
                            <button type="submit" @mouseenter="speakUzbek('Kassaga yo\'naltirish')" :disabled="isSubmitting" class="w-full neon-btn-yellow py-3 text-sm font-bold font-orbitron tracking-widest uppercase rounded-sm outline-none flex justify-center items-center">
                                <span x-show="!isSubmitting">Kassaga Yo'naltirish</span>
                                <span x-show="isSubmitting" class="animate-pulse">YUBORILMOQDA...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Right Panel: Sales Pipeline + Missions -->
            <div class="w-1/2 flex flex-col gap-4">
                
                <!-- Pipeline -->
                <div class="glass-card flex-1 min-h-0 p-4 rounded-sm flex flex-col">
                    <h2 class="font-orbitron tracking-widest text-gray-400 uppercase text-xs mb-4 border-b border-gray-800 pb-2">Shartnomalar Holati (Pipeline)</h2>
                    <div class="flex-1 overflow-y-auto slim-scroll pr-2 laser-path space-y-4">
                        <template x-for="contract in stats.contracts" :key="contract.id">
                            <div class="relative pl-6 hover:bg-white hover:bg-opacity-5 rounded" @mouseenter="speakUzbek(contract.status == 'pending' ? 'Kutilmoqda' : (contract.status == 'approved' ? 'Tasdiqlangan' : 'Rad etilgan'))">
                                <span class="absolute left-0 top-1/2 -mt-1.5 w-3 h-3 rounded-full bg-[#0d0d0d] border border-gray-600 z-10" :class="{
                                    'border-[var(--cyber-yellow)] shadow-[0_0_8px_var(--cyber-yellow)]' : contract.status == 'pending',
                                    'border-green-500 shadow-[0_0_8px_rgba(0,255,0,0.5)]' : contract.status == 'approved',
                                    'border-red-500 shadow-[0_0_8px_rgba(255,0,0,0.5)]' : contract.status == 'rejected'
                                }"></span>
                                
                                <div class="bg-black bg-opacity-50 p-2 border border-gray-800 rounded-sm hover:border-[var(--electric-blue)] transition-colors group">
                                    <div class="flex justify-between items-center mb-1">
                                        <span class="text-xs font-bold text-gray-200 group-hover:text-[var(--electric-blue)] transition-colors" x-text="contract.contract_id"></span>
                                        <span class="text-xs uppercase tracking-widest px-1 border" :class="{
                                            'text-[var(--cyber-yellow)] border-[var(--cyber-yellow)]': contract.status == 'pending',
                                            'text-green-500 border-green-500': contract.status == 'approved',
                                            'text-red-500 border-red-500': contract.status == 'rejected',
                                        }" x-text="contract.status == 'pending' ? 'Kutilmoqda' : (contract.status == 'approved' ? 'Tasdiqlangan' : 'Rad Etilgan')"></span>
                                    </div>
                                    <div class="flex justify-between items-end">
                                        <div class="text-sm text-gray-500 truncate w-32" x-text="contract.client_name"></div>
                                        <div class="text-sm font-orbitron" :class="contract.status == 'approved' ? 'text-green-500' : 'text-gray-300'" x-text="new Intl.NumberFormat('uz-UZ').format(contract.amount)"></div>
                                    </div>
                                    <div class="flex justify-between items-end mt-1 text-xs opacity-60">
                                        <div class="flex flex-col gap-0.5">
                                            <span x-text="'Tur: ' + contract.custom_type"></span>
                                            <span x-text="'Foiz: ' + contract.operator_share_percentage + '%'"></span>
                                        </div>
                                        <div class="text-right flex flex-col items-end">
                                            <span x-text="'Foyda: ' + new Intl.NumberFormat('uz-UZ').format(contract.amount - contract.cost_price)" class="text-[var(--electric-blue)]"></span>
                                            <span x-text="'Ulushim: ' + new Intl.NumberFormat('uz-UZ').format((contract.amount - contract.cost_price) * (contract.operator_share_percentage / 100))" class="text-[var(--cyber-yellow)] font-bold"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                        <template x-if="stats.contracts.length === 0">
                            <div class="text-sm text-gray-600 uppercase tracking-widest text-center mt-10">Jarayonda Shartnomalar Yo'q</div>
                        </template>
                    </div>
                </div>

                <!-- Missions / Tasks -->
                <div class="glass-card h-1/3 min-h-[150px] p-4 rounded-sm flex flex-col">
                    <h2 class="font-orbitron tracking-widest text-[var(--cyber-yellow)] uppercase text-xs mb-3 flex justify-between">
                        Mening Vazifalarim
                        <a href="{{ route('operator.chat.index') }}" class="text-sm text-gray-500 hover:text-[var(--cyber-yellow)] underline">CHATGA O'TISH</a>
                    </h2>
                    <div class="flex-1 overflow-y-auto slim-scroll space-y-2 pr-2">
                        <template x-for="task in stats.tasks" :key="task.id">
                            <a :href="'{{ route('operator.chat.index') }}'" class="block pl-2 border-l-2 border-[var(--cyber-yellow)] hover:bg-black hover:bg-opacity-50 transition-colors p-1 cursor-pointer">
                                <div class="text-xs text-gray-200 uppercase tracking-wide truncate" x-text="task.title"></div>
                                <div class="flex justify-between mt-1 items-center">
                                    <span class="text-xs text-[var(--cyber-yellow)] opacity-70" x-text="'XP: ' + task.xp_reward"></span>
                                    <span class="text-xs text-[var(--electric-blue)]" x-text="'Due: ' + new Date(task.deadline).toLocaleDateString()"></span>
                                </div>
                            </a>
                        </template>
                        <template x-if="stats.tasks.length === 0">
                            <div class="text-sm text-gray-600 uppercase tracking-widest text-center mt-4">Faol Vazifalar Yo'q</div>
                        </template>
                    </div>
                </div>
            </div>
            </div>

            <!-- ARCHIVE TAB -->
            <div x-show="activeTab === 'archive'" class="flex-1 flex gap-4 min-h-0" style="display: none;">
                <div class="glass-card flex-1 p-5 rounded-sm flex flex-col">
                    <h2 class="font-orbitron tracking-widest text-[var(--electric-blue)] uppercase text-sm mb-4">Shartnomalar Arxivi (Cheklar)</h2>
                    <div class="flex-1 overflow-y-auto slim-scroll pr-2 space-y-2">
                        <template x-for="receipt in stats.receipts" :key="receipt.id">
                            <div class="bg-black bg-opacity-40 border border-gray-800 p-3 rounded-sm flex justify-between items-center hover:border-[var(--electric-blue)] transition-colors">
                                <div>
                                    <div class="font-bold text-[var(--electric-blue)] text-sm" x-text="receipt.contract_id"></div>
                                    <div class="text-xs text-gray-400 mt-1" x-text="receipt.client_name + ' | ' + receipt.custom_type"></div>
                                    <div class="text-sm text-gray-500 mt-1" x-text="new Date(receipt.created_at).toLocaleString('uz-UZ')"></div>
                                </div>
                                <div class="text-right">
                                    <div class="text-green-500 font-bold text-sm font-orbitron" x-text="new Intl.NumberFormat('uz-UZ').format(receipt.amount) + ' UZS'"></div>
                                    <div class="flex flex-col gap-1 mt-2">
                                        <template x-if="receipt.file_path">
                                            <a :href="'/contracts/' + receipt.id + '/download'" class="text-sm uppercase text-green-500 hover:underline flex items-center justify-end gap-1">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                                Yuklash
                                            </a>
                                        </template>
                                        <button @click="window.open('{{ url('/contracts/print') }}/' + receipt.id, '_blank', 'width=400,height=600,noopener,noreferrer')" class="text-sm uppercase text-[var(--electric-blue)] hover:underline flex items-center justify-end gap-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                            Chop etish
                                        </button>
                                        <button @click="reFillForm(receipt); activeTab = 'console'" class="text-sm uppercase text-[var(--cyber-yellow)] hover:underline">Oldin/Qisman to'lov (Yangi tuzish)</button>
                                    </div>
                                </div>
                            </div>
                        </template>
                        <template x-if="!stats.receipts || stats.receipts.length === 0">
                            <div class="text-xs text-gray-500 mt-6 text-center uppercase tracking-widest">Arxiv bo'sh</div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- SETTINGS TAB -->
            <div x-show="activeTab === 'settings'" class="flex-1 flex gap-4 min-h-0" style="display: none;">
               <div class="glass-card w-1/2 p-5 rounded-sm flex flex-col mx-auto">
                    <h2 class="font-orbitron tracking-widest text-[var(--electric-blue)] uppercase text-sm mb-4 border-b border-[var(--electric-blue)] border-opacity-30 pb-2">Shaxsiy Sozlamalar</h2>
                    
                    <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-4 text-xs max-w-sm">
                        @csrf @method('patch')
                        <div class="flex flex-col gap-2">
                            <label class="uppercase text-gray-500 text-sm">Avatar (Profil Rasmi)</label>
                            <input type="file" name="avatar" class="file:bg-gray-800 file:text-gray-300 file:border-0 file:py-1 file:px-3 file:rounded cursor-pointer hover:file:bg-[var(--electric-blue)] hover:file:text-black">
                        </div>
                        <div class="flex flex-col gap-2">
                            <label class="uppercase text-gray-500 text-sm">Parol O'zgartirish</label>
                            <input type="password" name="password" placeholder="Yangi parol" class="bg-black bg-opacity-40 border border-gray-700 text-white p-2 outline-none focus:border-[var(--electric-blue)]">
                        </div>
                        <div class="pt-4 border-t border-gray-800">
                            <h3 class="text-[var(--cyber-yellow)] mb-2 uppercase text-sm">Tizim Dasturchisi</h3>
                            <p class="text-gray-400">Obsidian OS</p>
                            <p class="text-gray-500 text-sm mt-1">Aloqa: dev@obsidian-os.uz</p>
                        </div>
                        <div class="mt-6 flex gap-2 w-full">
                            <button type="submit" class="w-full neon-btn-yellow py-2 text-xs font-bold font-orbitron uppercase rounded-sm outline-none text-center block" style="background:var(--active-color); color:#000; border:none; text-shadow:none;">SAQLASH</button>
                            <a href="{{ route('profile.edit') }}" class="w-full py-2 text-xs font-bold font-orbitron uppercase rounded-sm border border-gray-500 text-gray-400 text-center hover:bg-gray-800 block">Kengaytirilgan Profil</a>
                        </div>
                    </form>
               </div>
            </div>

        </div>
    </template>

    <!-- Face ID Scanning Overlay -->
    <div x-show="faceIdOverlay" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-95 backdrop-blur-md">
        <div class="relative w-full max-w-sm p-6 glass-card text-center border-[var(--electric-blue)] shadow-[0_0_30px_rgba(0,240,255,0.2)]">
            <h2 class="text-sm font-orbitron font-bold text-[var(--electric-blue)] mb-4 tracking-widest uppercase">Biometrik Tasdiqlash</h2>
            
            <div class="relative w-48 h-48 mx-auto border border-gray-700 bg-black overflow-hidden rounded-full shadow-[0_0_20px_rgba(0,240,255,0.1)]">
                <video id="webcam" autoplay playsinline class="w-full h-full object-cover transform scale-x-[-1] opacity-70"></video>
                <div class="absolute inset-x-0 h-1 bg-[var(--electric-blue)] shadow-[0_0_10px_var(--electric-blue)]" style="top: 0; animation: scanLine 2s linear infinite;"></div>
                <div class="absolute inset-0 flex items-center justify-center mix-blend-screen text-[var(--electric-blue)] opacity-40">
                    <svg class="w-full h-full p-6" viewBox="0 0 100 100" fill="none" stroke="currentColor" stroke-width="1"><path d="M20 30 V20 H30 M70 20 H80 V30 M80 70 V80 H70 M30 80 H20 V70" stroke-linecap="square"/></svg>
                </div>
            </div>
            <style>@keyframes scanLine { 100% { top: 100%; } }</style>

            <div class="mt-6 font-mono text-xs text-gray-400">
                <p x-text="faceIdMessage" class="uppercase tracking-widest glitch-anim"></p>
                <div class="w-full bg-gray-900 h-1 mt-3">
                    <div class="h-full bg-[var(--electric-blue)] transition-all duration-300" :style="'width: ' + faceIdProgress + '%'"></div>
                </div>
            </div>

            <div class="mt-4 flex flex-col gap-2 relative">
                <button type="button" @click="showPinFallback = !showPinFallback; if(showPinFallback) speakUzbek('PIN kodni kiriting')" class="text-sm text-[var(--cyber-yellow)] underline uppercase tracking-widest opacity-80 hover:opacity-100">Kamera ishlamayaptimi? PIN kod kiritish</button>
                
                <div x-show="showPinFallback" class="mt-3 flex gap-2 w-full max-w-[200px] mx-auto bg-black border border-[var(--cyber-yellow)] p-1">
                    <input type="password" id="fallbackPinInputs" maxlength="10" placeholder="PIN" class="w-full bg-transparent border-none text-[var(--cyber-yellow)] text-center tracking-[0.5em] font-bold outline-none font-mono">
                    <button type="button" @click="document.getElementById('startShiftPin').value = document.getElementById('fallbackPinInputs').value; document.getElementById('startShiftForm').submit();" class="bg-[var(--cyber-yellow)] text-black px-2 py-1 font-bold text-xs uppercase hover:opacity-80">OK</button>
                </div>
            </div>

            <form id="startShiftForm" method="POST" action="{{ route('shift.start') }}" class="hidden">
                @csrf
                <input type="hidden" name="pin_code" id="startShiftPin">
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('operatorDashboard', () => ({
            activeTab: 'console',
            serviceType: 'Consulting',
            faceIdOverlay: false,
            showPinFallback: false,
            faceIdMessage: 'INITIALIZING CAMERA...',
            faceIdProgress: 0,
            isSubmitting: false,
            stats: {
                balance: '{{ number_format(auth()->user()->balance, 0, ".", " ") }}',
                xp: {{ auth()->user()->xp ?? 0 }},
                salary: {{ auth()->user()->salary ?? 0 }},
                monthlyHours: 0,
                contracts: @json($myContracts),
                tasks: @json($myTasks),
                receipts: [],
                shiftDuration: '00:00:00',
                hasActiveShift: {{ $activeShift ? 'true' : 'false' }},
                isPaused: {{ auth()->user()->status === 'away' ? 'true' : 'false' }},
                shiftSeconds: {{ $activeShift ? $shiftSeconds : 0 }}
            },
            
            reFillForm(receipt) {
                this.clientName = receipt.client_name;
                this.clientPhone = receipt.client_phone;
                this.clientAddress = receipt.client_address;
                this.serviceType = receipt.custom_type;
            },
            
            speakUzbek(text) {
                // Ignore empty text
                if (!text) return;
                try {
                    // Use Google Translate TTS Endpoint directly via Native HTML5 Audio for super fluent Voice.
                    const url = 'https://translate.google.com/translate_tts?ie=UTF-8&tl=uz&client=tw-ob&q=' + encodeURIComponent(text);
                    const audio = new Audio(url);
                    audio.play().catch(e => {
                        console.warn("Audio play error, falling back to Web Speech API...", e);
                        // Fallback completely to speechSynthesis if Audio restricted by browser interaction rules
                        if ('speechSynthesis' in window) {
                            const utterance = new SpeechSynthesisUtterance(text);
                            utterance.lang = 'uz-UZ';
                            utterance.rate = 1.0;
                            const voices = window.speechSynthesis.getVoices();
                            const targetVoice = voices.find(v => v.lang.includes('uz')) || voices.find(v => v.lang.includes('ru')) || voices[0];
                            if (targetVoice) utterance.voice = targetVoice;
                            window.speechSynthesis.speak(utterance);
                        }
                    });
                } catch(err) {
                    console.error("TTS Error:", err);
                }
            },

            init() {
                this.fetchStats();
                
                // Immediately calculate duration if shift active
                if (this.stats.hasActiveShift) {
                    this.updateShiftTimer();
                }
                
                // On initial load, welcome the user if already shift active, otherwise ask to start
                setTimeout(() => {
                    if (this.stats.hasActiveShift) {
                        this.speakUzbek("Xush kelibsiz. Tizim to'liq faol holatda.");
                    } else {
                        this.speakUzbek("Salom! Iltimos, ishni boshlash uchun kamera orqali yuzingizni tasdiqlang.");
                    }
                }, 1000);

                setInterval(() => {
                    if (this.stats.hasActiveShift && !this.stats.isPaused) {
                        this.stats.shiftSeconds++;
                        this.updateShiftTimer();
                    }
                }, 1000);

                // Real-time Dashboard Sync
                setInterval(() => {
                    if (this.stats.hasActiveShift) {
                        this.fetchStats();
                    }
                }, 10000);
            },
            
            updateShiftTimer() {
                let delta = this.stats.shiftSeconds;
                let h = Math.floor(delta / 3600);
                let m = Math.floor((delta % 3600) / 60);
                let s = Math.floor(delta % 60);
                this.stats.shiftDuration = [h, m, s].map(v => v < 10 ? "0" + v : v).join(":");
            },
            
            fetchStats() {
                fetch('{{ route("operator.stats") }}', {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                    .then(r => {
                        if (r.status === 401) {
                            window.location.href = '/';
                            return null;
                        }
                        return r.json();
                    })
                    .then(data => {
                        this.stats = {
                            ...this.stats,
                            balance: new Intl.NumberFormat('uz-UZ').format(data.balance || 0),
                            xp: data.xp || 0,
                            salary: data.salary || 0,
                            monthlyHours: data.monthlyHours || 0,
                            contracts: data.contracts || [],
                            tasks: data.tasks || [],
                            receipts: data.receipts || [],
                            hasActiveShift: data.hasActiveShift,
                            isPaused: data.isPaused,
                            shiftSeconds: data.shiftSeconds !== undefined ? data.shiftSeconds : this.stats.shiftSeconds
                        };
                        
                        if (data.hasActiveShift) {
                            this.updateShiftTimer();
                        }
                    }).catch(err => console.error(err));
             },
            
            async submitContract(e) {
                if (this.isSubmitting) return;
                this.isSubmitting = true;
                
                const form = e.target;
                const formData = new FormData(form);
                
                try {
                    const response = await fetch('{{ route('contracts.store') }}', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    });
                    
                    if (response.ok) {
                        const jsonStatus = await response.json();
                        if(jsonStatus.success) {
                            // Success!
                            this.clientName = '';
                            this.clientPhone = '';
                            this.clientAddress = '';
                            form.reset();
                            this.serviceType = 'Konsultatsiya';
                            // Force a rapid update to fetch new contract into pipeline
                            this.fetchStats();
                            
                            this.speakUzbek("Shartnoma kassaga yuborildi");
                        } else {
                            // Fail due to validation logic encoded in success: false
                            this.speakUzbek("Xatolik! " + jsonStatus.message);
                            alert("Xatolik: " + jsonStatus.message);
                        }
                    } else {
                        // Handle errors (validation or server block 422)
                        let data = await response.json();
                        this.speakUzbek("Shartnomani yuborishda xatolik yuz berdi.");
                        alert("Xatolik: " + (data.message || 'Validation error'));
                    }
                } catch (error) {
                    console.error('Submission Error:', error);
                    this.speakUzbek("Tarmoqda xatolik mavjud.");
                } finally {
                    this.isSubmitting = false;
                }
            },
            
            captureFrame(video) {
                const canvas = document.createElement('canvas');
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
                return canvas.toDataURL('image/jpeg', 0.8);
            },
            
            async togglePause() {
                const action = this.stats.isPaused ? 'resume' : 'pause';
                try {
                    const response = await fetch(`/shift/${action}`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    });
                    const data = await response.json();
                    if (data.success) {
                        this.stats.isPaused = !this.stats.isPaused;
                        this.speakUzbek(data.message);
                        // Optional: refresh stats to sync everything
                        this.fetchStats();
                    } else {
                        alert(data.message || 'Xatolik yuz berdi');
                    }
                } catch (error) {
                    console.error('Pause Toggle Error:', error);
                }
            },
            initiateFaceID() {
                this.faceIdOverlay = true;
                this.faceIdMessage = 'KAMERA INITALIZATSIYASI...';
                this.faceIdProgress = 10;
                this.speakUzbek("Yuzingizni kameraga tuting");
                
                navigator.mediaDevices.getUserMedia({ video: { facingMode: "user" } })
                    .then(stream => {
                        const video = document.getElementById('webcam');
                        if (video) video.srcObject = stream;
                        
                        this.faceIdMessage = 'NEYRON TARMOQ KO\'RMOQDA...';
                        this.faceIdProgress = 40;
                        
                        video.onloadeddata = () => {
                            setTimeout(async () => {
                                this.faceIdProgress = 70;
                                this.faceIdMessage = 'GEMINI AI ORQALI TASDIQLANMOQDA...';
                                
                                const frameData = this.captureFrame(video);
                                
                                try {
                                    const response = await fetch('{{ route('operator.face_verify') }}', {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                                        },
                                        body: JSON.stringify({ image: frameData })
                                    });
                                    const result = await response.json();
                                    
                                    if (result.success) {
                                        this.faceIdProgress = 100;
                                        this.faceIdMessage = 'RUXSAT ETILDI: ' + result.message;
                                        this.speakUzbek(result.message);
                                        
                                        const tracks = stream.getTracks();
                                        tracks.forEach(track => track.stop());
                                        
                                        setTimeout(() => {
                                            document.getElementById('startShiftForm').submit();
                                        }, 3000); // give 3 seconds so voice finishes before reload
                                    } else {
                                        this.faceIdMessage = 'RAD ETILDI: ' + result.message;
                                        this.speakUzbek("Kirish rad etildi. " + result.message);
                                        this.faceIdProgress = 0;
                                        const tracks = stream.getTracks();
                                        tracks.forEach(track => track.stop());
                                        setTimeout(() => { this.faceIdOverlay = false; }, 3000);
                                    }
                                } catch (e) {
                                    this.faceIdMessage = 'AI YADROSI BILAN ALOQA UZILDI';
                                    this.speakUzbek("Tarmoq yoki yadro bilan aloqa uzildi");
                                    const tracks = stream.getTracks();
                                    tracks.forEach(track => track.stop());
                                    setTimeout(() => { this.faceIdOverlay = false; }, 2000);
                                }
                            }, 1500);
                        };
                    })
                    .catch(err => {
                        console.error('Face ID Error:', err);
                        this.faceIdMessage = 'KAMERAGA RUXSAT YO\'Q. BEKOR QILINMOQDA...';
                        this.speakUzbek("Kameraga ruxsat etilmadi.");
                        setTimeout(() => { this.faceIdOverlay = false; }, 2000);
                    });
            },

            activateVoiceAI() {
                const phrases = [
                    "Salom! Men Obsidian operatsion tizimiman. Sizga qanday yordam bera olaman?",
                    "Tizim barcha funksiyalari faol. Men tayyorman.",
                    "Hozirgi vaqtda barcha ko'rsatmalar normal holatda.",
                    "Sizning navbatingiz muvaffaqiyatli davom etmoqda. Omad!"
                ];
                const randomPhrase = phrases[Math.floor(Math.random() * phrases.length)];
                this.speakUzbek(randomPhrase);
                
                // Visual feedback
                const orb = document.querySelector('.gemini-orb');
                if (orb) {
                    orb.style.boxShadow = "0 0 50px var(--cyber-yellow)";
                    orb.style.background = "radial-gradient(circle, var(--cyber-yellow) 0%, transparent 70%)";
                    setTimeout(() => {
                        orb.style.boxShadow = "";
                        orb.style.background = "";
                    }, 4000);
                }
            }
        }));
    });
</script>
@endsection
