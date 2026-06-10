<?php

namespace Tests\Feature\Authorization;

use App\Models\User;
use App\Models\WorkOrder;
use App\Models\WorkOrderAttachment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * Matrix of who may download/view work-order attachments.
 *
 * Rule: customer-owner, employee assigned to the work order, or admin.
 * Everyone else gets 403 — including employees NOT assigned to the work order.
 */
class AttachmentAccessTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private User $otherCustomer;
    private User $assignedEmployee;
    private User $unassignedEmployee;
    private User $admin;
    private WorkOrder $workOrder;
    private WorkOrderAttachment $attachment;
    private string $filePath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner              = User::factory()->customer()->create();
        $this->otherCustomer      = User::factory()->customer()->create();
        $this->assignedEmployee   = User::factory()->employee()->create();
        $this->unassignedEmployee = User::factory()->employee()->create();
        $this->admin              = User::factory()->admin()->create();

        $this->workOrder = WorkOrder::factory()
            ->for($this->owner, 'customer')
            ->create();

        $this->workOrder->assignments()->create([
            'user_id'     => $this->assignedEmployee->id,
            'assigned_by' => $this->admin->id,
        ]);

        $this->attachment = WorkOrderAttachment::factory()->create([
            'work_order_id' => $this->workOrder->id,
            'uploaded_by'   => $this->owner->id,
        ]);

        $dir = storage_path('app/uploads/work-orders/' . $this->workOrder->id);
        File::ensureDirectoryExists($dir);
        $this->filePath = $dir . '/' . $this->attachment->stored_name;
        File::put($this->filePath, 'fake-image-bytes');
    }

    protected function tearDown(): void
    {
        File::deleteDirectory(storage_path('app/uploads/work-orders/' . $this->workOrder->id));
        parent::tearDown();
    }

    public function test_owner_customer_can_download_attachment(): void
    {
        $this->actingAs($this->owner)
            ->get(route('attachments.download', $this->attachment))
            ->assertOk();
    }

    public function test_owner_customer_can_view_attachment_inline(): void
    {
        $this->actingAs($this->owner)
            ->get(route('attachments.view', $this->attachment))
            ->assertOk();
    }

    public function test_other_customer_cannot_access_attachment(): void
    {
        $this->actingAs($this->otherCustomer)
            ->get(route('attachments.download', $this->attachment))
            ->assertForbidden();

        $this->actingAs($this->otherCustomer)
            ->get(route('attachments.view', $this->attachment))
            ->assertForbidden();
    }

    public function test_assigned_employee_can_access_attachment(): void
    {
        $this->actingAs($this->assignedEmployee)
            ->get(route('attachments.download', $this->attachment))
            ->assertOk();
    }

    public function test_unassigned_employee_cannot_access_attachment(): void
    {
        $this->actingAs($this->unassignedEmployee)
            ->get(route('attachments.download', $this->attachment))
            ->assertForbidden();

        $this->actingAs($this->unassignedEmployee)
            ->get(route('attachments.view', $this->attachment))
            ->assertForbidden();
    }

    public function test_admin_can_access_attachment(): void
    {
        $this->actingAs($this->admin)
            ->get(route('attachments.download', $this->attachment))
            ->assertOk();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('attachments.download', $this->attachment))
            ->assertRedirect(route('login'));
    }
}
