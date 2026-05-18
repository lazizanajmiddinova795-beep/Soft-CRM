@extends('layouts.cyber')

@section('sidebar')
    @include('partials.admin_sidebar')
@endsection

@section('content')
<div x-data="adminDashboard()" class="w-full flex-1 flex flex-col gap-6">
    
    <!-- Top Stats Grid -->
    <div class="stats-grid">
        <!-- Total Treasury -->
        <div class="stat-card">
            <div class="stat-title">{{ __('messages.total_treasury') }}</div>
            <div class="flex items-center justify-between">
                <div class="stat-value"><span x-text="stats.totalTreasury">{{ number_format($totalTreasury, 0, '.', ' ') }}</span> <span class="text-sm opacity-50">UZS</span></div>
                <button @click="showFinanceModal = true" class="btn-ios btn-neon p-2">
                    <i class="fa-solid fa-plus"></i>
                </button>
            </div>
            <div class="mt-2 flex gap-4 text-xs font-semibold">
                <span class="text-green-400"><i class="fa-solid fa-arrow-up text-[10px]"></i> +<span x-text="stats.dailyIncome"></span></span>
                <span class="text-red-400"><i class="fa-solid fa-arrow-down text-[10px]"></i> -<span x-text="stats.dailyExpense"></span></span>
            </div>
        </div>

        <!-- Active Operators -->
        <div class="stat-card">
            <div class="stat-title">{{ __('messages.active_operators') }}</div>
            <div class="stat-value">
                <span x-text="stats.activeOperators">{{ $activeOperators }}</span> 
                <span class="text-sm opacity-50">/ <span x-text="stats.totalOperators">{{ $totalOperators }}</span></span>
            </div>
            <div class="mt-4 h-1.5 w-full bg-white/5 rounded-full overflow-hidden">
                <div class="h-full bg-cyan-400 shadow-[0_0_10px_rgba(0,255,204,0.5)] transition-all duration-1000" :style="'width: ' + getPercentage() + '%'"></div>
            </div>
        </div>

        <!-- AI Status -->
        <div class="stat-card" style="border-left: 4px solid var(--neon-purple); border-image: none;">
            <div class="stat-title">{{ __('messages.neural_ia_advice') }}</div>
            <div class="flex items-center gap-2 mb-2">
                <div class="w-2 h-2 rounded-full bg-purple-500 animate-pulse"></div>
                <span class="text-[10px] font-bold text-purple-400 uppercase tracking-widest">Active Analyser</span>
            </div>
            <div class="text-[13px] font-medium leading-tight text-white/80 uppercase italic" x-text="getAdvice()"></div>
        </div>

        <!-- System Status -->
        <div class="stat-card" style="border-left: 4px solid var(--neon-pink); border-image: none;">
            <div class="stat-title">System Uptime</div>
            <div class="stat-value text-xl">99.9%</div>
            <div class="text-[10px] mt-2 font-mono uppercase tracking-widest text-pink-400">Stable Matrix v1.0.4</div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="content-row flex-1 min-h-0">
        <!-- Ghost Feed / Logs -->
        <div class="glass-panel p-6 flex flex-col h-full min-h-[400px]">
            <div class="panel-title flex justify-between items-center w-full">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-terminal text-cyan-400"></i>
                    <span>{{ __('messages.ghost_log_feed') }}</span>
                </div>
                <a href="{{ route('admin.audit_logs.index') }}" class="text-[10px] font-bold text-cyan-400 hover:text-white uppercase tracking-widest border border-cyan-400/20 px-3 py-1 rounded-full transition-all">
                    Full Archive <i class="fa-solid fa-chevron-right ml-1 text-[8px]"></i>
                </a>
            </div>
            <ul class="flex-1 overflow-y-auto pr-2 slim-scroll space-y-4">
                <template x-for="(log, i) in stats.logs" :key="i">
                    <li class="p-3 rounded-2xl bg-white/5 border border-white/5 hover:bg-white/10 transition-all cursor-default">
                        <div class="flex justify-between items-center mb-1">
                            <span class="text-[10px] font-bold text-cyan-400/70" x-text="log.user"></span>
                            <span class="text-[10px] font-mono opacity-40 px-2 py-0.5 rounded-full bg-black/40" x-text="log.time"></span>
                        </div>
                        <div class="text-[13px] font-medium leading-relaxed">
                            <span class="text-purple-400" x-text="log.action"></span>
                            <span class="opacity-60 ml-1" x-text="log.details"></span>
                        </div>
                    </li>
                </template>
                <template x-if="stats.logs.length === 0">
                    <div class="flex flex-col items-center justify-center h-full opacity-30 italic">
                        <i class="fa-solid fa-ghost text-4xl mb-4"></i>
                        <p>{{ __('messages.no_logs_found') }}</p>
                    </div>
                </template>
            </ul>
        </div>

        <!-- FCC Verification Hub -->
        <div class="glass-panel p-6 flex flex-col h-full min-h-[400px]">
            <div class="panel-title justify-between">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-shield-halved text-purple-400"></i>
                    <span>FCC Verification</span>
                </div>
                <span class="text-[10px] font-bold bg-purple-500/20 text-purple-400 px-3 py-1 rounded-full border border-purple-500/30" x-text="stats.pendingVerifications + ' PENDING'"></span>
            </div>
            
            <div class="flex-1 overflow-y-auto slim-scroll space-y-4">
                <template x-if="stats.pendingList && stats.pendingList.length > 0">
                    <template x-for="ct in stats.pendingList" :key="ct.id">
                        <div class="p-4 rounded-2xl bg-white/5 border border-white/5 hover:border-purple-500/30 transition-all">
                            <div class="flex justify-between items-start mb-3">
                                <div>
                                    <h3 class="font-bold text-sm text-cyan-400" x-text="ct.contract_id"></h3>
                                    <p class="text-xs opacity-50" x-text="ct.client_name"></p>
                                </div>
                                <div class="text-right">
                                    <div class="font-bold text-sm" x-text="parseFloat(ct.amount).toLocaleString('uz-UZ') + ' UZS'"></div>
                                    <div class="text-[10px] text-green-400 font-bold" x-text="'+' + parseFloat(ct.amount - ct.cost_price).toLocaleString('uz-UZ')"></div>
                                </div>
                            </div>
                            <div class="flex items-center justify-between gap-3 pt-3 border-t border-white/5">
                                <span class="text-[10px] font-bold opacity-40 uppercase" x-text="'OP: ' + ct.user"></span>
                                <div class="flex gap-2">
                                    <button @click.prevent="handleContract(ct.id, 'approve')" class="btn-ios btn-neon text-[11px] px-4 py-1.5">Approve</button>
                                    <button @click.prevent="handleContract(ct.id, 'reject')" class="btn-ios text-[11px] px-4 py-1.5 hover:bg-red-500/20 hover:text-red-400">Reject</button>
                                </div>
                            </div>
                        </div>
                    </template>
                </template>
                <template x-if="!stats.pendingList || stats.pendingList.length === 0">
                    <div class="flex flex-col items-center justify-center h-full opacity-30">
                        <i class="fa-solid fa-circle-check text-4xl mb-4 text-green-400"></i>
                        <p class="text-sm font-bold uppercase tracking-widest">{{ __('messages.no_pending_tasks') }}</p>
                    </div>
                </template>
            </div>
            <a href="{{ route('admin.fcc.index') }}" class="mt-4 btn-ios text-center text-[11px] uppercase tracking-widest font-bold border border-white/10 hover:bg-white/5">Visualise FCC Registry Hub <i class="fa-solid fa-chevron-right ml-2 text-[8px]"></i></a>
        </div>
    </div>

    <!-- Quick Finance Modal -->
    <div x-show="showFinanceModal" class="fixed inset-0 z-[100] flex items-center justify-center bg-black/60 backdrop-blur-xl p-4" x-transition style="display: none;">
        <div class="glass-panel p-8 w-full max-w-md" @click.away="showFinanceModal = false">
            <h2 class="text-xl font-extrabold mb-6 flex items-center gap-3">
                <i class="fa-solid fa-vault text-cyan-400"></i>
                <span>G'azna Operatsiyasi</span>
            </h2>
            <form method="POST" action="{{ route('treasury.manual') }}" class="space-y-6">
                @csrf
                <div x-data="{ adminManualType: 'income' }" class="space-y-6">
                    <div class="grid grid-cols-2 gap-3">
                        <label class="cursor-pointer group">
                            <input type="radio" name="type" value="income" x-model="adminManualType" class="hidden peer">
                            <div class="text-center p-3 rounded-2xl border border-white/10 group-hover:border-green-500/50 peer-checked:bg-green-500/10 peer-checked:border-green-500 transition-all h-full flex flex-col justify-center items-center">
                                <i class="fa-solid fa-money-bill text-green-400 mb-1"></i>
                                <div class="text-[10px] font-bold uppercase tracking-widest text-green-400">Kirim (Naqd)</div>
                            </div>
                        </label>
                        <label class="cursor-pointer group">
                            <input type="radio" name="type" value="income_card" x-model="adminManualType" class="hidden peer">
                            <div class="text-center p-3 rounded-2xl border border-white/10 group-hover:border-blue-500/50 peer-checked:bg-blue-500/10 peer-checked:border-blue-500 transition-all h-full flex flex-col justify-center items-center">
                                <i class="fa-solid fa-credit-card text-blue-400 mb-1"></i>
                                <div class="text-[10px] font-bold uppercase tracking-widest text-blue-400">Kirim (Karta)</div>
                            </div>
                        </label>
                        <label class="cursor-pointer group">
                            <input type="radio" name="type" value="expense" x-model="adminManualType" class="hidden peer">
                            <div class="text-center p-3 rounded-2xl border border-white/10 group-hover:border-red-500/50 peer-checked:bg-red-500/10 peer-checked:border-red-500 transition-all h-full flex flex-col justify-center items-center">
                                <i class="fa-solid fa-arrow-turn-up text-red-400 mb-1"></i>
                                <div class="text-[10px] font-bold uppercase tracking-widest text-red-400">Chiqim</div>
                            </div>
                        </label>
                        <label class="cursor-pointer group">
                            <input type="radio" name="type" value="cash_to_card" x-model="adminManualType" class="hidden peer">
                            <div class="text-center p-3 rounded-2xl border border-white/10 group-hover:border-cyan-500/50 peer-checked:bg-cyan-500/10 peer-checked:border-cyan-500 transition-all h-full flex flex-col justify-center items-center">
                                <i class="fa-solid fa-rotate text-cyan-400 mb-1"></i>
                                <div class="text-[10px] font-bold uppercase tracking-widest text-cyan-400">Naqd -> Karta</div>
                            </div>
                        </label>
                    </div>
                    
                    <div x-show="['expense'].includes(adminManualType)" x-transition>
                        <select name="payment_method" :disabled="!['expense'].includes(adminManualType)" class="w-full bg-white/5 border border-white/10 rounded-2xl p-4 text-white focus:border-cyan-400/50 outline-none transition-all">
                            <option value="cash" class="bg-black">💵 Naqd pul orqali</option>
                            <option value="card" class="bg-black">💳 Karta orqali</option>
                        </select>
                    </div>

                <div>
                   <input type="number" name="amount" required placeholder="Miqdor (UZS)" class="w-full bg-white/5 border border-white/10 rounded-2xl p-4 text-white focus:border-cyan-400/50 outline-none transition-all placeholder:text-white/20">
                </div>
                <div>
                    <textarea name="description" required placeholder="Operatsiya sababi..." class="w-full bg-white/5 border border-white/10 rounded-2xl p-4 h-28 text-white focus:border-cyan-400/50 outline-none transition-all placeholder:text-white/20 resize-none text-sm"></textarea>
                </div>
                <div class="flex gap-3">
                    <button type="button" @click="showFinanceModal = false" class="flex-1 btn-ios">Bekor Qilish</button>
                    <button type="submit" class="flex-1 btn-ios btn-neon">Tasdiqlash</button>
                </div>
                </div> <!-- End x-data space-y-6 -->

            </form>
        </div>
    </div>

    <!-- AI Chat Toggle Button -->
    <button @click="showAIChat = true" class="fixed bottom-6 right-6 w-14 h-14 rounded-full bg-gradient-to-br from-cyan-500 to-purple-600 shadow-[0_0_30px_rgba(0,255,204,0.5)] flex items-center justify-center text-white text-2xl z-50 hover:scale-110 transition-transform active:scale-95">
        <i class="fa-solid fa-sparkles"></i>
    </button>

    <!-- Interactive AI Chat HUD -->
    <div x-show="showAIChat" class="fixed bottom-24 right-6 w-96 glass-panel border-purple-500/30 flex flex-col shadow-2xl z-[100]" x-transition style="display: none; height: 500px;">
        <div class="p-4 border-b border-white/10 flex justify-between items-center bg-white/5">
            <div class="flex items-center gap-3">
                <div class="w-2 h-2 rounded-full bg-green-400 animate-pulse"></div>
                <span class="text-xs font-bold uppercase tracking-widest italic">Neural Link: Online</span>
            </div>
            <button @click="showAIChat = false" class="text-white/50 hover:text-white transition-colors">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <div id="ai-chat-body" class="flex-1 overflow-y-auto p-4 space-y-4 slim-scroll bg-black/20">
            <template x-for="(msg, i) in aiMessages" :key="i">
                <div :class="msg.role === 'ai' ? 'items-start' : 'items-end'" class="flex flex-col gap-1 w-full">
                    <div :class="msg.role === 'ai' ? 'bg-purple-500/10 text-purple-200 border-purple-500/20' : 'bg-cyan-500/10 text-cyan-200 border-cyan-500/20'" class="max-w-[85%] p-3 rounded-2xl border text-[13px] leading-relaxed" x-text="msg.text"></div>
                    <span class="text-[9px] font-bold opacity-30 uppercase ml-2 mr-2" x-text="msg.role === 'ai' ? 'System Intel' : 'Admin'"></span>
                </div>
            </template>
            <div x-show="aiTyping" class="flex items-center gap-2 text-purple-400">
                <div class="dot-typing"></div>
                <span class="text-[10px] font-bold uppercase italic">Processing neural patterns...</span>
            </div>
        </div>
        <div class="p-4 border-t border-white/10 bg-white/5">
            <div class="relative">
                <input type="text" x-model="aiInput" @keydown.enter="sendUIToAI" placeholder="Ask Gemini Tactical Advice..." class="w-full bg-black/40 border border-white/10 rounded-2xl p-4 pr-12 text-sm text-white focus:border-purple-500/50 outline-none transition-all placeholder:text-white/20 font-medium">
                <button @click="sendUIToAI" class="absolute right-4 top-4 text-purple-400 hover:text-white transition-colors">
                    <i class="fa-solid fa-paper-plane"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    .dot-typing {
        width: 4px; height: 4px; border-radius: 5px; background-color: var(--neon-purple); display: inline-block;
        animation: dotTyping 1.5s infinite linear;
    }
    @keyframes dotTyping { 0% { box-shadow: 0 0 0 0 white; } 50% { box-shadow: 6px 0 0 0 white, 12px 0 0 0 white; } 100% { box-shadow: 0 0 0 0 white; } }
