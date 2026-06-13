@extends('layouts.cyber')

@section('sidebar')
    @include('partials.cashier_sidebar')
@endsection

@section('content')
<script>
    document.addEventListener('alpine:init', () => {
        if (!Alpine.data('cashierDashboard')) {
            Alpine.data('cashierDashboard', () => ({
                faceIdOverlay: false,
                showPinFallback: false,
                faceIdMessage: 'KAMERA INITALIZATSIYASI...',
                faceIdProgress: 0,
                showZReport: false,
                shiftSeconds: {{ $shiftDurationSeconds ?? 0 }},
                shiftDuration: '00:00:00',
                
                init() {
                    if ({{ $activeShift ? 'true' : 'false' }}) {
                        this.updateShiftDuration();
                        setInterval(() => {
                            this.shiftSeconds++;
                            this.updateShiftDuration();
                        }, 1000);
                    }
                },
                
                updateShiftDuration() {
                    let delta = this.shiftSeconds;
                    let h = Math.floor(delta / 3600);
                    let m = Math.floor((delta % 3600) / 60);
                    let s = Math.floor(delta % 60);
                    this.shiftDuration = [h, m, s].map(v => v < 10 ? "0" + v : v).join(":");
                },
                
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
                            if (result.print_url) {
                                window.open(result.print_url, "_blank", "width=400,height=600,noopener,noreferrer");
                            }
                            setTimeout(() => {
                                alert(result.message);
                                location.reload(); 
                            }, 500);
                        } else {
                            alert(result.message);
                        }
                    } catch (e) {
                        console.error('Contract Action Error:', e);
                        alert('Tizimda xatolik yuz berdi.');
                    }
                },

                captureFrame(video) {
                    const canvas = document.createElement('canvas');
                    canvas.width = video.videoWidth;
                    canvas.height = video.videoHeight;
                    canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);
                    return canvas.toDataURL('image/jpeg', 0.8);
                },
                
                initiateFaceID() {
                    this.faceIdOverlay = true;
                    this.faceIdMessage = 'KAMERA INITALIZATSIYASI...';
                    this.faceIdProgress = 10;
                    if(window.speakUzbekGlobal) window.speakUzbekGlobal("Yuzingizni kameraga tuting");
                    
                    navigator.mediaDevices.getUserMedia({ video: { facingMode: "user" } })
                        .then(stream => {
                            const video = document.getElementById('webcam');
                            if (video) video.srcObject = stream;
                            
                            this.faceIdMessage = 'NEYRON TARMOQ KO\'RMOQDA...';
                            this.faceIdProgress = 40;
                            
                            video.onloadeddata = () => {
                                let secondsLeft = 30;
                                this.faceIdMessage = `YUZNI ANIQLASH: ${secondsLeft} SEKUND...`;
                                this.faceIdProgress = 10;

                                let countdownInterval = setInterval(async () => {
                                    secondsLeft--;
                                    this.faceIdProgress = 10 + ((30 - secondsLeft) * 2.5);
                                    this.faceIdMessage = `YUZNI ANIQLASH: ${secondsLeft} SEKUND...`;

                                    if (secondsLeft <= 0) {
                                        clearInterval(countdownInterval);
                                        this.faceIdProgress = 90;
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
                                                if(window.speakUzbekGlobal) window.speakUzbekGlobal(result.message);
                                                
                                                const tracks = stream.getTracks();
                                                tracks.forEach(track => track.stop());
                                                
                                                setTimeout(() => {
                                                    document.getElementById('startShiftForm').submit();
                                                }, 2500); 
                                            } else {
                                                this.faceIdMessage = 'RAD ETILDI: ' + result.message;
                                                if(window.speakUzbekGlobal) window.speakUzbekGlobal("Kirish rad etildi. " + result.message);
                                                this.faceIdProgress = 0;
                                                stream.getTracks().forEach(t => t.stop());
                                                setTimeout(() => { this.faceIdOverlay = false; }, 3000);
                                            }
                                        } catch (e) {
                                            this.faceIdMessage = 'AI YADROSI BILAN ALOQA UZILDI';
                                            if(window.speakUzbekGlobal) window.speakUzbekGlobal("Tarmoq yoki yadro bilan aloqa uzildi");
                                            stream.getTracks().forEach(t => t.stop());
                                            setTimeout(() => { this.faceIdOverlay = false; }, 2000);
                                        }
                                    }
                                }, 1000);
                            };
                        })
                        .catch(err => {
                            console.error('Face ID Error:', err);
                            this.faceIdMessage = 'KAMERAGA RUXSAT YO\'Q. BEKOR QILINMOQDA...';
                            if(window.speakUzbekGlobal) window.speakUzbekGlobal("Kameraga ruxsat etilmadi.");
                            setTimeout(() => { this.faceIdOverlay = false; }, 2000);
                        });
                }
            }));
        }
    });
