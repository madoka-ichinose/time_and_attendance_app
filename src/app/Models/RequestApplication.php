<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestApplication extends Model
{
    use HasFactory;

    protected $fillable = ['attendance_id', 'user_id', 'status', 'reason', 'applied_at','work_date',
    'clock_in',
    'clock_out',];

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function breakTimes()
    {
        return $this->hasMany(RequestBreakTime::class);
    }

    public function requestBreakTimes()
    {
        return $this->hasMany(RequestBreakTime::class);
    }

    public function breaks()
    {
        return $this->hasMany(RequestBreakTime::class, 'request_application_id');
    }

}
