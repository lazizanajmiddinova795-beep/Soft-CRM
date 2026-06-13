<?php

namespace App\Http\Controllers\Academy;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Group;
use App\Models\Student;
use App\Models\TelegramBot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AttendanceController extends Controller
{
    public function getStudents(Request $request, Group $group)
    {
        $students = $group->students; 
        
        foreach ($students as $student) {
            // Weekly stats (last 7 days)
            $weeklyAttendance = \App\Models\Attendance::where('student_id', $student->id)
                ->where('group_id', $group->id)
                ->where('date', '>=', now()->subDays(7)->toDateString())
                ->get();
            $student->weekly_present = $weeklyAttendance->where('status', 'present')->count();
            $student->weekly_absent = $weeklyAttendance->where('status', 'absent')->count();
            $student->weekly_late = $weeklyAttendance->where('status', 'late')->count();

            // Monthly stats (last 30 days)
            $monthlyAttendance = \App\Models\Attendance::where('student_id', $student->id)
                ->where('group_id', $group->id)
                ->where('date', '>=', now()->subDays(30)->toDateString())
                ->get();
            $student->monthly_present = $monthlyAttendance->where('status', 'present')->count();
            $student->monthly_absent = $monthlyAttendance->where('status', 'absent')->count();
            $student->monthly_late = $monthlyAttendance->where('status', 'late')->count();

            // Monthly average grade (last 30 days)
            $avgGrade = \App\Models\Grade::where('student_id', $student->id)
                ->where('group_id', $group->id)
                ->where('date', '>=', now()->subDays(30)->toDateString())
                ->avg('grade');
            $student->monthly_avg_grade = $avgGrade ? round($avgGrade, 1) : '-';
        }
        
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'group' => $group->name,
                'students' => $students
            ]);
        }
        
        return view('academy.attendance.index', compact('group', 'students'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'group_id' => 'required|exists:groups,id',
            'attendances' => 'required|array',
            'attendances.*.student_id' => 'required|exists:students,id',
            'attendances.*.status' => 'required|in:present,absent,late',
            'attendances.*.late_minutes' => 'nullable|integer',
            'attendances.*.grade' => 'nullable|integer|min:0|max:100',
        ]);

        $groupId = $request->group_id;
        $group = Group::with(['telegramBot', 'teacher'])->find($groupId);
        $date = now()->format('Y-m-d');
        $time = now()->format('H:i:s');
        $now = now();

        // Check teacher lateness
        $schedule = \App\Models\Schedule::where('group_id', $groupId)
            ->where('day_of_week', $now->dayOfWeekIso)
            ->first();

        if ($schedule) {
            $startTime = \Carbon\Carbon::createFromFormat('H:i:s', $schedule->start_time)->setDate($now->year, $now->month, $now->day);
            if ($now->greaterThan($startTime->addMinutes(10))) { // 10 minute buffer
                $diff = $now->diffInMinutes($startTime);
                $this->notifyTeacherLateness($group, $diff);
            }
        }

        foreach ($request->attendances as $attData) {
            $attendance = Attendance::updateOrCreate(
                [
                    'group_id' => $groupId,
                    'student_id' => $attData['student_id'],
                    'date' => $date,
                ],
                [
                    'company_id' => $group->company_id ?? auth()->user()->company_id,
                    'time' => $time,
                    'status' => $attData['status'],
                    'late_minutes' => $attData['late_minutes'] ?? 0,
                ]
            );

            // Save grade if provided
            if (isset($attData['grade']) && $attData['grade'] !== null && $attData['grade'] !== '') {
                \App\Models\Grade::updateOrCreate(
                    [
                        'group_id' => $groupId,
                        'student_id' => $attData['student_id'],
                        'date' => $date,
                    ],
                    [
                        'company_id' => $group->company_id ?? auth()->user()->company_id,
                        'teacher_id' => $group->teacher_id ?? auth()->id(),
                        'grade' => $attData['grade'],
                    ]
                );
            }

            // Notify via Telegram for all statuses
            $this->sendTelegramNotification($group, $attData, $attendance);
        }

        return response()->json(['success' => true, 'message' => 'Davomat saqlandi va xabarnomalar yuborildi!']);
    }

    private function sendTelegramNotification($group, $attData, $attendance)
    {
        if (!$group->telegramBot) return;

        $student = Student::find($attData['student_id']);
        if (!$student) return;

        if ($attData['status'] === 'absent') {
            $statusEmoji = '❌';
            $statusText = 'KELMADI (SABABSIZ)';
        } elseif ($attData['status'] === 'late') {
            $statusEmoji = '⏳';
            $statusText = "KECHIKDI ({$attData['late_minutes']} daqiqa)";
        } else {
            $statusEmoji = '✅';
            $statusText = 'KELDI (DARSDA)';
        }

        $message = "🔔 *DAVOMAT XABARNOMASI*\n\n";
        $message .= "🎓 *Guruh:* {$group->name}\n";
        $message .= "👤 *O'quvchi:* {$student->name}\n";
        $message .= "📊 *Holat:* {$statusEmoji} {$statusText}\n";
        $message .= "📅 *Sana:* " . now()->format('d.m.Y') . "\n";
        $message .= "🕒 *Vaqt:* " . now()->format('H:i') . "\n\n";
        $message .= "⚠️ _Ota-onalarga ogohlantirish yuborildi (Tizimda qayd etildi)._";

        $botToken = $group->telegramBot->bot_token;
        $chatId = $group->telegramBot->chat_id;

        try {
            Http::post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'Markdown'
            ]);
        } catch (\Exception $e) {
            // Log error if needed
        }
    }

    private function notifyTeacherLateness($group, $diff)
    {
        if (!$group->telegramBot) return;

        $teacher = $group->teacher;
        if (!$teacher) return;

        $fineAmount = $diff * 1000; // Example: 1000 UZS per minute late

        $message = "⚠️ *DIQQAT: O'QITUVCHI KECHIKDI*\n\n";
        $message .= "👨‍🏫 *O'qituvchi:* {$teacher->name}\n";
        $message .= "🎓 *Guruh:* {$group->name}\n";
        $message .= "⏰ *Kechikish:* {$diff} daqiqa\n";
        $message .= "💸 *Taxminiy Jarima:* " . number_format($fineAmount, 0, ',', ' ') . " UZS\n\n";
        $message .= "📢 _Adminlar guruhida bu holat nazoratga olindi._";

        $botToken = $group->telegramBot->bot_token;
        $chatId = $group->telegramBot->chat_id;

        try {
            Http::post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'Markdown'
            ]);
        } catch (\Exception $e) {
            // Log error if needed
        }
    }
}
