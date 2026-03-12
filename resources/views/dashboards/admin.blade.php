@extends('layouts.cyber')

@section('sidebar')
    @include('partials.admin_sidebar')
@endsection

@section('content')
<div x-data="adminDashboard()" class="w-full flex-1 flex flex-col gap-4">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-2 shrink-0">
        <!-- Total Balance Box -->
        <div class="cyber-panel p-4 border-b-2" style="border-bottom-color: var(--active-color);">
            <h3 class="text-sm font-mono mb-1 uppercase tracking-widest opacity-80 text-[var(--active-color)]">{{ __('messages.total_treasury') }}</h3>
            <p class="text-2xl font-bold font-orbitron text-[var(--active-color)] drop-shadow-[0_0_10px_var(--active-color)] flex justify-between items-center">
                <span><span x-text="stats.totalTreasury">{{ number_format($totalTreasury, 0, '.', ' ') }}</span> <span class="text-sm opacity-50">UZS</span></span>
                <button @click="showFinanceModal = true" class="p-1 border border-[var(--active-color)] hover:bg-[var(--active-color)] hover:text-black transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                </button>
            </p>
            <div class="mt-2 flex justify-between text-sm font-mono opacity-80">
                <span class="text-green-500">+<span x-text="stats.dailyIncome"></span></span>
                <span class="text-red-500">-<span x-text="stats.dailyExpense"></span></span>
            </div>
        </div>

        <!-- Active Agents Box -->
        <div class="cyber-panel p-4 border-b-2" style="border-bottom-color: var(--active-color);">
            <h3 class="text-sm font-mono mb-1 uppercase tracking-widest opacity-80 text-[var(--active-color)]">{{ __('messages.active_operators') }}</h3>
            <p class="text-2xl font-bold font-orbitron text-[var(--active-color)] leading-none">
                <span x-text="stats.activeOperators">{{ $activeOperators }}</span> 
                <span class="text-xs opacity-50 text-[var(--text-color)]">/ <span x-text="stats.totalOperators">{{ $totalOperators }}</span></span>
            </p>
            <div class="mt-3 flex gap-2 w-full bg-black bg-opacity-50 h-1 rounded-full overflow-hidden">
                <div class="bg-[var(--active-color)] transition-all duration-1000 shadow-[0_0_10px_var(--active-color)]" :style="'width: ' + getPercentage() + '%'"></div>
            </div>
        </div>

        <!-- AI Tactical Advice -->
        <div class="cyber-panel p-4 border-b-2" style="border-bottom-color: var(--cyber-yellow);">
            <div class="flex justify-between items-center mb-1">
                <h3 class="text-sm font-mono uppercase tracking-widest text-[var(--cyber-yellow)]">{{ __('messages.neural_ia_advice') }}</h3>
                <span class="animate-pulse text-[var(--cyber-yellow)]">●</span>
            </div>
            <p class="text-xs border-l border-[var(--cyber-yellow)] pl-2 font-mono leading-tight text-[var(--text-color)] opacity-80 uppercase line-clamp-2" x-text="getAdvice()"></p>
        </div>

        <!-- System Integrity / Health -->
        <div class="cyber-panel p-4 border-b-2" style="border-bottom-color: #00ff00;">
            <div class="flex justify-between items-center mb-1">
                <h3 class="text-sm font-mono uppercase tracking-widest text-green-500">System Integrity</h3>
                <span class="animate-pulse text-green-500 text-xs">● ONLINE</span>
            </div>
            <div class="grid grid-cols-2 gap-2 mt-2 font-mono text-xs uppercase">
                <div class="flex flex-col">
                    <span class="opacity-50">Engine:</span>
                    <span class="text-white">v1.0.4 stable</span>
                </div>
                <div class="flex flex-col">
                    <span class="opacity-50">DB Latency:</span>
                    <span class="text-white">12ms</span>
                </div>
                <div class="flex flex-col">
                    <span class="opacity-50">Storage:</span>
                    <span class="text-white">4.2GB / 50GB</span>
                </div>
                <div class="flex flex-col">
                    <span class="opacity-50">Uptime:</span>
                    <span class="text-white text-xs">99.9% (324h)</span>
                </div>
            </div>
            <div class="mt-2 h-0.5 bg-green-900 bg-opacity-30 relative overflow-hidden">
                <div class="absolute inset-0 bg-green-500 animate-[scan_2s_linear_infinite]" style="width: 20px;"></div>
            </div>
            <style>
                @keyframes scan { 0% { left: -20px; } 100% { left: 100%; } }
            </style>
        </div>
    </div>

    <!-- Quick Finance Modal -->
    <div x-show="showFinanceModal" class="fixed inset-0 z-[100] flex items-center justify-center bg-black bg-opacity-90 backdrop-blur-md" x-transition style="display: none;">
        <div class="cyber-panel p-6 w-full max-w-md border border-[var(--active-color)]" @click.away="showFinanceModal = false">
            <h2 class="font-orbitron font-bold text-[var(--active-color)] border-b border-[var(--active-color)] border-opacity-30 pb-2 mb-4 uppercase tracking-widest">G'azna Operatsiyasi</h2>
            <form method="POST" action="{{ route('treasury.manual') }}" class="space-y-4 font-mono">
                @csrf
                <div class="flex gap-2">
                    <label class="flex-1">
                        <input type="radio" name="type" value="income" checked class="hidden peer">
                        <div class="text-center p-2 border border-gray-700 peer-checked:border-green-500 peer-checked:text-green-500 transition-all cursor-pointer text-xs uppercase font-bold">Kirim (+)</div>
                    </label>
                    <label class="flex-1">
                        <input type="radio" name="type" value="expense" class="hidden peer">
                        <div class="text-center p-2 border border-gray-700 peer-checked:border-red-500 peer-checked:text-red-500 transition-all cursor-pointer text-xs uppercase font-bold">Chiqim (-)</div>
                    </label>
                </div>
                <div>
                    <input type="number" name="amount" required placeholder="MIQDOR (UZS)" class="w-full bg-black border border-gray-800 p-2 text-[var(--active-color)] outline-none focus:border-[var(--active-color)]">
                </div>
                <div>
                    <textarea name="description" required placeholder="IZOH / OPERATSIYA SABABI" class="w-full bg-black border border-gray-800 p-2 h-20 text-[var(--active-color)] outline-none focus:border-[var(--active-color)] text-xs resize-none"></textarea>
                </div>
                <div class="flex gap-2 pt-2">
                    <button type="button" @click="showFinanceModal = false" class="flex-1 py-2 border border-gray-700 text-gray-500 uppercase font-bold text-xs hover:bg-gray-800 transition-all tracking-widest">BEKOR</button>
                    <button type="submit" class="flex-1 py-2 bg-[var(--active-color)] text-black uppercase font-bold text-xs shadow-[0_0_15px_var(--active-color)] tracking-widest">TASDIQLASH</button>
                </div>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 flex-1 min-h-0">
        <div class="cyber-panel p-6 flex flex-col h-full">
            <h2 class="text-sm font-orbitron mb-4 border-b border-[var(--active-color)] border-opacity-30 pb-2 text-[var(--active-color)] uppercase tracking-widest">{{ __('messages.ghost_log_feed') }}</h2>
            <ul class="space-y-3 font-mono text-sm overflow-y-auto flex-1 pr-2 slim-scroll">
                <template x-for="(log, i) in stats.logs" :key="i">
                    <li class="flex flex-col gap-1 pb-2 border-b border-gray-800 transition-all hover:bg-gray-500 hover:bg-opacity-10 p-2 rounded-sm cursor-default">
                        <div class="flex justify-between text-sm text-[var(--text-color)] opacity-50">
                            <span x-text="log.time"></span>
                        </div>
                        <div class="text-[var(--text-color)] leading-tight">
                            <span class="text-[var(--active-color)] font-bold">> </span>
                            <span class="text-[var(--electric-blue)]" x-text="log.user"></span>: 
                            <span class="text-[var(--cyber-yellow)]" x-text="log.action"></span>
                            <span class="opacity-70 ml-1" x-text="log.details"></span>
                        </div>
                    </li>
                </template>
                <template x-if="stats.logs.length === 0">
                    <li class="opacity-50 text-[var(--text-color)] uppercase text-center mt-10">{{ __('messages.no_logs_found') }}</li>
                </template>
            </ul>
        </div>

        <div class="cyber-panel p-6 flex flex-col relative h-full max-h-[400px]">
            <h2 class="text-sm font-orbitron mb-4 border-b border-[var(--cyber-yellow)] border-opacity-30 pb-2 w-full text-left self-start text-[var(--cyber-yellow)] uppercase tracking-widest flex justify-between">
                <span>{{ __('messages.pending_verifications') }} (FCC)</span>
                <span class="text-[var(--text-color)] opacity-70" x-text="'JAMI: ' + stats.pendingVerifications"></span>
            </h2>
            <div class="flex-1 overflow-y-auto slim-scroll space-y-3 font-mono text-sm w-full">
                <template x-if="stats.pendingList && stats.pendingList.length > 0">
                    <template x-for="ct in stats.pendingList" :key="ct.id">
                        <div class="p-3 border border-[var(--active-color)] border-opacity-30 bg-[var(--input-bg)] flex flex-col lg:flex-row justify-between lg:items-center gap-2 transition-colors hover:bg-[var(--hover-bg)]">
                            <div>
                                <h3 class="font-bold text-[12px] uppercase text-[var(--cyber-yellow)]" x-text="ct.contract_id"></h3>
                                <p class="text-[var(--text-color)] opacity-80 font-bold" x-text="ct.service + ' • ' + parseFloat(ct.amount).toLocaleString('uz-UZ') + ' UZS'"></p>
                                <p class="text-[var(--cyber-yellow)] opacity-70 text-xs font-bold" x-text="'FOYDA: ' + parseFloat(ct.amount - ct.cost_price).toLocaleString('uz-UZ') + ' UZS'"></p>
                                <p class="text-[var(--text-color)] opacity-50 uppercase mt-1" x-text="'Mijoz: ' + ct.client_name + ' | OP: ' + (ct.user ? ct.user : ct.user_id)"></p>
                            </div>
                            <div class="flex gap-2">
                                <button @click.prevent="handleContract(ct.id, 'approve')" class="px-2 py-1 bg-[var(--active-color)] text-black border border-transparent font-bold uppercase transition-all hover:bg-transparent hover:border-[var(--active-color)] hover:text-[var(--active-color)] text-xs shadow-[0_0_5px_var(--active-color)] hover:shadow-none">Tasdiqlash</button>
                                <button @click.prevent="handleContract(ct.id, 'reject')" class="px-2 py-1 border border-red-500 text-red-500 hover:bg-red-500 hover:text-black transition-colors font-bold uppercase text-xs">Rad etish</button>
                            </div>
                        </div>
                    </template>
                </template>
                <template x-if="!stats.pendingList || stats.pendingList.length === 0">
                    <div class="flex flex-col items-center justify-center h-full opacity-50 text-[var(--active-color)] mt-10">
                        <svg class="w-12 h-12 mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <p class="font-mono tracking-widest text-xs uppercase">{{ __('messages.no_pending_tasks') }}</p>
                    </div>
                </template>
            </div>
            <a href="{{ route('admin.fcc.index') }}" class="mt-4 block text-center text-sm w-full text-[var(--electric-blue)] font-bold tracking-widest uppercase hover:text-white transition-colors underline opacity-70 hover:opacity-100">Barcha FCC Bazasi >></a>
        </div>
    <!-- Interactive AI Chat HUD -->
    <div x-show="showAIChat" class="fixed bottom-6 right-6 w-80 z-[100] cyber-panel border border-[var(--active-color)] flex flex-col shadow-[0_0_30px_rgba(0,0,0,0.8)]" x-transition style="display: none; height: 400px;">
        <div class="p-3 border-b border-[var(--active-color)] bg-[var(--active-color)] text-black font-bold font-orbitron text-sm flex justify-between items-center uppercase tracking-widest">
            <span>Neural Assistant Online</span>
            <button @click="showAIChat = false" class="hover:text-white transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <div id="ai-chat-body" class="flex-1 overflow-y-auto p-3 space-y-3 font-mono text-sm slim-scroll bg-black bg-opacity-90">
            <template x-for="(msg, i) in aiMessages" :key="i">
                <div :class="msg.role === 'ai' ? 'text-[var(--active-color)]' : 'text-[var(--cyber-yellow)] text-right'" class="break-words">
                    <span class="opacity-50" x-text="msg.role === 'ai' ? '[SYSTEM]: ' : '[ADMIN]: '"></span>
                    <span x-text="msg.text" class="leading-relaxed"></span>
                </div>
            </template>
            <div x-show="aiTyping" class="text-[var(--active-color)] animate-pulse">
                [SYSTEM]: Matn tahlil qilinmoqda...
            </div>
        </div>
        <div class="p-2 border-t border-[var(--active-color)] bg-black">
            <div class="relative">
                <input type="text" x-model="aiInput" @keydown.enter="sendUIToAI" placeholder="Buyruq kiriting..." class="w-full bg-black border border-gray-800 p-2 pr-10 text-[var(--active-color)] text-sm outline-none focus:border-[var(--active-color)] placeholder-opacity-30">
                <button @click="sendUIToAI" class="absolute right-2 top-2 text-[var(--active-color)] hover:text-white">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('adminDashboard', () => ({
            stats: {
                totalTreasury: '{{ number_format($totalTreasury, 0, ".", " ") }}',
                dailyIncome: '{{ number_format($dailyIncome, 0, ".", " ") }}',
                dailyExpense: '{{ number_format($dailyExpense, 0, ".", " ") }}',
                activeOperators: {{ $activeOperators }},
                totalOperators: {{ $totalOperators }},
                pendingVerifications: {{ $pendingVerifications }},
                pendingList: {!! json_encode($pendingContracts->map(function($ct) {
                    return [
                        'id' => $ct->id,
                        'contract_id' => $ct->contract_id,
                        'client_name' => $ct->client_name,
                        'amount' => $ct->amount,
                        'cost_price' => $ct->cost_price,
                        'service' => $ct->service->name ?? 'Custom',
                        'user' => $ct->user->name ?? 'Operator'
                    ];
                })) !!},
                logs: {!! json_encode($logs) !!}
            },
            showFinanceModal: false,
            voiceEnabled: localStorage.getItem('voice_enabled') === 'true',
            showAIChat: false,
            aiInput: '',
            aiMessages: [],
            aiTyping: false,
            
            async handleContract(contractId, action) {
                if (!confirm('Haqiqatdan ham bu amalni bajarmoqchimisiz?')) return;
                try {
                    const response = await fetch(`/contracts/${contractId}/${action}`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    });
                    const result = await response.json();
                    if (result.success) {
                        if (result.print_url) window.open(result.print_url, "_blank", "width=400,height=600,noopener,noreferrer");
                        setTimeout(() => {
                            alert(result.message);
                            this.fetchStats(); 
                        }, 500);
                    } else {
                        alert(result.message);
                    }
                } catch (e) {
                    console.error('Contract Action Error:', e);
                    alert('Tizimda xatolik yuz berdi.');
                }
            },
            
            init() {
                setInterval(() => {
                    this.fetchStats();
                }, 4000); // Polling every 4 seconds
            },

            toggleVoice() {
                this.voiceEnabled = !this.voiceEnabled;
                localStorage.setItem('voice_enabled', this.voiceEnabled);
                if(this.voiceEnabled) {
                    window.speakUzbekGlobal("Ovozli yordamchi yoqildi. Men sizga xizmat qilishga tayyorman.");
                }
            },

            startVoiceChat() {
                this.showAIChat = true;
                this.addAIMessage("Assalomu alaykum Admin. Bugungi g'azna holati " + this.stats.totalTreasury + " so'mni tashkil etmoqda. " + this.stats.activeOperators + " ta operator onlayn rejimda ishlamoqda. Men yana qanday yordamlashishim mumkin?");
            },

            addAIMessage(text) {
                this.aiMessages.push({ role: 'ai', text: text });
                if(this.voiceEnabled) {
                    window.speakUzbekGlobal(text);
                }
                this.$nextTick(() => {
                    const el = document.getElementById('ai-chat-body');
                    if(el) el.scrollTop = el.scrollHeight;
                });
            },

            async sendUIToAI() {
                if(!this.aiInput.trim()) return;
                
                const userText = this.aiInput;
                this.aiMessages.push({ role: 'user', text: userText });
                this.aiInput = '';
                this.aiTyping = true;

                try {
                    let response = await fetch('{{ route("admin.ai.chat") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({ message: userText })
                    });
                    
                    if(response.ok) {
                        let data = await response.json();
                        this.aiTyping = false;
                        this.addAIMessage(data.reply);
                    } else {
                        this.aiTyping = false;
                        this.addAIMessage("Neural link error: Tizimga ulanib bo'lmadi.");
                    }
                } catch (e) {
                    this.aiTyping = false;
                    console.error("AI fetch failed", e);
                    this.addAIMessage("Ulanishda xatolik yuz berdi.");
                }
            },
            
            async fetchStats() {
                try {
                    let response = await fetch('{{ route("admin.stats") }}', {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    if(response.ok) {
                        this.stats = await response.json();
                    } else if(response.status === 401) {
                        window.location.href = '/';
                    }
                } catch (e) {
                    console.error("Real-time fetch failed", e);
                }
            },
            
            getPercentage() {
                if (this.stats.totalOperators > 0) {
                    return (this.stats.activeOperators / this.stats.totalOperators) * 100;
                }
                return 0;
            },
            
            getAdvice() {
                if(this.stats.pendingVerifications > 5) {
                    return '{!! addslashes(__("messages.ia_warning_bottleneck")) !!}';
                } else if(this.stats.activeOperators == 0) {
                    return '{!! addslashes(__("messages.ia_warning_offline")) !!}';
                } else {
                    return '{!! addslashes(__("messages.ia_normal")) !!}';
                }
            }
        }));
    });
</script>
@endsection
