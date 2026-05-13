<?php

namespace App\Http\Controllers\Academy;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'group_id' => 'required|exists:groups,id',
            'day_of_week' => 'required|integer|min:1|max:7',
            'start_time' => 'required',
        ]);

        \App\Models\Schedule::create([
            'company_id' => auth()->user()->company_id,
            'group_id' => $request->group_id,
            'day_of_week' => $request->day_of_week,
            'start_time' => $request->start_time,
            'end_time' => date('H:i', strtotime($request->start_time . ' + 2 hours')),
        ]);

        return redirect()->back()->with('success', 'Dars jadvali saqlandi!');
    }
}
