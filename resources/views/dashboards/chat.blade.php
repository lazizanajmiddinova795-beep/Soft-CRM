@extends('layouts.cyber')

@section('sidebar')
    @if(auth()->user()->role === 'admin')
        @include('partials.admin_sidebar')
    @elseif(auth()->user()->role === 'cashier')
        @include('partials.cashier_sidebar')
    @elseif(auth()->user()->role === 'operator')
        @include('partials.operator_sidebar')
    @endif
@endsection

@section('content')
<div class="mb-4 flex flex-col md:flex-row justify-between md:items-end border-b border-[var(--border-color)] border-opacity-30 pb-4 shrink-0">
    <div>
        <h1 class="text-2xl md:text-3xl font-orbitron font-bold tracking-widest text-[var(--electric-blue)] drop-shadow-[0_0_10px_var(--electric-blue)] uppercase">The Syndicate Chat</h1>
        <p class="text-sm opacity-70 mt-1 font-mono tracking-widest uppercase text-[var(--text-color)]">Xodimlar o'rtasida xavfsiz va tezkor yozishmalar</p>
    </div>
</div>

@if (session('success'))
    <div class="p-3 mb-4 border border-[var(--active-color)] bg-[var(--active-color)] text-[var(--bg-color)] font-bold uppercase tracking-widest text-xs shrink-0 drop-shadow-[0_0_5px_var(--active-color)]">
        >> {{ session('success') }}
    </div>
@endif
@if (session('error'))
    <div class="p-3 mb-4 border border-red-500 bg-red-500 bg-opacity-10 text-red-500 font-bold uppercase tracking-widest text-xs shrink-0 drop-shadow-[0_0_5px_rgba(255,0,0,0.5)]">
        >> {{ session('error') }}
    </div>
@endif

