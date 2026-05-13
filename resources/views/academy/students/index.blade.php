@extends('layouts.cyber')

@section('sidebar')
    @if(auth()->user()->role === 'admin')
        @include('partials.admin_sidebar')
    @elseif(auth()->user()->role === 'cashier')
        @include('partials.cashier_sidebar')
    @endif
@endsection

@section('content')
<div class="flex-1 min-h-0 overflow-y-auto w-full p-4 lg:p-8 space-y-6 slim-scroll" x-data="studentManager()">
    <div class="glass-panel p-6">
        <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-8 pt-4">
            <div class="panel-title mb-0 shrink-0 flex items-center gap-4">
                <button onclick="window.history.back()" class="w-8 h-8 md:w-10 md:h-10 rounded-full bg-white/5 border border-white/10 flex items-center justify-center text-white/50 hover:text-cyan-400 hover:border-cyan-400/50 transition-all shrink-0">
                    <i class="fa-solid fa-arrow-left"></i>
                </button>
                <div>
                    <i class="fa-solid fa-user-graduate text-cyan-400"></i>
                    <span class="text-sm md:text-xl uppercase tracking-widest">O'QUVCHILAR BAZASI</span>
                </div>
            </div>
            
            <div class="flex items-center gap-3 w-full md:w-auto">
                <div class="relative flex-1 md:w-64">
                    <input type="text" x-model="searchQuery" @input.debounce.500ms="fetchStudents()" placeholder="Qidiruv (Ism yoki Tel)..." class="w-full bg-black/40 border border-white/10 rounded p-2 pl-8 text-xs text-white focus:border-cyan-400 outline-none transition-all">
                    <i class="fa-solid fa-search absolute left-3 top-2.5 text-white/30 text-[10px]"></i>
                </div>
                <button @click="showAddModal = true" class="px-4 py-2 bg-cyan-500/20 text-cyan-400 border border-cyan-500 text-[10px] font-bold uppercase tracking-widest hover:bg-cyan-500 hover:text-black transition-colors rounded-sm shadow-[0_0_15px_rgba(0,255,204,0.3)]">QO'SHISH</button>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($students as $student)
            <div class="glass-panel bg-white/5 border border-white/10 p-4 relative group hover:border-cyan-500/30 transition-all flex flex-col">
                <div class="flex justify-between items-start mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-cyan-500/10 flex items-center justify-center text-cyan-400 border border-cyan-500/20 font-bold">
                            {{ substr($student->name, 0, 1) }}
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-white">{{ $student->name }}</h4>
                            <p class="text-[10px] font-mono text-white/40">{{ $student->phone }}</p>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <button class="text-white/20 hover:text-cyan-400 transition-colors"><i class="fa-solid fa-pen-to-square"></i></button>
                    </div>
                </div>

                <div class="space-y-2 mb-4 flex-1">
                    <div class="text-[9px] uppercase text-white/30 font-bold tracking-widest">GURUHLARI:</div>
                    <div class="flex flex-wrap gap-1">
                        @forelse($student->groups as $group)
                            <span class="px-2 py-0.5 bg-purple-500/10 text-purple-400 border border-purple-500/20 rounded text-[9px] font-bold">{{ $group->name }}</span>
                        @empty
                            <span class="text-[10px] italic text-white/20">Guruhga ulanmagan</span>
                        @endforelse
                    </div>
                </div>

                <div class="pt-3 border-t border-white/5 flex gap-2">
                    <a href="tel:{{ $student->phone }}" class="flex-1 text-center py-1.5 bg-green-500/10 hover:bg-green-500/20 text-green-400 text-[9px] font-bold uppercase tracking-widest border border-green-500/20 rounded-sm transition-all flex items-center justify-center gap-1"><i class="fa-solid fa-phone"></i> QO'NG'IROQ</a>
                    <a href="https://t.me/{{ preg_replace('/[^0-9]/', '', $student->phone) }}" target="_blank" class="flex-1 text-center py-1.5 bg-blue-500/10 hover:bg-blue-500/20 text-blue-400 text-[9px] font-bold uppercase tracking-widest border border-blue-500/20 rounded-sm transition-all flex items-center justify-center gap-1"><i class="fa-brands fa-telegram"></i> TELEGRAM</a>
                </div>
                <div class="pt-2 flex gap-2">
                    <button @click="openGroupModal({{ $student->id }}, '{{ $student->name }}')" class="flex-1 py-1.5 bg-white/5 hover:bg-white/10 text-white/60 text-[9px] font-bold uppercase tracking-widest border border-white/10 rounded-sm transition-all">Guruhga Qo'shish</button>
                    <button @click="openPaymentModal({{ $student->id }}, '{{ $student->name }}')" class="flex-1 py-1.5 bg-cyan-500/10 hover:bg-cyan-500/20 text-cyan-400 text-[9px] font-bold uppercase tracking-widest border border-cyan-500/20 rounded-sm transition-all">To'lov Olish</button>
                </div>
            </div>
            @endforeach
        </div>

        <div class="mt-8">
            {{ $students->links() }}
        </div>
    </div>

    <!-- ... (Previous modals) ... -->

    <!-- Payment Modal -->
    <div x-show="showPaymentModal" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm p-4">
        <div @click.away="showPaymentModal = false" class="bg-[#111] w-full max-w-md border border-white/10 rounded-lg shadow-2xl overflow-hidden">
            <div class="p-4 border-b border-white/5 bg-white/5 flex justify-between items-center">
                <h3 class="text-xs font-bold uppercase tracking-widest text-cyan-400" x-text="activeStudentName + ': TO\'LOV QABUL QILISH'"></h3>
                <button @click="showPaymentModal = false" class="text-white/40 hover:text-white"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form action="{{ route('admin.academy.payments.store') }}" method="POST" class="p-6 space-y-4">
                @csrf
                <input type="hidden" name="student_id" :value="activeStudentId">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] uppercase font-bold text-white/40 mb-1">GURUH</label>
                        <select name="group_id" required class="w-full bg-black border border-white/10 rounded p-2 text-xs text-white focus:border-cyan-400 outline-none">
                            <option value="">-- Tanlang --</option>
                            @foreach($groups as $group)
                                <option value="{{ $group->id }}">{{ $group->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] uppercase font-bold text-white/40 mb-1">USLUB</label>
                        <select name="payment_method" required class="w-full bg-black border border-white/10 rounded p-2 text-xs text-white focus:border-cyan-400 outline-none">
                            <option value="cash">NAQT</option>
                            <option value="card">KARTA / CLICK</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] uppercase font-bold text-white/40 mb-1">SUMMA (UZS)</label>
                        <input type="number" name="amount" required placeholder="0.00" class="w-full bg-black border border-white/10 rounded p-2 text-xs text-cyan-400 font-bold focus:border-cyan-400 outline-none">
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-[10px] uppercase font-bold text-white/40 mb-1">OY</label>
                            <select name="month" required class="w-full bg-black border border-white/10 rounded p-1 text-xs text-white focus:border-cyan-400 outline-none">
                                @foreach(['Yanvar','Fevral','Mart','Aprel','May','Iyun','Iyul','Avgust','Sentabr','Oktabr','Noyabr','Dekabr'] as $m)
                                    <option value="{{ $m }}" {{ $m == now()->translatedFormat('F') ? 'selected' : '' }}>{{ $m }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] uppercase font-bold text-white/40 mb-1">YIL</label>
                            <input type="number" name="year" value="{{ date('Y') }}" class="w-full bg-black border border-white/10 rounded p-1 text-xs text-white focus:border-cyan-400 outline-none">
                        </div>
                    </div>
                </div>
                <div>
                    <label class="block text-[10px] uppercase font-bold text-white/40 mb-1">IZOH</label>
                    <textarea name="comment" rows="2" class="w-full bg-black border border-white/10 rounded p-2 text-xs text-white focus:border-cyan-400 outline-none"></textarea>
                </div>
                <button type="submit" class="w-full py-4 bg-cyan-500/20 text-cyan-400 border border-cyan-500 font-bold text-[10px] uppercase tracking-[0.3em] hover:bg-cyan-500 hover:text-black transition-all shadow-[0_0_20px_rgba(0,255,204,0.2)]">TRANZAKSIYANI TASDIQLASH</button>
            </form>
        </div>
    </div>

    <!-- Add to Group Modal -->
    <div x-show="showGroupModal" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm p-4">
        <div @click.away="showGroupModal = false" class="bg-[#111] w-full max-w-sm border border-white/10 rounded-lg shadow-2xl overflow-hidden">
            <div class="p-4 border-b border-white/5 bg-white/5 flex justify-between items-center">
                <h3 class="text-xs font-bold uppercase tracking-widest text-purple-400" x-text="'Guruhga biriktirish: ' + activeStudentName"></h3>
                <button @click="showGroupModal = false" class="text-white/40 hover:text-white"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="p-6 space-y-4">
                <select x-model="selectedGroupId" class="w-full bg-black border border-white/10 rounded p-2 text-xs text-white focus:border-purple-400 outline-none">
                    <option value="">-- Guruhni Tanlang --</option>
                    @foreach($groups as $group)
                        <option value="{{ $group->id }}">{{ $group->name }} ({{ $group->course->name ?? 'N/A' }})</option>
                    @endforeach
                </select>
                <button @click="addToGroup()" class="w-full py-3 bg-purple-500/20 text-purple-400 border border-purple-500 font-bold text-[10px] uppercase tracking-[0.2em] hover:bg-purple-500 hover:text-black transition-all">GURUHGA QO'SHISH</button>
            </div>
        </div>
    </div>
    <!-- Add Student Modal -->
    <div x-show="showAddModal" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm p-4">
        <div @click.away="showAddModal = false" class="bg-[#111] w-full max-w-md border border-white/10 rounded-lg shadow-2xl overflow-hidden">
            <div class="p-4 border-b border-white/5 bg-white/5 flex justify-between items-center">
                <h3 class="text-xs font-bold uppercase tracking-widest text-cyan-400">Yangi O'quvchi Ro'yxati</h3>
                <button @click="showAddModal = false" class="text-white/40 hover:text-white"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form action="{{ route('admin.academy.students.store') }}" method="POST" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-[10px] uppercase font-bold text-white/40 mb-1">ISM FAMILIYA</label>
                    <input type="text" name="name" required class="w-full bg-black border border-white/10 rounded p-2 text-xs text-white focus:border-cyan-400 outline-none">
                </div>
                <div>
                    <label class="block text-[10px] uppercase font-bold text-white/40 mb-1">TEL RAQAM</label>
                    <input type="text" name="phone" required class="w-full bg-black border border-white/10 rounded p-2 text-xs text-white focus:border-cyan-400 outline-none">
                </div>
                <div>
                    <label class="block text-[10px] uppercase font-bold text-white/40 mb-1">MANZIL</label>
                    <input type="text" name="address" class="w-full bg-black border border-white/10 rounded p-2 text-xs text-white focus:border-cyan-400 outline-none">
                </div>
                <div>
                    <label class="block text-[10px] uppercase font-bold text-white/40 mb-1">GURUHGA BIRIKTIRISH (IXTIYORIY)</label>
                    <select name="group_id" class="w-full bg-black border border-white/10 rounded p-2 text-xs text-white focus:border-cyan-400 outline-none">
                        <option value="">-- Tanlang --</option>
                        @foreach($groups as $group)
                            <option value="{{ $group->id }}">{{ $group->name }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="w-full py-3 bg-cyan-500/20 text-cyan-400 border border-cyan-500 font-bold text-[10px] uppercase tracking-[0.2em] hover:bg-cyan-500 hover:text-black transition-all">TASDIQLASH</button>
            </form>
        </div>
    </div>
</div>

<script>
    function studentManager() {
        return {
            searchQuery: '',
            showAddModal: false,
            showGroupModal: false,
            showPaymentModal: false,
            activeStudentId: null,
            activeStudentName: '',
            selectedGroupId: '',

            openGroupModal(id, name) {
                this.activeStudentId = id;
                this.activeStudentName = name;
                this.showGroupModal = true;
            },

            openPaymentModal(id, name) {
                this.activeStudentId = id;
                this.activeStudentName = name;
                this.showPaymentModal = true;
            },

            addToGroup() {
                if(!this.selectedGroupId) return;
                fetch('{{ route('admin.academy.students.add_to_group') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        student_id: this.activeStudentId,
                        group_id: this.selectedGroupId
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if(data.success) {
                        alert(data.message);
                        location.reload();
                    }
                });
            },

            fetchStudents() {
                // For simple search, redirect with query param
                if(this.searchQuery.length > 2 || this.searchQuery.length === 0) {
                    window.location.href = `{{ route('admin.academy.students.index') }}?search=${this.searchQuery}`;
                }
            }
        }
    }
</script>
@endsection
