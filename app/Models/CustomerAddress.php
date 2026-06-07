<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerAddress extends Model
{
    protected $fillable = ['user_id', 'company_id', 'label', 'street', 'city', 'state', 'zip', 'county', 'is_default', 'is_active'];

    protected function casts(): array
    {
        return ['is_default' => 'boolean', 'is_active' => 'boolean'];
    }

    public function user()    { return $this->belongsTo(User::class); }
    public function company() { return $this->belongsTo(Company::class); }

    public function formattedAddress(): string
    {
        return "{$this->street}, {$this->city}, {$this->state} {$this->zip}";
    }

    /**
     * Returns the correct query for a user's sites — company-scoped if they
     * belong to an active company, otherwise personal.
     */
    public static function forUser(User $user): \Illuminate\Database\Eloquent\Builder
    {
        $companyId = $user->companyMemberships()
            ->where('status', 'active')
            ->value('company_id');

        if ($companyId) {
            return static::where('company_id', $companyId);
        }

        return static::where('user_id', $user->id)->whereNull('company_id');
    }
}
