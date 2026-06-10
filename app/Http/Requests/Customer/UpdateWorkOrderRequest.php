<?php

namespace App\Http\Requests\Customer;

use App\Http\Requests\Concerns\WorkOrderValidationRules;
use App\Models\WorkOrder;
use Illuminate\Foundation\Http\FormRequest;

class UpdateWorkOrderRequest extends FormRequest
{
    use WorkOrderValidationRules;

    /**
     * Only the owning customer may edit, and only while the order is still
     * New or Triaged. Returning false yields the same 403 the controller used.
     */
    public function authorize(): bool
    {
        $workOrder = $this->route('workOrder');

        return $workOrder instanceof WorkOrder
            && $workOrder->customer_id === $this->user()?->id
            && in_array($workOrder->status, [WorkOrder::STATUS_NEW, WorkOrder::STATUS_TRIAGED]);
    }

    public function rules(): array
    {
        return array_merge($this->commonWorkOrderRules(), [
            'description'              => 'required|string|min:10',
            'site_street'              => 'nullable|string|max:255',
            'preferred_date'           => 'nullable|date|after_or_equal:today',
            'update_customer_defaults' => 'nullable|boolean',
        ]);
    }
}
