@extends('layouts.cyber')

@section('sidebar')
    @include('partials.admin_sidebar')
@endsection

@section('content')
<script>
    document.addEventListener('alpine:init', () => {
        if (!Alpine.data('financeDashboard')) {
            Alpine.data('financeDashboard', () => ({
                showTxModal: false,
                editTx: null,
                async handleContract(contractId, action) {
                    if (!confirm('Haqiqatdan ham bu amalni bajarmoqchimisiz?')) return;
                    try {
                        const response = await fetch(`/contracts/${contractId}/${action}`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            }
                        });
                        const result = await response.json();
                        if (result.success) {
                            if (result.print_url) window.open(result.print_url, "_blank", "width=400,height=600,noopener,noreferrer");
                            setTimeout(() => {
                                alert(result.message);
                                location.reload(); 
                            }, 500);
                        } else {
                            alert(result.message);
                        }
                    } catch (e) {
                        console.error('Contract Action Error:', e);
                        alert('Tizimda xatolik yuz berdi.');
                    }
                }
            }));
        }
    });
</script>
<div class="mb-6 flex justify-between items-end border-b border-[var(--active-color)] pb-4">
    <div>
        <h1 class="text-3xl font-orbitron font-bold tracking-widest text-[var(--active-color)]">{{ __('messages.finance_ledger') }}</h1>
        <p class="font-mono text-sm opacity-70 mt-1">{{ __('messages.finance_desc') }}</p>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
    <div class="cyber-panel p-6 border-l-4 border-green-500">
        <h3 class="text-sm font-orbitron mb-2 opacity-80 pb-2 text-green-500">{{ __('messages.total_revenue') }}</h3>
        <p class="text-4xl font-mono font-bold">{{ number_format($totalIncome, 0, ',', ' ') }} <span class="text-sm opacity-50">UZS</span></p>
    </div>
    <div class="cyber-panel p-6 border-l-4 border-red-500">
        <h3 class="text-sm font-orbitron mb-2 opacity-80 pb-2 text-red-500">{{ __('messages.total_expense') }}</h3>
        <p class="text-4xl font-mono font-bold">{{ number_format($totalExpense, 0, ',', ' ') }} <span class="text-sm opacity-50">UZS</span></p>
    </div>
</div>

