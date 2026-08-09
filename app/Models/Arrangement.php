<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Arrangement extends Model
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
