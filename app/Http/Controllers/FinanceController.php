<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;

class FinanceController extends Controller
{
    public function index()
    {
        if (auth()->user()->role !== 'admin') abort(403);
        $transactions = Transaction::with('user', 'contract')->orderBy('created_at', 'desc')->paginate(50);
        $totalIncome = Transaction::where('type', 'income')->sum('amount');
        $totalExpense = Transaction::where('type', 'expense')->sum('amount');
        return view('dashboards.admin_finance', compact('transactions', 'totalIncome', 'totalExpense'));
    }

    public function update(Request $request, Transaction $transaction)
    {
        if (auth()->user()->role !== 'admin') abort(403);

        $request->validate([
            'amount' => 'required|numeric',
            'type' => 'required|in:income,expense',
            'description' => 'required|string'
        ]);

        $transaction->amount = $request->amount;
        $transaction->type = $request->type;
        $transaction->description = $request->description;
        $transaction->save();

        \App\Models\AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'ADMIN OVERRIDE: Updated Transaction #' . $transaction->id,
            'new_values' => ['type' => $transaction->type, 'amount' => $transaction->amount],
            'ip_address' => request()->ip()
        ]);

        return redirect()->back()->with('success', 'Ledger Entry Updated Successfully.');
    }

    public function destroy(Transaction $transaction)
    {
        if (auth()->user()->role !== 'admin') abort(403);

        \App\Models\AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'ADMIN OVERRIDE: Deleted Transaction #' . $transaction->id,
            'new_values' => ['amount' => $transaction->amount],
            'ip_address' => request()->ip()
        ]);

        $transaction->delete();

        return redirect()->back()->with('success', 'Ledger Entry Erased.');
    }
}
