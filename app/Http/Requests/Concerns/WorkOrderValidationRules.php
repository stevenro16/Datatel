<?php

namespace App\Http\Requests\Concerns;

/**
 * Validation rules shared by the customer and admin work-order forms, so the two
 * portals cannot drift apart (e.g. when the urgency enum or service list changes).
 */
trait WorkOrderValidationRules
{
    /**
     * Rules common to every work-order create/update form, regardless of role.
     */
    protected function commonWorkOrderRules(): array
    {
        return [
            'equipment_details'      => 'nullable|string',
            'urgency'                => 'required|in:routine,urgent,emergency',
            'site_contact_name'      => 'nullable|string|max:255',
            'site_contact_phone'     => 'nullable|string|max:30',
            'preferred_availability' => 'nullable|string',
            'service_ids'            => 'nullable|array',
            'service_ids.*'          => 'exists:service_types,id',
        ];
    }
}
