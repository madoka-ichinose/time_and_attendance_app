<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestBreakTime extends Model
{
    use HasFactory;

    protected $fillable = ['request_application_id', 'start_time', 'end_time'];

    public function requestApplication()
    {
        return $this->belongsTo(RequestApplication::class);
    }
}
