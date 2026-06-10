<?php

namespace Tests\Unit;

use App\Models\WorkOrder;
use PHPUnit\Framework\TestCase;

/**
 * The status/urgency label + colour metadata is the single source of truth the
 * three portals (and the x-wo.urgency-badge component) read from. These tests
 * lock the values so a change is deliberate, not accidental drift.
 */
class WorkOrderMetadataTest extends TestCase
{
    public function test_status_label_maps_known_status(): void
    {
        $wo = new WorkOrder(['status' => WorkOrder::STATUS_SERVICES_PERFORMED]);
        $this->assertSame('Services Performed', $wo->statusLabel());
    }

    public function test_status_label_falls_back_for_unknown_status(): void
    {
        $wo = new WorkOrder(['status' => 'some_new_state']);
        $this->assertSame('Some new state', $wo->statusLabel());
    }

    public function test_urgency_label_maps_known_urgency(): void
    {
        $wo = new WorkOrder(['urgency' => WorkOrder::URGENCY_EMERGENCY]);
        $this->assertSame('Emergency', $wo->urgencyLabel());
    }

    public function test_urgency_colors_match_the_canonical_palette(): void
    {
        $emergency = (new WorkOrder(['urgency' => WorkOrder::URGENCY_EMERGENCY]))->urgencyColors();
        $this->assertSame(['bg' => '#fee2e2', 'text' => '#991b1b'], $emergency);

        $routine = (new WorkOrder(['urgency' => WorkOrder::URGENCY_ROUTINE]))->urgencyColors();
        $this->assertSame(['bg' => '#f3f4f6', 'text' => '#374151'], $routine);
    }

    public function test_urgency_colors_fall_back_safely(): void
    {
        $wo = new WorkOrder(['urgency' => 'unknown']);
        $this->assertSame(['bg' => '#f3f4f6', 'text' => '#374151'], $wo->urgencyColors());
    }
}
