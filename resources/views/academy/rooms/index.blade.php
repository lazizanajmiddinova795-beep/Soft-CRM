@extends('layouts.cyber')

@section('sidebar')
    @if(auth()->user()->role === 'admin')
        @include('partials.admin_sidebar')
    @elseif(auth()->user()->role === 'cashier')
        @include('partials.cashier_sidebar')
    @endif
@endsection

@section('content')
<div class="flex-1 min-h-0 overflow-y-auto w-full p-4 lg:p-8 space-y-6 slim-scroll" x-data="roomManager()">
    <div class="glass-panel p-6">
        <div class="flex justify-between items-center mb-8 pt-4">
            <div class="panel-title mb-0 flex items-center gap-4">
                <button onclick="window.history.back()" class="w-8 h-8 md:w-10 md:h-10 rounded-full bg-white/5 border border-white/10 flex items-center justify-center text-white/50 hover:text-cyan-400 hover:border-cyan-400/50 transition-all shrink-0">
                    <i class="fa-solid fa-arrow-left"></i>
                </button>
                <div>
                    <i class="fa-solid fa-door-open text-purple-400"></i>
                    <span class="text-sm md:text-xl uppercase tracking-widest">XONALAR VA BANDLIK JADVALI</span>
                </div>
            </div>
            <button @click="showAddModal = true" class="px-4 py-2 bg-purple-500/20 text-purple-400 border border-purple-500 text-[10px] font-bold uppercase tracking-widest hover:bg-purple-500 hover:text-black transition-colors rounded-sm shadow-[0_0_15px_rgba(168,85,247,0.3)]">YANGI XONA</button>
        </div>

        <div class="grid grid-cols-1 gap-8">
            @foreach($rooms as $room)
            <div class="glass-panel bg-white/5 border border-white/10 p-6 rounded-2xl overflow-hidden relative">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4 border-b border-white/5 pb-4">
                    <div>
                        <h3 class="text-lg font-black text-white uppercase tracking-tighter">{{ $room->name }}</h3>
                        <p class="text-[10px] font-mono text-white/40 uppercase tracking-widest">Sig'im: {{ $room->capacity }} kishi | ID: #RM-{{ $room->id }}</p>
                    </div>
                    <div class="flex gap-2">
                        <button @click="openEditModal({{ json_encode(['id' => $room->id, 'name' => $room->name, 'capacity' => $room->capacity]) }})" class="px-3 py-1 bg-white/5 hover:bg-white/10 text-[9px] font-bold uppercase border border-white/10 transition-colors">Tahrirlash</button>
                    </div>
                </div>

                <!-- Scheduling Grid -->
                <div class="overflow-x-auto overflow-y-hidden slim-scroll pb-2">
                    <div class="min-w-[800px] grid grid-cols-8 gap-1 bg-white/5 p-1 rounded-sm">
                        <!-- Header Hours -->
                        <div class="p-2 text-[9px] font-bold text-white/20 uppercase text-center border-r border-white/5">Vaqt</div>
                        @foreach(['Dush','Sesh','Chor','Pay','Jum','Shan','Yak'] as $day)
                            <div class="p-2 text-[9px] font-bold text-purple-400 uppercase text-center">{{ $day }}</div>
                        @endforeach

                        <!-- Time Rows (08:00 to 20:00) -->
                        @for($h = 8; $h <= 20; $h++)
                            <div class="p-2 text-[10px] font-mono text-white/40 border-r border-white/5 text-center bg-black/20">{{ sprintf('%02d:00', $h) }}</div>
                            @for($d = 1; $d <= 7; $d++)
                                <div class="relative h-12 bg-black/40 border border-white/5 group hover:bg-white/5 transition-colors">
                                    @php
                                        $occupancy = null;
                                        foreach($room->groups as $group) {
                                            foreach($group->schedules as $sch) {
                                                if($sch->day_of_week == $d && intval(substr($sch->start_time, 0, 2)) == $h) {
                                                    $occupancy = $group;
                                                    break 2;
                                                }
                                            }
                                        }
                                    @endphp
                                    @if($occupancy)
                                        <div class="absolute inset-x-1 inset-y-1 bg-purple-500/20 border border-purple-500/40 rounded p-1 text-[8px] flex flex-col justify-center items-center text-purple-400 cursor-pointer hover:bg-purple-500/40 transition-all">
                                            <span class="font-bold truncate w-full text-center">{{ $occupancy->name }}</span>
                                            <span class="opacity-60">{{ substr($occupancy->schedules->where('day_of_week', $d)->first()->start_time, 0, 5) }}</span>
                                        </div>
                                    @else
                                        <div @click="openScheduleModal({{ $room->id }}, {{ $d }}, '{{ sprintf('%02d:00', $h) }}')" class="absolute inset-0 cursor-pointer hover:bg-white/10 flex items-center justify-center opacity-0 hover:opacity-100 transition-opacity">
                                            <i class="fa-solid fa-plus text-white/30"></i>
                                        </div>
                                    @endif
                                </div>
                            @endfor
                        @endfor
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Add Room Modal -->
    <div x-show="showAddModal" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm p-4">
        <div @click.away="showAddModal = false" class="bg-[#111] w-full max-w-sm border border-white/10 rounded-lg shadow-2xl overflow-hidden">
            <div class="p-4 border-b border-white/5 bg-white/5 flex justify-between items-center">
                <h3 class="text-xs font-bold uppercase tracking-widest text-purple-400">Yangi Xona Qo'shish</h3>
                <button @click="showAddModal = false" class="text-white/40 hover:text-white"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form action="{{ route('admin.academy.rooms.store') }}" method="POST" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-[10px] uppercase font-bold text-white/40 mb-1">XONA NOMI</label>
                    <input type="text" name="name" required placeholder="i.e. Room 101" class="w-full bg-black border border-white/10 rounded p-2 text-xs text-white focus:border-purple-400 outline-none transition-all">
                </div>
                <div>
                    <label class="block text-[10px] uppercase font-bold text-white/40 mb-1">SIG'IM (KISHI)</label>
                    <input type="number" name="capacity" placeholder="0" class="w-full bg-black border border-white/10 rounded p-2 text-xs text-white focus:border-purple-400 outline-none transition-all">
                </div>
                <button type="submit" class="w-full py-4 bg-purple-500/20 text-purple-400 border border-purple-500 font-bold text-[10px] uppercase tracking-[0.3em] hover:bg-purple-500 hover:text-black transition-all">SAQLASH</button>
            </form>
        </div>
    </div>
    
    <!-- Edit Room Modal -->
    <div x-show="showEditModal" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm p-4">
        <div @click.away="showEditModal = false" class="bg-[#111] w-full max-w-sm border border-white/10 rounded-lg shadow-2xl overflow-hidden">
            <div class="p-4 border-b border-white/5 bg-white/5 flex justify-between items-center">
                <h3 class="text-xs font-bold uppercase tracking-widest text-purple-400">Xonani Tahrirlash</h3>
                <button @click="showEditModal = false" type="button" class="text-white/40 hover:text-white"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form :action="editUrl" method="POST" class="p-6 space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-[10px] uppercase font-bold text-white/40 mb-1">XONA NOMI</label>
                    <input type="text" name="name" x-model="editData.name" required placeholder="i.e. Room 101" class="w-full bg-black border border-white/10 rounded p-2 text-xs text-white focus:border-purple-400 outline-none transition-all">
                </div>
                <div>
                    <label class="block text-[10px] uppercase font-bold text-white/40 mb-1">SIG'IM (KISHI)</label>
                    <input type="number" name="capacity" x-model="editData.capacity" placeholder="0" class="w-full bg-black border border-white/10 rounded p-2 text-xs text-white focus:border-purple-400 outline-none transition-all">
                </div>
                <button type="submit" class="w-full py-4 bg-purple-500/20 text-purple-400 border border-purple-500 font-bold text-[10px] uppercase tracking-[0.3em] hover:bg-purple-500 hover:text-black transition-all">YANGILASH</button>
            </form>
        </div>
    </div>
    <!-- Add Schedule Modal -->
    <div x-show="showScheduleModal" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm p-4">
        <div @click.away="showScheduleModal = false" class="bg-[#111] w-full max-w-sm border border-white/10 rounded-lg shadow-2xl overflow-hidden">
            <div class="p-4 border-b border-white/5 bg-white/5 flex justify-between items-center">
                <h3 class="text-xs font-bold uppercase tracking-widest text-cyan-400">Jadvalga Guruh Biriktirish</h3>
                <button @click="showScheduleModal = false" class="text-white/40 hover:text-white"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form action="{{ route('admin.academy.schedules.store') }}" method="POST" class="p-6 space-y-4">
                @csrf
                <input type="hidden" name="day_of_week" x-model="scheduleData.day_of_week">
                <input type="hidden" name="start_time" x-model="scheduleData.start_time">
                
                <div>
                    <label class="block text-[10px] uppercase font-bold text-white/40 mb-1">GURUH</label>
                    <select name="group_id" required class="w-full bg-black border border-white/10 rounded p-2 text-xs text-white focus:border-cyan-400 outline-none transition-all">
                        <option value="">-- Guruhni tanlang --</option>
                        @foreach($allGroups as $g)
                            <option value="{{ $g->id }}">{{ $g->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] uppercase font-bold text-white/40 mb-1">VAQT / KUN</label>
                    <div class="p-2 border border-white/5 bg-white/5 rounded text-xs text-white/60">
                        Kun: <span x-text="scheduleData.day_of_week" class="font-bold text-cyan-400"></span>, 
                        Vaqt: <span x-text="scheduleData.start_time" class="font-bold text-cyan-400"></span>
                    </div>
                </div>
                <button type="submit" class="w-full py-4 bg-cyan-500/20 text-cyan-400 border border-cyan-500 font-bold text-[10px] uppercase tracking-[0.3em] hover:bg-cyan-500 hover:text-black transition-all">JADVALNI SAQLASH</button>
            </form>
        </div>
    </div>
</div>
<script>
    function roomManager() {
        return {
            showAddModal: false,
            showEditModal: false,
            showScheduleModal: false,
            editUrl: '',
            editData: { name: '', capacity: '' },
            scheduleData: { room_id: '', day_of_week: '', start_time: '' },
            openEditModal(room) {
                this.editData.name = room.name;
                this.editData.capacity = room.capacity;
                this.editUrl = `{{ url('admin/academy/rooms') }}/${room.id}`;
                this.showEditModal = true;
            },
            openScheduleModal(roomId, day, time) {
                this.scheduleData.room_id = roomId;
                this.scheduleData.day_of_week = day;
                this.scheduleData.start_time = time;
                this.showScheduleModal = true;
            }
        }
    }
</script>
@endsection
