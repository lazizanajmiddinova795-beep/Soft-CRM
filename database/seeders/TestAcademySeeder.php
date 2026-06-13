<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Course;
use App\Models\Room;
use App\Models\Group;
use App\Models\Student;
use App\Models\Company;
use Illuminate\Support\Facades\Hash;

class TestAcademySeeder extends Seeder
{
    public function run(): void
    {
        // Fetch or create a company
        $company = Company::first();
        if (!$company) {
            $company = Company::create([
                'name' => 'ITCloud Academy',
                'slug' => 'itcloud-academy',
            ]);
        }
        $companyId = $company->id;

        // 1. Create a Course
        $course = Course::create([
            'company_id' => $companyId,
            'name' => 'PHP Laravel Backend',
            'price' => 1200000,
            'duration' => 6, // 6 months
            'description' => 'PHP Laravel framework course from scratch.',
        ]);

        // 2. Create a Room
        $room = Room::create([
            'company_id' => $companyId,
            'name' => 'Tesla Lab',
            'capacity' => 15,
        ]);

        // 3. Create a Teacher (User with role teacher)
        $teacher = User::create([
            'company_id' => $companyId,
            'name' => 'Rustam Alimov',
            'email' => 'rustam.teacher@itcloud.uz',
            'password' => Hash::make('password123'),
            'role' => 'teacher',
            'status' => 'offline',
            'internal_id' => 'TCH-9999',
            'phone' => '+998909876543',
            'approval_status' => 'approved',
        ]);

        // 4. Create a Group
        $group = Group::create([
            'company_id' => $companyId,
            'course_id' => $course->id,
            'teacher_id' => $teacher->id,
            'room_id' => $room->id,
            'name' => 'PHP-999',
            'status' => 'active',
            'days' => [1, 3, 5], // Du, Ch, Ju
            'start_time' => '15:00:00',
        ]);

        // 5. Create a Student
        $student = Student::create([
            'company_id' => $companyId,
            'name' => 'Kamoliddin Solihov',
            'phone' => '+998901112233',
            'address' => 'Toshkent sh., Chilonzor',
            'status' => 'active',
        ]);

        // Attach student to group
        $group->students()->attach($student->id);
    }
}
