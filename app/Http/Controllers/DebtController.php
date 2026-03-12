<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Debt;
use App\Models\DebtInstallment;
use Illuminate\Http\Request;

class DebtController extends Controller
{
    public function store(Request $request)
    {
        if (auth()->user()->role === 'operator') abort(403);

        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'total_amount' => 'required|numeric|min:1',
            'deadline' => 'nullable|date',
            'type' => 'required|in:one-time,installment',
            'description' => 'nullable|string',
            'installment_count' => 'required_if:type,installment|nullable|integer|min:1',
        ]);

        $debt = Debt::create([
            'client_id' => $validated['client_id'],
            'created_by' => auth()->id(),
            'total_amount' => $validated['total_amount'],
            'remaining_amount' => $validated['total_amount'],
            'deadline' => $validated['deadline'],
            'type' => $validated['type'],
            'description' => $validated['description'],
            'status' => 'pending',
        ]);

        if ($validated['type'] === 'installment') {
            $amountPerInstallment = $validated['total_amount'] / $validated['installment_count'];
            for ($i = 1; $i <= $validated['installment_count']; $i++) {
                DebtInstallment::create([
                    'debt_id' => $debt->id,
                    'amount' => $amountPerInstallment,
                    'due_date' => now()->addMonths($i)->startOfMonth()->addDays(9), // Example: due on 10th of each month
                    'status' => 'pending',
                ]);
            }
        }

        // Update client debt amount
        $client = $debt->client;
        $client->increment('debt_amount', $validated['total_amount']);

        return redirect()->back()->with('success', 'Qarz muvaffaqiyatli rasmiylashtirildi.');
    }

    public function payInstallment(Request $request, DebtInstallment $installment)
    {
        if (auth()->user()->role === 'operator') abort(403);
        if ($installment->status === 'paid') return redirect()->back()->with('error', 'Bu to\'lov allaqachon to\'langan.');

        $installment->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        $debt = $installment->debt;
        $debt->decrement('remaining_amount', $installment->amount);

        if ($debt->remaining_amount <= 0) {
            $debt->update(['status' => 'paid']);
        } else {
            $debt->update(['status' => 'partially_paid']);
        }

        // Update client debt balance
        $client = $debt->client;
        $client->decrement('debt_amount', $installment->amount);

        return redirect()->back()->with('success', 'To\'lov qabul qilindi.');
    }

    public function payOneTime(Request $request, Debt $debt)
    {
        if (auth()->user()->role === 'operator') abort(403);
        
        $amount = $request->validate(['amount' => 'required|numeric|min:1'])['amount'];
        
        if ($amount > $debt->remaining_amount) {
            $amount = $debt->remaining_amount;
        }

        $debt->decrement('remaining_amount', $amount);
        
        if ($debt->remaining_amount <= 0) {
            $debt->update(['status' => 'paid']);
        } else {
            $debt->update(['status' => 'partially_paid']);
        }

        $client = $debt->client;
        $client->decrement('debt_amount', $amount);

        return redirect()->back()->with('success', 'Qarz to\'lovi qabul qilindi.');
    }
    public function showSchedule(Debt $debt)
    {
        $debt->load(['client', 'installments']);
        return view('dashboards.debt_schedule', compact('debt'));
    }

    public function printSchedule(Debt $debt)
    {
        $debt->load(['client', 'installments']);
        return view('dashboards.print_schedule', compact('debt'));
    }
}
