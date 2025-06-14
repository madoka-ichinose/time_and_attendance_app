<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    public function user()
    {
    return $this->belongsTo(User::class);
    }
    public function breaks()
    {
    return $this->hasMany(BreakTime::class);
    }

    protected $fillable = [
        'user_id', 'work_date', 'clock_in', 'clock_out', 'note', 'total_work_minutes'
    ];

}
