@extends('layouts.cyber')

@section('sidebar')
    @include('partials.admin_sidebar')
@endsection

@section('content')
<div class="mb-6 flex justify-between items-end border-b border-[var(--active-color)] pb-4">
    <div>
        <h1 class="text-3xl font-orbitron font-bold tracking-widest text-[var(--active-color)]">{{ __('messages.sys_config') }}</h1>
        <p class="font-mono text-sm opacity-70 mt-1">{{ __('messages.sys_config_desc') }}</p>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
    <div class="cyber-panel p-6">
        @if (session('success'))
            <div class="p-3 mb-4 border border-[var(--active-color)] bg-[var(--active-color)] text-[var(--bg-color)] font-bold uppercase tracking-widest text-xs relative">
                >> {{ session('success') }}
            </div>
        @endif
        @if ($errors->any())
            <div class="p-3 mb-4 border border-red-500 bg-red-900 bg-opacity-20 text-red-500 font-bold uppercase tracking-widest text-xs relative">
                >> Error saving configuration.
                <ul class="mt-2 list-disc pl-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <h2 class="text-xl font-orbitron font-bold text-[var(--active-color)] mb-4 border-b border-gray-700 pb-2">{{ __('messages.global_theming') }}</h2>
        <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="space-y-4 font-mono text-sm">
            @csrf
            
            <!-- Global Brand -->
            <div>
                <label class="block opacity-70 mb-1">{{ __('messages.company_name') }}</label>
                <input type="text" name="company_name" value="{{ $settings['company_name'] ?? 'OBSIDIAN.OS' }}" class="w-full bg-black border border-gray-700 p-2 text-white focus:outline-none focus:border-[var(--active-color)]">
            </div>
            
            <div>
                <label class="block opacity-70 mb-1">{{ __('messages.upload_logo') }}</label>
                <input type="file" name="company_logo_file" class="w-full bg-black border border-gray-700 p-2 text-white focus:outline-none focus:border-[var(--active-color)] file:bg-[var(--active-color)] file:text-black file:font-bold file:border-0 file:px-2 cursor-pointer">
                @if(isset($settings['company_logo']))
                    <p class="text-sm text-[var(--active-color)] mt-1">{{ __('messages.current') }} {{ $settings['company_logo'] }}</p>
                @endif
            </div>

            <div>
                <label class="block opacity-70 mb-1">{{ __('messages.upload_bg') }}</label>
                <input type="file" name="bg_image_file" class="w-full bg-black border border-gray-700 p-2 text-white focus:outline-none focus:border-[var(--active-color)] file:bg-[var(--active-color)] file:text-black file:font-bold file:border-0 file:px-2 cursor-pointer">
                @if(isset($settings['bg_image_url']))
                    <p class="text-sm text-[var(--active-color)] mt-1">{{ __('messages.current') }} {{ $settings['bg_image_url'] }}</p>
                @endif
            </div>

            <!-- UI Font & Colors -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block opacity-70 mb-1">{{ __('messages.global_font_size') }}</label>
                    <select name="sys_font_size" class="w-full bg-black border border-gray-700 p-2 text-white focus:outline-none focus:border-[var(--active-color)]">
                        <option value="text-sm" {{ ($settings['sys_font_size'] ?? '') == 'text-sm' ? 'selected' : '' }}>{{ __('messages.font_small') }}</option>
                        <option value="text-base" {{ ($settings['sys_font_size'] ?? 'text-base') == 'text-base' ? 'selected' : '' }}>{{ __('messages.font_medium') }}</option>
                        <option value="text-lg" {{ ($settings['sys_font_size'] ?? '') == 'text-lg' ? 'selected' : '' }}>{{ __('messages.font_large') }}</option>
                    </select>
                </div>
                <div>
                    <label class="block opacity-70 mb-1">{{ __('messages.sys_language') }}</label>
                    <select name="sys_language" class="w-full bg-black border border-gray-700 p-2 text-white focus:outline-none focus:border-[var(--active-color)]">
                        <option value="uz" {{ ($settings['sys_language'] ?? 'en') == 'uz' ? 'selected' : '' }}>O'zbekcha</option>
                        <option value="ru" {{ ($settings['sys_language'] ?? 'en') == 'ru' ? 'selected' : '' }}>Русский</option>
                        <option value="en" {{ ($settings['sys_language'] ?? 'en') == 'en' ? 'selected' : '' }}>English</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 pt-2">
                <div>
                    <label class="block opacity-70 mb-1 text-sm">{{ __('messages.color_admin') }}</label>
                    <div class="flex items-center gap-2">
                        <input type="color" name="color_admin" value="{{ $settings['color_admin'] ?? '#FFD700' }}" class="h-8 w-12 bg-transparent border-none cursor-pointer p-0">
                        <span class="text-xs uppercase">{{ $settings['color_admin'] ?? '#FFD700' }}</span>
                    </div>
                </div>
                <div>
                    <label class="block opacity-70 mb-1 text-sm">{{ __('messages.color_operator') }}</label>
                    <div class="flex items-center gap-2">
                        <input type="color" name="color_operator" value="{{ $settings['color_operator'] ?? '#00E5FF' }}" class="h-8 w-12 bg-transparent border-none cursor-pointer p-0">
                        <span class="text-xs uppercase">{{ $settings['color_operator'] ?? '#00E5FF' }}</span>
                    </div>
                </div>
                <div>
                    <label class="block opacity-70 mb-1 text-sm">{{ __('messages.color_cashier') }}</label>
                    <div class="flex items-center gap-2">
                        <input type="color" name="color_cashier" value="{{ $settings['color_cashier'] ?? '#00FF00' }}" class="h-8 w-12 bg-transparent border-none cursor-pointer p-0">
                        <span class="text-xs uppercase">{{ $settings['color_cashier'] ?? '#00FF00' }}</span>
                    </div>
                </div>
                <div>
                    <label class="block opacity-70 mb-1 text-sm">{{ __('messages.color_dev') }}</label>
                    <div class="flex items-center gap-2">
                        <input type="color" name="color_dev" value="{{ $settings['color_dev'] ?? '#FF00FF' }}" class="h-8 w-12 bg-transparent border-none cursor-pointer p-0">
                        <span class="text-xs uppercase">{{ $settings['color_dev'] ?? '#FF00FF' }}</span>
                    </div>
                </div>
            </div>
            
            <hr class="border-gray-800 my-4">

            <!-- Personal Avatar -->
            <h2 class="text-xl font-orbitron font-bold text-[var(--active-color)] mb-4 border-b border-gray-700 pb-2">{{ __('messages.personal_prefs') }}</h2>
            
            <div>
                <label class="block opacity-70 mb-1">{{ __('messages.upload_avatar') }}</label>
                <div class="flex items-center gap-4">
                    @if(auth()->user()->avatar)
                        <img src="{{ auth()->user()->avatar }}" class="w-12 h-12 border border-[var(--active-color)] object-cover bg-gray-900" alt="Avatar">
                    @endif
                    <input type="file" name="user_avatar" class="flex-1 bg-black border border-gray-700 p-2 text-white focus:outline-none focus:border-[var(--active-color)] file:bg-[var(--active-color)] file:text-black file:font-bold file:border-0 file:px-2 cursor-pointer">
                </div>
            </div>

            <div>
                <label class="block opacity-70 mb-1">{{ __('messages.enforce_strict_mode') }}</label>
                <select name="strict_mode" class="w-full bg-black border border-gray-700 p-2 text-white focus:outline-none focus:border-[var(--active-color)]">
                    <option value="1" {{ ($settings['strict_mode'] ?? '1') == '1' ? 'selected' : '' }}>{{ __('messages.enabled') }}</option>
                    <option value="0" {{ ($settings['strict_mode'] ?? '1') == '0' ? 'selected' : '' }}>{{ __('messages.disabled') }}</option>
                </select>
            </div>
            
            <button type="submit" class="w-full mt-4 p-4 bg-[var(--active-color)] text-[var(--bg-color)] font-bold tracking-widest uppercase hover:bg-transparent hover:text-[var(--active-color)] border border-transparent hover:border-[var(--active-color)] transition-all shadow-[0_0_15px_var(--active-color)] hover:shadow-none">
                {{ __('messages.deploy_config') }}
            </button>
        </form>
    </div>
    
    <div class="cyber-panel p-6 h-fit text-sm font-mono opacity-60 flex flex-col items-center justify-center text-center mb-6">
        <svg class="w-16 h-16 mb-4 text-[var(--active-color)] animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
        <p>{{ __('messages.sys_config_info') }}</p>
    </div>

    <!-- Clear Tizim Ma'lumotlari -->
    <div class="cyber-panel p-6 border-l-4 border-red-600 col-span-1 md:col-span-2">
        <h2 class="text-xl font-orbitron font-bold text-red-600 mb-2 uppercase tracking-widest">XAVFLI HUDUD: TIZIM MA'LUMOTLARINI TOZALASH</h2>
        <p class="font-mono text-sm opacity-70 mb-4">Diqqat! Ushbu amal tizimdagi BARCHA tranzaksiyalar, mijozlar bazasi, shartnomalar, xodimlar (admindan tashqari barcha xodimlar) va boshqa operatsion tarixlarni O'CHIRIB TASHLAYDI va tizimni "0" holatga tushiradi. Operatsiyani ortga qaytarib bo'lmaydi.</p>
        
        <form method="POST" action="{{ route('admin.settings.clearData') }}" onsubmit="const word = prompt('Tizimni tozalash uchun qutiga katta xarflar bilan TASDIQLAYMAN deb yozing:'); if(word === 'TASDIQLAYMAN'){ return true; } else { alert('Xato so\'z kiritildi. Amal bekor qilindi.'); return false; }">
            @csrf
            @method('DELETE')
            <button type="submit" class="w-full md:w-auto px-8 py-4 bg-red-600 text-white font-bold tracking-widest uppercase shadow-[0_0_20px_rgba(220,38,38,0.6)] hover:bg-transparent hover:text-red-500 border border-transparent hover:border-red-500 transition-all flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                BARCHA MA'LUMOTNI O'CHIRISH (RESET 0)
            </button>
        </form>
    </div>
</div>
@endsection
