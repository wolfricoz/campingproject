<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Arrangement extends Model
{
    use HasFactory, HasUuids;

    protected $guarded = [];

    /**
     * @return list<string>
     */
    public function uniqueIds(): array
    {
        return ['guid'];
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_date' => 'datetime',
            'end_date' => 'datetime',
            'payment_received' => 'boolean',
            'confirmation_email_sent' => 'boolean',
        ];
    }

    /**
     * Searches and sorts the overview the way the front desk asked for.
     *
     * @param  Builder<$this>  $query
     * @param  array{search?: string|null, sort?: string|null, direction?: string|null}  $filters
     * @return Builder<$this>
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        $search = $filters['search'] ?? null;
        $direction = ($filters['direction'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

        // The search sits in its own group, otherwise the or breaks out of the status filter.
        $query->when($search, function (Builder $query) use ($search): void {
            $query->where(function (Builder $query) use ($search): void {
                $query->whereHas('customer', function (Builder $customer) use ($search): void {
                    $customer->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                })->orWhereHas('location', function (Builder $location) use ($search): void {
                    $location->where('name', 'like', "%{$search}%");
                });
            });
        });

        // Only the columns below may be sorted on, anything else falls back to the arrival date.
        return match ($filters['sort'] ?? null) {
            'customer' => $query->orderBy(Customer::select('name')->whereColumn('customers.id', 'arrangements.customer_id'), $direction),
            'location' => $query->orderBy(Location::select('name')->whereColumn('locations.id', 'arrangements.location_id'), $direction),
            'end_date', 'total_price', 'created_at' => $query->orderBy($filters['sort'], $direction),
            default => $query->orderBy('start_date', $direction),
        };
    }

    /**
     * @return BelongsTo<Customer, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * @return BelongsTo<Location, $this>
     */
    public function location(): BelongsTo
    {
        return $this->BelongsTo(Location::class);
    }
}
