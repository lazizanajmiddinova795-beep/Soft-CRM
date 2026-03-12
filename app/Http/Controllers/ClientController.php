<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\Contract;

class ClientController extends Controller
{
    public function index()
    {
        $clients = Client::withCount('contracts')
            ->with(['debts' => function($q) {
                $q->where('type', 'installment')->where('status', '!=', 'paid');
            }])
            ->orderBy('name')
            ->paginate(20);
        return view('dashboards.admin_clients', compact('clients'));
    }

    public function search(Request $request)
    {
        $query = $request->get('q');
        
        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $clients = Client::where('name', 'LIKE', "%{$query}%")
            ->orWhere('phone', 'LIKE', "%{$query}%")
            ->limit(10)
            ->get();

        return response()->json($clients);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
        ]);

        $client = Client::create($validated);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'client' => $client]);
        }

        return redirect()->back()->with('success', 'Mijoz muvaffaqiyatli qo\'shildi.');
    }

    public function update(Request $request, Client $client)
    {
        if (auth()->user()->role === 'operator') abort(403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        $client->update($validated);

        return redirect()->back()->with('success', 'Mijoz ma\'lumotlari yangilandi.');
    }

    public function destroy(Client $client)
    {
        if (auth()->user()->role !== 'admin') abort(403);

        $client->delete();

        return redirect()->back()->with('success', 'Mijoz tizimdan o\'chirildi.');
    }
}