</script>

@if(session('print_receipt'))
<script>
    window.open("{{ route('contracts.print', session('print_receipt')) }}", "ReceiptPrint", "width=400,height=600,noopener,noreferrer");
</script>
@endif
<div x-data="cashierDashboard()" class="flex-1 min-h-0 flex flex-col h-full font-mono text-sm relative">

<!-- Navigation Tabs (Compact) -->
<div class="flex gap-2 mb-3 bg-black/40 p-1 rounded-xl border border-white/5 shrink-0">
    <a href="{{ route('cashier.dashboard', ['tab' => 'dashboard']) }}" class="flex-1 py-1.5 px-3 rounded-lg text-center text-[10px] font-bold uppercase transition-all {{ $currentTab == 'dashboard' ? 'bg-cyan-500 text-black shadow-lg shadow-cyan-500/20' : 'text-white/40 hover:text-white hover:bg-white/5' }}">Command</a>
    <a href="{{ route('cashier.dashboard', ['tab' => 'transactions']) }}" class="flex-1 py-1.5 px-3 rounded-lg text-center text-[10px] font-bold uppercase transition-all {{ $currentTab == 'transactions' ? 'bg-cyan-500 text-black shadow-lg shadow-cyan-500/20' : 'text-white/40 hover:text-white hover:bg-white/5' }}">History</a>
    <a href="{{ route('cashier.dashboard', ['tab' => 'reports']) }}" class="flex-1 py-1.5 px-3 rounded-lg text-center text-[10px] font-bold uppercase transition-all {{ $currentTab == 'reports' ? 'bg-cyan-500 text-black shadow-lg shadow-cyan-500/20' : 'text-white/40 hover:text-white hover:bg-white/5' }}">Fleet</a>
</div>

@if($currentTab == 'dashboard')
<div class="grid grid-cols-2 md:grid-cols-6 gap-3 mb-3 shrink-0">
    <!-- Shift Control Card (Compact) -->
    <div class="md:col-span-1 cyber-panel p-3 flex flex-col justify-center items-center border border-white/10 rounded-xl">
        @if(!$activeShift)
            <button @click="initiateFaceID()" class="w-full py-2 bg-cyan-500 text-black font-black text-[9px] uppercase rounded-lg hover:bg-cyan-400 transition-all">{{ __('messages.start_shift') ?? 'START SHIFT' }}</button>
        @else
            <div class="text-[9px] text-cyan-400 font-bold uppercase opacity-60 mb-1">Live: <span x-text="shiftDuration"></span></div>
            <div class="flex gap-1 w-full">
                <button @click="showZReport = true" class="flex-1 py-1.5 bg-red-500/20 text-red-500 border border-red-500/30 text-[8px] font-black uppercase rounded">{{ __('messages.z_report') ?? 'Z-REP' }}</button>
            </div>
        @endif
    </div>

    <!-- Stats Matrix (Mini) -->
    <div class="stat-card p-2 border-l-2 border-cyan-400 bg-white/5">
        <div class="text-[8px] text-white/40 uppercase font-black">{{ __('messages.daily_rev') ?? 'Daily Rev' }}</div>
        <div class="text-base font-black text-white">{{ number_format($dailyReceipts, 0, ',', ' ') }}</div>
    </div>
    <div class="stat-card p-2 border-l-2 border-yellow-400 bg-white/5">
        <div class="text-[8px] text-white/40 uppercase font-black">{{ __('messages.net_profit') ?? 'Net Profit' }}</div>
        <div class="text-base font-black text-white">{{ number_format($dailyProfit, 0, ',', ' ') }}</div>
    </div>
    <div class="stat-card p-2 border-l-2 border-red-500 bg-white/5">
        <div class="text-[8px] text-white/40 uppercase font-black">{{ __('messages.exp_out') ?? 'Exp/Out' }}</div>
        <div class="text-base font-black text-white">{{ number_format($manualExpenses, 0, ',', ' ') }}</div>
    </div>
    <div class="stat-card p-2 border-l-2 border-emerald-400 bg-white/5">
        <div class="text-[8px] text-emerald-400 uppercase font-black">{{ __('messages.vault') ?? 'Vault' }}</div>
        <div class="text-base font-black text-emerald-400">{{ number_format($vault, 0, ',', ' ') }}</div>
    </div>
    <div class="stat-card p-2 border-l-2 border-blue-400 bg-white/5">
        <div class="text-[8px] text-blue-400 uppercase font-black">{{ __('messages.card_vault') ?? 'Card Vault' }}</div>
        <div class="text-base font-black text-blue-400">{{ number_format($cardVault ?? 0, 0, ',', ' ') }}</div>
    </div>
