@extends('layouts.cyber')

@section('sidebar')
    @include('partials.admin_sidebar')
@endsection

@section('content')
<div class="flex-1 min-h-0 overflow-y-auto w-full p-4 lg:p-8 space-y-6 slim-scroll" x-data="courseManager()">
    <div class="glass-panel p-6">
        <div class="flex justify-between items-center mb-8 pt-4">
            <div class="panel-title mb-0 flex items-center gap-4">
                <button onclick="window.history.back()" class="w-8 h-8 md:w-10 md:h-10 rounded-full bg-white/5 border border-white/10 flex items-center justify-center text-white/50 hover:text-cyan-400 hover:border-cyan-400/50 transition-all shrink-0">
                    <i class="fa-solid fa-arrow-left"></i>
                </button>
                <div>
                    <i class="fa-solid fa-graduation-cap text-cyan-400"></i>
                    <span class="text-sm md:text-xl uppercase tracking-widest">O'QUV KURSLARI</span>
                </div>
            </div>
            <button @click="showAddModal = true" class="px-4 py-2 bg-cyan-500/20 text-cyan-400 border border-cyan-500 text-[10px] font-bold uppercase tracking-widest hover:bg-cyan-500 hover:text-black transition-colors rounded-sm shadow-[0_0_15px_rgba(6,182,212,0.3)]">YANGI KURS</button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($courses as $course)
            <div class="glass-panel bg-white/5 border border-white/10 p-6 rounded-2xl group hover:border-cyan-500/50 transition-all">
                <div class="flex justify-between items-start mb-4">
                    <h3 class="text-lg font-bold text-white uppercase">{{ $course->name }}</h3>
                    <span class="text-[10px] bg-cyan-500/20 text-cyan-400 px-2 py-1 rounded shadow-[0_0_10px_rgba(6,182,212,0.2)]">{{ $course->groups_count }} GURUH</span>
                </div>
                <div class="space-y-4">
                    <div class="flex justify-between items-end border-b border-white/5 pb-2">
                        <span class="text-[10px] text-white/40 uppercase">Oylik To'lov</span>
                        <span class="text-xl font-black text-cyan-400 font-mono">{{ number_format($course->price, 0) }} <span class="text-xs font-normal">UZS</span></span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-[10px] text-white/40 uppercase">Davomiyligi</span>
                        <span class="text-xs text-white">{{ $course->duration ?? 'Aniqlanmagan' }}</span>
                    </div>
                </div>
                <div class="pt-6 flex gap-2">
                    <button @click="openEditModal({{ json_encode($course) }})" class="flex-1 py-2 bg-white/5 hover:bg-white/10 text-[9px] font-bold uppercase tracking-widest text-white/60 border border-white/10 transition-all">Tahrirlash</button>
                    <form action="{{ route('admin.academy.courses.destroy', $course->id) }}" method="POST" class="flex-1">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full py-2 bg-red-500/10 hover:bg-red-500 hover:text-white text-red-500 text-[9px] font-bold uppercase tracking-widest border border-red-500/20 transition-all">O'chirish</button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Modal -->
    <div x-show="showAddModal" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm p-4">
        <div @click.away="showAddModal = false" class="bg-[#111] w-full max-w-md border border-white/10 rounded-lg shadow-2xl overflow-hidden">
            <div class="p-4 border-b border-white/5 bg-white/5 flex justify-between items-center">
                <h3 class="text-xs font-bold uppercase tracking-widest text-cyan-400">Yangi Kurs Yaratish</h3>
                <button @click="showAddModal = false" class="text-white/40 hover:text-white"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form action="{{ route('admin.academy.courses.store') }}" method="POST" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-[10px] uppercase font-bold text-white/40 mb-1">KURS NOMI</label>
                    <input type="text" name="name" required class="w-full bg-black border border-white/10 rounded p-2 text-xs text-white focus:border-cyan-400 outline-none">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] uppercase font-bold text-white/40 mb-1">OYLIK NARXI (UZS)</label>
                        <input type="number" name="price" required class="w-full bg-black border border-white/10 rounded p-2 text-xs text-cyan-400 font-bold focus:border-cyan-400 outline-none">
                    </div>
                    <div>
                        <label class="block text-[10px] uppercase font-bold text-white/40 mb-1">DAVOMIYLIGI</label>
                        <input type="text" name="duration" placeholder="i.e. 6 oy" class="w-full bg-black border border-white/10 rounded p-2 text-xs text-white focus:border-cyan-400 outline-none">
                    </div>
                </div>
                <div>
                    <label class="block text-[10px] uppercase font-bold text-white/40 mb-1">TAVSIF (IXTIYORIY)</label>
                    <textarea name="description" class="w-full bg-black border border-white/10 rounded p-2 text-xs text-white focus:border-cyan-400 outline-none" rows="3"></textarea>
                </div>
                <button type="submit" class="w-full py-4 bg-cyan-500/20 text-cyan-400 border border-cyan-500 font-bold text-[10px] uppercase tracking-[0.3em] hover:bg-cyan-500 hover:text-black transition-all">KURSNI TASDIQLASH</button>
            </form>
        </div>
    </div>

    <!-- Edit Modal -->
    <div x-show="showEditModal" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm p-4">
        <div @click.away="showEditModal = false" class="bg-[#111] w-full max-w-md border border-white/10 rounded-lg shadow-2xl overflow-hidden">
            <div class="p-4 border-b border-white/5 bg-white/5 flex justify-between items-center">
                <h3 class="text-xs font-bold uppercase tracking-widest text-cyan-400">Kursni Tahrirlash</h3>
                <button @click="showEditModal = false" class="text-white/40 hover:text-white"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form :action="`/admin/academy/courses/${editForm.id}`" method="POST" class="p-6 space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-[10px] uppercase font-bold text-white/40 mb-1">KURS NOMI</label>
                    <input type="text" name="name" x-model="editForm.name" required class="w-full bg-black border border-white/10 rounded p-2 text-xs text-white focus:border-cyan-400 outline-none">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] uppercase font-bold text-white/40 mb-1">OYLIK NARXI (UZS)</label>
                        <input type="number" name="price" x-model="editForm.price" required class="w-full bg-black border border-white/10 rounded p-2 text-xs text-cyan-400 font-bold focus:border-cyan-400 outline-none">
                    </div>
                    <div>
                        <label class="block text-[10px] uppercase font-bold text-white/40 mb-1">DAVOMIYLIGI</label>
                        <input type="text" name="duration" x-model="editForm.duration" placeholder="i.e. 6 oy" class="w-full bg-black border border-white/10 rounded p-2 text-xs text-white focus:border-cyan-400 outline-none">
                    </div>
                </div>
                <div>
                    <label class="block text-[10px] uppercase font-bold text-white/40 mb-1">TAVSIF (IXTIYORIY)</label>
                    <textarea name="description" x-model="editForm.description" class="w-full bg-black border border-white/10 rounded p-2 text-xs text-white focus:border-cyan-400 outline-none" rows="3"></textarea>
                </div>
                <button type="submit" class="w-full py-4 bg-cyan-500/20 text-cyan-400 border border-cyan-500 font-bold text-[10px] uppercase tracking-[0.3em] hover:bg-cyan-500 hover:text-black transition-all">SAQLASH</button>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('courseManager', () => ({
            showAddModal: false,
            showEditModal: false,
            editForm: {
                id: null,
                name: '',
                price: '',
                duration: '',
                description: ''
            },
            openEditModal(course) {
                this.editForm.id = course.id;
                this.editForm.name = course.name;
                this.editForm.price = course.price;
                this.editForm.duration = course.duration;
                this.editForm.description = course.description || '';
                this.showEditModal = true;
            }
        }));
    });
</script>
@endsection
