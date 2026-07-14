<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Thread extends Model
{
    protected $fillable = [
        'board_id',
        'title',
        'reply_count',
        'is_locked',
        'last_posted_at',
    ];

    protected $casts = [
        'is_locked' => 'boolean',
        'last_posted_at' => 'datetime',
    ];

    public function board(): BelongsTo
    {
        return $this->belongsTo(Board::class);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(ThreadPost::class)->orderBy('number');
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }

    public function opPost(): HasOne
    {
        return $this->hasOne(ThreadPost::class)->where('number', 1);
    }
}
