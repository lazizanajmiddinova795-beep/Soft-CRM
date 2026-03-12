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
<div class="mb-4 flex flex-col md:flex-row justify-between md:items-end border-b border-[var(--active-color)] pb-4 shrink-0">
    <div>
        <h1 class="text-2xl md:text-3xl font-orbitron font-bold tracking-widest text-[var(--active-color)] uppercase">MENING PROFILIM</h1>
        <p class="text-sm opacity-70 mt-1 font-mono tracking-widest uppercase">Shaxsiy ma'lumotlar va interfeys sozlamalari</p>
    </div>
</div>

@if (session('success'))
    <div class="p-3 mb-4 border border-[var(--active-color)] bg-[var(--active-color)] text-[var(--bg-color)] font-bold uppercase tracking-widest text-xs shrink-0 drop-shadow-[0_0_5px_var(--active-color)]">
        >> {{ session('success') }}
    </div>
@endif

<div class="grid grid-cols-1 md:grid-cols-2 gap-4 flex-1 overflow-y-auto slim-scroll min-h-0">
    <!-- Profile Edit -->
    <div class="cyber-panel p-4 flex flex-col gap-4">
        <h2 class="font-orbitron text-[var(--active-color)] text-sm tracking-widest border-b border-[var(--active-color)] border-opacity-30 pb-2 uppercase">Asosiy Ma'lumotlar</h2>
        
        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="font-mono text-xs flex flex-col gap-3">
            @csrf
            @method('patch')

            <div>
                <label class="block opacity-70 mb-1 uppercase tracking-widest text-xs">Ism</label>
                <input type="text" name="name" value="{{ old('name', auth()->user()->name) }}" required class="w-full bg-[var(--bg-color)] border border-[var(--border-color)] p-2 text-[var(--text-color)] focus:outline-none focus:border-[var(--active-color)] uppercase">
            </div>

            <div>
                <label class="block opacity-70 mb-1 uppercase tracking-widest text-xs">Avatar yuklash</label>
                <div class="flex items-center gap-3">
                    @if(auth()->user()->avatar)
                        <img src="{{ auth()->user()->avatar }}" class="w-10 h-10 border border-[var(--active-color)] object-cover bg-gray-900" alt="Avatar">
                    @endif
                    <input type="file" name="user_avatar" class="flex-1 bg-[var(--bg-color)] border border-[var(--border-color)] p-2 text-[var(--text-color)] focus:outline-none focus:border-[var(--active-color)] file:bg-[var(--active-color)] file:text-[var(--bg-color)] file:font-bold file:border-0 file:px-2 cursor-pointer text-sm">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3 mt-2">
                <div>
                    <label class="block opacity-70 mb-1 uppercase tracking-widest text-xs">Interfeys tili</label>
                    <select name="ui_language" class="w-full bg-[var(--bg-color)] border border-[var(--border-color)] p-2 text-[var(--text-color)] focus:outline-none focus:border-[var(--active-color)] appearance-none">
                        <option value="uz" {{ old('ui_language', auth()->user()->ui_language) == 'uz' ? 'selected' : '' }}>O'zbekcha</option>
                        <option value="ru" {{ old('ui_language', auth()->user()->ui_language) == 'ru' ? 'selected' : '' }}>Русский</option>
                        <option value="en" {{ old('ui_language', auth()->user()->ui_language) == 'en' ? 'selected' : '' }}>English</option>
                    </select>
                </div>
                <div>
                    <label class="block opacity-70 mb-1 uppercase tracking-widest text-xs">Shrift o'lchami</label>
                    <select name="ui_font_size" class="w-full bg-[var(--bg-color)] border border-[var(--border-color)] p-2 text-[var(--text-color)] focus:outline-none focus:border-[var(--active-color)] appearance-none">
                        <option value="text-sm" {{ old('ui_font_size', auth()->user()->ui_font_size) == 'text-sm' ? 'selected' : '' }}>Kichik</option>
                        <option value="text-base" {{ old('ui_font_size', auth()->user()->ui_font_size) == 'text-base' ? 'selected' : '' }}>O'rtacha</option>
                        <option value="text-lg" {{ old('ui_font_size', auth()->user()->ui_font_size) == 'text-lg' ? 'selected' : '' }}>Katta</option>
                    </select>
                </div>
            </div>

            <div class="mt-2">
                <label class="block opacity-70 mb-1 uppercase tracking-widest text-xs">Interfeys matn rangi (Shaxsiy)</label>
                <div class="flex items-center gap-2">
                    <input type="color" name="ui_font_color" value="{{ old('ui_font_color', auth()->user()->ui_font_color ?? '#00f0ff') }}" class="h-8 w-12 bg-transparent border-none cursor-pointer p-0" title="Rang tanlang">
                    <span class="text-xs opacity-50 uppercase tracking-widest">(Asosiy rangni o'zgartirish uchun)</span>
                </div>
            </div>

            <button type="submit" class="mt-4 px-4 py-2 bg-[var(--active-color)] text-[var(--bg-color)] hover:bg-transparent hover:text-[var(--active-color)] hover:border-[var(--active-color)] border border-transparent transition-all uppercase font-bold tracking-widest text-sm shadow-[0_0_10px_var(--active-color)] hover:shadow-none">
                SOZLAMALARNI SAQLASH
            </button>
        </form>
    </div>
    
    <!-- Password reset / info area -->
    <div class="cyber-panel p-4 flex flex-col gap-4">
        <h2 class="font-orbitron text-[var(--active-color)] text-sm tracking-widest border-b border-[var(--active-color)] border-opacity-30 pb-2 uppercase">Xavfsizlik & Statistika</h2>
        
        <div class="font-mono text-xs flex flex-col gap-3">
            <div class="bg-[var(--bg-color)] border border-[var(--border-color)] p-3 border-opacity-30">
                <div class="text-xs opacity-50 uppercase tracking-widest mb-1">Email manzili</div>
                <div class="text-[var(--active-color)]">{{ auth()->user()->email }}</div>
            </div>
            
            <div class="grid grid-cols-2 gap-3">
                <div class="bg-[var(--bg-color)] border border-[var(--border-color)] p-3 border-opacity-30">
                    <div class="text-xs opacity-50 uppercase tracking-widest mb-1">Lavozim</div>
                    <div class="uppercase text-[var(--active-color)]">{{ auth()->user()->role }}</div>
                </div>
                <div class="bg-[var(--bg-color)] border border-[var(--border-color)] p-3 border-opacity-30">
                    <div class="text-xs opacity-50 uppercase tracking-widest mb-1">XP / Daraja</div>
                    <div class="text-[var(--cyber-yellow)] font-bold">{{ auth()->user()->xp }} XP</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
