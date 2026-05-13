<?php

namespace App\Http\Controllers\Academy;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Group;
use App\Models\Course;
use App\Models\User;
use App\Models\Room;
use App\Models\Schedule;

class GroupController extends Controller
{
    public function index()
    {
        $groups = Group::forCompany()->with(['course', 'teacher', 'room', 'telegramBot'])->latest()->get();
        $courses = Course::forCompany()->get();
        $teachers = User::forCompany()->where('role', 'teacher')->get();
        $rooms = Room::forCompany()->get();
        $telegramBots = \App\Models\TelegramBot::forCompany()->get();
        
        return view('academy.groups.index', compact('groups', 'courses', 'teachers', 'rooms', 'telegramBots'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'course_id' => 'required|exists:courses,id',
            'teacher_id' => 'required|exists:users,id',
            'room_id' => 'required|exists:rooms,id',
            'start_time' => 'required',
            'days' => 'required|array',
            'telegram_bot_id' => 'nullable|exists:telegram_bots,id',
        ]);

        $group = Group::create([
            'company_id' => auth()->user()->company_id,
            'name' => $request->name,
            'course_id' => $request->course_id,
            'teacher_id' => $request->teacher_id,
            'room_id' => $request->room_id,
            'start_time' => $request->start_time,
            'days' => $request->days,
            'telegram_bot_id' => $request->telegram_bot_id,
            'status' => 'active',
        ]);

        // Create initial schedules based on days and start_time
        foreach($request->days as $day) {
            Schedule::create([
                'company_id' => auth()->user()->company_id,
                'group_id' => $group->id,
                'day_of_week' => $day,
                'start_time' => $request->start_time,
                'end_time' => date('H:i', strtotime($request->start_time . ' + 2 hours')), // Default 2 hours
            ]);
        }

        return redirect()->back()->with('success', 'Guruh yaratildi va xonaga biriktirildi!');
    }

    public function update(Request $request, Group $group)
    {
        $request->validate([
            'name' => 'required|string',
            'course_id' => 'required|exists:courses,id',
            'teacher_id' => 'required|exists:users,id',
            'room_id' => 'required|exists:rooms,id',
            'start_time' => 'required',
            'telegram_bot_id' => 'nullable|exists:telegram_bots,id',
        ]);

        $group->update([
            'name' => $request->name,
            'course_id' => $request->course_id,
            'teacher_id' => $request->teacher_id,
            'room_id' => $request->room_id,
            'start_time' => $request->start_time,
            'telegram_bot_id' => $request->telegram_bot_id,
        ]);

        return redirect()->back()->with('success', 'Guruh ma\'lumotlari yangilandi!');
    }

    public function destroy(Group $group)
    {
        $group->delete();
        return redirect()->back()->with('success', 'Guruh o\'chirildi.');
    }
}
