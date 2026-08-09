<?php

namespace App\Models;

use App\Enums\ArrangementStatus;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{
    use HasFactory, HasUuids;

    protected $guarded = [];

    /**
     * The auto-incrementing id stays the primary key; only `guid` gets a UUID.
     *
     * @return list<string>
     */
    public function uniqueIds(): array
    {
        return ['guid'];
    }

    /**
     * Only the locations that have no overlapping arrangement in the given period.
     * Cancelled and rejected arrangements do not occupy a location, and when an
     * existing arrangement is being edited it should not block itself.
     */
    #[Scope]
    public function available(Builder $query, $start, $end, ?int $ignoreArrangementId = null): Builder
    {
        return $query->whereDoesntHave('arrangements', function (Builder $query) use ($start, $end, $ignoreArrangementId) {
            $query->where('status', 1)
                ->whereNotIn('booking_status', [
                    ArrangementStatus::CANCELLED->value,
                    ArrangementStatus::REJECTED->value,
                ])
                ->where('start_date', '<', $end)
                ->where('end_date', '>', $start)
                ->when($ignoreArrangementId, function (Builder $query) use ($ignoreArrangementId) {
                    return $query->whereKeyNot($ignoreArrangementId);
                });
        });
    }

    /**
     * Whether the location is free for the whole period. Used by both the availability
     * endpoint and the validation in the arrangement/booking store methods.
     */
    public static function isAvailable(int $locationId, string $start, string $end, ?int $ignoreArrangementId = null): bool
    {
        return static::query()
            ->where('status', 1)
            ->whereKey($locationId)
            ->available($start, $end, $ignoreArrangementId)
            ->exists();
    }

    public function arrangements(): HasMany
    {
        return $this->hasMany(Arrangement::class);
    }
}
