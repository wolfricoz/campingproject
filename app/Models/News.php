<?php

namespace App\Models;

use Database\Factories\NewsFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class News extends Model
{
    /** @use HasFactory<NewsFactory> */
    use HasFactory, HasUuids;

    protected $guarded = [];

    /**
     * @var list<string>
     */
    protected $appends = ['image_url'];

    /**
     * @return list<string>
     */
    public function uniqueIds(): array
    {
        return ['guid'];
    }

    #[Scope]
    public function published(Builder $query): Builder
    {
        return $query->where('status', 1)->where('published', true);
    }

    protected function imageUrl(): Attribute
    {
        return Attribute::get(function (): ?string {
            if (blank($this->image)) {
                return null;
            }

            if ($this->image === 'images/header.jpg') {
                return $this->image;
            }

            return Storage::disk('public')->url($this->image);
        });
    }
}
