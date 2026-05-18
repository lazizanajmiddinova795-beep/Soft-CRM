<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function storeManual(Request $request)
    {
        $request->validate([
            'type' => 'required|in:income,expense,tech_expense,salary_payout,commission_payout,staff_loan,transport_expense,cash_to_card,income_card',
            'payment_method' => 'nullable|in:cash,card',
            'amount' => 'required|numeric|min:1',
            'description' => 'required|string|max:255',
            'staff_id' => 'nullable|exists:users,id'
        ]);

        if ($request->type === 'cash_to_card') {
            // Expense from cash
            Transaction::create([
                'user_id' => auth()->id(),
                'type' => 'expense',
                'amount' => $request->amount,
                'payment_method' => 'cash',
                'description' => 'KASSADAN KARTAGA: ' . $request->description,
            ]);
            // Income to card
            Transaction::create([
                'user_id' => auth()->id(),
                'type' => 'income',
                'amount' => $request->amount,
                'payment_method' => 'card',
                'description' => 'KASSADAN KARTAGA: ' . $request->description,
            ]);

            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'manual_transaction',
                'new_values' => [
                    'type' => 'cash_to_card',
                    'amount' => $request->amount,
                    'staff_id' => $request->staff_id,
                    'desc' => $request->description
                ],
            ]);

            return redirect()->back()->with('success', 'Amallar muvaffaqiyatli bajarildi. Tizim orqali tranzaksiya qayd etildi.');
        }

        $dbType = in_array($request->type, ['income', 'income_card']) ? 'income' : 'expense';
        
        $paymentMethod = $request->payment_method ?? 'cash';
        if ($request->type === 'income_card') {
            $paymentMethod = 'card';
        }

        $finalDescription = strtoupper($request->type) . ': ' . $request->description;

        
        // Handle User specific payouts/loans
        if (in_array($request->type, ['salary_payout', 'commission_payout', 'staff_loan']) && $request->staff_id) {
            $user = User::find($request->staff_id);
            if ($user) {
                if ($request->type === 'salary_payout') {
                    $user->salary -= $request->amount;
                    $finalDescription = "PAYROLL (SALARY) TO {$user->name}: " . $request->description;
                } elseif (in_array($request->type, ['commission_payout', 'staff_loan'])) {
                    // deduct from balance. if balance is 0 or less, it naturally becomes negative (debt).
                    $user->balance -= $request->amount;
                    $finalDescription = "PAYOUT/AVANS TO {$user->name}: " . $request->description;
                }
                $user->save();
            }
        }

        Transaction::create([
            'user_id' => auth()->id(),
            'type' => $dbType,
            'amount' => $request->amount,
            'payment_method' => $paymentMethod,

            'description' => $finalDescription,
        ]);

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'manual_transaction',
            'new_values' => [
                'type' => $request->type,
                'amount' => $request->amount,
                'staff_id' => $request->staff_id,
                'desc' => $request->description
            ],
        ]);

        return redirect()->back()->with('success', 'Amallar muvaffaqiyatli bajarildi. Tizim orqali tranzaksiya qayd etildi.');
    }
}
