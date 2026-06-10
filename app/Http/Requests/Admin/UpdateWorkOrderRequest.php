<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Concerns\WorkOrderValidationRules;
use Illuminate\Foundation\Http\FormRequest;

class UpdateWorkOrderRequest extends FormRequest
{
    use WorkOrderValidationRules;

    public function authorize(): bool
    {
        return (bool) $this->user()?->isAdmin();
    }

    public function rules(): array
    {
        return array_merge($this->commonWorkOrderRules(), [
            'description'              => 'nullable|string',
            'status'                   => 'required|string',
            'site_street'              => 'nullable|string|max:255',
            'site_city'                => 'nullable|string|max:100',
            'site_state'               => 'nullable|string|max:50',
            'site_zip'                 => 'nullable|string|max:20',
            'preferred_date'           => 'nullable|date',
            'scheduled_date'           => 'nullable|date',
            'scheduled_time'           => 'nullable|date_format:H:i',
            'update_customer_defaults' => 'nullable|boolean',
        ]);
    }
}
