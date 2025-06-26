<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'clock_in' => 'nullable|date_format:H:i',
            'clock_out' => 'nullable|date_format:H:i|after:clock_in',
            'note' => 'required|string',
            'breaks.*.start' => 'nullable|date_format:H:i',
            'breaks.*.end' => 'nullable|date_format:H:i',
        ];
    }

    public function messages(): array
    {
        return [
            'clock_out.after' => '出勤時間もしくは退勤時間が不適切な値です',
            'note.required' => '備考を記入してください',
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
                $start = isset($break['start']) ? strtotime($break['start']) : null;
                $end = isset($break['end']) ? strtotime($break['end']) : null;

                if (($start && ($start < $inTime || $start > $outTime)) ||
                    ($end && ($end < $inTime || $end > $outTime))) {
                    $validator->errors()->add("breaks.$id.start", '休憩時間が勤務時間外です');
                }
            }
        }
    });
}

}
