@extends('layouts.cyber')

@section('sidebar')
    @include('partials.admin_sidebar')
@endsection

@section('content')
<script>
    document.addEventListener('alpine:init', () => {
        if (!Alpine.data('fccDashboard')) {
            Alpine.data('fccDashboard', () => ({
                showEditModal: false,
                editItem: null,
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
    <div class="flex items-center gap-4">
        <button onclick="window.history.back()" class="w-8 h-8 md:w-10 md:h-10 rounded-full bg-white/5 border border-white/10 flex items-center justify-center text-white/50 hover:text-[var(--active-color)] hover:border-[var(--active-color)] transition-all shrink-0">
            <i class="fa-solid fa-arrow-left"></i>
        </button>
        <div>
            <h1 class="text-xl md:text-3xl font-orbitron font-bold tracking-widest text-[var(--active-color)]">{{ __('messages.fcc_hub_title') }}</h1>
            <p class="font-mono text-sm opacity-70 mt-1">{{ __('messages.fcc_desc') }}</p>
        </div>
    </div>
</div>

<div class="cyber-panel p-6 overflow-y-auto w-full" style="max-height: 800px;" x-data="fccDashboard()">
    @if (session('success'))
        <div class="p-3 mb-4 border border-[var(--active-color)] bg-[var(--active-color)] text-[var(--bg-color)] font-bold uppercase tracking-widest text-xs relative">
            >> {{ session('success') }}
        </div>
    @endif
    
    <div class="space-y-4 font-mono w-full">
        <table class="w-full text-left text-sm opacity-80">
            <thead>
                <tr class="border-b-2 border-[var(--active-color)] font-bold text-[var(--active-color)]">
                    <th class="pb-2">{{ __('messages.id') }}</th>
                    <th class="pb-2">{{ __('messages.operator') }}</th>
                    <th class="pb-2">{{ __('messages.service') }}</th>
                    <th class="pb-2">{{ __('messages.client_alias') }}</th>
                    <th class="pb-2 text-right">{{ __('messages.amount_uzs') }}</th>
                    <th class="pb-2 text-center">{{ __('messages.status') }}</th>
                    <th class="pb-2 text-center">{{ __('messages.files') }}</th>
                    <th class="pb-2 text-right">{{ __('messages.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($contracts as $ct)
                    <tr class="border-b border-gray-700 hover:bg-black hover:bg-opacity-50 transition-colors">
                        <td class="py-3 font-bold">{{ $ct->contract_id }}</td>
                        <td class="py-3 opacity-70">{{ $ct->user?->name ?? 'Noma\'lum operator' }}</td>
                        <td class="py-3">{{ $ct->service?->name ?? $ct->custom_type ?? 'O\'chirilgan/Maxsus xizmat' }}</td>
                        <td class="py-3">{{ $ct->client_name }}</td>
                        <td class="py-3 text-right">
                            <div class="font-bold text-[var(--active-color)]">{{ number_format($ct->amount, 0) }}</div>
                            <div class="text-xs opacity-70 text-[var(--cyber-yellow)] font-bold">Foyda: {{ number_format($ct->amount - $ct->cost_price, 0) }}</div>
                        </td>
                        <td class="py-3 text-center">
                            @if($ct->status == 'pending')
                                <span class="text-yellow-500 animate-pulse bg-yellow-900 bg-opacity-20 px-2 uppercase">{{ __('messages.pending') }}</span>
                            @elseif($ct->status == 'approved')
                                <span class="text-green-500 bg-green-900 bg-opacity-20 px-2 uppercase">{{ __('messages.approved') }}</span>
                            @elseif($ct->status == 'rejected')
                                <span class="text-red-500 bg-red-900 bg-opacity-20 px-2 uppercase">{{ __('messages.rejected') }}</span>
                            @endif
                        </td>
                        <td class="py-3 text-center">
                            @if($ct->file_path)
                                @php $ext = strtoupper(pathinfo($ct->file_path, PATHINFO_EXTENSION)); @endphp
                                <a href="{{ route('contracts.download', $ct->id) }}" class="text-blue-400 hover:text-white underline font-bold" title="Yuklab olish">.{{ $ext ?: 'PFC' }} Yuklash</a>
                            @else
                                <span class="opacity-30">{{ __('messages.na') }}</span>
                            @endif
                        </td>
                        <td class="py-3 flex justify-end gap-2">
                            @if($ct->status == 'pending')
                                <button @click.prevent="handleContract({{ $ct->id }}, 'approve')" class="px-3 py-1 bg-[var(--active-color)] text-[var(--bg-color)] text-xs font-bold uppercase transition-all hover:bg-transparent border border-transparent hover:border-[var(--active-color)] hover:text-[var(--active-color)]">{{ __('messages.accept') }}</button>
                                <button @click.prevent="handleContract({{ $ct->id }}, 'reject')" class="px-3 py-1 border border-red-500 text-red-500 text-xs font-bold uppercase transition-all hover:bg-red-500 hover:text-white">{{ __('messages.deny') }}</button>
                            @endif
                            
                            <div class="flex gap-2">
                                @if($ct->status == 'approved')
                                    <button type="button" @click.stop="window.open('{{ route('contracts.print', $ct->id) }}', '_blank', 'width=400,height=600,noopener,noreferrer')" class="px-3 py-1 bg-[var(--cyber-yellow)] text-black text-xs font-bold uppercase transition-all hover:bg-white" title="Chek chiqarish">PRINT</button>
                                @endif
                                <button @click="editItem = {{ json_encode([
                                    'id' => $ct->id,
                                    'client_name' => $ct->client_name,
                                    'amount' => $ct->amount,
                                    'status' => $ct->status,
                                    'contract_id' => $ct->contract_id
                                ]) }}; showEditModal = true;" class="px-3 py-1 bg-gray-800 text-white text-xs font-bold uppercase transition-all hover:bg-gray-600 border border-gray-600">Edit</button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-6 opacity-50 font-bold tracking-widest uppercase">{{ __('messages.no_records') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        
        <div class="mt-4">
            {{ $contracts->links() }}
        </div>
    </div>

    <!-- Alpine Edit Modal array -->
    <div x-show="showEditModal" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-90 backdrop-blur-md text-left">
        <div class="cyber-panel p-6 border-[var(--active-color)] w-full max-w-lg shadow-[0_0_20px_var(--hover-bg)]">
            <h2 class="text-xl font-orbitron mb-4 border-b border-[var(--active-color)] border-opacity-30 pb-2 text-[var(--active-color)] uppercase tracking-widest flex justify-between">
                MA'LUMOTLARNI TAHRIRLASH <span class="text-[var(--text-color)] font-mono text-sm opacity-70" x-text="editItem?.contract_id"></span>
            </h2>
            
            <form :action="'{{ url('admin/fcc') }}/' + editItem?.id" method="POST" class="space-y-4 font-mono text-xs mt-4">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block opacity-70 mb-1 text-[var(--text-color)] font-bold tracking-widest uppercase">{{ __('messages.client_alias') }}</label>
                        <input type="text" name="client_name" x-model="editItem.client_name" required class="w-full bg-[var(--input-bg)] border border-[var(--border-color)] p-2 text-[var(--text-color)] font-bold outline-none focus:border-[var(--active-color)] focus:shadow-[0_0_10px_rgba(0,255,0,0.1)] transition-all">
                    </div>
                    <div>
                        <label class="block opacity-70 mb-1 text-[var(--text-color)] font-bold tracking-widest uppercase">{{ __('messages.amount_uzs') }}</label>
                        <input type="number" name="amount" x-model="editItem.amount" required class="w-full bg-[var(--input-bg)] border border-[var(--border-color)] p-2 text-[var(--text-color)] font-bold outline-none focus:border-[var(--active-color)] focus:shadow-[0_0_10px_rgba(0,255,0,0.1)] transition-all">
                    </div>
                    <div>
                        <label class="block opacity-70 mb-1 text-[var(--text-color)] font-bold tracking-widest uppercase">{{ __('messages.status') }} / HOLATI</label>
                        <select name="status" x-model="editItem.status" class="w-full bg-[var(--input-bg)] border border-[var(--border-color)] p-2 text-[var(--text-color)] font-bold outline-none focus:border-[var(--active-color)] focus:shadow-[0_0_10px_rgba(0,255,0,0.1)] transition-all uppercase">
                            <option value="pending">{{ __('messages.pending') }} (Kutilmoqda)</option>
                            <option value="approved">{{ __('messages.approved') }} (Tasdiqlangan)</option>
                            <option value="rejected">{{ __('messages.rejected') }} (Rad etilgan)</option>
                        </select>
                    </div>
                </div>

                <div class="flex flex-col md:flex-row justify-between items-center gap-4 mt-6 pt-4 border-t border-[var(--active-color)] border-opacity-30">
                    <button type="button" @click="if(confirm('Kiritilgan xizmatni barcha bog\'langan hisoboti bilan butunlay yo\'q qilmoqchimisiz?')) { document.getElementById('deleteFCCForm').action = '{{ url('admin/fcc') }}/' + editItem.id; document.getElementById('deleteFCCForm').submit(); }" class="px-3 py-2 border border-red-500 text-red-500 hover:bg-red-500 hover:text-[var(--bg-color)] transition-colors font-bold uppercase tracking-widest w-full md:w-auto text-center shadow-[0_0_5px_rgba(255,0,0,0.3)] hover:shadow-none">Tizimdan O'chirish</button>
                    <div class="flex gap-2 w-full md:w-auto">
                        <button type="button" @click="showEditModal = false" class="flex-1 md:flex-none px-4 py-2 border border-[var(--text-color)] border-opacity-30 text-[var(--text-color)] opacity-70 hover:opacity-100 hover:bg-[var(--hover-bg)] transition-colors font-bold uppercase tracking-widest">Bekor</button>
                        <button type="submit" class="flex-1 md:flex-none px-4 py-2 bg-[var(--active-color)] text-[var(--bg-color)] border border-transparent hover:bg-transparent hover:text-[var(--active-color)] hover:border-[var(--active-color)] transition-all font-bold uppercase tracking-widest shadow-[0_0_10px_var(--active-color)] hover:shadow-none">Saqlash</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <form id="deleteFCCForm" method="POST" class="hidden">
        @csrf
        @method('DELETE')
    </form>
</div>


@endsection
