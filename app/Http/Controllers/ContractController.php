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
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'client_name' => 'required|string|max:255',
            'client_phone' => 'required|string|max:255',
            'client_address' => 'required|string|max:255',
            'client_id' => 'nullable|exists:clients,id',
            'payment_method' => 'required|in:cash,card',
            'services_json' => 'required|string', // Comes as JSON string from Alpine
            'amount' => 'required|numeric|min:0',
            'pfc_file' => 'nullable|file',
            'operator_share_percentage' => 'required|numeric|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        try {
            $validated = $validator->validated();
            $items = json_decode($validated['services_json'], true);

            if (!is_array($items)) {
                return response()->json(['success' => false, 'message' => 'Xizmatlar ma\'lumoti noto\'g\'ri formatda.'], 422);
            }

            // Validation for E-imzo requirements
            $hasImzo = false;
            foreach($items as $item) {
                if (isset($item['type']) && str_contains(strtolower($item['type']), 'imzo')) $hasImzo = true;
            }

            if ($hasImzo && !$request->hasFile('pfc_file')) {
                return response()->json(['success' => false, 'message' => 'E-imzo xizmati bo\'lganda buyurtma fayli (.pfx) kiritilishi majburiy!'], 422);
            }

            // Use the first service as the primary one for legacy table structure
            $primaryService = $items[0] ?? ['name' => 'Multiple Services', 'type' => 'General', 'cost' => 0, 'price' => 0];
            $companyId = auth()->user()->company_id;

            $service = \App\Models\Service::firstOrCreate(
                ['name' => $primaryService['name'], 'company_id' => $companyId],
                [
                    'type' => $primaryService['type'] ?? 'General',
                    'cost_price' => $primaryService['cost'] ?? 0,
                    'client_price' => $primaryService['price'] ?? 0,
                    'operator_share_percentage' => 10,
                ]
            );

            $client = null;
            $clientId = $request->input('client_id');
            if (!empty($clientId)) {
                $client = \App\Models\Client::where('company_id', $companyId)->find($clientId);
            }

            if (!$client) {
                $client = \App\Models\Client::firstOrCreate(
                    ['phone' => $validated['client_phone'], 'company_id' => $companyId],
                    [
                        'name' => $validated['client_name'],
                        'address' => $validated['client_address'],
                    ]
                );
            }

        // Calculate total cost_price from all items
        $totalCost = 0;
        foreach($items as $it) {
            $totalCost += (float)($it['cost'] ?? 0);
        }

        $contractData = [
            'company_id' => auth()->user()->company_id,
            'user_id' => auth()->id(),
            'service_id' => $service->id,
            'client_id' => $client ? $client->id : null,
            'client_name' => $validated['client_name'],
            'client_phone' => $validated['client_phone'],
            'client_address' => $validated['client_address'],
            'amount' => $validated['amount'],
            'cost_price' => $totalCost,
            'payment_method' => $validated['payment_method'],
            'operator_share_percentage' => $validated['operator_share_percentage'],
            'custom_type' => count($items) > 1 ? 'Multiple Services' : $primaryService['type'],
            'services_json' => $items,
            'status' => 'pending',
            'contract_id' => 'REQ-' . rand(1000, 9999) . '-' . strtoupper(Str::random(4)), 
        ];

        if ($request->hasFile('pfc_file')) {
            $uploadedFile = $request->file('pfc_file');
            $originalExtension = $uploadedFile->getClientOriginalExtension();
            $filename = $contractData['contract_id'] . '.' . ($originalExtension ?: 'bin');
            $path = $uploadedFile->storeAs('contracts', $filename);
            $contractData['file_path'] = $path;
        }

            Contract::create($contractData);

            return response()->json(['message' => 'Shartnoma kassaga yo\'naltirildi.', 'success' => true]);
        } catch (\Exception $e) {
            \Log::error("Contract Store Error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Tizim xatosi: ' . $e->getMessage()
            ], 500);
        }
    }

    public function approve(Request $request, Contract $contract)
    {
        if (auth()->user()->role !== 'cashier' && auth()->user()->role !== 'admin') {
            abort(403);
        }

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            // Kassir hududidagi asosiy to'lov (karta yoki naqd)
            $paymentMethod = $request->input('payment_method', $contract->payment_method);
            
            Transaction::create([
                'user_id' => auth()->id(),
                'contract_id' => $contract->id,
                'type' => 'income',
                'amount' => $contract->amount,
                'payment_method' => $paymentMethod,
                'description' => 'Payment for ' . $contract->contract_id,
            ]);

            // Davlatga xizmat tannarxini to'lash - buni darhol kartadan yechib olamiz
            if ($contract->cost_price > 0) {
                Transaction::create([
                    'user_id' => auth()->id(),
                    'contract_id' => $contract->id,
                    'type' => 'expense',
                    'amount' => $contract->cost_price,
                    'payment_method' => 'card', // Cost price is always deducted from card 
                    'description' => 'State service fee deduction for ' . $contract->contract_id,
                ]);
            }

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

            $contract->update([
                'status' => 'approved', 
                'payment_method' => $paymentMethod
            ]);
            
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

        $path = storage_path('app/' . $contract->file_path);
        
        if (!file_exists($path)) {
            $path = storage_path('app/private/' . $contract->file_path);
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
