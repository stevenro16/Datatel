<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected static function booted(): void
    {
        static::creating(function (WorkOrder $wo) {
            $wo->wo_number = (static::withTrashed()->max('wo_number') ?? 9999) + 1;
        });
    }

    public function woLabel(): string
    {
        return 'WO-' . ($this->wo_number ?? '?');
    }

    const STATUS_NEW                = 'new';
    const STATUS_TRIAGED            = 'triaged';
    const STATUS_SCHEDULED          = 'scheduled';
    const STATUS_AWAITING_FEEDBACK  = 'awaiting_feedback';
    const STATUS_SERVICES_PERFORMED = 'services_performed';
    const STATUS_INVOICE_PREPARED   = 'invoice_prepared';
    const STATUS_BILLED             = 'billed';
    const STATUS_COMPLETED          = 'completed';
    const STATUS_CANCELED           = 'canceled';

    const CONFIRMATION_PENDING   = 'pending';
    const CONFIRMATION_CONFIRMED = 'confirmed';
    const CONFIRMATION_DECLINED  = 'declined';

    const URGENCY_ROUTINE   = 'routine';
    const URGENCY_URGENT    = 'urgent';
    const URGENCY_EMERGENCY = 'emergency';

    /**
     * Canonical (admin-facing) status labels. Customer-facing views may relabel
     * (e.g. "Submitted" for new), but this is the single source of truth for the
     * default display label and for any non-customer surface.
     */
    const STATUS_LABELS = [
        self::STATUS_NEW                => 'New',
        self::STATUS_TRIAGED            => 'Triaged',
        self::STATUS_SCHEDULED          => 'Scheduled',
        self::STATUS_AWAITING_FEEDBACK  => 'Awaiting Customer Feedback',
        self::STATUS_SERVICES_PERFORMED => 'Services Performed',
        self::STATUS_INVOICE_PREPARED   => 'Invoice Prepared',
        self::STATUS_BILLED             => 'Billed',
        self::STATUS_COMPLETED          => 'Completed',
        self::STATUS_CANCELED           => 'Canceled',
    ];

    const URGENCY_LABELS = [
        self::URGENCY_ROUTINE   => 'Routine',
        self::URGENCY_URGENT    => 'Urgent',
        self::URGENCY_EMERGENCY => 'Emergency',
    ];

    /**
     * Urgency pill colors [bg, text] — single source of truth so the admin,
     * customer, and employee portals can never drift apart.
     */
    const URGENCY_COLORS = [
        self::URGENCY_EMERGENCY => ['bg' => '#fee2e2', 'text' => '#991b1b'],
        self::URGENCY_URGENT    => ['bg' => '#fef3c7', 'text' => '#92400e'],
        self::URGENCY_ROUTINE   => ['bg' => '#f3f4f6', 'text' => '#374151'],
    ];

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? ucfirst(str_replace('_', ' ', (string) $this->status));
    }

    public function urgencyLabel(): string
    {
        return self::URGENCY_LABELS[$this->urgency] ?? ucfirst((string) $this->urgency);
    }

    /**
     * @return array{bg: string, text: string}
     */
    public function urgencyColors(): array
    {
        return self::URGENCY_COLORS[$this->urgency] ?? ['bg' => '#f3f4f6', 'text' => '#374151'];
    }

    /** Statuses for an order that has not yet been scheduled. */
    const PRE_SCHEDULED_STATUSES = [self::STATUS_NEW, self::STATUS_TRIAGED];

    /**
     * When work is completed on an order that was never scheduled (still New or
     * Triaged), back-fill a Scheduled step so the lifecycle and audit trail stay
     * continuous instead of jumping straight to Services Performed.
     *
     * Returns true if a Scheduled transition was written.
     */
    public function backfillScheduledStep(?int $changedBy): bool
    {
        if (!in_array($this->status, self::PRE_SCHEDULED_STATUSES)) {
            return false;
        }

        $old = $this->status;
        $this->update(['status' => self::STATUS_SCHEDULED]);

        WorkOrderHistory::create([
            'work_order_id' => $this->id,
            'changed_by'    => $changedBy,
            'field_name'    => 'status',
            'old_value'     => $old,
            'new_value'     => self::STATUS_SCHEDULED,
            'comment'       => 'Auto-advanced to Scheduled when the work was marked complete without a prior scheduled visit.',
            'changed_at'    => now(),
        ]);

        return true;
    }

    protected $fillable = [
        'customer_id', 'company_id', 'status', 'confirmation_status', 'urgency', 'building_type',
        'site_address_id', 'site_address', 'site_street', 'site_city', 'site_state', 'site_zip',
        'site_contact_name', 'site_contact_phone',
        'description', 'equipment_details', 'tech_questions', 'num_drops', 'circuit_ref',
        'preferred_date', 'availability_from', 'availability_to', 'preferred_availability',
        'scheduled_at', 'duration_estimate_minutes',
        'travel_time_cache',
        'cancel_reason', 'canceled_by', 'created_by',
        'needs_invoice',
    ];

    protected function casts(): array
    {
        return [
            'preferred_date'       => 'date',
            'scheduled_at'         => 'datetime',
            'num_drops'            => 'integer',
            'travel_time_cache'    => 'array',
            'preferred_availability' => 'array',
            'needs_invoice'          => 'boolean',
        ];
    }

    public function customer()    { return $this->belongsTo(User::class, 'customer_id'); }
    public function company()     { return $this->belongsTo(Company::class); }
    public function siteAddress() { return $this->belongsTo(CustomerAddress::class, 'site_address_id'); }

    public function serviceTypes()
    {
        return $this->belongsToMany(ServiceType::class, 'work_order_services');
    }

    public function assignedEmployees()
    {
        return $this->belongsToMany(User::class, 'work_order_assignments')
            ->withPivot('assigned_by')
            ->withTimestamps();
    }

    public function assignments()        { return $this->hasMany(WorkOrderAssignment::class); }
    public function notes()              { return $this->hasMany(WorkOrderNote::class); }
    public function attachments()        { return $this->hasMany(WorkOrderAttachment::class); }
    public function history()            { return $this->hasMany(WorkOrderHistory::class); }
    public function invoice()            { return $this->hasOne(Invoice::class); }
    public function invoices()           { return $this->hasMany(Invoice::class); }
    public function completionSignature(){ return $this->hasOne(WorkOrderSignature::class); }
    public function visits()             { return $this->hasMany(WorkOrderVisit::class)->orderBy('scheduled_at'); }

    public function syncConfirmationStatus(): void
    {
        $next = $this->visits()->where('scheduled_at', '>=', now())->orderBy('scheduled_at')->first();
        \Illuminate\Support\Facades\DB::table('work_orders')
            ->where('id', $this->id)
            ->update(['confirmation_status' => $next?->confirmation_status]);
    }

    public function syncScheduledAt(): void
    {
        $next = $this->visits()->where('scheduled_at', '>=', now())->orderBy('scheduled_at')->first();
        $ref  = $next ?? $this->visits()->orderByDesc('scheduled_at')->first();

        \Illuminate\Support\Facades\DB::table('work_orders')
            ->where('id', $this->id)
            ->update(['scheduled_at' => $ref?->scheduled_at]);
    }

}
