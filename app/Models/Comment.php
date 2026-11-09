<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'user_id',
        'content',
    ];

    /** Задача, до якої належить коментар */
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    /** Автор коментаря */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