<div x-data="{ activeTab: 'chat', editMsgId: null, editMsgText: '' }" class="flex-1 flex flex-col min-h-0">
    <div class="flex gap-2 md:gap-4 mb-4 border-b border-[var(--border-color)] border-opacity-30 pb-2 shrink-0 overflow-x-auto slim-scroll">
        <button @click="activeTab = 'chat'" class="px-4 py-2 text-xs font-bold uppercase tracking-widest transition-all border-b-2 whitespace-nowrap" :class="activeTab === 'chat' ? 'border-[var(--electric-blue)] text-[var(--electric-blue)] drop-shadow-[0_0_5px_var(--electric-blue)]' : 'border-transparent text-[var(--text-color)] opacity-60 hover:opacity-100'">
            Jamoa Chati
        </button>
        <button @click="activeTab = 'tasks'" class="px-4 py-2 text-xs font-bold uppercase tracking-widest transition-all border-b-2 whitespace-nowrap" :class="activeTab === 'tasks' ? 'border-[var(--cyber-yellow)] text-[var(--cyber-yellow)] drop-shadow-[0_0_5px_var(--cyber-yellow)]' : 'border-transparent text-[var(--text-color)] opacity-60 hover:opacity-100'">
            Vazifalar & Nazorat <span class="bg-[var(--cyber-yellow)] text-[var(--bg-color)] px-1.5 py-0.5 ml-2 font-mono text-xs">{{ count($tasks) }}</span>
        </button>
    </div>

    <!-- Chat Tab -->
    <div x-show="activeTab === 'chat'" class="cyber-panel flex flex-col overflow-hidden w-full flex-1">
        <div class="bg-black bg-opacity-50 p-3 border-b border-[var(--border-color)] border-opacity-30 w-full flex justify-between items-center text-[var(--text-color)] shrink-0">
            <span class="font-orbitron font-bold text-sm tracking-widest uppercase flex items-center gap-2 text-[var(--electric-blue)]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"></path></svg>
                Umumiy Guruh
            </span>
            <div class="flex items-center gap-4 text-xs font-mono">
                <span class="text-[var(--bg-color)] bg-[var(--active-color)] font-bold px-2 py-0.5 border border-[var(--active-color)] flex items-center gap-1 shadow-[0_0_5px_var(--active-color)]">
                    <span class="w-2 h-2 rounded-full bg-[var(--bg-color)] animate-ping inline-block shadow-[0_0_5px_var(--bg-color)]"></span> Online Aktiv
                </span>
                @if(auth()->user()->role === 'admin')
                <form method="POST" action="{{ route('chat.clear') }}" onsubmit="return confirm('Barcha xabarlarni butunlay o\'chirib tashlaysizmi?')">
                    @csrf
                    <button type="submit" class="text-sm text-red-500 px-2 py-1 uppercase tracking-widest border border-red-500 border-opacity-30 hover:bg-red-500 hover:text-white hover:border-red-500 transition-all shadow-[0_0_10px_rgba(255,0,0,0.2)]">Tozalash</button>
                </form>
                @endif
            </div>
        </div>
        
        <!-- Messages Area -->
        <div class="flex-1 overflow-y-auto p-4 space-y-4 slim-scroll bg-black bg-opacity-50" id="chat-container">
            @forelse($messages as $msg)
                @if($msg->sender_id == auth()->id())
                    <div class="flex justify-end relative group">
                        <div class="mr-2 opacity-0 group-hover:opacity-100 transition-opacity flex items-center gap-2">
                            <button @click="editMsgId = {{ $msg->id }}; editMsgText = '{{ addslashes(str_replace(' (tahrirlandi)', '', $msg->message)) }}'" class="text-sm text-[var(--electric-blue)] hover:text-white uppercase"><svg class="w-4 h-4" transform="scale(-1, 1)" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg></button>
                            <form method="POST" action="{{ route('chat.message.delete', $msg->id) }}" onsubmit="return confirm('Xabarni o\'chirasizmi?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-sm text-red-500 hover:text-white uppercase"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg></button>
                            </form>
                        </div>
                        <div class="max-w-[80%] md:max-w-[70%] text-right font-mono">
                            <div class="bg-[var(--electric-blue)] text-[var(--bg-color)] font-bold border border-[var(--electric-blue)] p-3 shadow-[0_0_10px_rgba(0,240,255,0.4)] mb-1 text-xs md:text-sm text-left inline-block">
                                {{ $msg->message }}
                                @if($msg->file_path)
                                    <div class="mt-2 text-xs border-t border-[var(--bg-color)] border-opacity-30 pt-2">
                                        <a href="/storage/{{ $msg->file_path }}" target="_blank" class="underline flex items-center gap-1 hover:text-white transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg> 
                                            FILE_ATTACHMENT
                                        </a>
                                    </div>
                                @endif
                            </div>
                            <div class="text-xs text-[var(--text-color)] opacity-60 pr-1 uppercase tracking-wider">Siz • {{ $msg->created_at->format('H:i') }}</div>
                        </div>
                    </div>
                @else
                    <div class="flex justify-start relative group">
                        <div class="max-w-[80%] md:max-w-[70%] font-mono">
                            <div class="text-sm font-bold mb-1 pl-1 tracking-widest uppercase" style="color: {{ $msg->sender->role == 'admin' ? '#ef4444' : ($msg->sender->role == 'operator' ? 'var(--electric-blue)' : 'var(--active-color)') }}">
                                {{ $msg->sender->name ?? 'Tizim' }} ({{ ucfirst($msg->sender->role ?? 'Bot') }})
                            </div>
                            <div class="bg-black bg-opacity-80 text-[var(--text-color)] p-3 border border-[var(--border-color)] border-opacity-50 mb-1 text-xs md:text-sm inline-block {{ str_contains($msg->message, 'SYSTEM ALERT') || str_contains($msg->message, 'NEW DIRECTIVE') ? 'border-[var(--cyber-yellow)] text-[var(--cyber-yellow)] shadow-[inset_0_0_10px_rgba(252,238,10,0.2)]' : '' }}">
                                {{ $msg->message }}
                                @if($msg->file_path)
                                    <div class="mt-2 text-xs border-t border-[var(--text-color)] border-opacity-30 pt-2">
                                        <a href="/storage/{{ $msg->file_path }}" target="_blank" class="underline flex items-center gap-1 text-[var(--electric-blue)] hover:text-white transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg> 
                                            INCOMING_FILE
                                        </a>
                                    </div>
                                @endif
                            </div>
                            <div class="text-xs text-[var(--text-color)] opacity-60 pl-1 uppercase tracking-wider">{{ $msg->created_at->format('H:i') }}</div>
                        </div>
                        @if(auth()->user()->role === 'admin')
                        <div class="ml-2 opacity-0 group-hover:opacity-100 transition-opacity flex items-center mt-4">
                            <form method="POST" action="{{ route('chat.message.delete', $msg->id) }}" onsubmit="return confirm('Xabarni o\'chirasizmi?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-sm text-red-500 hover:text-white uppercase"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg></button>
                            </form>
                        </div>
                        @endif
                    </div>
                @endif
            @empty
                <div class="h-full flex flex-col items-center justify-center opacity-40 text-center text-[var(--text-color)] font-mono text-xs uppercase tracking-widest">
                    <svg class="w-10 h-10 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                    <p>Hech qanday xabar yo'q.<br>Suhbatni boshlash uchun pastga yozing.</p>
                </div>
            @endforelse
        </div>

        <!-- Input Area -->
        <div class="p-3 bg-black bg-opacity-50 border-t border-[var(--border-color)] border-opacity-30 shrink-0" x-data="{ fileName: '' }">
            <!-- Normal Send -->
            <form x-show="!editMsgId" method="POST" action="{{ route('chat.send') }}" enctype="multipart/form-data" class="flex gap-2 items-center relative">
                @csrf
                <label class="cursor-pointer bg-[var(--bg-color)] p-2 border border-[var(--border-color)] border-opacity-50 text-[var(--electric-blue)] hover:border-[var(--electric-blue)] transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                    <input type="file" name="attachment" class="hidden" x-ref="fileInput" @change="fileName = $refs.fileInput.files[0]?.name || ''">
                </label>
                
                <div class="flex-1 bg-[var(--bg-color)] border border-[var(--border-color)] flex items-center px-3 py-1 focus-within:border-[var(--electric-blue)] focus-within:shadow-[0_0_10px_rgba(0,240,255,0.2)] transition-colors relative">
                    <div class="text-[var(--electric-blue)] mr-2 font-mono text-xs">></div>
                    <input type="text" name="message" autofocus placeholder="Xabar yozing (CMD_INPUT)..." class="w-full bg-transparent text-[var(--text-color)] font-mono text-xs py-1.5 focus:outline-none placeholder-opacity-50 uppercase" autocomplete="off">
                    <!-- File Name Indicator -->
                    <div x-show="fileName" class="absolute -top-6 left-0 text-xs text-[var(--cyber-yellow)] tracking-widest bg-[var(--bg-color)] border border-[var(--cyber-yellow)] px-2 py-0.5 truncate max-w-full" x-text="'ATTACHED: ' + fileName" style="display: none;"></div>
                </div>
                
                <button type="submit" class="bg-[var(--electric-blue)] text-[var(--bg-color)] px-4 py-2 hover:bg-transparent hover:text-[var(--electric-blue)] hover:border-[var(--electric-blue)] border border-transparent transition-all flex items-center justify-center uppercase tracking-widest font-bold text-sm">
                    SEND_TX
                </button>
            </form>

            <!-- Edit Send -->
            <form x-show="editMsgId" method="POST" :action="'/chat/message/' + editMsgId" class="flex gap-2 items-center" style="display: none;">
                @csrf
                <div class="flex-1 bg-[var(--bg-color)] border border-[var(--cyber-yellow)] flex items-center px-3 py-1 shadow-[0_0_10px_rgba(252,238,10,0.2)]">
                    <div class="text-[var(--cyber-yellow)] mr-2 font-mono text-xs">EDIT></div>
                    <input type="text" name="message" x-model="editMsgText" required class="w-full bg-transparent text-[var(--cyber-yellow)] font-mono text-xs py-1.5 focus:outline-none uppercase" autocomplete="off">
                </div>
                <button type="button" @click="editMsgId = null" class="bg-transparent border border-[var(--border-color)] text-[var(--text-color)] px-3 py-2 hover:text-white transition-colors uppercase tracking-widest text-sm">
                    CANCEL
                </button>
                <button type="submit" class="bg-[var(--cyber-yellow)] text-[var(--bg-color)] px-4 py-2 hover:bg-transparent hover:text-[var(--cyber-yellow)] border border-[var(--cyber-yellow)] transition-colors flex items-center justify-center font-bold tracking-widest text-sm uppercase shadow-[0_0_10px_rgba(252,238,10,0.3)]">
                    UPDATE
                </button>
            </form>
        </div>
    </div>
    
    <!-- Tasks Section -->
    <div x-show="activeTab === 'tasks'" style="display: none;" class="grid grid-cols-1 md:grid-cols-3 gap-4 flex-1 min-h-0">

        <!-- Assign Task Form (All Users) -->
        <div class="cyber-panel p-4 flex flex-col md:col-span-1 shrink-0 bg-opacity-20">
            <h3 class="font-orbitron font-bold text-[var(--cyber-yellow)] mb-3 border-b border-[var(--cyber-yellow)] border-opacity-30 pb-2 flex justify-between items-center text-xs uppercase tracking-widest">
                Vazifa Berish
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
            </h3>
            <form method="POST" action="{{ route('chat.task.assign') }}" class="space-y-3 font-mono">
                @csrf
                <div>
                    <label class="block text-[var(--text-color)] opacity-70 mb-1 text-xs uppercase tracking-widest">Xodimni tanlang</label>
                    <select name="assigned_to" required class="w-full bg-[var(--bg-color)] border border-[var(--border-color)] p-2 text-[var(--active-color)] focus:outline-none focus:border-[var(--cyber-yellow)] text-xs appearance-none">
                        @foreach($users as $u)
                            <option value="{{ $u->id }}">{{ $u->name }} ({{ ucfirst($u->role) }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[var(--text-color)] opacity-70 mb-1 text-xs uppercase tracking-widest">Vazifa Nomi</label>
                    <input type="text" name="title" required placeholder="Masalan: Kunlik hisobotni topshirish" class="w-full bg-[var(--bg-color)] border border-[var(--border-color)] p-2 text-[var(--text-color)] focus:outline-none focus:border-[var(--cyber-yellow)] text-xs uppercase placeholder-opacity-50">
                </div>
                <div>
                    <label class="block text-[var(--text-color)] opacity-70 mb-1 text-xs uppercase tracking-widest">Qo'shimcha Ma'lumot</label>
                    <textarea name="description" required rows="2" class="w-full bg-[var(--bg-color)] border border-[var(--border-color)] p-2 text-[var(--text-color)] focus:outline-none focus:border-[var(--cyber-yellow)] text-xs resize-none placeholder-opacity-50 uppercase" placeholder="Vazifani tushuntiring..."></textarea>
                </div>
                <div class="grid grid-cols-3 gap-2">
                    <div>
                        <label class="block text-red-500 mb-1 text-xs uppercase tracking-widest">Jarima (UZS)</label>
                        <input type="number" name="fine_amount" value="50000" required class="w-full bg-[var(--bg-color)] border border-red-500 border-opacity-50 p-2 text-red-500 focus:outline-none focus:border-red-500 shadow-[inset_0_0_5px_rgba(255,0,0,0.1)] text-xs font-bold">
                    </div>
                    <div>
                        <label class="block text-[var(--cyber-yellow)] mb-1 text-xs uppercase tracking-widest">XP Reward</label>
                        <input type="number" name="xp_reward" value="100" required class="w-full bg-[var(--bg-color)] border border-[var(--cyber-yellow)] border-opacity-50 p-2 text-[var(--cyber-yellow)] focus:outline-none focus:border-[var(--cyber-yellow)] shadow-[inset_0_0_5px_rgba(252,238,10,0.1)] text-xs font-bold">
                    </div>
                    <div>
                        <label class="block text-[var(--text-color)] opacity-70 mb-1 text-xs uppercase tracking-widest">Oxirgi muddat</label>
                        <input type="datetime-local" name="deadline" required class="w-full bg-[var(--bg-color)] border border-[var(--border-color)] p-2 text-[var(--text-color)] focus:outline-none focus:border-[var(--cyber-yellow)] text-xs">
                    </div>
                </div>
                <button type="submit" class="w-full py-2 bg-[var(--cyber-yellow)] text-[var(--bg-color)] font-bold text-sm uppercase tracking-widest mt-2 hover:bg-transparent hover:text-[var(--cyber-yellow)] border border-transparent hover:border-[var(--cyber-yellow)] transition-all shadow-[0_0_10px_rgba(252,238,10,0.2)] hover:shadow-none">
                    Topshiriqni Yuborish
                </button>
            </form>
        </div>

        <!-- Active Tasks List -->
        <div class="cyber-panel p-4 flex flex-col md:col-span-2 overflow-y-auto slim-scroll relative border border-[var(--cyber-yellow)] border-opacity-30 shadow-[inset_0_0_20px_rgba(252,238,10,0.05)]">
            <h3 class="font-orbitron font-bold text-[var(--cyber-yellow)] mb-4 border-b border-[var(--border-color)] border-opacity-30 pb-2 flex justify-between items-center sticky top-0 bg-[var(--panel-bg)] backdrop-blur-md pt-2 z-10 uppercase tracking-widest text-sm drop-shadow-[0_0_5px_var(--cyber-yellow)]">
                Barcha Vazifalar (Nazorat)
                <span class="bg-[var(--cyber-yellow)] text-[var(--bg-color)] text-sm px-2 py-0.5 font-bold shadow-[0_0_5px_var(--cyber-yellow)]">{{ count($tasks) }}</span>
            </h3>
            
            <div class="space-y-3 font-mono w-full">
                @forelse($tasks as $task)
                    <div x-data="{ editingTask: false }" class="p-4 border {{ $task->status == 'pending' ? 'border-[var(--cyber-yellow)] hover:bg-gray-500 hover:bg-opacity-10' : ($task->status == 'done' ? 'border-[var(--active-color)] opacity-70' : 'border-red-500 opacity-80 shadow-[inset_0_0_10px_rgba(255,0,0,0.2)]') }} transition-all relative">
                        
                        <div x-show="!editingTask" class="flex flex-col h-full justify-between">
                            <div>
                                <!-- Status Badge & Admin Tools -->
                                <div class="flex justify-between items-start mb-2 gap-2">
                                    <div class="flex-1">
                                        <div class="text-xs uppercase font-bold tracking-widest {{ $task->status == 'pending' ? 'text-[var(--cyber-yellow)]' : '' }} {{ $task->status == 'done' ? 'text-[var(--active-color)]' : '' }} {{ $task->status == 'failed' ? 'text-red-500' : '' }} mb-1">
                                            > STATUS: {{ $task->status == 'pending' ? 'KUTILMOQDA' : ($task->status == 'done' ? 'BAJARILDI' : 'MUDDAT O\'TDI') }}
                                        </div>
                                        <h4 class="font-bold text-sm text-[var(--text-color)] leading-tight uppercase tracking-wider mb-2">{{ $task->title }}</h4>
                                    </div>
                                    @if(auth()->user()->role === 'admin')
                                    <div class="flex gap-1 shrink-0">
                                        <button @click="editingTask = true" class="text-xs border border-[var(--electric-blue)] text-[var(--electric-blue)] px-2 py-0.5 hover:bg-[var(--electric-blue)] hover:text-[var(--bg-color)] transition-colors uppercase font-bold tracking-widest">EDT</button>
                                        <form method="POST" action="{{ route('chat.task.delete', $task->id) }}" onsubmit="return confirm('Vazifani o\'chirib tashlaysizmi?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-xs border border-red-500 text-red-500 px-2 py-0.5 hover:bg-red-500 hover:text-white transition-colors uppercase font-bold tracking-widest">DEL</button>
                                        </form>
                                    </div>
                                    @endif
                                </div>
                                
                                <div class="text-sm text-[var(--text-color)] opacity-70 mb-2 flex justify-between items-center bg-black bg-opacity-50 p-1 border border-[var(--border-color)] border-opacity-30">
                                    @if(auth()->user()->role === 'admin')
                                        <span class="uppercase tracking-widest">Mijrochi: <b class="text-[var(--active-color)]">{{ $task->assignee->name ?? 'Noma\'lum' }}</b></span>
                                    @else
                                        <span class="uppercase tracking-widest">Buyruq: <b class="text-[var(--electric-blue)]">{{ $task->assigner->name ?? 'Admin' }}</b></span>
                                    @endif
                                </div>
                                
                                <p class="text-sm text-[var(--text-color)] opacity-80 mb-3 bg-black bg-opacity-30 p-2 border border-transparent border-l-[var(--border-color)]">{{ $task->description }}</p>
                            </div>

                            <div class="flex flex-col sm:flex-row justify-between sm:items-end border-t border-[var(--border-color)] border-opacity-30 pt-2 gap-3 mt-auto">
                                <div>
                                    <div class="text-sm {{ \Carbon\Carbon::parse($task->deadline)->isPast() && $task->status == 'pending' ? 'text-red-500 font-bold animate-pulse' : 'text-[var(--text-color)] opacity-70' }} uppercase tracking-widest">
                                        DL: {{ \Carbon\Carbon::parse($task->deadline)->format('d.m.Y H:i') }}
                                    </div>
                                    @if($task->fine_amount > 0)
                                        <div class="text-sm text-red-500 mt-1 font-bold bg-red-500 bg-opacity-10 px-1 py-0.5 inline-block border border-red-500 border-opacity-30 uppercase tracking-widest">
                                            ! PENALTY: {{ number_format($task->fine_amount, 0) }} UZS
                                        </div>
                                    @endif
                                    @if($task->xp_reward > 0)
                                        <div class="text-sm text-[var(--cyber-yellow)] mt-1 ml-1 font-bold bg-gray-500 bg-opacity-10 px-1 py-0.5 inline-block border border-[var(--cyber-yellow)] border-opacity-30 uppercase tracking-widest">
                                            + {{ $task->xp_reward }} XP
                                        </div>
                                    @endif
                                </div>

                                @if($task->status == 'pending' && (auth()->user()->role === 'admin' || auth()->id() == $task->assigned_to))
                                    <form method="POST" action="{{ route('chat.task.complete', $task->id) }}">
                                        @csrf
                                        <button type="submit" class="text-sm px-3 py-1 bg-[var(--active-color)] text-[var(--bg-color)] uppercase tracking-widest font-bold border border-transparent hover:bg-transparent hover:text-[var(--active-color)] hover:border-[var(--active-color)] transition-colors shadow-[0_0_10px_rgba(0,255,0,0.2)] hover:shadow-none w-full sm:w-auto">
                                            > COMPLETE
                                        </button>
                                    </form>
                                @endif
                                @if($task->status == 'done')
                                    <div class="text-sm text-[var(--active-color)] font-bold uppercase tracking-widest">> TASK VERIFIED</div>
                                @endif
                            </div>
                        </div>

                        <!-- Inline Edit Form (Admin Only) -->
                        @if(auth()->user()->role === 'admin')
                        <div x-show="editingTask" style="display: none;" class="mt-2 text-sm border-t border-[var(--cyber-yellow)] border-opacity-50 pt-3">
                            <h4 class="font-bold text-[var(--cyber-yellow)] mb-3 text-sm uppercase tracking-widest">EDIT_TASK_MODULE</h4>
                            <form method="POST" action="{{ route('chat.task.edit', $task->id) }}" class="space-y-2">
                                @csrf
                                <div>
                                    <label class="block text-[var(--text-color)] opacity-70 mb-0.5 text-xs uppercase tracking-widest">Xodim</label>
                                    <select name="assigned_to" required class="w-full bg-[var(--bg-color)] border border-[var(--border-color)] p-1.5 text-[var(--text-color)] focus:border-[var(--cyber-yellow)] focus:outline-none text-sm appearance-none">
                                        @foreach($users as $u)
                                            <option value="{{ $u->id }}" {{ $task->assigned_to == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[var(--text-color)] opacity-70 mb-0.5 text-xs uppercase tracking-widest">Vazifa Nomi</label>
                                    <input type="text" name="title" value="{{ $task->title }}" required class="w-full bg-[var(--bg-color)] border border-[var(--border-color)] p-1.5 text-[var(--text-color)] focus:border-[var(--cyber-yellow)] focus:outline-none text-sm uppercase">
                                </div>
                                <div>
                                    <label class="block text-[var(--text-color)] opacity-70 mb-0.5 text-xs uppercase tracking-widest">Qo'shimcha</label>
                                    <textarea name="description" required rows="2" class="w-full bg-[var(--bg-color)] border border-[var(--border-color)] p-1.5 text-[var(--text-color)] focus:border-[var(--cyber-yellow)] focus:outline-none text-sm uppercase resize-none">{{ $task->description }}</textarea>
                                </div>
                                <div class="grid grid-cols-3 gap-2">
                                    <div>
                                        <label class="block text-red-500 mb-0.5 text-xs uppercase tracking-widest">Jarima UZS</label>
                                        <input type="number" name="fine_amount" value="{{ $task->fine_amount }}" required class="w-full bg-[var(--bg-color)] border border-red-500 border-opacity-50 p-1.5 text-red-500 focus:border-red-500 focus:outline-none text-sm font-bold">
                                    </div>
                                    <div>
                                        <label class="block text-[var(--cyber-yellow)] mb-0.5 text-xs uppercase tracking-widest">XP Reward</label>
                                        <input type="number" name="xp_reward" value="{{ $task->xp_reward }}" required class="w-full bg-[var(--bg-color)] border border-[var(--cyber-yellow)] border-opacity-50 p-1.5 text-[var(--cyber-yellow)] focus:border-[var(--cyber-yellow)] focus:outline-none text-sm font-bold">
                                    </div>
                                    <div>
                                        <label class="block text-[var(--text-color)] opacity-70 mb-0.5 text-xs uppercase tracking-widest">Muddat</label>
                                        <input type="datetime-local" name="deadline" value="{{ \Carbon\Carbon::parse($task->deadline)->format('Y-m-d\TH:i') }}" required class="w-full bg-[var(--bg-color)] border border-[var(--border-color)] p-1.5 text-[var(--text-color)] focus:border-[var(--cyber-yellow)] focus:outline-none text-sm">
                                    </div>
                                </div>
                                <div class="flex gap-2 justify-end mt-2 pt-2 border-t border-[var(--border-color)] border-opacity-30">
                                    <button type="button" @click="editingTask = false" class="px-2 py-1 bg-transparent border border-[var(--border-color)] text-[var(--text-color)] text-xs hover:text-[var(--cyber-yellow)] transition-colors uppercase tracking-widest">Bekor</button>
                                    <button type="submit" class="px-2 py-1 bg-[var(--cyber-yellow)] text-[var(--bg-color)] font-bold text-xs hover:bg-transparent hover:text-[var(--cyber-yellow)] border border-transparent hover:border-[var(--cyber-yellow)] transition-colors uppercase tracking-widest">Saqlash</button>
                                </div>
                            </form>
                        </div>
                        @endif
                    </div>
                @empty
                    <div class="flex flex-col items-center justify-center text-[var(--text-color)] py-10 opacity-40">
                        <svg class="w-10 h-10 mb-2 text-[var(--cyber-yellow)]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                        <p class="text-sm font-bold uppercase tracking-widest text-[var(--text-color)]">Yangi vazifalar yo'q</p>
                        <p class="text-xs mt-1 font-mono uppercase tracking-widest">> RELAXING_MODE=1</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        var chatDiv = document.getElementById("chat-container");
        if(chatDiv) {
            chatDiv.scrollTop = chatDiv.scrollHeight;
        }
    });
</script>

@endsection
