<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

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

    public function getFormattedClockInAttribute()
{
    return $this->clock_in ? Carbon::parse($this->clock_in)->format('H:i') : '--:--';
}

public function getFormattedClockOutAttribute()
{
    return $this->clock_out ? Carbon::parse($this->clock_out)->format('H:i') : '--:--';
}

public function getFormattedWorkDateAttribute()
{
    return Carbon::parse($this->work_date)->locale('ja')->isoFormat('MM/DD（dd）');
}

public function getFormattedBreakTimeAttribute()
{
    $total = $this->breaks->sum(function ($break) {
        return $break->start_time && $break->end_time
            ? Carbon::parse($break->end_time)->diffInMinutes($break->start_time)
            : 0;
    });
    return $this->formatMinutes($total);
}

public function getFormattedWorkTimeAttribute()
{
    return $this->total_work_minutes ? $this->formatMinutes($this->total_work_minutes) : '--:--';
}

private function formatMinutes($minutes)
{
    $hours = floor($minutes / 60);
    $mins = $minutes % 60;
    return sprintf('%02d:%02d', $hours, $mins);
}

}
