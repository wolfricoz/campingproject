<?php

namespace App\Models;

use Database\Factories\NewsFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    /** @use HasFactory<NewsFactory> */
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
}
