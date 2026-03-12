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
<div class="mb-6 flex justify-between items-end border-b border-[var(--active-color)] pb-4">
    <div>
        <h1 class="text-3xl font-orbitron font-bold tracking-widest text-[var(--active-color)]">TO'LOV GRAFIGI</h1>
        <p class="font-mono text-sm opacity-70 mt-1">#QARZ-{{ $debt->id }} | Mijoz: {{ $debt->client->name }}</p>
    </div>
    <div class="flex gap-4">
        <a href="{{ route('admin.clients.index') }}" class="px-6 py-2 border border-gray-700 text-gray-400 font-bold font-orbitron uppercase tracking-widest hover:bg-gray-900 transition-all">
            ORQAGA
        </a>
        <button onclick="window.open('{{ route('admin.debts.printSchedule', $debt->id) }}', 'PrintSchedule', 'width=800,height=900')" class="px-6 py-2 bg-[var(--cyber-yellow)] text-black font-bold font-orbitron uppercase tracking-widest shadow-[0_0_15px_var(--cyber-yellow)] hover:scale-105 transition-all">
            CHOP ETISH
        </button>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <!-- Debt Info Card -->
    <div class="cyber-panel p-6 border-l-4 border-red-500">
        <h3 class="text-xs font-orbitron opacity-50 mb-4 uppercase tracking-widest">Umumiy Ma'lumot</h3>
        <div class="space-y-4 font-mono">
            <div>
                <span class="block text-sm opacity-50 uppercase">Umumiy Summa</span>
                <span class="text-xl font-bold text-white">{{ number_format($debt->total_amount, 0, '.', ' ') }} UZS</span>
            </div>
            <div>
                <span class="block text-sm opacity-50 uppercase">Qolgan Summa</span>
                <span class="text-xl font-bold text-red-500">{{ number_format($debt->remaining_amount, 0, '.', ' ') }} UZS</span>
            </div>
            <div>
                <span class="block text-sm opacity-50 uppercase">Turi</span>
                <span class="text-sm font-bold text-[var(--active-color)] uppercase">{{ $debt->type === 'installment' ? 'Bo\'lib to\'lash' : 'Bir martalik' }}</span>
            </div>
            <div>
                <span class="block text-sm opacity-50 uppercase">Holati</span>
                <span class="px-2 py-0.5 text-sm font-bold uppercase rounded-sm {{ $debt->status === 'paid' ? 'bg-green-900 text-green-300' : 'bg-red-900 text-red-300 animate-pulse' }}">
                    {{ $debt->status === 'paid' ? 'TO\'LANGAN' : 'QARZDORLIK' }}
                </span>
            </div>
        </div>
    </div>

    <!-- Monthly Breakdown Table -->
    <div class="md:col-span-2 cyber-panel p-6">
        <h3 class="text-xs font-orbitron opacity-50 mb-4 uppercase tracking-widest">To'lovlar Jadvali</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-left font-mono text-xs">
                <thead>
                    <tr class="border-b border-gray-800 text-[var(--active-color)] opacity-70">
                        <th class="pb-3">#</th>
                        <th class="pb-3">TO'LOV SANASI</th>
                        <th class="pb-3 text-right">SUMMA</th>
                        <th class="pb-3 text-center">HOLATI</th>
                        <th class="pb-3 text-right">AMAL</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-900">
                    @foreach($debt->installments->sortBy('due_date') as $index => $inst)
                        <tr class="hover:bg-white hover:bg-opacity-5 transition-colors">
                            <td class="py-4">{{ $index + 1 }}</td>
                            <td class="py-4 font-bold {{ $inst->status === 'pending' && $inst->due_date < now() ? 'text-red-500' : '' }}">
                                {{ \Carbon\Carbon::parse($inst->due_date)->format('d.m.Y') }}
                                @if($inst->status === 'pending' && $inst->due_date < now())
                                    <span class="text-xs border border-red-500 px-1 ml-2">MUDDAT O'TGAN</span>
                                @endif
                            </td>
                            <td class="py-4 text-right font-bold">{{ number_format($inst->amount, 0, '.', ' ') }} UZS</td>
                            <td class="py-4 text-center">
                                @if($inst->status === 'paid')
                                    <span class="text-green-500">✔ TO'LANDAN</span>
                                    <div class="text-xs opacity-50">{{ \Carbon\Carbon::parse($inst->paid_at)->format('d.m.Y H:i') }}</div>
                                @else
                                    <span class="text-gray-500 font-bold">KUTILMOQDA</span>
                                @endif
                            </td>
                            <td class="py-4 text-right">
                                @if($inst->status === 'pending' && auth()->user()->role !== 'operator')
                                    <form method="POST" action="{{ route('admin.debts.payInstallment', $inst->id) }}">
                                        @csrf
                                        <button type="submit" class="px-2 py-1 bg-green-600 text-white font-bold hover:bg-green-500 transition-all uppercase text-xs">TO'LOVNI QABUL QILISH</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
