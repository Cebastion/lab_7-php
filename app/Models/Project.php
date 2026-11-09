<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'user_id',
    ];

    /** Творець проєкту */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Задачі проєкту */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    /** Файли проєкту */
    public function files(): MorphMany
    {
        return $this->morphMany(File::class, 'fileable');
    }
}
