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
            'type' => 'required|in:income,expense,tech_expense,salary_payout,commission_payout,staff_loan',
            'amount' => 'required|numeric|min:1',
            'description' => 'required|string|max:255',
            'staff_id' => 'nullable|exists:users,id'
        ]);

        $dbType = $request->type === 'income' ? 'income' : 'expense';
        $finalDescription = strtoupper($request->type) . ': ' . $request->description;
        
        // Handle User specific payouts/loans
        if (in_array($request->type, ['salary_payout', 'commission_payout', 'staff_loan']) && $request->staff_id) {
            $user = User::find($request->staff_id);
            if ($user) {
                if ($request->type === 'salary_payout') {
                    $user->salary -= $request->amount;
                    $finalDescription = "PAYROLL (SALARY) TO {$user->name}: " . $request->description;
                } elseif ($request->type === 'commission_payout') {
                    $user->balance -= $request->amount;
                    $finalDescription = "PAYROLL (COMMISSION) TO {$user->name}: " . $request->description;
                } elseif ($request->type === 'staff_loan') {
                    $user->debt += $request->amount;
                    $finalDescription = "LOAN (QARZ) TO {$user->name}: " . $request->description;
                }
                $user->save();
            }
        }

        Transaction::create([
            'user_id' => auth()->id(),
            'type' => $dbType,
            'amount' => $request->amount,
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