<div class="cyber-panel p-6 overflow-y-auto w-full" style="max-height: 800px;" x-data="financeDashboard()">
    @if (session('success'))
        <div class="p-3 mb-4 border border-[var(--active-color)] bg-[var(--active-color)] text-[var(--bg-color)] font-bold uppercase tracking-widest text-xs relative">
            >> {{ session('success') }}
        </div>
    @endif
    <h2 class="text-2xl font-orbitron font-bold text-[var(--active-color)] tracking-widest uppercase border-b border-[var(--active-color)] pb-3 mb-6 flex justify-between">
        {{ __('messages.global_ledger') }}
    </h2>

    <div class="space-y-4 font-mono w-full">
        <table class="w-full text-left text-sm opacity-80">
            <thead>
                <tr class="border-b-2 border-gray-700 font-bold opacity-70">
                    <th class="pb-2">{{ __('messages.date_time') }}</th>
                    <th class="pb-2">{{ __('messages.actor') }}</th>
                    <th class="pb-2">{{ __('messages.source_contract') }}</th>
                    <th class="pb-2">{{ __('messages.details') }}</th>
                    <th class="pb-2 text-right">{{ __('messages.amount_uzs') }}</th>
                    <th class="pb-2 text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $tx)
                    <tr class="border-b border-[var(--border-color)] border-opacity-30 hover:bg-[var(--active-color)] hover:text-[var(--bg-color)] hover:bg-opacity-80 transition-colors group">
                        <td class="py-3 font-bold">{{ $tx->created_at->format('Y-m-d H:i') }}</td>
                        <td class="py-3">{{ $tx->user->name }}</td>
                        <td class="py-3">{{ $tx->contract_id ? __('messages.contract_prefix') . ' ' . $tx->contract->contract_id : __('messages.manual_entry') }}</td>
                        <td class="py-3">{{ $tx->description }}</td>
                        <td class="py-3 text-right font-bold w-40">
                            @if($tx->type == 'income')
                                <span class="text-green-500 group-hover:text-green-900">+{{ number_format($tx->amount, 0) }}</span>
                            @else
                                <span class="text-red-500 group-hover:text-red-900">-{{ number_format($tx->amount, 0) }}</span>
                            @endif
                        </td>
                        <td class="py-3 text-right">
                            <div class="flex gap-2 justify-end">
                                @if($tx->contract_id)
                                    <button type="button" @click.stop="window.open('{{ route('contracts.print', $tx->contract_id) }}', '_blank', 'width=400,height=600,noopener,noreferrer')" class="px-3 py-1 bg-[var(--cyber-yellow)] text-black text-xs font-bold uppercase transition-all hover:bg-white" title="Chek chiqarish">PRINT</button>
                                    @if($tx->contract && $tx->contract->status == 'pending')
                                        <button @click.prevent="handleContract({{ $tx->contract_id }}, 'approve')" class="px-3 py-1 bg-[var(--active-color)] text-[var(--bg-color)] text-xs font-bold uppercase transition-all hover:bg-transparent border border-transparent hover:border-[var(--active-color)] hover:text-[var(--active-color)]">{{ __('messages.accept') }}</button>
                                        <button @click.prevent="handleContract({{ $tx->contract_id }}, 'reject')" class="px-3 py-1 border border-red-500 text-red-500 text-xs font-bold uppercase transition-all hover:bg-red-500 hover:text-white">{{ __('messages.deny') }}</button>
                                    @endif
                                @endif
                                <button @click="editTx = {{ json_encode([
                                    'id' => $tx->id,
                                    'amount' => $tx->amount,
                                    'type' => $tx->type,
                                    'description' => $tx->description
                                ]) }}; showTxModal = true;" class="px-3 py-1 bg-gray-900 text-[var(--active-color)] border border-[var(--active-color)] text-xs font-bold uppercase hover:bg-black">Edit</button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-6 opacity-50 font-bold tracking-widest uppercase">{{ __('messages.no_financial_movements') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        
        <div class="mt-4">
            {{ $transactions->links() }}
        </div>
    </div>
    
    <!-- Alpine Edit Modal -->
    <div x-show="showTxModal" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-90 backdrop-blur-md text-left">
        <div class="cyber-panel p-6 border-[var(--active-color)] w-full max-w-lg bg-black border">
            <h2 class="text-xl font-orbitron mb-4 border-b border-[var(--active-color)] pb-2 text-[var(--active-color)] uppercase tracking-widest">
                Edit Transaction <span class="text-white">#<span x-text="editTx?.id"></span></span>
            </h2>
            
            <form :action="'{{ url('admin/finance') }}/' + editTx?.id" method="POST" class="space-y-4 font-mono text-sm mt-4">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block opacity-70 mb-1 text-[var(--active-color)]">Amount (UZS)</label>
                        <input type="number" name="amount" x-model="editTx.amount" required class="w-full bg-gray-900 border border-[var(--active-color)] p-2 text-white outline-none">
                    </div>
                    <div>
                        <label class="block opacity-70 mb-1 text-[var(--active-color)]">Operation Type</label>
                        <select name="type" x-model="editTx.type" class="w-full bg-gray-900 border border-[var(--active-color)] p-2 text-white outline-none uppercase">
                            <option value="income">Income (+/+)</option>
                            <option value="expense">Expense (-/-)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block opacity-70 mb-1 text-[var(--active-color)]">Description / Memo</label>
                        <input type="text" name="description" x-model="editTx.description" required class="w-full bg-gray-900 border border-[var(--active-color)] p-2 text-white outline-none">
                    </div>
                </div>

                <div class="flex justify-between items-center mt-6 pt-4 border-t border-[var(--active-color)]">
                    <button type="button" @click="if(confirm('Are you sure you want to permanently delete this transaction?')) { document.getElementById('deleteTxForm').action = '{{ url('admin/finance') }}/' + editTx.id; document.getElementById('deleteTxForm').submit(); }" class="px-4 py-2 bg-red-900 bg-opacity-20 border border-red-500 text-red-500 hover:bg-red-500 hover:text-black transition-colors font-bold uppercase tracking-widest text-xs">Delete Record</button>
                    <div class="flex gap-2">
                        <button type="button" @click="showTxModal = false" class="px-4 py-2 border border-gray-500 text-gray-500 hover:bg-gray-500 hover:text-white transition-colors font-bold uppercase tracking-widest">Abort</button>
                        <button type="submit" class="px-4 py-2 bg-[var(--active-color)] text-[var(--bg-color)] border border-transparent hover:bg-transparent hover:border-[var(--active-color)] hover:text-[var(--active-color)] transition-colors font-bold uppercase tracking-widest shadow-[0_0_10px_var(--active-color)] hover:shadow-none">Execute Save</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <form id="deleteTxForm" method="POST" class="hidden">
        @csrf
        @method('DELETE')
    </form>
</div>


@endsection