</div>

@if($activeShift)
    <!-- Z-Report Modal -->
    <div x-show="showZReport" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-95 backdrop-blur-md">
        <div class="cyber-panel p-8 max-w-md w-full border border-red-500 shadow-[0_0_30px_rgba(255,0,0,0.2)]">
            <h2 class="text-2xl font-orbitron font-bold text-red-500 uppercase tracking-widest border-b border-red-500 border-opacity-30 pb-2 mb-4 text-center">Z-HISOBOT (FINAL)</h2>
            <div class="font-mono text-xs space-y-4 mb-6">
                <p class="text-center opacity-70 mb-4 text-[var(--text-color)] text-sm">Smenani yakunlash va kassani topshirish varaqasi</p>
                <div class="flex justify-between border-b border-gray-800 pb-2 text-[var(--text-color)]">
                    <span>Umumiy Kirim:</span> <span class="text-green-500 font-bold">+{{ number_format($dailyReceipts, 0, ',', ' ') }} UZS</span>
                </div>
                <div class="flex justify-between border-b border-gray-800 pb-2 text-[var(--text-color)]">
                    <span>Sof Foyda:</span> <span class="text-[var(--cyber-yellow)] font-bold">{{ number_format($dailyProfit, 0, ',', ' ') }} UZS</span>
                </div>
                <div class="flex justify-between border-b border-gray-800 pb-2 text-[var(--text-color)]">
                    <span>Jami Chiqim:</span> <span class="text-red-500 font-bold">-{{ number_format($manualExpenses, 0, ',', ' ') }} UZS</span>
                </div>
                <div class="flex justify-between text-base pt-2 border-t border-[var(--active-color)] border-opacity-30">
                    <span class="text-[var(--active-color)] font-orbitron uppercase">Kassa Qoldig'i:</span> 
                    <span class="text-[var(--active-color)] font-bold">{{ number_format($vault, 0, ',', ' ') }} UZS</span>
                </div>
            </div>
            <div class="bg-red-500 bg-opacity-10 border border-red-500 border-opacity-30 p-3 text-sm text-red-500 text-center mb-6 uppercase tracking-widest">
                KASSA PULINI SANAB TOPSHIRING VA CHIQUVCHI SIGNALNI KUTING.
            </div>
            <div class="flex flex-col gap-3">
                <a href="{{ route('reports.daily') }}" class="w-full py-2 bg-blue-600/20 text-blue-400 border border-blue-500 font-bold text-[10px] uppercase tracking-widest text-center hover:bg-blue-600 hover:text-white transition-all">
                    📥 KUNLIK PDF HISOBOTNI YUKLASH
                </a>
                <div class="flex gap-4">
                    <button @click="showZReport = false" type="button" class="w-1/2 py-2 text-[var(--text-color)] hover:text-[var(--active-color)] hover:bg-gray-500 hover:bg-opacity-10 uppercase tracking-widest font-bold transition-all text-xs border border-gray-800">Bekor qilish</button>
                    <form method="POST" action="{{ route('shift.stop') }}" class="w-1/2">
                        @csrf
                        <button type="submit" class="w-full py-2 bg-red-600 border border-red-500 text-white font-bold uppercase tracking-widest text-xs hover:bg-red-700 transition-all shadow-[0_0_15px_rgba(255,0,0,0.3)]">Smenani Yopish</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endif

