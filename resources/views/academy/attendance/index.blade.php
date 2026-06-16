@extends('layouts.cyber')

@section('sidebar')
    @include('partials.admin_sidebar')
@endsection

@section('content')
<div class="flex-1 min-h-0 overflow-y-auto w-full p-4 lg:p-8 space-y-6 slim-scroll" x-data="attendanceManager()">
    <div class="glass-panel p-6">
        <div class="flex justify-between items-center mb-8 pt-4 border-b border-white/5 pb-4">
            <div class="panel-title mb-0 flex items-center gap-4">
                <button onclick="window.history.back()" class="w-8 h-8 md:w-10 md:h-10 rounded-full bg-white/5 border border-white/10 flex items-center justify-center text-white/50 hover:text-cyan-400 hover:border-cyan-400/50 transition-all shrink-0">
                    <i class="fa-solid fa-arrow-left"></i>
                </button>
                <div>
                    <i class="fa-solid fa-clipboard-check text-green-400"></i>
                    <span class="text-sm md:text-xl uppercase tracking-widest">{{ $group->name }}: DAVOMAT</span>
                </div>
            </div>
            <div class="text-[10px] font-mono text-white/40 uppercase">{{ now()->format('d.m.Y') }}</div>
        </div>

        <div class="space-y-4">
            @foreach($students as $student)
            <div class="glass-panel bg-white/5 border border-white/10 p-4 rounded-xl flex flex-col md:flex-row justify-between items-center gap-4">
                <div class="flex items-center gap-4 w-full md:w-auto">
                    <div class="w-10 h-10 rounded-full bg-green-500/10 flex items-center justify-center text-green-400 border border-green-500/20 font-bold">
                        {{ substr($student->name, 0, 1) }}
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-white">{{ $student->name }}</h4>
                        <p class="text-[10px] font-mono text-white/40">{{ $student->phone }}</p>
                    </div>
                </div>

                <div class="flex gap-2 w-full md:w-auto overflow-x-auto pb-1">
                    <button @click="mark('{{ $student->id }}', 'present')" :class="getStatusClass('{{ $student->id }}', 'present')" class="px-4 py-2 text-[9px] font-bold uppercase tracking-widest border transition-all">KELDI</button>
                    <button @click="mark('{{ $student->id }}', 'absent')" :class="getStatusClass('{{ $student->id }}', 'absent')" class="px-4 py-2 text-[9px] font-bold uppercase tracking-widest border transition-all">KELMADI</button>
                    <button @click="mark('{{ $student->id }}', 'late')" :class="getStatusClass('{{ $student->id }}', 'late')" class="px-4 py-2 text-[9px] font-bold uppercase tracking-widest border transition-all">KECHIKDI</button>
                </div>
                
                <div x-show="attendances['{{ $student->id }}'] == 'late'" class="w-24 shrink-0" x-transition>
                    <input type="number" x-model="lateMinutes['{{ $student->id }}']" placeholder="Min" class="w-full bg-black border border-white/10 rounded p-1 text-xs text-center text-yellow-400">
                </div>
            </div>
            @endforeach
        </div>

        <div class="mt-10 pt-6 border-t border-white/5">
            <button @click="saveAttendance()" class="w-full py-4 bg-green-600/20 text-green-400 border border-green-500 font-bold text-xs uppercase tracking-[0.3em] hover:bg-green-600 hover:text-white transition-all shadow-[0_0_20px_rgba(34,197,94,0.2)]">DAVOMATNI SAQLASH</button>
        </div>
    </div>
</div>

<script>
    function attendanceManager() {
        return {
            groupId: '{{ $group->id }}',
            attendances: {},
            lateMinutes: {},
            
            mark(studentId, status) {
                this.attendances[studentId] = status;
            },
            
            getStatusClass(studentId, status) {
                const current = this.attendances[studentId];
                if (current === status) {
                    if (status === 'present') return 'bg-green-500/20 border-green-500 text-green-400';
                    if (status === 'absent') return 'bg-red-500/20 border-red-500 text-red-400';
                    if (status === 'late') return 'bg-yellow-500/20 border-yellow-500 text-yellow-400';
                }
                return 'bg-white/5 border-white/10 text-white/30 hover:border-white/30';
            },
            
            saveAttendance() {
                const batch = [];
                Object.keys(this.attendances).forEach(id => {
                    batch.push({
                        student_id: id,
                        status: this.attendances[id],
                        late_minutes: this.lateMinutes[id] || 0
                    });
                });
                
                if (batch.length < {{ count($students) }}) {
                    if (!confirm("Barcha o'quvchilar belgilanmagan. Davom etaveraylikmi?")) return;
                }

                fetch('{{ route('admin.academy.attendance.store') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        group_id: this.groupId,
                        attendances: batch
                    })
                })
                .then(async res => {
                    const data = await res.json();
                    if (res.ok && data.success) {
                        alert(data.message);
                        window.location.href = '{{ route('admin.academy.groups.index') }}';
                    } else {
                        alert("Xatolik: " + (data.message || "Ma'lumotlar to'liq belgilanmagan yoki xato."));
                    }
                })
                .catch(err => {
                    alert("Tarmoq xatosi: " + err.message);
                });
            }
        }
    }
</script>
@endsection
