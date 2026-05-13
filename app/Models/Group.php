<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use \App\Traits\BelongsToCompany;

    protected $guarded = [];

    protected $casts = [
        'days' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function telegramBot()
    {
        return $this->belongsTo(TelegramBot::class);
    }

    public function students()
    {
        return $this->belongsToMany(Student::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }
}
