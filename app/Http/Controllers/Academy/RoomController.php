<?php

namespace App\Http\Controllers\Academy;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Room;
use App\Models\Group;

class RoomController extends Controller
{
    public function index()
    {
        $rooms = Room::forCompany()->with(['groups.schedules'])->get();
        $allGroups = Group::forCompany()->get();
        return view('academy.rooms.index', compact('rooms', 'allGroups'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'capacity' => 'nullable|integer',
        ]);

        Room::create([
            'company_id' => auth()->user()->company_id,
            'name' => $request->name,
            'capacity' => $request->capacity ?? 0,
        ]);

        return redirect()->back()->with('success', 'Xona muvaffaqiyatli qo\'shildi!');
    }

    public function update(Request $request, Room $room)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'capacity' => 'nullable|integer',
        ]);

        $room->update([
            'name' => $request->name,
            'capacity' => $request->capacity ?? 0,
        ]);

        return redirect()->back()->with('success', 'Xona muvaffaqiyatli yangilandi!');
    }

    public function destroy(Room $room)
    {
        $room->delete();
        return redirect()->back()->with('success', 'Xona o\'chirildi.');
    }
}
