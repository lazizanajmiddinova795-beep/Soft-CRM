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
        <h1 class="text-3xl font-orbitron font-bold tracking-widest text-[var(--active-color)]">MIJOZLAR BAZASI</h1>
        <p class="font-mono text-sm opacity-70 mt-1">Mijozlar ro'yxati, hisob-kitoblar va qarzlar boshqaruvi</p>
    </div>
    <button @click="$dispatch('open-client-modal')" class="px-6 py-2 bg-[var(--active-color)] text-black font-bold font-orbitron uppercase tracking-widest shadow-[0_0_15px_var(--active-color)] hover:scale-105 transition-all">
        + YANGI MIJOZ
    </button>
</div>

<div class="cyber-panel p-6" x-data="{ 
    showEditModal: false, 
    showDebtModal: false, 
    selectedClient: null,
    search: ''
}">
    @if (session('success'))
        <div class="p-3 mb-4 border border-[var(--active-color)] bg-[var(--active-color)] text-[var(--bg-color)] font-bold uppercase tracking-widest text-xs">
            >> {{ session('success') }}
        </div>
    @endif

    <div class="mb-4">
        <input type="text" x-model="search" placeholder="MIJOZ QIDIRISH (ISM YOKI TELEFON)..." class="w-full bg-black bg-opacity-50 border border-[var(--active-color)] p-3 font-mono text-sm text-[var(--active-color)] outline-none focus:shadow-[0_0_10px_var(--active-color)] transition-all">
    </div>

    <div class="overflow-x-auto overflow-y-auto slim-scroll min-h-[400px]">
        <table class="w-full text-left font-mono text-xs">
            <thead>
                <tr class="border-b border-[var(--active-color)] text-[var(--active-color)] opacity-70 uppercase tracking-widest font-bold">
                    <th class="pb-3">ISM / SHARIF</th>
                    <th class="pb-3">TELEFON</th>
                    <th class="pb-3">MANZIL</th>
                    <th class="pb-3 text-right">BALANS</th>
                    <th class="pb-3 text-right">QARZDORLIK</th>
                    <th class="pb-3 text-center">SHARTNOMALAR</th>
                    <th class="pb-3 text-right">AMALLAR</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800">
                @forelse($clients as $client)
                    <tr class="hover:bg-black hover:bg-opacity-40 transition-colors" x-show="!search || '{{ strtolower($client->name) }}'.includes(search.toLowerCase()) || '{{ $client->phone }}'.includes(search)">
                        <td class="py-4 font-bold text-[var(--active-color)]">{{ $client->name }}</td>
                        <td class="py-4 opacity-80">{{ $client->phone ?: 'N/A' }}</td>
                        <td class="py-4 opacity-60 text-sm">{{ Str::limit($client->address, 30) ?: 'N/A' }}</td>
                        <td class="py-4 text-right font-bold text-green-500">{{ number_format($client->balance, 0, '.', ' ') }} UZS</td>
                        <td class="py-4 text-right font-bold {{ $client->debt_amount > 0 ? 'text-red-500 animate-pulse' : 'text-gray-600' }}">
                            {{ number_format($client->debt_amount, 0, '.', ' ') }} UZS
                        </td>
                        <td class="py-4 text-center">
                            <span class="px-2 py-0.5 border border-gray-700 rounded-sm">{{ $client->contracts_count }}</span>
                        </td>
                        <td class="py-4 text-right">
                            <div class="flex justify-end gap-2">
                                <button @click="selectedClient = {{ json_encode($client) }}; showDebtModal = true" class="px-3 py-1 border border-red-500 text-red-500 text-sm font-bold uppercase hover:bg-red-500 hover:text-black transition-all">QARZ +</button>
                                <button @click="selectedClient = {{ json_encode($client) }}; showEditModal = true" class="px-3 py-1 border border-blue-500 text-blue-500 text-sm font-bold uppercase hover:bg-blue-500 hover:text-black transition-all">TAHRIR</button>
                                
                                @foreach($client->debts as $debt)
                                    <a href="{{ route('admin.debts.showSchedule', $debt->id) }}" class="px-3 py-1 border border-[var(--cyber-yellow)] text-[var(--cyber-yellow)] text-sm font-bold uppercase hover:bg-[var(--cyber-yellow)] hover:text-black transition-all">Grafik #{{ $debt->id }}</a>
                                @endforeach

                                @if(auth()->user()->role === 'admin')
                                    <form method="POST" action="{{ route('admin.clients.destroy', $client->id) }}" onsubmit="return confirm('Mijozni o\'chirmoqchimisiz?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="p-1 text-red-700 hover:text-red-500">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center opacity-30 uppercase tracking-[0.5em]">Tizimda mijozlar mavjud emas</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-4">
        {{ $clients->links() }}
    </div>

    <!-- Create/Edit Client Modal -->
    <div x-show="showEditModal" @open-client-modal.window="selectedClient = null; showEditModal = true" style="display: none;" class="fixed inset-0 z-[60] flex items-center justify-center bg-black bg-opacity-90 backdrop-blur-sm">
        <div class="cyber-panel p-8 w-full max-w-md border-t-4 border-[var(--active-color)]">
            <h2 class="text-xl font-orbitron font-bold mb-6 text-[var(--active-color)] tracking-widest uppercase" x-text="selectedClient ? 'MIJOZNI TAHRIRLASH' : 'YANGI MIJOZ QO\'SHISH'"></h2>
            
            <form :action="selectedClient ? '{{ url('admin/clients') }}/' + selectedClient.id : '{{ route('admin.clients.store') }}'" method="POST" class="space-y-4 font-mono text-xs">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block opacity-70 mb-1 uppercase tracking-widest">To'liq ism-sharifi</label>
                        <input type="text" name="name" :value="selectedClient?.name" required class="w-full bg-black border border-gray-700 p-3 text-[var(--active-color)] focus:border-[var(--active-color)] outline-none">
                    </div>
                    <div>
                        <label class="block opacity-70 mb-1 uppercase tracking-widest">Telefon raqami</label>
                        <input type="text" name="phone" :value="selectedClient?.phone" placeholder="+998" class="w-full bg-black border border-gray-700 p-3 text-[var(--active-color)] focus:border-[var(--active-color)] outline-none">
                    </div>
                    <div>
                        <label class="block opacity-70 mb-1 uppercase tracking-widest">Yashash manzili (Sektor)</label>
                        <textarea name="address" :value="selectedClient?.address" class="w-full bg-black border border-gray-700 p-3 text-[var(--active-color)] focus:border-[var(--active-color)] outline-none h-20"></textarea>
                    </div>
                    <div x-show="selectedClient">
                        <label class="block opacity-70 mb-1 uppercase tracking-widest">Holati</label>
                        <select name="status" class="w-full bg-black border border-gray-700 p-3 text-[var(--active-color)] focus:border-[var(--active-color)] outline-none">
                             <option value="active" :selected="selectedClient?.status === 'active'">AKTIV</option>
                             <option value="inactive" :selected="selectedClient?.status === 'inactive'">BLOKLANGAN</option>
                        </select>
                    </div>
                </div>

                <div class="flex gap-4 mt-8">
                    <button type="button" @click="showEditModal = false" class="flex-1 py-3 border border-gray-700 text-gray-500 font-bold uppercase tracking-widest hover:bg-gray-900 transition-all">Bekor</button>
                    <button type="submit" class="flex-1 py-3 bg-[var(--active-color)] text-black font-bold uppercase tracking-widest hover:scale-105 transition-all shadow-[0_0_15px_var(--active-color)]">Saqlash</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Debt Creation Modal -->
    <div x-show="showDebtModal" style="display: none;" class="fixed inset-0 z-[60] flex items-center justify-center bg-black bg-opacity-95 backdrop-blur-md">
        <div class="cyber-panel p-8 w-full max-w-lg border-t-4 border-red-500">
            <h2 class="text-xl font-orbitron font-bold mb-2 text-red-500 tracking-widest uppercase">QARZ RASMIYLASHTIRISH</h2>
            <p class="text-sm opacity-70 mb-6 font-mono" x-text="'MIJOZ: ' + (selectedClient?.name || '')"></p>

            <form action="{{ route('admin.debts.store') }}" method="POST" class="space-y-4 font-mono text-xs" x-data="{ type: 'one-time' }">
                @csrf
                <input type="hidden" name="client_id" :value="selectedClient?.id">
                
                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2">
                        <label class="block opacity-70 mb-1 uppercase tracking-[0.2em]">Qarz Miqdori (UZS)</label>
                        <input type="number" name="total_amount" required class="w-full bg-black border border-red-900 border-opacity-40 p-3 text-red-500 text-lg font-bold outline-none focus:border-red-500">
                    </div>
                    
                    <div>
                        <label class="block opacity-70 mb-1 uppercase tracking-[0.2em]">To'lov Turi</label>
                        <select name="type" x-model="type" class="w-full bg-black border border-gray-700 p-3 text-white outline-none focus:border-red-500 uppercase">
                            <option value="one-time">Bir martalik (Deadline)</option>
                            <option value="installment">Bo'lib to'lash (Oylik)</option>
                        </select>
                    </div>

                    <div x-show="type === 'one-time'">
                        <label class="block opacity-70 mb-1 uppercase tracking-[0.2em]">Qaytarish Sanasi</label>
                        <input type="date" name="deadline" class="w-full bg-black border border-gray-700 p-3 text-white outline-none focus:border-red-500">
                    </div>

                    <div x-show="type === 'installment'">
                        <label class="block opacity-70 mb-1 uppercase tracking-[0.2em]">Oylar Soni</label>
                        <input type="number" name="installment_count" min="1" max="60" class="w-full bg-black border border-gray-700 p-3 text-white outline-none focus:border-red-500">
                    </div>
                </div>

                <div>
                    <label class="block opacity-70 mb-1 uppercase tracking-[0.2em]">Qo'shimcha izoh / Maqsad</label>
                    <textarea name="description" placeholder="Nima uchun qarz berilayotgani haqida..." class="w-full bg-black border border-gray-700 p-3 text-white outline-none focus:border-red-500 h-20"></textarea>
                </div>

                <div class="flex gap-4 mt-8">
                    <button type="button" @click="showDebtModal = false" class="flex-1 py-3 border border-gray-700 text-gray-500 font-bold uppercase tracking-widest hover:bg-gray-900 transition-all">Yopish</button>
                    <button type="submit" class="flex-1 py-3 bg-red-600 text-white font-bold uppercase tracking-widest hover:scale-105 transition-all shadow-[0_0_20px_rgba(255,0,0,0.5)]">QARZNI TASDIQLASH</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    [x-cloak] { display: none !important; }
    .cyber-panel table th { position: sticky; top: 0; background: var(--bg-color); z-index: 10; }
</style>
@endsection
