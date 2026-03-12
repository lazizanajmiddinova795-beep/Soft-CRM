<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Contract;

class FccController extends Controller
{
    public function index()
    {
        if (auth()->user()->role !== 'admin') abort(403);
        $contracts = Contract::with('user', 'service')->orderBy('created_at', 'desc')->paginate(20);
        return view('dashboards.admin_fcc', compact('contracts'));
    }

    public function update(Request $request, Contract $contract)
    {
        if (auth()->user()->role !== 'admin') abort(403);

        $request->validate([
            'client_name' => 'required|string',
            'amount' => 'required|numeric',
            'status' => 'required|in:pending,approved,rejected'
        ]);

        $contract->client_name = $request->client_name;
        $contract->amount = $request->amount;
        $contract->status = $request->status;
        $contract->save();

        \App\Models\AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'ADMIN OVERRIDE: Updated Contract ' . $contract->contract_id,
            'new_values' => ['status' => $contract->status, 'amount' => $contract->amount],
            'ip_address' => request()->ip()
        ]);

        return redirect()->back()->with('success', 'Contract Updated Successfully.');
    }

    public function destroy(Contract $contract)
    {
        if (auth()->user()->role !== 'admin') abort(403);
        
        \App\Models\AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'ADMIN OVERRIDE: Deleted Contract ' . $contract->contract_id,
            'new_values' => ['client' => $contract->client_name],
            'ip_address' => request()->ip()
        ]);

        \App\Models\Transaction::where('contract_id', $contract->id)->delete();
        $contract->delete();

        return redirect()->back()->with('success', 'Contract Terminated Permanently.');
    }
}
