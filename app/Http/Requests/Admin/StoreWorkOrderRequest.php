<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Concerns\WorkOrderValidationRules;
use Illuminate\Foundation\Http\FormRequest;

class StoreWorkOrderRequest extends FormRequest
{
    use WorkOrderValidationRules;

    public function authorize(): bool
    {
        return (bool) $this->user()?->isAdmin();
    }

    public function rules(): array
    {
        return array_merge($this->commonWorkOrderRules(), [
            'customer_id'    => 'required|exists:users,id',
            'description'    => 'nullable|string',
            'site_street'    => 'nullable|string|max:255',
            'preferred_date' => 'nullable|date',
            'scheduled_date' => 'nullable|date',
            'scheduled_time' => 'nullable|date_format:H:i',
        ]);
    }
}
