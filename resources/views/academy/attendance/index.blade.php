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

        <div class="overflow-x-auto slim-scroll bg-[#0a0a0f] rounded-2xl border border-white/5">
            <table class="w-full text-left font-mono text-xs border-collapse min-w-[900px]">
                <thead>
                    <tr class="border-b border-white/10 text-white/40 text-[10px] uppercase tracking-wider bg-white/5">
                        <th class="py-4 px-4 font-black">O'quvchi</th>
                        <th class="py-4 px-4 font-black">Bugungi Davomat</th>
                        <th class="py-4 px-4 font-black text-center">Baho (Bugun)</th>
                        <th class="py-4 px-4 font-black text-center">Haftalik (Keldi/Kelmadi/Kechikdi)</th>
                        <th class="py-4 px-4 font-black text-center">Oylik (Keldi/Kelmadi/Kechikdi)</th>
                        <th class="py-4 px-4 font-black text-center">O'rtacha Baho (30 kun)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @foreach($students as $student)
                    <tr class="hover:bg-white/5 transition-colors">
                        <td class="py-4 px-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-green-500/10 flex items-center justify-center text-green-400 border border-green-500/20 font-bold text-xs">
                                    {{ substr($student->name, 0, 1) }}
                                </div>
                                <div>
                                    <div class="font-bold text-white text-sm">{{ $student->name }}</div>
                                    <div class="text-[10px] text-white/40">{{ $student->phone }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="py-4 px-4">
                            <div class="flex items-center gap-2">
                                <button type="button" @click="mark('{{ $student->id }}', 'present')" :class="getStatusClass('{{ $student->id }}', 'present')" class="px-3 py-1.5 text-[9px] font-bold uppercase tracking-widest border transition-all rounded">KELDI</button>
                                <button type="button" @click="mark('{{ $student->id }}', 'absent')" :class="getStatusClass('{{ $student->id }}', 'absent')" class="px-3 py-1.5 text-[9px] font-bold uppercase tracking-widest border transition-all rounded">KELMADI</button>
                                <button type="button" @click="mark('{{ $student->id }}', 'late')" :class="getStatusClass('{{ $student->id }}', 'late')" class="px-3 py-1.5 text-[9px] font-bold uppercase tracking-widest border transition-all rounded">KECHIKDI</button>
                                
                                <div x-show="attendances['{{ $student->id }}'] == 'late'" class="w-16" x-transition>
                                    <input type="number" x-model="lateMinutes['{{ $student->id }}']" placeholder="Daq" class="w-full bg-black border border-white/10 rounded px-1.5 py-1 text-xs text-center text-yellow-400 outline-none focus:border-yellow-400">
                                </div>
                            </div>
                        </td>
                        <td class="py-4 px-4 text-center">
                            <input type="number" min="0" max="100" x-model="grades['{{ $student->id }}']" placeholder="Baho" class="w-16 bg-black border border-white/10 rounded px-2 py-1 text-xs text-center text-green-400 outline-none focus:border-green-400">
                        </td>
                        <td class="py-4 px-4 text-center text-[11px]">
                            <span class="text-green-400 font-bold" title="Kelgan">{{ $student->weekly_present }}</span> / 
                            <span class="text-red-400 font-bold" title="Kelmadi">{{ $student->weekly_absent }}</span> / 
                            <span class="text-yellow-400 font-bold" title="Kechikdi">{{ $student->weekly_late }}</span>
                        </td>
                        <td class="py-4 px-4 text-center text-[11px]">
                            <span class="text-green-400 font-bold" title="Kelgan">{{ $student->monthly_present }}</span> / 
                            <span class="text-red-400 font-bold" title="Kelmadi">{{ $student->monthly_absent }}</span> / 
                            <span class="text-yellow-400 font-bold" title="Kechikdi">{{ $student->monthly_late }}</span>
                        </td>
                        <td class="py-4 px-4 text-center font-bold text-sm text-yellow-400">
                            {{ $student->monthly_avg_grade }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-10 pt-6 border-t border-white/5">
            <button @click="saveAttendance()" class="w-full py-4 bg-green-600/20 text-green-400 border border-green-500 font-bold text-xs uppercase tracking-[0.3em] hover:bg-green-600 hover:text-white transition-all shadow-[0_0_20px_rgba(34,197,94,0.2)]">DAVOMAT VA BAHOLARNI SAQLASH</button>
        </div>
    </div>
</div>

<script>
    function attendanceManager() {
        return {
            groupId: '{{ $group->id }}',
            attendances: {},
            lateMinutes: {},
            grades: {},
            
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
                        late_minutes: this.lateMinutes[id] || 0,
                        grade: this.grades[id] || null
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