</style>

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
            aiMessages: [
                { role: 'ai', text: "Greeting, Admin. All tactical modules online. Treasury is at " + '{{ number_format($totalTreasury, 0, ".", " ") }}' + " UZS. I am ready to process your next command." }
            ],
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
                        if (result.print_url) window.open(result.print_url, "_blank", "width=400,height=600 scrollbars=yes");
                        this.fetchStats(); 
                    } else {
                        alert(result.message);
                    }
                } catch (e) {
                    console.error('Contract Error:', e);
                }
            },
            
            init() {
                setInterval(() => this.fetchStats(), 60000);
            },

            addAIMessage(text) {
                this.aiMessages.push({ role: 'ai', text: text });
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
                this.$nextTick(() => { const el = document.getElementById('ai-chat-body'); if(el) el.scrollTop = el.scrollHeight; });

                try {
                    let response = await fetch('{{ route("admin.ai.chat") }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                        body: JSON.stringify({ message: userText })
                    });
                    
                    if(response.ok) {
                        let data = await response.json();
                        this.aiTyping = false;
                        this.addAIMessage(data.reply);
                    } else {
                        this.aiTyping = false;
                        this.addAIMessage("Neural link failure. Try refreshing your link.");
                    }
                } catch (e) {
                    this.aiTyping = false;
                    this.addAIMessage("Connection unstable.");
                }
            },
            
            async fetchStats() {
                try {
                    let response = await fetch('{{ route("admin.stats") }}', {
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    if(response.ok) {
                        this.stats = await response.json();
                    }
                } catch (e) {}
            },
            
            getPercentage() {
                return (this.stats.totalOperators > 0) ? (this.stats.activeOperators/this.stats.totalOperators)*100 : 0;
            },
            
            getAdvice() {
                if(this.stats.pendingVerifications > 5) return "Warning: Verification bottleneck detected in FCC Hub.";
                if(this.stats.activeOperators == 0) return "Alert: All nodes offline. System at standstill.";
                return "System optimal. Neural patterns stable.";
            }
        }));
    });
</script>
@endsection
