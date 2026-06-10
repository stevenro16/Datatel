<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\WorkOrder;
use App\Models\WorkOrderAttachment;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class AttachmentController extends Controller
{
    /**
     * Force-download an attachment.
     */
    public function download(WorkOrderAttachment $attachment): BinaryFileResponse
    {
        $path = $this->authorizedPath($attachment);

        return response()->download($path, $attachment->original_name);
    }

    /**
     * Serve an attachment inline (thumbnails / lightbox).
     */
    public function view(WorkOrderAttachment $attachment): BinaryFileResponse
    {
        $path = $this->authorizedPath($attachment);

        return response()->file($path, ['Content-Type' => $attachment->mime_type]);
    }

    /**
     * Authorize the current user against the attachment's work order and return
     * the on-disk path, or abort. Access is granted to: an admin, the work
     * order's customer, or an employee assigned to the work order (at WO or
     * visit level).
     */
    private function authorizedPath(WorkOrderAttachment $attachment): string
    {
        $workOrder = $attachment->workOrder;
        $user      = auth()->user();

        abort_unless($this->canAccess($user, $workOrder), 403);

        $path = storage_path('app/uploads/work-orders/' . $workOrder->id . '/' . $attachment->stored_name);
        abort_unless(is_file($path), 404);

        return $path;
    }

    private function canAccess(User $user, WorkOrder $workOrder): bool
    {
        if ($user->role === User::ROLE_ADMIN) {
            return true;
        }

        if ($user->role === User::ROLE_CUSTOMER) {
            return $workOrder->customer_id === $user->id;
        }

        if ($user->role === User::ROLE_EMPLOYEE) {
            // Assigned at the work-order level...
            if ($workOrder->assignments()->where('user_id', $user->id)->exists()) {
                return true;
            }

            // ...or assigned to any visit on this work order.
            return $workOrder->visits()
                ->whereHas('techs', fn ($q) => $q->where('user_id', $user->id))
                ->exists();
        }

        return false;
    }
}
