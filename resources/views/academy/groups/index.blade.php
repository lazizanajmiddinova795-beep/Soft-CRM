@extends('layouts.cyber')

@section('sidebar')
    @include('partials.admin_sidebar')
@endsection

@section('content')
<div class="flex-1 min-h-0 overflow-y-auto w-full p-4 lg:p-8 space-y-6 slim-scroll" x-data="groupManager()">
    <div class="glass-panel p-6">
        <div class="flex justify-between items-center mb-8 pt-4">
            <div class="panel-title mb-0 flex items-center gap-4">
                <button onclick="window.history.back()" class="w-8 h-8 md:w-10 md:h-10 rounded-full bg-white/5 border border-white/10 flex items-center justify-center text-white/50 hover:text-cyan-400 hover:border-cyan-400/50 transition-all shrink-0">
                    <i class="fa-solid fa-arrow-left"></i>
                </button>
                <div>
                    <i class="fa-solid fa-people-group text-blue-400"></i>
                    <span class="text-sm md:text-xl uppercase tracking-widest">FAOL GURUHLAR</span>
                </div>
            </div>
            <button @click="showAddModal = true" class="px-4 py-2 bg-blue-500/20 text-blue-400 border border-blue-500 text-[10px] font-bold uppercase tracking-widest hover:bg-blue-500 hover:text-black transition-colors rounded-sm shadow-[0_0_15px_rgba(59,130,246,0.3)]">YANGI GURUH</button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($groups as $group)
            <div class="glass-panel bg-white/5 border border-white/10 p-6 rounded-2xl relative overflow-hidden group hover:border-blue-500/50 transition-all">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h3 class="text-lg font-bold text-white uppercase">{{ $group->name }}</h3>
                        <p class="text-[10px] text-white/40 font-mono">{{ $group->course->name }}</p>
                    </div>
                    <span class="text-[10px] bg-blue-500/20 text-blue-400 px-2 py-1 rounded">{{ strtoupper($group->status) }}</span>
                </div>
                
                <div class="space-y-3 pt-2">
                    <div class="flex items-center gap-3 text-xs text-white/70">
                        <i class="fa-solid fa-chalkboard-user text-blue-400 w-4"></i>
                        <span>{{ $group->teacher->name }}</span>
                    </div>
                    <div class="flex items-center gap-3 text-xs text-white/70">
                        <i class="fa-solid fa-location-dot text-blue-400 w-4"></i>
                        <span>{{ $group->room->name ?? 'Xona yo\'q' }}</span>
                    </div>
                    <div class="flex items-center gap-3 text-[10px] text-white/40 font-mono">
                        <i class="fa-solid fa-calendar-days text-blue-400 w-4"></i>
                        <span>
                            @foreach($group->days as $day)
                                {{ ['1'=>'Du','2'=>'Se','3'=>'Ch','4'=>'Pa','5'=>'Ju','6'=>'Sh','7'=>'Ya'][$day] }}@if(!$loop->last), @endif
                            @endforeach
                            | {{ substr($group->start_time, 0, 5) }}
                        </span>
                    </div>
                </div>

                <div class="mt-6 pt-4 border-t border-white/5 flex gap-2">
                    <button @click="openEditModal({{ $group->id }}, '{{ addslashes($group->name) }}', '{{ $group->course_id }}', '{{ $group->teacher_id }}', '{{ $group->room_id }}', '{{ $group->start_time }}', '{{ $group->telegram_bot_id }}')" class="flex-1 py-2 bg-white/5 hover:bg-white/10 text-[9px] font-bold uppercase text-white/60 border border-white/10 transition-all">Tahrirlash</button>
                    <a href="{{ route('admin.academy.attendance.students', $group->id) }}" class="flex-1 py-2 bg-blue-500/10 hover:bg-blue-600 text-blue-400 hover:text-white text-center text-[9px] font-bold uppercase border border-blue-500/20 transition-all">Davomat</a>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Add Group Modal -->
    <div x-show="showAddModal" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm p-4">
        <div @click.away="showAddModal = false" class="bg-[#111] w-full max-w-md border border-white/10 rounded-lg shadow-2xl overflow-hidden">
            <div class="p-4 border-b border-white/5 bg-white/5 flex justify-between items-center">
                <h3 class="text-xs font-bold uppercase tracking-widest text-blue-400">Yangi Guruh Ochish</h3>
                <button @click="showAddModal = false" class="text-white/40 hover:text-white"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form action="{{ route('admin.academy.groups.store') }}" method="POST" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-[10px] uppercase font-bold text-white/40 mb-1">GURUH NOMI</label>
                    <input type="text" name="name" required placeholder="i.e. Backend Node-1" class="w-full bg-black border border-white/10 rounded p-2 text-xs text-white focus:border-blue-400 outline-none">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] uppercase font-bold text-white/40 mb-1">KURS</label>
                        <select name="course_id" required class="w-full bg-black border border-white/10 rounded p-2 text-xs text-white focus:border-blue-400 outline-none">
                            @foreach($courses as $course)
                                <option value="{{ $course->id }}">{{ $course->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] uppercase font-bold text-white/40 mb-1">O'QITUVCHI</label>
                        <select name="teacher_id" required class="w-full bg-black border border-white/10 rounded p-2 text-xs text-white focus:border-blue-400 outline-none">
                            @foreach($teachers as $teacher)
                                <option value="{{ $teacher->id }}">{{ $teacher->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] uppercase font-bold text-white/40 mb-1">XONA</label>
                        <select name="room_id" required class="w-full bg-black border border-white/10 rounded p-2 text-xs text-white focus:border-blue-400 outline-none">
                            @foreach($rooms as $room)
                                <option value="{{ $room->id }}">{{ $room->name }} ({{ $room->capacity }} kishi)</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] uppercase font-bold text-white/40 mb-1">DARTS VAQTI</label>
                        <input type="time" name="start_time" required class="w-full bg-black border border-white/10 rounded p-2 text-xs text-white focus:border-blue-400 outline-none">
                    </div>
                </div>
                <div>
                    <label class="block text-[10px] uppercase font-bold text-white/40 mb-1">KUNLAR</label>
                    <div class="grid grid-cols-4 gap-2">
                        @foreach(['1'=>'Du','2'=>'Se','3'=>'Ch','4'=>'Pa','5'=>'Ju','6'=>'Sh','7'=>'Ya'] as $v => $l)
                        <label class="flex items-center gap-2 p-2 border border-white/5 bg-white/5 rounded cursor-pointer hover:bg-white/10">
                            <input type="checkbox" name="days[]" value="{{ $v }}" class="accent-blue-500">
                            <span class="text-[10px] text-white/60">{{ $l }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
                <div>
                    <label class="block text-[10px] uppercase font-bold text-white/40 mb-1">DAVOMAT TELEGRAM BOT (IXTIYORIY)</label>
                    <select name="telegram_bot_id" class="w-full bg-black border border-white/10 rounded p-2 text-xs text-white focus:border-blue-400 outline-none">
                        <option value="">-- Bot tanlanmagan --</option>
                        @foreach($telegramBots as $bot)
                            <option value="{{ $bot->id }}">{{ $bot->name }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="w-full py-4 bg-blue-500/20 text-blue-400 border border-blue-500 font-bold text-[10px] uppercase tracking-[0.3em] hover:bg-blue-500 hover:text-black transition-all">GURUHNI TASDIQLASH</button>
            </form>
        </div>
    </div>
    <!-- Edit Group Modal -->
    <div x-show="showEditModal" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm p-4">
        <div @click.away="showEditModal = false" class="bg-[#111] w-full max-w-md border border-white/10 rounded-lg shadow-2xl overflow-hidden">
            <div class="p-4 border-b border-white/5 bg-white/5 flex justify-between items-center">
                <h3 class="text-xs font-bold uppercase tracking-widest text-blue-400">Guruhni Tahrirlash</h3>
                <button @click="showEditModal = false" class="text-white/40 hover:text-white"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form :action="editUrl" method="POST" class="p-6 space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-[10px] uppercase font-bold text-white/40 mb-1">GURUH NOMI</label>
                    <input type="text" name="name" x-model="editData.name" required class="w-full bg-black border border-white/10 rounded p-2 text-xs text-white focus:border-blue-400 outline-none">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] uppercase font-bold text-white/40 mb-1">KURS</label>
                        <select name="course_id" x-model="editData.course_id" required class="w-full bg-black border border-white/10 rounded p-2 text-xs text-white focus:border-blue-400 outline-none">
                            @foreach($courses as $course)
                                <option value="{{ $course->id }}">{{ $course->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] uppercase font-bold text-white/40 mb-1">O'QITUVCHI</label>
                        <select name="teacher_id" x-model="editData.teacher_id" required class="w-full bg-black border border-white/10 rounded p-2 text-xs text-white focus:border-blue-400 outline-none">
                            @foreach($teachers as $teacher)
                                <option value="{{ $teacher->id }}">{{ $teacher->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] uppercase font-bold text-white/40 mb-1">XONA</label>
                        <select name="room_id" x-model="editData.room_id" required class="w-full bg-black border border-white/10 rounded p-2 text-xs text-white focus:border-blue-400 outline-none">
                            @foreach($rooms as $room)
                                <option value="{{ $room->id }}">{{ $room->name }} ({{ $room->capacity }} kishi)</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] uppercase font-bold text-white/40 mb-1">DARTS VAQTI</label>
                        <input type="time" name="start_time" x-model="editData.start_time" required class="w-full bg-black border border-white/10 rounded p-2 text-xs text-white focus:border-blue-400 outline-none">
                    </div>
                </div>
                <div>
                    <label class="block text-[10px] uppercase font-bold text-white/40 mb-1">DAVOMAT TELEGRAM BOT (IXTIYORIY)</label>
                    <select name="telegram_bot_id" x-model="editData.telegram_bot_id" class="w-full bg-black border border-white/10 rounded p-2 text-xs text-white focus:border-blue-400 outline-none">
                        <option value="">-- Bot tanlanmagan --</option>
                        @foreach($telegramBots as $bot)
                            <option value="{{ $bot->id }}">{{ $bot->name }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="w-full py-4 bg-blue-500/20 text-blue-400 border border-blue-500 font-bold text-[10px] uppercase tracking-[0.3em] hover:bg-blue-500 hover:text-black transition-all">YANGILASH</button>
            </form>
        </div>
    </div>
</div>

<script>
    function groupManager() {
        return {
            showAddModal: false,
            showEditModal: false,
            editUrl: '',
            editData: {
                name: '',
                course_id: '',
                teacher_id: '',
                room_id: '',
                start_time: '',
                telegram_bot_id: ''
            },
            openEditModal(id, name, course_id, teacher_id, room_id, start_time, bot_id) {
                this.editUrl = `{{ url('admin/academy/groups') }}/${id}`;
                this.editData.name = name;
                this.editData.course_id = course_id;
                this.editData.teacher_id = teacher_id;
                this.editData.room_id = room_id;
                this.editData.start_time = start_time;
                this.editData.telegram_bot_id = bot_id;
                this.showEditModal = true;
            }
        }
    }
</script>
@endsection
