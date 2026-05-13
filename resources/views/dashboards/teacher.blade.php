@extends('layouts.app')
@section('title', 'O\'qituvchi Paneli | Obsidian OS')
@section('content')
<div x-data="teacherDashboard()" class="h-screen flex flex-col md:flex-row bg-[#0b0c10] text-[#c5c6c7] font-sans selection:bg-[#66fcf1] selection:text-[#0b0c10] overflow-hidden">
    
    <!-- Sidebar -->
    <aside class="w-full md:w-64 bg-[#1f2833] shadow-2xl flex flex-col transition-all duration-300 z-20 cyber-border border-r border-[#66fcf1]/20">
        <div class="p-6 flex items-center justify-center border-b border-[#66fcf1]/10 bg-gradient-to-r from-[#1f2833] to-[#0b0c10]">
            <h1 class="text-2xl font-black text-transparent bg-clip-text bg-gradient-to-r from-[#66fcf1] to-[#45a29e] tracking-wider drop-shadow-[0_0_10px_rgba(102,252,241,0.5)]">OBSIDIAN</h1>
        </div>
        
        <div class="flex-1 overflow-y-auto cyber-scrollbar p-4 space-y-2">
            <a href="#" @click="activeTab = 'dashboard'" :class="activeTab === 'dashboard' ? 'bg-gradient-to-r from-[#66fcf1]/20 to-transparent text-[#66fcf1] border-l-2 border-[#66fcf1]' : 'text-gray-400 hover:text-[#45a29e] hover:bg-[#66fcf1]/5'" class="flex items-center space-x-3 w-full p-3 rounded-lg transition-all">
                <svg class="w-5 h-5 drop-shadow-[0_0_5px_rgba(102,252,241,0.8)]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                <span class="font-bold tracking-wide">Bosh Sahifa</span>
            </a>
            
            <a href="{{ route('teacher.students') }}" :class="activeTab === 'students' ? 'bg-gradient-to-r from-[#66fcf1]/20 to-transparent text-[#66fcf1] border-l-2 border-[#66fcf1]' : 'text-gray-400 hover:text-[#45a29e] hover:bg-[#66fcf1]/5'" class="flex items-center space-x-3 w-full p-3 rounded-lg transition-all group">
                <svg class="w-5 h-5 group-hover:drop-shadow-[0_0_5px_rgba(69,162,158,0.8)]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                <span class="font-semibold transition-colors group-hover:text-[#45a29e]">O'quvchilarim</span>
            </a>
        </div>
        
        <div class="p-4 border-t border-[#66fcf1]/10 bg-[#0b0c10]/50 backdrop-blur-sm">
            <div class="flex items-center space-x-3 mb-4">
                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-[#66fcf1] to-[#45a29e] flex items-center justify-center text-[#0b0c10] font-bold shadow-[0_0_10px_rgba(102,252,241,0.5)]">
                    {{ substr(auth()->user()->name, 0, 1) }}
                </div>
                <div class="flex-1 overflow-hidden">
                    <p class="text-sm font-bold text-white truncate">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-[#45a29e] uppercase tracking-wider">{{ auth()->user()->role }}</p>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="w-full py-2 bg-red-500/10 hover:bg-red-500/20 text-red-400 hover:text-red-300 rounded-lg transition-all font-semibold flex items-center justify-center space-x-2 border border-red-500/20 hover:border-red-500/40 hover:shadow-[0_0_15px_rgba(239,68,68,0.3)]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    <span>Tizimdan chiqish</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col h-screen overflow-hidden relative">
        <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] opacity-5 pointer-events-none"></div>
        <div class="absolute top-0 left-0 w-full h-[500px] bg-gradient-to-b from-[#66fcf1]/5 to-transparent pointer-events-none"></div>
        
        <header class="h-16 flex items-center justify-between px-6 bg-[#1f2833]/80 backdrop-blur-md border-b border-[#66fcf1]/10 z-10 sticky top-0">
            <h2 class="text-xl font-bold text-white flex items-center space-x-2">
                <span class="w-2 h-2 rounded-full bg-[#66fcf1] animate-pulse shadow-[0_0_8px_rgba(102,252,241,1)]"></span>
                <span>O'qituvchi Boshqaruvi</span>
            </h2>
            <div class="flex items-center space-x-4">
               <span class="text-sm font-mono text-[#45a29e] px-3 py-1 bg-[#0b0c10] border border-[#66fcf1]/30 rounded-md shadow-[inset_0_0_8px_rgba(102,252,241,0.1)]">
                   {{ now()->format('d.m.Y H:i') }}
               </span>
            </div>
        </header>

        <div class="flex-1 overflow-y-auto cyber-scrollbar p-6 z-10">
            <div x-show="activeTab === 'dashboard'">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="bg-[#1f2833] rounded-xl border border-[#66fcf1]/20 p-5 relative overflow-hidden group hover:border-[#66fcf1]/50 transition-all duration-300 shadow-[0_0_15px_rgba(0,0,0,0.5)]">
                        <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-[#66fcf1]/10 rounded-full blur-xl group-hover:bg-[#66fcf1]/20 transition-all"></div>
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-gray-400 font-semibold uppercase tracking-wider text-xs">Mening Guruhlarim</h3>
                            <div class="p-2 bg-[#0b0c10] rounded-lg border border-[#66fcf1]/30">
                                <svg class="w-5 h-5 text-[#66fcf1]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            </div>
                        </div>
                        <p class="text-3xl font-black text-white">{{ $groups->count() }} <span class="text-sm font-medium text-gray-500">ta guruh</span></p>
                    </div>
                    
                    <div class="bg-[#1f2833] rounded-xl border border-[#45a29e]/20 p-5 relative overflow-hidden group hover:border-[#45a29e]/50 transition-all duration-300 shadow-[0_0_15px_rgba(0,0,0,0.5)]">
                        <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-[#45a29e]/10 rounded-full blur-xl group-hover:bg-[#45a29e]/20 transition-all"></div>
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-gray-400 font-semibold uppercase tracking-wider text-xs">Jami O'quvchilar</h3>
                            <div class="p-2 bg-[#0b0c10] rounded-lg border border-[#45a29e]/30">
                                <svg class="w-5 h-5 text-[#45a29e]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                            </div>
                        </div>
                        @php $totalStudents = 0; foreach($groups as $g) { $totalStudents += $g->students->count(); } @endphp
                        <p class="text-3xl font-black text-white">{{ $totalStudents }} <span class="text-sm font-medium text-gray-500">ta o'quvchi</span></p>
                    </div>

                    <div class="bg-[#1f2833] rounded-xl border border-blue-500/20 p-5 relative overflow-hidden group hover:border-blue-500/50 transition-all duration-300 shadow-[0_0_15px_rgba(0,0,0,0.5)]">
                        <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-blue-500/10 rounded-full blur-xl group-hover:bg-blue-500/20 transition-all"></div>
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-gray-400 font-semibold uppercase tracking-wider text-xs">KPI & Reyting</h3>
                            <div class="p-2 bg-[#0b0c10] rounded-lg border border-blue-500/30">
                                <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                            </div>
                        </div>
                        <p class="text-3xl font-black text-white">4.8 <span class="text-sm font-medium text-gray-500">/ 5.0</span></p>
                    </div>
                </div>

                <!-- BUGUNGI DARSLAR JADVALI -->
                <div class="bg-[#1f2833] rounded-xl border border-[#66fcf1]/20 overflow-hidden shadow-[0_0_20px_rgba(0,0,0,0.6)] mb-8">
                    <div class="p-5 border-b border-[#66fcf1]/10 flex justify-between items-center bg-gradient-to-r from-[#1f2833] to-[#0b0c10]">
                        <h3 class="text-lg font-bold text-white flex items-center space-x-2">
                            <svg class="w-5 h-5 text-[#66fcf1]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <span>Bugungi Dars Jadvali</span>
                        </h3>
                    </div>
                    <div class="p-0">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-[#0b0c10]/80 text-gray-400 text-xs uppercase tracking-wider">
                                    <th class="py-3 px-6 font-semibold">Vaqt</th>
                                    <th class="py-3 px-6 font-semibold">Guruh & Kurs</th>
                                    <th class="py-3 px-6 font-semibold">Xona</th>
                                    <th class="py-3 px-6 font-semibold text-right">Harakatlar</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#66fcf1]/5 text-sm">
                                @forelse($schedules->where('day_of_week', now()->dayOfWeekIso) as $schedule)
                                <tr class="hover:bg-[#66fcf1]/5 transition-colors">
                                    <td class="py-4 px-6 font-mono text-[#66fcf1] font-medium">{{ substr($schedule->start_time, 0, 5) }} - {{ substr($schedule->end_time, 0, 5) }}</td>
                                    <td class="py-4 px-6">
                                        <div class="font-bold text-white">{{ $schedule->group->name }}</div>
                                        <div class="text-xs text-gray-400">{{ $schedule->group->course->name ?? 'Kurs kiritilmagan' }}</div>
                                    </td>
                                    <td class="py-4 px-6">
                                        <span class="px-2 py-1 bg-[#45a29e]/10 text-[#45a29e] rounded border border-[#45a29e]/30 text-xs">Xona: {{ $schedule->group->room->name ?? 'Kiritilmagan' }}</span>
                                    </td>
                                    <td class="py-4 px-6 text-right flex justify-end gap-2">
                                        <button @click="openAttendance({{ $schedule->group_id }})" class="px-3 py-1.5 bg-[#66fcf1]/10 hover:bg-[#66fcf1]/20 text-[#66fcf1] rounded border border-[#66fcf1]/30 hover:border-[#66fcf1]/60 transition-all text-[10px] font-bold uppercase hover:shadow-[0_0_10px_rgba(102,252,241,0.3)]">Davomat</button>
                                        <button @click="openJournal({{ $schedule->group_id }})" class="px-3 py-1.5 bg-blue-500/10 hover:bg-blue-500/20 text-blue-400 rounded border border-blue-500/30 hover:border-blue-500/60 transition-all text-[10px] font-bold uppercase hover:shadow-[0_0_10px_rgba(59,130,246,0.3)]">Jurnal & Baho</button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="py-8 px-6 text-center text-gray-500">Bugun rejalashtirilgan darslar yo'q</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Attendance Modal -->
        <div x-show="attendanceModal" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-black/90 backdrop-blur-md p-4">
            <div @click.away="attendanceModal = false" class="bg-[#1f2833] w-full max-w-2xl rounded-2xl border border-[#66fcf1]/30 shadow-[0_0_50px_rgba(0,0,0,0.8)] flex flex-col max-h-[90vh]">
                <div class="p-6 border-b border-[#66fcf1]/10 flex justify-between items-center bg-gradient-to-r from-[#1f2833] to-[#0b0c10]">
                    <div>
                        <h3 class="text-xl font-bold text-white" x-text="'Guruh: ' + activeGroupName"></h3>
                        <p class="text-xs text-[#66fcf1] font-mono tracking-widest uppercase mt-1">Davomat Terminali</p>
                    </div>
                    <button @click="attendanceModal = false" class="text-gray-500 hover:text-white transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                
                <div class="flex-1 overflow-y-auto cyber-scrollbar p-6">
                    <template x-if="loading">
                        <div class="flex flex-col items-center justify-center py-20 space-y-4">
                            <div class="w-12 h-12 border-4 border-[#66fcf1]/20 border-t-[#66fcf1] rounded-full animate-spin"></div>
                            <p class="text-xs text-[#66fcf1] font-mono animate-pulse uppercase tracking-[0.2em]">Sinxronizatsiya qilinmoqda...</p>
                        </div>
                    </template>
                    
                    <template x-if="!loading">
                        <div class="space-y-4">
                            <template x-for="(student, index) in activeStudents" :key="student.id">
                                <div class="bg-[#0b0c10]/50 p-4 rounded-xl border border-[#66fcf1]/5 hover:border-[#66fcf1]/20 transition-all flex flex-col md:flex-row md:items-center justify-between gap-4">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-8 h-8 rounded bg-[#66fcf1]/10 flex items-center justify-center text-[#66fcf1] font-mono text-xs border border-[#66fcf1]/20" x-text="index + 1"></div>
                                        <span class="text-sm font-bold text-white" x-text="student.name"></span>
                                    </div>
                                    
                                    <div class="flex items-center space-x-2">
                                        <button @click="setAttendance(student.id, 'present')" :class="getAttStatus(student.id) === 'present' ? 'bg-green-500/20 text-green-400 border-green-500' : 'bg-gray-500/10 text-gray-500 border-gray-500/20'" class="px-3 py-1.5 rounded-lg border text-[10px] font-black uppercase tracking-widest transition-all">KELDI</button>
                                        <button @click="setAttendance(student.id, 'absent')" :class="getAttStatus(student.id) === 'absent' ? 'bg-red-500/20 text-red-400 border-red-500' : 'bg-gray-500/10 text-gray-500 border-gray-500/20'" class="px-3 py-1.5 rounded-lg border text-[10px] font-black uppercase tracking-widest transition-all">KELMADI</button>
                                        <div class="flex items-center bg-gray-500/10 rounded-lg p-1 border border-gray-500/20">
                                            <button @click="setAttendance(student.id, 'late')" :class="getAttStatus(student.id) === 'late' ? 'bg-yellow-500/20 text-yellow-500 border-yellow-500' : 'bg-transparent text-gray-500 border-transparent'" class="px-3 py-1 rounded border text-[10px] font-black uppercase tracking-widest transition-all">LATE</button>
                                            <template x-if="getAttStatus(student.id) === 'late'">
                                                <input type="number" x-model="attendances[student.id].late_minutes" placeholder="min" class="w-12 bg-black/40 border-none text-xs text-yellow-500 focus:ring-0 text-center font-bold">
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>
                
                <div class="p-6 border-t border-[#66fcf1]/10 bg-[#0b0c10]/50">
                    <button @click="submitAttendance()" :disabled="submitting || activeStudents.length === 0" class="w-full py-4 bg-gradient-to-r from-[#66fcf1] to-[#45a29e] text-[#0b0c10] font-black uppercase tracking-[0.2em] rounded-xl hover:shadow-[0_0_20px_rgba(102,252,241,0.5)] transition-all disabled:opacity-50 flex items-center justify-center space-x-2">
                        <span x-show="!submitting">DAVOMATNI TASDIQLASH</span>
                        <span x-show="submitting" class="flex items-center">
                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-[#0b0c10]" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            JARAYONDA...
                        </span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Journal Modal -->
        <div x-show="journalModal" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-black/90 backdrop-blur-md p-4">
            <div @click.away="journalModal = false" class="bg-[#1f2833] w-full max-w-4xl rounded-2xl border border-blue-500/30 shadow-[0_0_50px_rgba(0,0,0,0.8)] flex flex-col max-h-[90vh]">
                <div class="p-6 border-b border-blue-500/10 flex justify-between items-center bg-gradient-to-r from-[#1f2833] to-[#0b0c10]">
                    <div>
                        <h3 class="text-xl font-bold text-white" x-text="'Jurnal - Guruh: ' + activeGroupName"></h3>
                        <p class="text-xs text-blue-400 font-mono tracking-widest uppercase mt-1">Baholash va Mavzu kiritish</p>
                    </div>
                    <button @click="journalModal = false" class="text-gray-500 hover:text-white transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                
                <div class="flex-1 overflow-y-auto cyber-scrollbar p-6 grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Mavzu kiritish (Chap tomon) -->
                    <div class="lg:col-span-1 bg-[#0b0c10]/50 p-4 rounded-xl border border-blue-500/10 flex flex-col gap-4">
                        <h4 class="text-sm font-bold text-blue-400 uppercase border-b border-blue-500/20 pb-2 mb-2"><i class="fa-solid fa-book-open"></i> Dars Mavzusi</h4>
                        <div>
                            <label class="block text-[10px] uppercase font-bold text-white/40 mb-1">Mavzu Nomi</label>
                            <input type="text" x-model="topicData.topic" placeholder="i.e. Laravel Eloquent" class="w-full bg-black border border-white/10 rounded p-2 text-xs text-white focus:border-blue-400 outline-none transition-all">
                        </div>
                        <div>
                            <label class="block text-[10px] uppercase font-bold text-white/40 mb-1">Qisqacha Tavsif</label>
                            <textarea x-model="topicData.description" rows="4" placeholder="..." class="w-full bg-black border border-white/10 rounded p-2 text-xs text-white focus:border-blue-400 outline-none transition-all"></textarea>
                        </div>
                        <button @click="submitTopic()" :disabled="submittingTopic || !topicData.topic" class="w-full py-2 bg-blue-500/20 text-blue-400 border border-blue-500 font-bold text-[10px] uppercase tracking-widest rounded hover:bg-blue-500 hover:text-black transition-all disabled:opacity-50 mt-auto">Mavzuni Saqlash</button>
                    </div>

                    <!-- Baholash (O'ng tomon) -->
                    <div class="lg:col-span-2">
                        <h4 class="text-sm font-bold text-blue-400 uppercase border-b border-blue-500/20 pb-2 mb-4"><i class="fa-solid fa-star"></i> O'quvchilarni Baholash</h4>
                        
                        <template x-if="loading">
                            <div class="flex justify-center py-10"><div class="w-8 h-8 border-4 border-blue-500/20 border-t-blue-500 rounded-full animate-spin"></div></div>
                        </template>
                        
                        <template x-if="!loading">
                            <div class="space-y-3">
                                <template x-for="(student, index) in activeStudents" :key="student.id">
                                    <div class="bg-[#0b0c10]/50 p-3 rounded-lg border border-blue-500/10 flex flex-col md:flex-row gap-3 items-center justify-between">
                                        <div class="font-bold text-white text-sm" x-text="student.name"></div>
                                        <div class="flex items-center gap-2 w-full md:w-auto">
                                            <input type="number" x-model="grades[student.id].grade" placeholder="Baho (1-100)" class="w-20 bg-black border border-white/10 rounded p-1 text-xs text-center text-white focus:border-blue-400 outline-none">
                                            <select x-model="grades[student.id].knowledge_level" class="w-24 bg-black border border-white/10 rounded p-1 text-xs text-white focus:border-blue-400 outline-none">
                                                <option value="">Daraja</option>
                                                <option value="A+">A+</option>
                                                <option value="A">A</option>
                                                <option value="B">B</option>
                                                <option value="C">C</option>
                                                <option value="Qoniqarsiz">Qoniqarsiz</option>
                                            </select>
                                            <input type="text" x-model="grades[student.id].comment" placeholder="Izoh..." class="flex-1 min-w-[100px] bg-black border border-white/10 rounded p-1 text-xs text-white focus:border-blue-400 outline-none">
                                            <button @click="submitGrade(student.id)" class="px-3 py-1.5 bg-blue-500 text-[#0b0c10] font-bold text-[10px] uppercase rounded hover:shadow-[0_0_10px_rgba(59,130,246,0.5)] transition-all shrink-0"><i class="fa-solid fa-check"></i></button>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>

    </main>
</div>

<script>
    function teacherDashboard() {
        return {
            activeTab: 'dashboard',
            attendanceModal: false,
            journalModal: false,
            loading: false,
            submitting: false,
            submittingTopic: false,
            activeGroupId: null,
            activeGroupName: '',
            activeStudents: [],
            attendances: {}, 
            grades: {}, // {student_id: {grade: '', knowledge_level: '', comment: ''}}
            topicData: {topic: '', description: ''},

            init() {
                console.log('Teacher Terminal Core Active');
            },

            openAttendance(groupId) {
                this.activeGroupId = groupId;
                this.attendanceModal = true;
                this.loading = true;
                this.activeStudents = [];
                this.attendances = {};

                fetch(`/admin/academy/attendance/${groupId}`, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(res => res.json())
                .then(data => {
                    this.activeGroupName = data.group;
                    this.activeStudents = data.students;
                    // Pre-fill present status
                    data.students.forEach(s => {
                        this.attendances[s.id] = { student_id: s.id, status: 'present', late_minutes: 0 };
                    });
                    this.loading = false;
                })
                .catch(err => {
                    console.error(err);
                    alert('Ma\'lumotlarni yuklashda xatolik!');
                    this.loading = false;
                });
            },

            setAttendance(studentId, status) {
                this.attendances[studentId].status = status;
                if(status !== 'late') this.attendances[studentId].late_minutes = 0;
            },

            getAttStatus(studentId) {
                return this.attendances[studentId]?.status;
            },

            submitAttendance() {
                if(this.submitting) return;
                this.submitting = true;

                const payload = {
                    group_id: this.activeGroupId,
                    attendances: Object.values(this.attendances)
                };

                fetch(`/admin/academy/attendance`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(payload)
                })
                .then(res => res.json())
                .then(data => {
                    if(data.success) {
                        alert(data.message);
                        this.attendanceModal = false;
                    } else {
                        alert('Xatolik: ' + data.message);
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert('Server bilan aloqa uzildi!');
                })
                .finally(() => {
                    this.submitting = false;
                });
            },

            openJournal(groupId) {
                this.activeGroupId = groupId;
                this.journalModal = true;
                this.loading = true;
                this.activeStudents = [];
                this.grades = {};
                this.topicData = {topic: '', description: ''};

                fetch(`/admin/academy/attendance/${groupId}`, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(res => res.json())
                .then(data => {
                    this.activeGroupName = data.group;
                    this.activeStudents = data.students;
                    data.students.forEach(s => {
                        this.grades[s.id] = { grade: '', knowledge_level: '', comment: '' };
                    });
                    this.loading = false;
                })
                .catch(err => {
                    console.error(err);
                    alert('Ma\'lumotlarni yuklashda xatolik!');
                    this.loading = false;
                });
            },

            submitTopic() {
                if(this.submittingTopic) return;
                this.submittingTopic = true;
                
                fetch(`/teacher/topic`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        group_id: this.activeGroupId,
                        topic: this.topicData.topic,
                        description: this.topicData.description
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if(data.success) {
                        alert(data.message);
                        this.topicData = {topic: '', description: ''};
                    } else {
                        alert('Xatolik: ' + data.message);
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert('Server xatosi!');
                })
                .finally(() => {
                    this.submittingTopic = false;
                });
            },

            submitGrade(studentId) {
                const gradeData = this.grades[studentId];
                if(!gradeData.grade) {
                    alert('Iltimos bahoni kiriting!');
                    return;
                }

                fetch(`/teacher/grade`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        group_id: this.activeGroupId,
                        student_id: studentId,
                        grade: gradeData.grade,
                        knowledge_level: gradeData.knowledge_level,
                        comment: gradeData.comment
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if(data.success) {
                        alert(data.message);
                        // Optional: show a checkmark or clear input
                    } else {
                        alert('Xatolik: ' + data.message);
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert('Server xatosi!');
                });
            }
        }
    }
</script>
@endsection
