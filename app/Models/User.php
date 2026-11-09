<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /** Створені проєкти */
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    /** Створені задачі */
    public function authoredTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'author_id');
    }

    /** Призначені задачі */
    public function assignedTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'assigned_to');
    }

    /** Залишені коментарі */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /** Аватари / файли користувача */
    public function files(): MorphMany
    {
        return $this->morphMany(File::class, 'fileable');
    }
}