<div class="grid grid-cols-1 md:grid-cols-3 gap-4 flex-1 min-h-0">

    <div class="md:col-span-2 cyber-panel p-6 overflow-y-auto flex flex-col h-full" x-data="{ searchQuery: '' }">
        <div class="flex flex-col md:flex-row justify-between md:items-center border-b border-[var(--active-color)] border-opacity-30 pb-3 mb-4 gap-4 shrink-0">
            <h2 class="text-sm font-orbitron font-bold text-[var(--active-color)] tracking-widest uppercase flex items-center gap-2">
                <span>{{ __('messages.pending_verifications') }}</span>
                <span class="text-sm bg-[var(--active-color)] text-[var(--bg-color)] px-2 py-0.5">{{ count($pendingContracts) }}</span>
            </h2>
            <div class="relative w-full md:w-64">
                <svg class="w-4 h-4 absolute left-3 top-2.5 text-[var(--active-color)] opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                <input type="text" x-model="searchQuery" @keydown.enter.prevent placeholder="{{ __('messages.scan_barcode') ?? 'Scan Barcode (Contract ID)' }}" class="w-full bg-[var(--input-bg)] border border-[var(--border-color)] p-2 pl-9 text-[var(--text-color)] font-bold text-xs font-mono outline-none focus:border-[var(--active-color)] focus:shadow-[0_0_10px_rgba(0,255,0,0.2)] transition-all uppercase placeholder-[var(--text-color)] placeholder-opacity-50" autocomplete="off" name="barcode_scanner_unique">
            </div>
        </div>

        @if (session('success'))
            <div class="p-2 mb-4 border border-[var(--active-color)] bg-[var(--active-color)] text-[var(--bg-color)] text-sm font-bold uppercase tracking-widest shrink-0">
                >> {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="p-2 mb-4 border border-red-500 bg-red-500 bg-opacity-10 text-red-500 text-sm font-bold uppercase tracking-widest shrink-0">
                >> {{ session('error') }}
            </div>
        @endif

        <div class="space-y-2 font-mono w-full overflow-y-auto pr-1 slim-scroll">
            @forelse($pendingContracts as $ct)
                <div x-show="searchQuery === '' || '{{ strtolower($ct->contract_id) }}'.includes(searchQuery.toLowerCase())" class="p-3 border border-white/5 bg-white/5 flex flex-col md:flex-row justify-between md:items-center group hover:border-cyan-400/30 transition-all rounded-xl gap-3">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-[10px] font-black text-white/30 tracking-widest">{{ $ct->contract_id }}</span>
                            <span class="text-[11px] font-bold text-cyan-400 uppercase">{{ $ct->service->name }}</span>
                        </div>
                        
                        @if($ct->services_json && is_array($ct->services_json))
                            <div class="space-y-1 bg-black/20 p-2 rounded-lg border border-white/5 mb-2">
                                @foreach($ct->services_json as $item)
                                    <div class="flex justify-between text-[10px] font-bold border-b border-white/5 pb-1 last:border-0 last:pb-0">
                                        <span class="text-white/60 uppercase">{{ $item['name'] }}</span>
                                        <span class="text-yellow-500">{{ number_format($item['price'], 0, ',', ' ') }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <div class="flex items-center gap-3 text-[9px] font-bold text-white/40 uppercase">
                            <span>OP: {{ $ct->user->name }}</span>
                            <span>CL: {{ $ct->client_name }}</span>
                        </div>
                    </div>
                    <div class="text-right flex flex-col md:flex-row items-center gap-4">
                        <div class="text-right">
                             <div class="text-base font-black text-white">{{ number_format($ct->amount, 0, ',', ' ') }} <span class="text-[10px] opacity-40">UZS</span></div>
                             <div class="text-[9px] font-bold text-emerald-400 uppercase">Profit: {{ number_format($ct->amount - $ct->cost_price, 0, ',', ' ') }}</div>
                        </div>
                        <div class="flex gap-2">
                            <button @click.prevent="handleContract({{ $ct->id }}, 'approve')" type="button" class="px-3 py-1.5 bg-cyan-500 text-black font-black text-[10px] uppercase rounded-lg hover:bg-cyan-400 transition-all shadow-lg shadow-cyan-500/10">{{ __('messages.pass') ?? 'Pass' }}</button>
                            <button @click.prevent="handleContract({{ $ct->id }}, 'reject')" type="button" class="px-3 py-1.5 border border-red-500/30 text-red-500 font-bold text-[10px] uppercase rounded-lg hover:bg-red-500 hover:text-white transition-all">{{ __('messages.deny') ?? 'Deny' }}</button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center opacity-80 text-[var(--text-color)]">
                    <p class="text-xs uppercase tracking-widest font-bold">{{ __('messages.no_pending_pipeline') }}</p>
                </div>
            @endforelse
            <div x-show="searchQuery !== '' && !Array.from($el.parentElement.children).some(child => child.style.display !== 'none' && child !== $el)" style="display: none;" class="p-6 text-center text-red-500">
                <p class="text-xs uppercase font-bold tracking-widest border border-red-500 bg-red-900 bg-opacity-40 p-2">NOT FOUND / BARCODE ERROR</p>
            </div>
        </div>
    </div>

    <div class="space-y-4 flex flex-col">
        <div class="cyber-panel p-6">
            <h3 class="font-orbitron font-bold mb-4 uppercase text-center border-b border-[var(--active-color)] border-opacity-30 pb-2 text-sm tracking-[0.2em] text-[var(--active-color)]">{{ __('messages.manual_entry') }}</h3>
            <div x-data="{ manualType: 'income' }">
                <form method="POST" action="{{ route('treasury.manual') }}" class="space-y-3">
                    @csrf
                    <select name="type" x-model="manualType" required class="w-full bg-[var(--input-bg)] text-[var(--text-color)] border border-[var(--border-color)] p-2 text-xs font-bold focus:outline-none focus:border-[var(--active-color)] transition-colors appearance-none font-mono">
                        <option value="income">{{ __('messages.type_income_cash') }}</option>
                        <option value="income_card" class="text-blue-400">💳 KIRIM (Karta hisobiga)</option>
                        <option value="cash_to_card" class="text-cyan-400">🔄 Naqd kassadan Kartaga o'tkazish</option>
                        <option value="expense">{{ __('messages.type_expense_office') }}</option>
                        <option value="tech_expense">{{ __('messages.type_expense_dev') }}</option>
                        <option value="salary_payout" class="text-green-500">💰 {{ __('messages.salary_payout') ?? 'Oylik Berish (Salary)' }}</option>
                        <option value="commission_payout" class="text-blue-500">📈 {{ __('messages.commission_payout') ?? 'Avans (Commission)' }}</option>
                        <option value="staff_loan" class="text-red-500">🏦 {{ __('messages.staff_loan') ?? 'Qarz Berish (Loan)' }}</option>
                        <option value="transport_expense" class="text-yellow-500">🚕 {{ __('messages.transport_expense') ?? 'Yo\'l Haqi (Kompaniyadan)' }}</option>
                    </select>

                    <div x-show="['salary_payout', 'commission_payout', 'staff_loan'].includes(manualType)" x-transition class="space-y-2">
                        <select name="staff_id" class="w-full bg-black text-[var(--cyber-yellow)] border border-[var(--cyber-yellow)] p-2 text-sm font-bold outline-none">
                            <option value="">-- {{ __('messages.select_staff') ?? 'Xodimni Tanlang' }} --</option>
                            @foreach($allStaff as $staff)
                                <option value="{{ $staff->id }}">{{ $staff->name }} ({{ $staff->role }}) - Balans: {{ number_format($staff->balance, 0, ',', ' ') }}</option>
                            @endforeach
                        </select>
                    </div>

                    <select name="payment_method" x-show="!['cash_to_card', 'income_card'].includes(manualType)" :required="!['cash_to_card', 'income_card'].includes(manualType)" class="w-full bg-[var(--input-bg)] text-[var(--text-color)] border border-[var(--border-color)] p-2 text-xs font-bold focus:outline-none focus:border-[var(--active-color)] transition-colors appearance-none font-mono mt-2">
                        <option value="" disabled selected>-- {{ __('messages.payment_method') ?? 'To\'lov usuli' }} --</option>
                        <option value="cash">💵 {{ __('messages.cash') ?? 'Naqd' }}</option>
                        <option value="card">💳 {{ __('messages.card') ?? 'Karta' }}</option>
                    </select>

                    <input type="number" name="amount" required placeholder="{{ __('messages.amount_uzs') }}" class="w-full bg-[var(--input-bg)] text-[var(--text-color)] placeholder-[var(--text-color)] placeholder-opacity-70 border border-[var(--border-color)] p-2 text-xs font-bold focus:outline-none focus:border-[var(--active-color)] transition-colors font-mono">
                    <textarea name="description" required placeholder="{{ __('messages.desc_admin_hash') }}" class="w-full bg-[var(--input-bg)] text-[var(--text-color)] placeholder-[var(--text-color)] placeholder-opacity-70 border border-[var(--border-color)] p-2 h-16 text-xs font-bold focus:outline-none focus:border-[var(--active-color)] transition-colors font-mono resize-none"></textarea>
                    <button type="submit" class="w-full p-2 bg-[var(--active-color)] text-[var(--bg-color)] font-orbitron font-bold text-sm uppercase tracking-widest hover:bg-transparent hover:text-[var(--active-color)] border border-transparent hover:border-[var(--active-color)] transition-all">{{ __('messages.submit_entry') }}</button>
                </form>
            </div>
        </div>

        <!-- Chat -->
        <div class="cyber-panel px-4 py-3 flex-1 min-h-[150px] overflow-y-auto slim-scroll">
            <h3 class="font-orbitron font-bold mb-3 uppercase text-center border-b border-[var(--cyber-yellow)] border-opacity-30 pb-1 text-sm tracking-widest text-[var(--cyber-yellow)]"><a href="{{ route('chat.index') }}" class="hover:underline">{{ __('messages.syndicate_chat') }}</a></h3>
            <div class="text-sm font-mono flex flex-col gap-2">
                @if($recentMessages->count() > 0)
                    @foreach($recentMessages as $msg)
                        <div class="bg-gray-500 bg-opacity-10 p-2 rounded-sm border-l {{ $msg->sender->role === 'admin' ? 'border-[var(--color-admin)]' : ($msg->sender->id === auth()->id() ? 'border-[var(--active-color)]' : 'border-[var(--cyber-yellow)]') }}">
                            <span class="font-bold {{ $msg->sender->role === 'admin' ? 'text-[var(--color-admin)]' : ($msg->sender->id === auth()->id() ? 'text-[var(--active-color)]' : 'text-[var(--cyber-yellow)]') }}">[{{ $msg->sender->name }}]:</span>
                            <span class="text-[var(--text-color)] opacity-80">{{ $msg->message }}</span>
                        </div>
                    @endforeach
                @else
                    <div class="bg-gray-500 bg-opacity-10 p-2 rounded-sm border-l border-[var(--cyber-yellow)]"><span class="text-[var(--cyber-yellow)] font-bold">[{{ __('messages.system') }}]:</span> <span class="text-[var(--text-color)] opacity-80">{{ __('messages.secure_chat_online') }}</span></div>
                @endif
            </div>
        </div>
    </div>
</div>
@elseif($currentTab == 'transactions')
<div class="cyber-panel p-4 md:p-6 flex flex-col flex-1 min-h-0" x-data="{ txSearch: '' }">
    <div class="flex justify-between items-center mb-4 border-b border-[var(--active-color)] border-opacity-30 pb-3">
        <h2 class="text-sm md:text-xl font-orbitron font-bold text-[var(--active-color)] tracking-widest uppercase">Tranzaksiyalar Tarixi</h2>
        <div class="relative w-64">
            <input type="text" x-model="txSearch" placeholder="Qidirish (ID, Mijoz, Izoh)..." class="w-full bg-[var(--input-bg)] border border-[var(--border-color)] p-2 pl-4 text-[var(--text-color)] font-bold text-sm font-mono outline-none focus:border-[var(--active-color)] transition-all uppercase placeholder-opacity-50" autocomplete="off" name="transaction_search_unique">
        </div>
    </div>

    <div class="overflow-x-auto overflow-y-auto slim-scroll flex-1 pr-2">
        <table class="w-full text-left font-mono text-xs min-w-[700px]">
            <thead class="text-[var(--active-color)] border-b border-[var(--active-color)] border-opacity-30">
                <tr>
                    <th class="py-2">SANA / VAQT</th>
                    <th class="py-2">HODIM / KASSIR</th>
                    <th class="py-2">TURI</th>
                    <th class="py-2 text-right">MIQDOR (UZS)</th>
                    <th class="py-2 pl-4">IZOH</th>
                    <th class="py-2 text-center">PRINT</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[var(--border-color)] divide-opacity-30">
                @forelse($recentTransactions as $tx)
                @php 
                    $searchData = strtolower($tx->description . ' ' . ($tx->contract->client_name ?? '') . ' ' . ($tx->contract->contract_id ?? ''));
                @endphp
                <tr class="hover:bg-[var(--hover-bg)] transition-colors" 
                    data-search="{{ $searchData }}"
                    x-show="txSearch === '' || $el.getAttribute('data-search').includes(txSearch.toLowerCase())">
                    <td class="py-2 text-sm">{{ $tx->created_at->format('d.m.y H:i') }}</td>
                    <td class="py-2 font-bold text-sm">{{ $tx->user->name ?? 'SYSTEM' }}</td>

                    <td class="py-2">
                        @if($tx->type == 'income') <span class="text-[var(--active-color)] font-bold">KIRIM</span>
                        @elseif($tx->type == 'expense') <span class="text-red-500 font-bold">CHIQIM (OFIS)</span>
                        @else <span class="text-blue-500 font-bold">CHIQIM (TEXNIK)</span>
                        @endif
                    </td>
                    <td class="py-2 text-right">
                        @if($tx->type == 'income')
                            <div class="font-bold text-[var(--active-color)]">+{{ number_format($tx->amount, 0, ',', ' ') }} <span class="text-xs opacity-70">UZS</span></div>
                            @if($tx->contract)
                                <div class="text-xs opacity-70 text-[var(--cyber-yellow)] font-bold">SOF FOYDA: {{ number_format($tx->contract->amount - $tx->contract->cost_price, 0, ',', ' ') }}</div>
                            @endif
                        @else
                            <div class="font-bold text-red-500">-{{ number_format($tx->amount, 0, ',', ' ') }} <span class="text-xs opacity-70">UZS</span></div>
                        @endif
                    </td>
                    <td class="py-2 opacity-100 pl-4 text-xs max-w-[200px] truncate" title="{{ $tx->description }}">
                        <span class="text-[var(--text-color)]">{{ $tx->description }}</span>
                        @if($tx->contract)
                             <br><span class="text-[var(--cyber-yellow)] opacity-60">ID: {{ $tx->contract->contract_id }} | {{ $tx->contract->client_name }}</span>
                        @endif
                    </td>

                    <td class="py-2 text-center">
                        @if($tx->contract_id)
                            <button type="button" @click.stop="window.open('{{ route('contracts.print', $tx->contract_id) }}', '_blank', 'width=400,height=600,noopener,noreferrer')" class="p-1 text-[var(--active-color)] hover:text-[var(--text-color)] transition-all transform hover:scale-110" title="Chop etish">
                                <svg class="w-4 h-4 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                            </button>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="py-6 text-center opacity-80 font-bold">HECH QANDAY TRANZAKSIYA TOPILMADI</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@elseif($currentTab == 'reports')
<div class="grid grid-cols-1 md:grid-cols-2 gap-4 flex-1 min-h-0">
    <div class="cyber-panel p-6 flex flex-col h-full min-h-0">
        <h2 class="text-xl font-orbitron font-bold text-[var(--active-color)] mb-4 tracking-widest uppercase border-b border-[var(--active-color)] border-opacity-30 pb-2">MENING ISH VAQTIM / OYLIK</h2>
        <div class="flex-1 overflow-y-auto slim-scroll space-y-4 font-mono text-xs">
            <div class="flex justify-between p-3 border border-[var(--active-color)] bg-[var(--input-bg)]">
                <span class="opacity-100 font-bold">Joriy Oy Ishlagan Soatingiz:</span>
                <span class="font-bold text-[var(--active-color)] text-lg" style="text-shadow: 0 0 5px var(--active-color);">{{ number_format($monthlyHours ?? 0, 1) }} SOAT</span>
            </div>
            <div class="flex justify-between p-3 border border-[var(--active-color)] bg-[var(--input-bg)]">
                <span class="opacity-100 font-bold text-[var(--text-color)]">Hisoblangan Maosh / Bonus (XP):</span>
                <span class="font-bold text-[var(--cyber-yellow)] text-lg" style="text-shadow: 0 0 5px var(--cyber-yellow);">{{ number_format(auth()->user()->salary ?? 0, 0, ',', ' ') }} UZS</span>
            </div>
            
            @if($activeShift)
                <div class="p-4 border border-[var(--active-color)] text-center text-[var(--active-color)] mt-6 relative overflow-hidden group">
                    <div class="absolute inset-0 bg-[var(--input-bg)] group-hover:bg-[var(--hover-bg)] transition-all"></div>
                    <p class="uppercase tracking-widest text-sm mb-2 opacity-100 font-bold relative z-10 text-[var(--text-color)]">Joriy Faol Smena Davomiyligi</p>
                    <div class="text-3xl font-orbitron font-bold timer-display relative z-10" x-text="shiftDuration" style="text-shadow: 0 0 10px var(--active-color);">00:00:00</div>
                </div>
            @endif
        </div>
    </div>
    
    <div class="cyber-panel p-6 flex flex-col h-full min-h-0">
        <h2 class="text-xl font-orbitron font-bold text-[var(--cyber-yellow)] mb-4 tracking-widest uppercase border-b border-[var(--cyber-yellow)] border-opacity-30 pb-2">OPERATORLAR REYTINGI (BUGUN)</h2>
        <div class="flex-1 overflow-y-auto slim-scroll">
            <div class="space-y-3 font-mono text-xs">
                @forelse($operatorsToday as $idx => $op)
                    <div class="flex items-center justify-between p-3 border border-[var(--border-color)] border-opacity-30 {{ $idx == 0 ? 'bg-[var(--input-bg)] border-[var(--cyber-yellow)]' : 'bg-[var(--input-bg)]' }}">
                        <div class="flex items-center gap-3">
                            <span class="text-lg font-bold text-[var(--panel-bg)] bg-[var(--text-color)] px-1">#{{ $idx + 1 }}</span>
                            <div>
                                <div class="font-bold {{ $idx == 0 ? 'text-[var(--cyber-yellow)] scale-110 ml-2' : 'text-[var(--active-color)]' }} uppercase">{{ $op->name }}</div>
                                <div class="opacity-100 text-xs text-[var(--text-color)]">{{ $op->today_deals }} TA SHARTNOMA / {{ number_format($op->today_profit, 0, ',', ' ') }} SOF FOYDA</div>
                            </div>
                        </div>
                        <div class="text-right font-bold flex flex-col {{ $idx == 0 ? 'text-[var(--cyber-yellow)] text-sm shadow-[0_0_10px_rgba(252,238,10,0.5)]' : 'text-[var(--text-color)]' }}">
                            {{ number_format($op->today_revenue, 0, ',', ' ') }} <span class="text-xs opacity-70">JAMI</span>
                        </div>
                    </div>
                @empty
                    <div class="text-center opacity-80 mt-10 text-[var(--active-color)] font-bold">Bugun hech qanday operator savdo qilmadi.</div>
                @endforelse

            </div>
        </div>
    </div>
</div>
@endif

    <!-- Face ID Scanning Overlay -->
    <div x-show="faceIdOverlay" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-95 backdrop-blur-md">
        <div class="relative w-full max-w-sm p-6 cyber-panel text-center border-[var(--active-color)] shadow-[0_0_30px_rgba(0,255,0,0.2)]">
            <h2 class="text-sm font-orbitron font-bold text-[var(--active-color)] mb-4 tracking-widest uppercase">Biometrik Tasdiqlash</h2>
            
            <div class="relative w-48 h-48 mx-auto border border-[var(--border-color)] bg-black overflow-hidden rounded-full shadow-[0_0_20px_rgba(0,255,0,0.1)]">
                <video id="webcam" autoplay playsinline class="w-full h-full object-cover transform scale-x-[-1] opacity-70"></video>
                <div class="absolute inset-x-0 h-1 bg-[var(--active-color)] shadow-[0_0_10px_var(--active-color)]" style="top: 0; animation: scanLine 2s linear infinite;"></div>
                <div class="absolute inset-0 flex items-center justify-center mix-blend-screen text-[var(--active-color)] opacity-40">
                    <svg class="w-full h-full p-6" viewBox="0 0 100 100" fill="none" stroke="currentColor" stroke-width="1"><path d="M20 30 V20 H30 M70 20 H80 V30 M80 70 V80 H70 M30 80 H20 V70" stroke-linecap="square"/></svg>
                </div>
            </div>
            <style>@keyframes scanLine { 100% { top: 100%; } }</style>

            <div class="mt-6 font-mono text-xs text-[var(--text-color)]">
                <p x-text="faceIdMessage" class="uppercase tracking-widest"></p>
                <div class="w-full bg-gray-900 h-1 mt-3">
                    <div class="h-full bg-[var(--active-color)] transition-all duration-300" :style="'width: ' + faceIdProgress + '%'"></div>
                </div>
            </div>

            <div class="mt-4 flex flex-col gap-2 relative">
                <button type="button" @click="showPinFallback = !showPinFallback; if(window.speakUzbekGlobal) window.speakUzbekGlobal('PIN kodni kiritishingiz mumkin')" class="text-sm text-[var(--cyber-yellow)] underline uppercase tracking-widest opacity-80 hover:opacity-100">Kamera ishlamayaptimi? PIN kod kiriting</button>
                
                <div x-show="showPinFallback" class="mt-3 flex gap-2 w-full max-w-[200px] mx-auto bg-black border border-[var(--cyber-yellow)] p-1">
                    <input type="password" id="fallbackPinInputsCashier" maxlength="10" placeholder="PIN" class="w-full bg-transparent border-none text-[var(--cyber-yellow)] text-center tracking-[0.5em] font-bold outline-none font-mono">
                    <button type="button" @click="document.getElementById('startShiftPinCashier').value = document.getElementById('fallbackPinInputsCashier').value; document.getElementById('startShiftForm').submit();" class="bg-[var(--cyber-yellow)] text-black px-2 py-1 font-bold text-xs uppercase hover:opacity-80">OK</button>
                </div>
            </div>

            <form id="startShiftForm" method="POST" action="{{ route('shift.start') }}" class="hidden">
                @csrf
                <input type="hidden" name="pin_code" id="startShiftPinCashier">
            </form>
        </div>
    </div>
</div>


@endsection
