<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BreakTime extends Model
{
    use HasFactory;

    protected $table = 'breaks';

    protected $fillable = [
        'attendance_id', 'start_time', 'end_time', 'reason'
    ];
    
    public function attendance()
    {
    return $this->belongsTo(Attendance::class);
    }

}
