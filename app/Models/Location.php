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
     * @return list<string>
     */
    public function uniqueIds(): array
    {
        return ['guid'];
    }

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
