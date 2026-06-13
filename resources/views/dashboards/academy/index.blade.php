@extends('layouts.cyber')

@section('sidebar')
    @if(auth()->user()->role === 'admin')
        @include('partials.admin_sidebar')
    @elseif(auth()->user()->role === 'cashier')
        @include('partials.cashier_sidebar')
    @endif
@endsection

@section('content')
<div class="w-full flex-1 flex flex-col gap-6" x-data="academyHub()">
    <!-- Header with back button -->
    <div class="flex items-center gap-4 mt-2 mb-2">
        <button onclick="window.history.back()" class="w-8 h-8 md:w-10 md:h-10 rounded-full bg-white/5 border border-white/10 flex items-center justify-center text-white/50 hover:text-cyan-400 hover:border-cyan-400/50 transition-all shrink-0">
            <i class="fa-solid fa-arrow-left"></i>
        </button>
        <h1 class="text-xl md:text-2xl font-orbitron font-bold tracking-widest text-cyan-400 uppercase">ACADEMY HUB</h1>
    </div>

    <!-- Quick Actions Row -->
    <div class="flex flex-wrap gap-4 mb-2">
        <a href="{{ route('admin.academy.groups.index') }}" class="px-4 py-2 bg-green-500/10 text-green-400 border border-green-500/20 hover:bg-green-500 hover:text-black transition-all text-[10px] font-bold uppercase tracking-widest rounded flex items-center gap-2">
            <i class="fa-solid fa-clipboard-check"></i> DAVOMAT NAZORATI
        </a>
        <a href="{{ route('admin.academy.students.index') }}" class="px-4 py-2 bg-cyan-500/10 text-cyan-400 border border-cyan-500/20 hover:bg-cyan-500 hover:text-black transition-all text-[10px] font-bold uppercase tracking-widest rounded flex items-center gap-2">
            <i class="fa-solid fa-user-plus"></i> O'QUVCHI QO'SHISH
        </a>
        <button @click="showAddTeacherModal = true" class="px-4 py-2 bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 hover:bg-emerald-500 hover:text-black transition-all text-[10px] font-bold uppercase tracking-widest rounded flex items-center gap-2">
            <i class="fa-solid fa-chalkboard-user"></i> O'QITUVCHI QO'SHISH
        </button>
        <a href="{{ route('admin.academy.groups.index') }}" class="px-4 py-2 bg-purple-500/10 text-purple-400 border border-purple-500/20 hover:bg-purple-500 hover:text-black transition-all text-[10px] font-bold uppercase tracking-widest rounded flex items-center gap-2">
            <i class="fa-solid fa-plus-circle"></i> GURUH OCHISH
        </a>
        <a href="{{ route('admin.academy.courses.index') }}" class="px-4 py-2 bg-pink-500/10 text-pink-400 border border-pink-500/20 hover:bg-pink-500 hover:text-black transition-all text-[10px] font-bold uppercase tracking-widest rounded flex items-center gap-2">
            <i class="fa-solid fa-book-medical"></i> KURS YARATISH
        </a>
        <a href="{{ route('admin.academy.rooms.index') }}" class="px-4 py-2 bg-white/5 text-white/60 border border-white/20 hover:bg-white hover:text-black transition-all text-[10px] font-bold uppercase tracking-widest rounded flex items-center gap-2">
            <i class="fa-solid fa-door-open"></i> YANGI XONA
        </a>
    </div>

    <!-- Top Stats Grid -->
    <div class="stats-grid">
        <a href="{{ route('admin.academy.students.index') }}" class="stat-card hover:bg-white/5 transition-all group" style="border-left: 4px solid var(--neon-cyan); border-image: none;">
            <div class="stat-title group-hover:text-cyan-400">Jami O'quvchilar</div>
            <div class="stat-value">{{ $studentsCount }}</div>
            <div class="text-[10px] mt-2 text-cyan-400 font-bold uppercase tracking-widest"><i class="fa-solid fa-users"></i> Baza boshqaruvi</div>
        </a>
        <a href="{{ route('admin.academy.groups.index') }}" class="stat-card hover:bg-white/5 transition-all group" style="border-left: 4px solid var(--neon-purple); border-image: none;">
            <div class="stat-title group-hover:text-purple-400">Faol Guruhlar</div>
            <div class="stat-value">{{ $groupsCount }}</div>
            <div class="text-[10px] mt-2 text-purple-400 font-bold uppercase tracking-widest"><i class="fa-solid fa-layer-group"></i> Jadval nazorati</div>
        </a>
        <a href="{{ route('admin.academy.courses.index') }}" class="stat-card hover:bg-white/5 transition-all group" style="border-left: 4px solid var(--neon-pink); border-image: none;">
            <div class="stat-title group-hover:text-pink-400">Kurslar</div>
            <div class="stat-value">{{ $coursesCount }}</div>
            <div class="text-[10px] mt-2 text-pink-400 font-bold uppercase tracking-widest"><i class="fa-solid fa-book-open"></i> O'quv dasturlari</div>
        </a>
        <a href="{{ route('admin.academy.rooms.index') }}" class="stat-card hover:bg-white/5 transition-all group" style="border-left: 4px solid #fff; border-image: none;">
            <div class="stat-title group-hover:text-white">Xonalar</div>
            <div class="stat-value">{{ $roomsCount }}</div>
            <div class="text-[10px] mt-2 text-white/50 font-bold uppercase tracking-widest"><i class="fa-solid fa-door-open"></i> Bandlik vizualizatsiyasi</div>
        </a>
    </div>

    <!-- Main Content Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 flex-1 min-h-[400px]">
        <!-- Recent Students -->
        <div class="glass-panel p-6 flex flex-col h-full">
            <div class="panel-title flex justify-between items-center mb-6">
                <div>
                    <i class="fa-solid fa-user-astronaut text-cyan-400 mr-2"></i>
                    <span>Yangi O'quvchilar</span>
                </div>
                <button class="btn-ios text-[10px] py-1 px-3 border border-white/10 hover:bg-white/5">ARCHIVE <i class="fa-solid fa-chevron-right text-[8px] ml-1"></i></button>
            </div>
            <div class="flex-1 overflow-y-auto slim-scroll space-y-3">
                @forelse($recentStudents as $student)
                <div class="p-3 rounded-2xl bg-white/5 border border-white/5 flex justify-between items-center hover:bg-white/10 transition-colors">
                    <div>
                        <div class="font-bold text-sm">{{ $student->name }}</div>
                        <div class="text-[10px] text-white/50 font-mono">{{ $student->phone }}</div>
                    </div>
                    <div class="text-[10px] font-bold px-2 py-1 bg-cyan-500/10 text-cyan-400 rounded border border-cyan-500/20 uppercase">
                        {{ $student->created_at->diffForHumans() }}
                    </div>
                </div>
                @empty
                <div class="flex flex-col items-center justify-center h-full opacity-30 italic">
                    <i class="fa-solid fa-user-slash text-4xl mb-4"></i>
                    <p>O'quvchilar topilmadi</p>
                </div>
                @endforelse
            </div>
        </div>

        <!-- Active Groups -->
        <div class="glass-panel p-6 flex flex-col h-full">
            <div class="panel-title flex justify-between items-center mb-6">
                <div>
                    <i class="fa-solid fa-layer-group text-purple-400 mr-2"></i>
                    <span>Faol Guruhlar</span>
                </div>
                <button class="btn-ios text-[10px] py-1 px-3 border border-white/10 hover:bg-white/5">ARCHIVE <i class="fa-solid fa-chevron-right text-[8px] ml-1"></i></button>
            </div>
            <div class="flex-1 overflow-y-auto slim-scroll space-y-3">
                @forelse($activeGroups as $group)
                <div class="p-4 rounded-2xl bg-white/5 border border-white/5 hover:border-purple-500/30 transition-all flex justify-between items-center gap-4">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <div class="font-bold text-sm text-purple-400">{{ $group->name }}</div>
                            <div class="text-[9px] font-bold opacity-60 uppercase bg-white/10 px-1.5 py-0.5 rounded text-white">{{ $group->course->name ?? 'N/A' }}</div>
                        </div>
                        <div class="flex gap-4 text-[10px] opacity-60">
                            <span><i class="fa-solid fa-chalkboard-user mr-1 text-purple-400"></i> {{ $group->teacher->name ?? 'N/A' }}</span>
                            <span><i class="fa-solid fa-door-open mr-1 text-purple-400"></i> {{ $group->room->name ?? 'N/A' }}</span>
                        </div>
                    </div>
                    <a href="{{ route('admin.academy.attendance.students', $group->id) }}" class="px-3 py-2 bg-green-500/10 hover:bg-green-600 border border-green-500/20 text-green-400 hover:text-black text-[9px] font-black uppercase tracking-wider rounded transition-all shrink-0">
                        <i class="fa-solid fa-clipboard-check mr-1"></i> Davomat
                    </a>
                </div>
                @empty
                <div class="flex flex-col items-center justify-center h-full opacity-30 italic">
                    <i class="fa-solid fa-ban text-4xl mb-4"></i>
                    <p>Faol guruhlar topilmadi</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
    
    <!-- Add Teacher Modal -->
    <div x-show="showAddTeacherModal" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm p-4">
        <div @click.away="showAddTeacherModal = false" class="bg-[#111] w-full max-w-lg border border-white/10 rounded-lg shadow-2xl overflow-hidden">
            <div class="p-4 border-b border-white/5 bg-white/5 flex justify-between items-center">
                <h3 class="text-xs font-bold uppercase tracking-widest text-emerald-400"><i class="fa-solid fa-chalkboard-user"></i> Yangi O'qituvchi Qo'shish</h3>
                <button @click="showAddTeacherModal = false" class="text-white/40 hover:text-white"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form action="{{ route('admin.academy.teachers.store') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-4 max-h-[80vh] overflow-y-auto slim-scroll">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] uppercase font-bold text-white/40 mb-1">F.I.O.</label>
                        <input type="text" name="name" required placeholder="To'liq ism-sharifi" class="w-full bg-black border border-white/10 rounded p-2 text-xs text-white focus:border-emerald-400 outline-none transition-all">
                    </div>
                    <div>
                        <label class="block text-[10px] uppercase font-bold text-white/40 mb-1">Telefon Raqami</label>
                        <input type="text" name="phone" placeholder="+998901234567" class="w-full bg-black border border-white/10 rounded p-2 text-xs text-white focus:border-emerald-400 outline-none transition-all">
                    </div>
                    <div>
                        <label class="block text-[10px] uppercase font-bold text-white/40 mb-1">Pasport Seriyasi</label>
                        <input type="text" name="passport_serial" placeholder="AA1234567" class="w-full bg-black border border-white/10 rounded p-2 text-xs text-white focus:border-emerald-400 outline-none transition-all">
                    </div>
                    <div>
                        <label class="block text-[10px] uppercase font-bold text-white/40 mb-1">Email / Login</label>
                        <input type="email" name="email" required placeholder="teacher@itcloud.uz" class="w-full bg-black border border-white/10 rounded p-2 text-xs text-white focus:border-emerald-400 outline-none transition-all">
                    </div>
                    <div>
                        <label class="block text-[10px] uppercase font-bold text-white/40 mb-1">Parol</label>
                        <input type="password" name="password" required placeholder="********" class="w-full bg-black border border-white/10 rounded p-2 text-xs text-white focus:border-emerald-400 outline-none transition-all">
                    </div>
                    <div>
                        <label class="block text-[10px] uppercase font-bold text-white/40 mb-1">Rasm (Face ID uchun)</label>
                        <input type="file" name="avatar" accept="image/*" class="w-full bg-black border border-white/10 rounded p-1.5 text-[10px] text-white focus:border-emerald-400 outline-none transition-all">
                    </div>
                </div>
                <button type="submit" class="w-full mt-4 py-4 bg-emerald-500/20 text-emerald-400 border border-emerald-500 font-bold text-[10px] uppercase tracking-[0.3em] hover:bg-emerald-500 hover:text-black transition-all">SAQLASH VA TIZIMGA QO'SHISH</button>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('academyHub', () => ({
            showAddTeacherModal: false,
            init() {
                console.log('Academy Module initialized');
            }
        }));
    });
</script>
@endsection
