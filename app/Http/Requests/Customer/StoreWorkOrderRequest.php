<?php

namespace App\Http\Requests\Customer;

use App\Http\Requests\Concerns\WorkOrderValidationRules;
use Illuminate\Foundation\Http\FormRequest;

class StoreWorkOrderRequest extends FormRequest
{
    use WorkOrderValidationRules;

    public function authorize(): bool
    {
        return (bool) $this->user()?->isCustomer();
    }

    public function rules(): array
    {
        return array_merge($this->commonWorkOrderRules(), [
            'description'              => 'required|string|min:10',
            'site_street'              => 'nullable|string|max:255',
            'preferred_date'           => 'nullable|date|after_or_equal:today',
            'save_phone_as_default'    => 'nullable|boolean',
            'update_customer_defaults' => 'nullable|boolean',
            'photos'                   => 'nullable|array|max:3',
            'photos.*'                 => 'file|mimes:jpg,jpeg,png,gif,webp|max:10240',
            'documents'                => 'nullable|array|max:3',
            'documents.*'              => 'file|mimes:pdf,doc,docx,xls,xlsx,txt|max:20480',
        ]);
    }
}
