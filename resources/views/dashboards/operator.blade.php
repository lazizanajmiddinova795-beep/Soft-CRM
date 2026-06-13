@extends('layouts.cyber')

@section('sidebar')
    @include('partials.operator_sidebar')
@endsection

@section('content')
<!-- The entire view fits within the screen. overflow-hidden handles scroll prevention. -->
<div x-data="operatorDashboard()" class="flex-1 min-h-0 flex flex-col gap-6 overflow-hidden">

    <!-- Top Activity Bar (HUD) -->
    <div class="glass-panel p-4 flex flex-col lg:flex-row justify-between items-center gap-6">
        <div class="flex items-center gap-6 w-full lg:w-auto justify-between lg:justify-start">
            <template x-if="!stats.hasActiveShift">
                <button @click="initiateFaceID()" class="btn-ios btn-neon px-8 py-3 font-extrabold uppercase tracking-widest flex items-center gap-2 text-sm shadow-[0_0_20px_rgba(0,255,204,0.3)]">
                    <i class="fa-solid fa-face-viewfinder text-lg"></i>
                    ISHNI BOSHLASH
                </button>
            </template>
            <template x-if="stats.hasActiveShift">
                <div class="flex items-center gap-6">
                    <div class="flex flex-col">
                        <span class="text-[10px] uppercase font-bold text-cyan-400 tracking-widest mb-1">Shift Timer</span>
                        <span class="text-3xl font-black font-mono leading-none tracking-tighter" x-text="stats.shiftDuration">00:00:00</span>
                    </div>

                    <div class="flex gap-2">
                        <template x-if="stats.isPaused">
                            <button @click="togglePause()" class="btn-ios px-4 py-2 border-cyan-500 text-cyan-400 bg-cyan-500/10 text-[11px] font-black uppercase tracking-widest">RESUME MATRIX</button>
                        </template>
                        <template x-if="!stats.isPaused">
                            <button @click="togglePause()" class="btn-ios px-4 py-2 bg-white/5 text-[11px] font-black uppercase tracking-widest hover:bg-white/10">PAUSE SHIFT</button>
                        </template>

                        <form method="POST" action="{{ route('shift.stop') }}">
                            @csrf
                            <button type="submit" class="btn-ios px-4 py-2 border-red-500/50 text-red-400 bg-red-500/10 text-[11px] font-black uppercase tracking-widest hover:bg-red-500/20">STOP ENGINE</button>
                        </form>
                    </div>
                </div>
            </template>
        </div>

        <div class="flex items-center gap-8 justify-center flex-1 order-last lg:order-none">
            <!-- Voice AI Button -->
            <div class="relative group cursor-pointer" @click="activateVoiceAI()">
                <div class="w-16 h-16 rounded-full bg-gradient-to-br from-cyan-400 via-purple-500 to-pink-500 p-[2px] animate-spin-slow">
                    <div class="w-full h-full rounded-full bg-black flex items-center justify-center">
                        <i class="fa-solid fa-microphone-lines text-2xl text-cyan-400 group-hover:scale-110 transition-transform"></i>
                    </div>
                </div>
                <div class="absolute -bottom-6 left-1/2 -translate-x-1/2 text-[9px] font-bold uppercase tracking-[0.3em] text-cyan-400 opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap">Voice Core Active</div>
            </div>
        </div>

        <div class="flex items-center gap-8 text-right w-full lg:w-auto justify-between lg:justify-end">
            <div class="flex flex-col">
                <span class="text-[10px] uppercase font-bold text-cyan-400 tracking-widest mb-1">Total Credits</span>
                <span class="text-2xl font-black text-white"><span x-text="stats.balance"></span> <span class="text-xs opacity-50">UZS</span></span>
            </div>
            <div class="flex flex-col border-l border-white/5 pl-8">
                <span class="text-[10px] uppercase font-bold text-purple-400 tracking-widest mb-1">Task Queue</span>
                <span class="text-2xl font-black text-white" x-text="stats.contracts.filter(c => c.status=='pending').length">0</span>
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
            <div class="flex gap-8 border-b border-white/5 mb-6 px-4">
                <button @click="activeTab = 'console'; speakUzbek('Asosiy boshqaruv paneli');" :class="activeTab === 'console' ? 'text-cyan-400 border-b-2 border-cyan-400' : 'text-white/40 hover:text-white/60'" class="pb-4 text-[11px] font-black uppercase tracking-[0.2em] transition-all">Command Center</button>
                <button @click="activeTab = 'archive'; speakUzbek('Shartnomalar arxivi');" :class="activeTab === 'archive' ? 'text-cyan-400 border-b-2 border-cyan-400' : 'text-white/40 hover:text-white/60'" class="pb-4 text-[11px] font-black uppercase tracking-[0.2em] transition-all">Archive Terminal</button>
                <button @click="activeTab = 'settings'; speakUzbek('Tizim sozlamalari');" :class="activeTab === 'settings' ? 'text-cyan-400 border-b-2 border-cyan-400' : 'text-white/40 hover:text-white/60'" class="pb-4 text-[11px] font-black uppercase tracking-[0.2em] transition-all">Matrix Settings</button>
            </div>

            <!-- MAIN CONSOLE TAB -->
            <div x-show="activeTab === 'console'" class="flex-1 flex flex-col lg:flex-row gap-6 min-h-0">
                
                <!-- Left Panel: Client Matrix & Deal Generation -->
                <div class="w-full lg:w-1/2 flex flex-col gap-6 min-h-0">
                    <div class="glass-panel flex-1 p-8 flex flex-col min-h-0">
                        <div class="panel-title mb-6">
                            <i class="fa-solid fa-bolt-lightning text-cyan-400"></i>
                            <span>SERVICE CORE GENERATOR</span>
                        </div>

                    <form id="contractForm" @submit.prevent="submitContract" enctype="multipart/form-data" class="flex flex-col h-full gap-6 slim-scroll overflow-y-auto pr-2">
                        @csrf
                        
                        <!-- Client Identity Wrapper -->
                        <div x-data="{
                            searchQuery: '', clients: [], showDropdown: false, clientName: '', clientPhone: '', clientAddress: '', clientId: '',
                            searchClient() {
                                if(this.searchQuery.length < 2) { this.clients = []; this.showDropdown = false; return; }
                                fetch('{{ route('operator.clients.search') }}?q=' + this.searchQuery, {
                                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                                })
                                    .then(res => res.json())
                                    .then(data => { this.clients = data; this.showDropdown = data.length > 0; });
                            },
                            selectClient(c) {
                                this.clientName = c.name; this.clientPhone = c.phone; this.clientAddress = c.address; this.clientId = c.id;
                                this.showDropdown = false; this.searchQuery = '';
                            }
                        }" class="bg-white/5 border border-white/5 p-4 rounded-xl space-y-3 relative focus-within:border-cyan-400/20 transition-all">
                            <input type="hidden" name="client_id" :value="clientId">
                            <div class="flex justify-between items-center">
                                <span class="text-[9px] font-bold text-cyan-400 uppercase tracking-[0.2em] opacity-80">Client Identity</span>
                                <div class="relative w-1/2">
                                    <input type="text" x-model="searchQuery" @input.debounce.500ms="searchClient" placeholder="Search DB..." class="w-full bg-black/40 border border-white/10 rounded-full text-white placeholder-white/20 text-[9px] px-3 py-1 outline-none">
                                    <div x-show="showDropdown" @click.away="showDropdown = false" class="absolute top-8 right-0 z-50 w-full bg-[#0a0a0f] border border-white/10 rounded-xl max-h-40 overflow-y-auto slim-scroll">
                                        <template x-for="c in clients" :key="c.id">
                                            <div @click="selectClient(c)" class="p-2 border-b border-white/5 hover:bg-white/5 cursor-pointer">
                                                <div class="font-bold text-cyan-400 text-[10px]" x-text="c.name"></div>
                                                <div class="text-[9px] text-white/40 mt-0.5" x-text="c.phone"></div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                <input type="text" name="client_name" x-model="clientName" class="w-full bg-black/20 border border-white/10 rounded-lg p-2 text-xs text-white focus:border-cyan-400/40 outline-none" required placeholder="Full Name">
                                <input type="text" name="client_phone" x-model="clientPhone" class="w-full bg-black/20 border border-white/10 rounded-lg p-2 text-xs text-white focus:border-cyan-400/40 outline-none" required placeholder="Phone">
                                <div class="md:col-span-2">
                                    <input type="text" name="client_address" x-model="clientAddress" class="w-full bg-black/20 border border-white/10 rounded-lg p-2 text-xs text-white focus:border-cyan-400/40 outline-none" required placeholder="Address / Location">
                                </div>
                            </div>
                        </div>

                        <!-- Multi-Service Details -->
                        <div class="bg-white/5 border border-white/5 p-4 rounded-xl space-y-3 focus-within:border-cyan-400/20 transition-all">
                            <div class="flex justify-between items-center">
                                <div class="flex items-center gap-2">
                                    <i class="fa-solid fa-rectangle-list text-purple-400 text-xs"></i>
                                    <span class="text-[9px] font-bold text-purple-400 uppercase tracking-widest opacity-80">Service Matrix</span>
                                </div>
                                <button type="button" @click="addServiceRow()" class="text-[8px] font-black text-cyan-400 hover:text-cyan-300 uppercase tracking-tighter border border-cyan-400/20 px-2 py-0.5 rounded-md transition-all bg-cyan-400/5">
                                    + ADD ROW
                                </button>
                            </div>
                            
                            <div class="space-y-3 max-h-[200px] overflow-y-auto slim-scroll pr-1">
                                <template x-for="(item, index) in items" :key="index">
                                    <div class="p-3 bg-black/30 border border-white/5 rounded-lg space-y-2 relative group-hover:border-white/10 transition-all">
                                        <button type="button" x-show="items.length > 1" @click="removeServiceRow(index)" class="absolute top-1 right-1 text-white/20 hover:text-red-500 transition-all">
                                            <i class="fa-solid fa-xmark text-[10px]"></i>
                                        </button>

                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                            <div class="md:col-span-2">
                                                <input type="text" x-model="item.name" class="w-full bg-white/5 border border-white/5 rounded-md p-2 text-[11px] text-white focus:border-cyan-400/40 outline-none" required placeholder="Xizmat nomi">
                                            </div>
                                            <div>
                                                <input type="text" x-model="item.type" list="serviceTypesList" class="w-full bg-white/5 border border-white/5 rounded-md p-2 text-[11px] text-white focus:border-cyan-400/40 outline-none" required placeholder="Turi">
                                            </div>
                                            <div class="flex gap-2">
                                                <input type="number" x-model="item.cost" class="w-1/2 bg-white/5 border border-white/5 rounded-md p-2 text-[11px] text-yellow-500 font-bold outline-none" placeholder="Boji">
                                                <input type="number" x-model="item.price" @input="calculateTotal()" class="w-1/2 bg-cyan-400/10 border border-cyan-400/20 rounded-md p-2 text-[11px] text-cyan-400 font-bold outline-none" required placeholder="Narxi">
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <div class="pt-2 border-t border-white/5 flex justify-between items-center text-[9px] font-bold">
                                <span class="text-white/40 uppercase tracking-widest">Total Value:</span>
                                <span class="text-cyan-400 text-lg font-black" x-text="new Intl.NumberFormat('uz-UZ').format(totalAmount) + ' UZS'"></span>
                            </div>

                            <div class="flex gap-2">
                                <select name="payment_method" class="w-2/3 bg-black/60 border border-white/5 rounded-lg p-2.5 text-[10px] text-white focus:border-cyan-400/40 outline-none font-bold uppercase" required>
                                    <option value="" disabled selected>Payment Method</option>
                                    <option value="card">💳 Card</option>
                                    <option value="cash">💵 Cash</option>
                                </select>
                                <input type="number" name="operator_share_percentage" min="0" max="100" class="w-1/3 bg-purple-500/10 border border-purple-500/30 rounded-lg p-2.5 text-[10px] text-purple-400 focus:border-purple-400/50 outline-none font-bold uppercase placeholder:text-purple-400/50" required placeholder="Ulush (%)" x-model="operatorShare">
                            </div>
                        </div>

                        <datalist id="serviceTypesList">
                            <option value="Davlat Xizmatlari">
                            <option value="E-imzo">
                            <option value="Konsultatsiya">
                            <option value="Soliq Hisoboti">
                            <option value="Informatika Hizmati">
                        </datalist>

                        <div class="border border-dashed border-white/10 p-3 rounded-lg flex items-center gap-3 transition-colors bg-white/5" :class="items.some(i => i.type.toLowerCase().includes('imzo')) ? 'border-cyan-400/50 bg-cyan-400/5' : ''">
                            <i class="fa-solid fa-file-shield text-cyan-400/60 text-lg"></i>
                            <div class="flex-1">
                                <span class="text-[9px] font-bold uppercase text-white/30 block">Documentation / PFC File <span x-show="items.some(i => i.type.toLowerCase().includes('imzo'))" class="text-red-500">*</span></span>
                                <input type="file" name="pfc_file" class="text-[10px] w-full file:bg-white/10 file:text-white/60 file:border-0 file:rounded-md file:px-2 file:py-1 cursor-pointer" :required="items.some(i => i.type.toLowerCase().includes('imzo'))">
                            </div>
                        </div>

                        <button type="submit" @mouseenter="speakUzbek('Kassaga yo\'naltirish')" :disabled="isSubmitting" class="w-full py-3 bg-cyan-500 text-black font-black text-[11px] uppercase tracking-widest hover:bg-cyan-400 transition-all rounded-lg shadow-[0_0_20px_rgba(0,255,204,0.2)] flex items-center justify-center gap-2">
                             <i class="fa-solid fa-bolt-lightning" x-show="!isSubmitting"></i>
                             <span x-show="!isSubmitting">AUTHORIZE & ROUTE TO CASHIER</span>
                             <span x-show="isSubmitting" class="animate-pulse">PROCESSING SYNC...</span>
                        </button>
                    </form>
                </div>
            </div>

                <!-- Right Panel: Sales Pipeline + Missions -->
                <div class="w-full lg:w-1/2 flex flex-col gap-6 min-h-0">
                    
                    <!-- Pipeline -->
                    <div class="glass-panel flex-[2] min-h-0 p-8 flex flex-col">
                        <div class="panel-title mb-6">
                            <i class="fa-solid fa-diagram-project text-purple-400"></i>
                            <span>LIVE TRANSACTION PIPELINE</span>
                        </div>
                        <div class="flex-1 overflow-y-auto slim-scroll pr-2 space-y-3">
                            <template x-for="contract in stats.contracts" :key="contract.id">
                                <div class="glass-panel p-3 border-white/5 bg-white/5 hover:border-cyan-400/30 transition-all group cursor-default" @mouseenter="speakUzbek(contract.status == 'pending' ? 'Kutilmoqda' : (contract.status == 'approved' ? 'Tasdiqlangan' : 'Rad etilgan'))">
                                    <div class="flex justify-between items-center mb-2">
                                        <div class="flex items-center gap-2">
                                            <div class="w-1.5 h-1.5 rounded-full animate-pulse" :class="{
                                                'bg-cyan-400 shadow-[0_0_8px_rgba(0,255,204,0.5)]' : contract.status == 'pending',
                                                'bg-green-400 shadow-[0_0_8px_rgba(34,197,94,0.5)]' : contract.status == 'approved',
                                                'bg-red-400 shadow-[0_0_8px_rgba(239,68,68,0.5)]' : contract.status == 'rejected'
                                            }"></div>
                                            <span class="text-[9px] font-black text-white/30 tracking-widest" x-text="contract.contract_id"></span>
                                        </div>
                                        <span class="text-[8px] font-black uppercase tracking-widest px-1.5 py-0.5 rounded" :class="{
                                            'text-cyan-400 bg-cyan-400/5': contract.status == 'pending',
                                            'text-green-400 bg-green-400/5': contract.status == 'approved',
                                            'text-red-400 bg-red-400/5': contract.status == 'rejected',
                                        }" x-text="contract.status == 'pending' ? 'PENDING' : (contract.status == 'approved' ? 'PASSED' : 'DENIED')"></span>
                                    </div>
                                    <div class="flex justify-between items-end">
                                        <div>
                                            <div class="text-[11px] font-bold text-white group-hover:text-cyan-400 transition-colors" x-text="contract.client_name"></div>
                                            <div class="text-[9px] text-white/30 uppercase mt-0.5" x-text="contract.custom_type"></div>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-base font-black" :class="contract.status == 'approved' ? 'text-green-400' : 'text-white'" x-text="new Intl.NumberFormat('uz-UZ').format(contract.amount) + ' UZS'"></div>
                                            <div class="text-[8px] font-bold text-cyan-400/60 mt-0.5" x-text="'BONUS: ' + new Intl.NumberFormat('uz-UZ').format((contract.amount - contract.cost_price) * (contract.operator_share_percentage / 100)) + ' UZS'"></div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                            <template x-if="stats.contracts.length === 0">
                                <div class="flex flex-col items-center justify-center h-full opacity-20">
                                    <i class="fa-solid fa-microchip text-4xl mb-4"></i>
                                    <p class="text-[10px] font-black uppercase tracking-[0.3em]">No Active Operations</p>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Missions / Tasks -->
                    <div class="glass-panel flex-1 min-h-[150px] p-8 flex flex-col">
                        <div class="panel-title mb-6 flex justify-between">
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-list-check text-cyan-400"></i>
                                <span>ACTIVE MISSIONS</span>
                            </div>
                            <a href="{{ route('operator.chat.index') }}" class="text-[9px] font-black tracking-widest text-white/30 hover:text-cyan-400 transition-colors">COMMUNICATIONS HUB <i class="fa-solid fa-arrow-right ml-1"></i></a>
                        </div>
                        <div class="flex-1 overflow-y-auto slim-scroll space-y-3 pr-4">
                            <template x-for="task in stats.tasks" :key="task.id">
                                <a :href="'{{ route('operator.chat.index') }}'" class="block p-4 bg-white/5 border border-white/5 hover:border-cyan-400/30 rounded-2xl transition-all">
                                    <div class="text-[11px] font-bold text-white uppercase tracking-wide truncate" x-text="task.title"></div>
                                    <div class="flex justify-between mt-3 items-center">
                                        <span class="text-[9px] font-black text-cyan-400 tracking-widest" x-text="'REWARD: ' + task.xp_reward + ' XP'"></span>
                                        <span class="text-[9px] font-black text-white/30 tracking-widest" x-text="'DEADLINE: ' + new Date(task.deadline).toLocaleDateString()"></span>
                                    </div>
                                </a>
                            </template>
                            <template x-if="stats.tasks.length === 0">
                                <div class="flex flex-col items-center justify-center h-full opacity-20">
                                    <p class="text-[10px] font-black uppercase tracking-[0.2em]">Zero Objectives Assigned</p>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ARCHIVE TAB -->
            <div x-show="activeTab === 'archive'" class="flex-1 min-h-0" style="display: none;">
                <div class="glass-panel h-full p-8 flex flex-col">
                    <div class="panel-title mb-8">
                        <i class="fa-solid fa-clock-rotate-left text-cyan-400"></i>
                        <span>HISTORICAL DATA ARCHIVE</span>
                    </div>
                    <div class="flex-1 overflow-y-auto slim-scroll pr-4 space-y-4">
                        <template x-for="receipt in stats.receipts" :key="receipt.id">
                            <div class="glass-panel p-6 bg-white/5 border-white/5 hover:border-cyan-400/30 transition-all flex justify-between items-center group">
                                <div class="flex items-center gap-6">
                                    <div class="w-12 h-12 rounded-2xl bg-white/5 flex items-center justify-center border border-white/5 group-hover:border-cyan-400/30 transition-all">
                                        <i class="fa-solid fa-file-invoice text-xl text-white/20 group-hover:text-cyan-400"></i>
                                    </div>
                                    <div>
                                        <div class="text-sm font-black text-white" x-text="receipt.contract_id"></div>
                                        <div class="text-[10px] text-white/40 uppercase font-bold mt-1" x-text="receipt.client_name + ' • ' + receipt.custom_type"></div>
                                        <div class="text-[9px] text-white/20 font-bold mt-1" x-text="'TIMESTAMP: ' + new Date(receipt.created_at).toLocaleString('uz-UZ')"></div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-xl font-black text-green-400 font-mono" x-text="new Intl.NumberFormat('uz-UZ').format(receipt.amount) + ' UZS'"></div>
                                    <div class="flex gap-4 mt-4 justify-end">
                                        <template x-if="receipt.file_path">
                                            <a :href="'/contracts/' + receipt.id + '/download'" class="text-[9px] font-black uppercase tracking-widest text-cyan-400 hover:text-cyan-300 flex items-center gap-2">
                                                <i class="fa-solid fa-download"></i> RETRIEVE
                                            </a>
                                        </template>
                                        <button @click="window.open('{{ url('/contracts/print') }}/' + receipt.id, '_blank', 'width=400,height=600,noopener,noreferrer')" class="text-[9px] font-black uppercase tracking-widest text-purple-400 hover:text-purple-300 flex items-center gap-2">
                                            <i class="fa-solid fa-print"></i> PRINT HARDCOPY
                                        </button>
                                        <button @click="reFillForm(receipt); activeTab = 'console'" class="text-[9px] font-black uppercase tracking-widest text-white/40 hover:text-white flex items-center gap-2">
                                            <i class="fa-solid fa-copy"></i> CLONE RECORD
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </template>
                        <template x-if="!stats.receipts || stats.receipts.length === 0">
                            <div class="flex flex-col items-center justify-center h-full opacity-20">
                                <i class="fa-solid fa-box-archive text-4xl mb-4"></i>
                                <p class="text-[10px] font-black uppercase tracking-[0.3em]">Archive Storage Empty</p>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- SETTINGS TAB -->
            <div x-show="activeTab === 'settings'" class="flex-1 min-h-0" style="display: none;">
               <div class="glass-panel max-w-2xl mx-auto p-12">
                    <div class="panel-title mb-10">
                        <i class="fa-solid fa-sliders text-cyan-400"></i>
                        <span>OPERATOR PROFILE CONFIG</span>
                    </div>
                    
                    <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
                        @csrf @method('patch')
                        
                        <div class="flex items-center gap-8">
                            <div class="w-24 h-24 rounded-3xl bg-white/5 border border-white/10 flex items-center justify-center relative group">
                                <i class="fa-solid fa-user text-3xl text-white/10"></i>
                                <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity rounded-3xl flex items-center justify-center cursor-pointer">
                                    <i class="fa-solid fa-camera text-white"></i>
                                </div>
                                <input type="file" name="avatar" class="absolute inset-0 opacity-0 cursor-pointer">
                            </div>
                            <div class="flex-1">
                                <label class="text-[10px] font-black text-white/40 uppercase tracking-widest mb-2 block">Visual Identity</label>
                                <p class="text-xs text-white/20">Update your avatar displayed across the ITcloud network.</p>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <label class="text-[10px] font-black text-cyan-400 uppercase tracking-widest block">Access Credentials</label>
                            <input type="password" name="password" placeholder="ENTER NEW ACCESS KEY" class="w-full bg-white/5 border border-white/10 rounded-2xl p-4 text-sm text-white focus:border-cyan-400/50 outline-none transition-all placeholder:text-white/10">
                        </div>

                        <div class="p-6 bg-cyan-400/5 rounded-2xl border border-cyan-400/10">
                            <h3 class="text-xs font-black text-cyan-400 uppercase tracking-widest mb-2">Network Administrator</h3>
                            <p class="text-xs text-white/60">OBSIDIAN OS PROTOCOL v1.0</p>
                            <p class="text-[10px] text-white/40 mt-1 uppercase font-bold">Contact Node: dev@obsidian-os.uz</p>
                        </div>

                        <div class="flex gap-4 pt-4">
                            <button type="submit" class="flex-1 btn-ios btn-neon py-4 text-[11px] font-black uppercase tracking-widest">COMMIT CHANGES</button>
                            <a href="{{ route('profile.edit') }}" class="flex-1 btn-ios border-white/10 bg-white/5 py-4 text-[11px] font-black uppercase tracking-widest text-center">FULL PROFILE ACCESS</a>
                        </div>
                    </form>
               </div>
            </div>

        </div>
    </template>

    <!-- Face ID Scanning Overlay -->
    <div x-show="faceIdOverlay" style="display: none;" class="fixed inset-0 z-[100] flex items-center justify-center bg-black/95 backdrop-blur-2xl">
        <div class="relative w-full max-w-lg p-12 glass-panel text-center border-cyan-400/30">
            <div class="panel-title justify-center mb-8">
                <i class="fa-solid fa-face-viewfinder text-cyan-400"></i>
                <span>BIOMETRIC AUTHENTICATION</span>
            </div>
            
            <div class="relative w-64 h-64 mx-auto border-2 border-white/5 bg-black rounded-3xl overflow-hidden shadow-[0_0_50px_rgba(0,255,204,0.1)] mb-8">
                <video id="webcam" autoplay playsinline class="w-full h-full object-cover transform scale-x-[-1] opacity-60"></video>
                <div class="absolute inset-x-0 h-0.5 bg-cyan-400 shadow-[0_0_15px_#00ffcc]" style="top: 0; animation: scanLine 3s linear infinite;"></div>
                <div class="absolute inset-0 flex items-center justify-center text-cyan-400/20">
                    <i class="fa-solid fa-expand text-9xl"></i>
                </div>
            </div>
            <style>@keyframes scanLine { 100% { top: 100%; } }</style>

            <div class="space-y-4">
                <p x-text="faceIdMessage" class="text-[11px] font-black uppercase tracking-[0.3em] text-cyan-400 animate-pulse"></p>
                <div class="w-full bg-white/5 h-1 rounded-full overflow-hidden">
                    <div class="h-full bg-cyan-400 transition-all duration-500 shadow-[0_0_10px_#00ffcc]" :style="'width: ' + faceIdProgress + '%'"></div>
                </div>
            </div>

            <div class="mt-10 pt-8 border-t border-white/5 space-y-4">
                <button type="button" @click="showPinFallback = !showPinFallback; if(showPinFallback) speakUzbek('PIN kodni kiriting')" class="text-[10px] font-black uppercase tracking-widest text-white/30 hover:text-white transition-colors">Emergency PIN Access</button>
                
                <div x-show="showPinFallback" class="flex gap-2 w-full max-w-xs mx-auto bg-black/40 border border-white/10 p-2 rounded-xl">
                    <input type="password" id="fallbackPinInputs" maxlength="10" placeholder="••••" class="w-full bg-transparent border-none text-cyan-400 text-center tracking-[1em] font-black outline-none">
                    <button type="button" @click="document.getElementById('startShiftPin').value = document.getElementById('fallbackPinInputs').value; document.getElementById('startShiftForm').submit();" class="btn-ios bg-cyan-400 text-black px-6 py-2 font-black text-[10px] uppercase">LOGIN</button>
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
            faceIdMessage: 'INITIALIZING OPTICAL SENSORS...',
            faceIdProgress: 0,
            isSubmitting: false,
            items: [{ name: '', type: 'Consulting', cost: '', price: '' }],
            totalAmount: 0,
            operatorShare: '',
            serviceType: 'Consulting',
            
            addServiceRow() {
                this.items.push({ name: '', type: 'Consulting', cost: '', price: '' });
                this.speakUzbek("Yangi xizmat qo'shildi.");
            },
            
            removeServiceRow(index) {
                this.items.splice(index, 1);
                this.calculateTotal();
            },
            
            calculateTotal() {
                this.totalAmount = this.items.reduce((sum, item) => sum + (parseFloat(item.price) || 0), 0);
            },
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
                if (!text) return;
                try {
                    const url = 'https://translate.google.com/translate_tts?ie=UTF-8&tl=uz&client=tw-ob&q=' + encodeURIComponent(text);
                    const audio = new Audio(url);
                    audio.play().catch(e => {
                        console.warn("Audio play error, falling back to Web Speech API...", e);
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
                if (this.stats.hasActiveShift) {
                    this.updateShiftTimer();
                }
                
                setTimeout(() => {
                    if (this.stats.hasActiveShift) {
                        this.speakUzbek("Xush kelibsiz. Tizim to'liq faol holatda.");
                    } else {
                        this.speakUzbek("Salom! Iltimos, ishni boshlash uchun kamera orqali yuzingizni tasdiqlang. OBSIDIAN sistema sizni kutmoqda.");
                    }
                }, 1000);

                setInterval(() => {
                    if (this.stats.hasActiveShift && !this.stats.isPaused) {
                        this.stats.shiftSeconds++;
                        this.updateShiftTimer();
                    }
                }, 1000);

                setInterval(() => {
                    if (this.stats.hasActiveShift) {
                        this.fetchStats();
                    }
                }, 60000);
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
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(r => r.status === 401 ? (window.location.href = '/') : r.json())
                .then(data => {
                    if(!data) return;
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
                    if (data.hasActiveShift) this.updateShiftTimer();
                }).catch(err => console.error(err));
             },
            
            async submitContract(e) {
                if (this.isSubmitting) return;
                this.isSubmitting = true;
                const form = e.target;
                const formData = new FormData(form);
                
                // Add items as JSON
                formData.append('services_json', JSON.stringify(this.items));
                formData.append('amount', this.totalAmount);
                // For legacy compatibility, set primary service name as first item
                if(this.items.length > 0) {
                    formData.append('service_name', this.items[0].name || 'Multiple Services');
                    formData.append('custom_type', this.items[0].type || 'General');
                    formData.append('cost_price', this.items.reduce((sum, i) => sum + (parseFloat(i.cost) || 0), 0));
                }

                try {
                    const response = await fetch('{{ route('contracts.store') }}', {
                        method: 'POST',
                        body: formData,
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                    });
                    if (response.ok) {
                        const jsonStatus = await response.json();
                        if(jsonStatus.success) {
                            form.reset();
                            this.serviceType = 'Consulting';
                            this.operatorShare = '';
                            this.fetchStats();
                            this.speakUzbek("Shartnoma muvaffaqiyatli markaziy bazaga saqlandi.");
                        } else {
                            this.speakUzbek("Xatolik! " + jsonStatus.message);
                            alert("ERROR: " + jsonStatus.message);
                        }
                    } else {
                        let data = await response.json();
                        this.speakUzbek("Protokol xatosi yuz berdi.");
                        alert("CRITICAL ERROR: " + (data.message || 'Validation error'));
                    }
                } catch (error) {
                    this.speakUzbek("Tarmoq bilan aloqa uzildi.");
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
                        this.fetchStats();
                    } else {
                        alert(data.message || 'Xatolik');
                    }
                } catch (error) {
                    console.error(error);
                }
            },
            initiateFaceID() {
                this.faceIdOverlay = true;
                this.faceIdMessage = 'BOOTING OPTICAL CORE...';
                this.faceIdProgress = 15;
                this.speakUzbek("Yuzingizni kameraga to'g'rilang.");
                
                navigator.mediaDevices.getUserMedia({ video: { facingMode: "user" } })
                    .then(stream => {
                        const video = document.getElementById('webcam');
                        if (video) video.srcObject = stream;
                        this.faceIdMessage = 'SCANNING RETINAL PATTERNS...';
                        this.faceIdProgress = 45;
                        
                        video.onloadeddata = () => {
                            let secondsLeft = 30;
                            this.faceIdMessage = `SCANNING RETINAL PATTERNS (${secondsLeft}s)...`;
                            this.faceIdProgress = 20;

                            let countdownInterval = setInterval(async () => {
                                secondsLeft--;
                                this.faceIdProgress = 20 + ((30 - secondsLeft) * 2.3);
                                this.faceIdMessage = `SCANNING RETINAL PATTERNS (${secondsLeft}s)...`;

                                if (secondsLeft <= 0) {
                                    clearInterval(countdownInterval);
                                    this.faceIdProgress = 90;
                                    this.faceIdMessage = 'UPLOADING TO NEURAL NETWORK...';
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
                                            this.faceIdMessage = 'ACCESS GRANTED: ' + result.message;
                                            this.speakUzbek("Tashrifingiz tasdiqlandi. " + result.message);
                                            const tracks = stream.getTracks();
                                            tracks.forEach(track => track.stop());
                                            setTimeout(() => document.getElementById('startShiftForm').submit(), 2500);
                                        } else {
                                            this.faceIdMessage = 'ACCESS DENIED: ' + result.message;
                                            this.speakUzbek("Kirish bloklandi. " + result.message);
                                            this.faceIdProgress = 0;
                                            const tracks = stream.getTracks();
                                            tracks.forEach(track => track.stop());
                                            setTimeout(() => { this.faceIdOverlay = false; }, 3000);
                                        }
                                    } catch (e) {
                                        this.faceIdMessage = 'LINK ERROR: UPLINK LOST';
                                        this.speakUzbek("Aloqa uzildi.");
                                        const tracks = stream.getTracks();
                                        tracks.forEach(track => track.stop());
                                        setTimeout(() => { this.faceIdOverlay = false; }, 2000);
                                    }
                                }
                            }, 1000);
                        };
                    })
                    .catch(err => {
                        this.faceIdMessage = 'HARDWARE ERROR: CAMERA NOT FOUND';
                        this.speakUzbek("Kamera aniqlanmadi.");
                        setTimeout(() => { this.faceIdOverlay = false; }, 2000);
                    });
            },

            activateVoiceAI() {
                const phrases = [
                    "OBSIDIAN operatsion tizimi barcha sektorlarda barqaror ishlamoqda.",
                    "Sizning ish unumdorligingiz bugun yuqori darajada. Davom eting.",
                    "Barcha ma'lumotlar shifrlangan va xavfsiz holatda.",
                    "Mijozlar bilan aloqa kanallari ochiq. Men yordamga tayyorman."
                ];
                const randomPhrase = phrases[Math.floor(Math.random() * phrases.length)];
                this.speakUzbek(randomPhrase);
                
                // Audio visual feedback via orb pulse
            }
        }));
    });
</script>
@endsection
