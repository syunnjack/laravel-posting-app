<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Report extends Model
{
    protected $fillable = [
        'channel',
        'thread_id',
        'thread_post_id',
        'reporter_email',
        'reason',
    ];

    protected $hidden = [
        'ip_hash',
    ];

    public function thread(): BelongsTo
    {
        return $this->belongsTo(Thread::class);
    }

    public function threadPost(): BelongsTo
    {
        return $this->belongsTo(ThreadPost::class);
    }
}
