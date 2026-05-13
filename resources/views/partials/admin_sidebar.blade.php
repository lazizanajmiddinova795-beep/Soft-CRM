<a href="{{ route('admin.dashboard') }}" class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
    <i class="fa-solid fa-gauge-high"></i>
    <span>{{ __('messages.command_center') }}</span>
</a>
<a href="{{ route('admin.staff.index') }}" class="nav-item {{ request()->routeIs('admin.staff.*') ? 'active' : '' }}">
    <i class="fa-solid fa-users-gear"></i>
    <span>{{ __('messages.staff_registry') }}</span>
</a>
<a href="{{ route('admin.fcc.index') }}" class="nav-item {{ request()->routeIs('admin.fcc.*') ? 'active' : '' }}">
    <i class="fa-solid fa-shield-halved"></i>
    <span>{{ __('messages.fcc_hub') }}</span>
</a>
<a href="{{ route('admin.clients.index') }}" class="nav-item {{ request()->routeIs('admin.clients.*') ? 'active' : '' }}">
    <i class="fa-solid fa-address-book"></i>
    <span>{{ __('messages.clients') ?? 'MIJOZLAR BAZASI' }}</span>
</a>
<a href="{{ route('admin.finance.index') }}" class="nav-item {{ request()->routeIs('admin.finance.*') ? 'active' : '' }}">
    <i class="fa-solid fa-vault"></i>
    <span>{{ __('messages.finance_treasury') }}</span>
</a>
<a href="{{ route('admin.academy.index') }}" class="nav-item group {{ request()->routeIs('admin.academy.index') ? 'active' : '' }}">
    <i class="fa-solid fa-graduation-cap text-purple-400"></i>
    <span>O'QUV BO'LIMI</span>
    <span class="ml-auto text-[8px] bg-purple-500/20 text-purple-400 px-1 rounded hover:bg-purple-500/40 transition-colors">V 1.0</span>
</a>
<a href="{{ route('admin.academy.students.index') }}" class="nav-item {{ request()->routeIs('admin.academy.students.*') ? 'active' : '' }}">
    <i class="fa-solid fa-user-graduate text-cyan-400"></i>
    <span>O'QUVCHILAR BAZASI</span>
</a>
<a href="{{ route('admin.academy.telegram_bots.index') }}" class="nav-item {{ request()->routeIs('admin.academy.telegram_bots.*') ? 'active' : '' }}">
    <i class="fa-brands fa-telegram text-blue-400"></i>
    <span>TELEGRAM BOTLAR</span>
</a>
<a href="{{ route('chat.index') }}" class="nav-item {{ request()->routeIs('chat.*') ? 'active' : '' }}">
    <i class="fa-solid fa-comments"></i>
    <span>{{ __('messages.syndicate_chat') }}</span>
</a>
<a href="{{ route('admin.audit_logs.index') }}" class="nav-item {{ request()->routeIs('admin.audit_logs.*') ? 'active' : '' }}">
    <i class="fa-solid fa-ghost"></i>
    <span>{{ __('messages.ghost_log_feed') ?? 'GHOST LOG (ARXIV)' }}</span>
</a>
<a href="{{ route('admin.settings.index') }}" class="nav-item mt-auto {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
    <i class="fa-solid fa-gears"></i>
    <span>{{ __('messages.system_settings') }}</span>
</a>
