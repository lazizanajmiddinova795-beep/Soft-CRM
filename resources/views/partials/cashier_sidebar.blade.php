    <a href="{{ route('cashier.dashboard') }}" class="nav-item {{ request()->routeIs('cashier.dashboard') && !request()->has('tab') ? 'active font-bold text-[var(--active-color)]' : 'text-gray-400 hover:text-[var(--active-color)] transition-colors' }}">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <span>{{ __('messages.the_treasury') }}</span>
    </a>
    <a href="{{ route('cashier.dashboard', ['tab' => 'transactions']) }}" class="nav-item {{ request()->input('tab') == 'transactions' ? 'active font-bold text-[var(--active-color)]' : 'text-gray-400 hover:text-[var(--active-color)] transition-colors' }}">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122"></path></svg>
        <span>{{ __('messages.transactions') }}</span>
    </a>
    <a href="{{ route('cashier.dashboard', ['tab' => 'reports']) }}" class="nav-item {{ request()->input('tab') == 'reports' ? 'active font-bold text-[var(--active-color)]' : 'text-gray-400 hover:text-[var(--active-color)] transition-colors' }}">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
        <span>{{ __('messages.reports') }}</span>
    </a>
    <a href="{{ route('admin.clients.index') }}" class="nav-item {{ request()->routeIs('admin.clients.*') ? 'active font-bold text-[var(--active-color)]' : 'text-gray-400 hover:text-[var(--active-color)] transition-colors' }}">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
        <span>MIJOZLAR BAZASI</span>
    </a>
    <a href="{{ route('chat.index') }}" class="nav-item border-t border-[var(--border-color)] border-opacity-30 pt-4 mt-2 {{ request()->routeIs('chat.*') ? 'active font-bold text-[var(--cyber-yellow)] shadow-[inset_4px_0_0_var(--cyber-yellow)] border-transparent text-[var(--cyber-yellow)]' : 'text-gray-400 hover:text-[var(--cyber-yellow)] hover:shadow-[inset_4px_0_0_var(--cyber-yellow)] hover:border-transparent transition-all' }}">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"></path></svg>
        <span>{{ __('messages.syndicate_chat') }}</span>
    </a>
