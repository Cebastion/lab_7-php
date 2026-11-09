<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'status',
        'project_id',
        'author_id',
        'assigned_to',
        'due_date',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'datetime',
        ];
    }

    /** Проєкт, до якого належить задача */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /** Автор задачі */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /** Виконавець задачі */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /** Коментарі до задачі */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /** Файли задачі */
    public function files(): MorphMany
    {
        return $this->morphMany(File::class, 'fileable');
    }
}
