<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\Service;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ContractController extends Controller
{
    public function store(Request $request)
    {
        // Ensure the AJAX request doesn't get redirected on validation error by using Response JSON
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'service_name' => 'required|string|max:255',
            'custom_type' => 'required|string|max:255',
            'client_name' => 'required|string|max:255',
            'client_phone' => 'required|string|max:255',
            'client_address' => 'required|string|max:255',
            'client_id' => 'nullable|exists:clients,id',
            'amount' => 'required|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'operator_share_percentage' => 'nullable|numeric|min:0|max:100',
            'pfc_file' => 'nullable|file',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $validated = $validator->validated();
        $customTypeLower = strtolower(trim($validated['custom_type']));

        if (str_contains($customTypeLower, 'imzo') && !$request->hasFile('pfc_file')) {
            return response()->json(['success' => false, 'message' => 'E-imzo xizmati bo\'lganda buyurtma fayli (.pfx) kiritilishi majburiy!'], 422);
        }

        if ($request->hasFile('pfc_file')) {
            $extension = strtolower($request->file('pfc_file')->getClientOriginalExtension());
            if (str_contains($customTypeLower, 'imzo') && $extension !== 'pfx') {
                return response()->json(['success' => false, 'message' => 'E-imzo xizmati uchun fayl formati faqat .pfx bo\'lishi shart!'], 422);
            }
        }

        // Find existing service or create a new "Custom" one with default 10% operator share
        $service = \App\Models\Service::firstOrCreate(
            ['name' => $validated['service_name']],
            [
                'type' => 'Custom',
                'cost_price' => 0,
                'client_price' => $validated['amount'],
                'operator_share_percentage' => 10
            ]
        );

        // Client linkage logic
        $client = null;
        if (!empty($validated['client_id'])) {
            $client = \App\Models\Client::find($validated['client_id']);
        } else {
            // Try to find by name and phone to avoid duplicates
            $client = \App\Models\Client::firstOrCreate(
                ['phone' => $validated['client_phone']],
                [
                    'name' => $validated['client_name'],
                    'address' => $validated['client_address']
                ]
            );
        }

        $contractData = [
            'user_id' => auth()->id(),
            'service_id' => $service->id,
            'client_id' => $client ? $client->id : null,
            'client_name' => $validated['client_name'],
            'client_phone' => $validated['client_phone'],
            'client_address' => $validated['client_address'],
            'amount' => $validated['amount'],
            'cost_price' => $validated['cost_price'] ?? 0,
            'operator_share_percentage' => $validated['operator_share_percentage'] ?? 0,
            'custom_type' => $validated['custom_type'],
            'status' => 'pending',
            'contract_id' => 'REQ-' . rand(10000, 99999) . '-' . \Illuminate\Support\Str::upper(\Illuminate\Support\Str::random(3)), 
        ];

        if ($request->hasFile('pfc_file')) {
            $uploadedFile = $request->file('pfc_file');
            $originalExtension = $uploadedFile->getClientOriginalExtension();
            $filename = $contractData['contract_id'] . '.' . ($originalExtension ?: 'bin');
            $path = $uploadedFile->storeAs('contracts', $filename);
            $contractData['file_path'] = $path;
        }

        Contract::create($contractData);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['message' => 'Contract routed to Treasury.', 'success' => true]);
        }

        return redirect()->back()->with('success', 'Contract routed to Treasury.');
    }

    public function approve(Request $request, Contract $contract)
    {
        if (auth()->user()->role !== 'cashier' && auth()->user()->role !== 'admin') {
            abort(403);
        }

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            // Create transaction
            Transaction::create([
                'user_id' => auth()->id(),
                'contract_id' => $contract->id,
                'type' => 'income',
                'amount' => $contract->amount,
                'description' => 'Payment for ' . $contract->contract_id,
            ]);

            // Calculate operator percentage from COMPANY PROFIT and add to balance
            if ($contract->operator_share_percentage > 0) {
                $companyProfit = (float)$contract->amount - (float)$contract->cost_price;
                if ($companyProfit > 0) {
                    $operatorShare = ($companyProfit * (float)$contract->operator_share_percentage) / 100;
                    $operator = $contract->user;
                    if ($operator) {
                        $operator->balance += $operatorShare;
                        $operator->save();

                        // Log operator share internally as an audit trail (does not hit global company vault)
                        \App\Models\AuditLog::create([
                            'user_id' => $operator->id,
                            'action' => 'operator_share_credited',
                            'new_values' => ['amount' => $operatorShare, 'contract_id' => $contract->contract_id],
                            'ip_address' => $request->ip()
                        ]);
                    }
                }
            }

            $contract->update(['status' => 'approved']);
            \Illuminate\Support\Facades\DB::commit();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Buyurtma tasdiqlandi!',
                    'print_url' => route('contracts.print', $contract->id)
                ]);
            }

            return redirect()->back()->with([
                'success' => 'Buyurtma tasdiqlandi!',
                'print_receipt' => $contract->id
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            \Illuminate\Support\Log::error("Approval error: " . $e->getMessage());
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Xatolik: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->back()->with('error', 'Xatolik: ' . $e->getMessage());
        }
    }

    public function printLog(Contract $contract)
    {
        if (auth()->user()->role !== 'cashier' && auth()->user()->role !== 'admin') {
            abort(403);
        }
        
        $transaction = Transaction::where('contract_id', $contract->id)->where('type', 'income')->first();
        $cashier = $transaction ? $transaction->user : auth()->user();
        
        $settings = \App\Models\Setting::pluck('value', 'key')->toArray();
        
        return view('contracts.print', compact('contract', 'cashier', 'settings'));
    }

    public function download(Contract $contract)
    {
        if (auth()->user()->role !== 'cashier' && auth()->user()->role !== 'admin' && $contract->user_id !== auth()->id()) {
            abort(403);
        }

        if (!$contract->file_path) {
            return redirect()->back()->with('error', 'Fayl topilmadi.');
        }

        $path = storage_path('app/private/' . $contract->file_path);
        
        if (!file_exists($path)) {
            // Try without private if it's in standard app/ contracts
            $path = storage_path('app/' . $contract->file_path);
        }

        if (!file_exists($path)) {
            return redirect()->back()->with('error', 'Fayl tizimda mavjud emas.');
        }

        return response()->download($path);
    }

    public function reject(Request $request, Contract $contract)
    {
        if (auth()->user()->role !== 'cashier' && auth()->user()->role !== 'admin') {
            abort(403);
        }

        $contract->update(['status' => 'rejected']);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Buyurtma rad etildi.']);
        }

        return redirect()->back()->with('error', 'Contract Rejected.');
    }
}
