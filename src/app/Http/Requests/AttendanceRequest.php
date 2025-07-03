<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'clock_in' => 'nullable|date_format:H:i',
            'clock_out' => 'nullable|date_format:H:i|after:clock_in',
            'note' => 'required|string',
            'breaks.*.start_time' => 'nullable|date_format:H:i',
            'breaks.*.end_time' => 'nullable|date_format:H:i',
        ];
    }

    public function messages(): array
    {
        return [
            'clock_out.after' => '出勤時間もしくは退勤時間が不適切な値です',
            'note.required' => '備考を記入してください',
            'breaks.*.start_time.date_format' => '休憩時間の形式が不正です',
            'breaks.*.end_time.date_format' => '休憩時間の形式が不正です',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $in = $this->input('clock_in');
            $out = $this->input('clock_out');

            if ($in && $out) {
                $inTime = strtotime($in);
                $outTime = strtotime($out);

                if ($inTime >= $outTime) {
                    $validator->errors()->add('clock_in', '出勤時間もしくは退勤時間が不適切な値です');
                }

                foreach ($this->input('breaks', []) as $id => $break) {
                    $start = isset($break['start_time']) ? strtotime($break['start_time']) : null;
                    $end = isset($break['end_time']) ? strtotime($break['end_time']) : null;

                    if (($start && ($start < $inTime || $start > $outTime)) ||
                        ($end && ($end < $inTime || $end > $outTime))) {
                        $validator->errors()->add("breaks.$id.start_time", '休憩時間が勤務時間外です');
                    }
                }
            }
        });
    }
}
