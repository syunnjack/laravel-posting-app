<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ThreadPost extends Model
{
    protected $fillable = [
        'name',
        'trip',
        'body',
        'number',
    ];

    // 生IPと一方向ハッシュは公開レスポンスに絶対に含めない
    // (発信者情報開示請求対応のため保持するが、通常のシリアライズでは隠す)
    protected $hidden = [
        'ip_address',
        'ip_hash',
    ];

    public function thread(): BelongsTo
    {
        return $this->belongsTo(Thread::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }

    public function displayName(): string
    {
        return $this->name ?: '名無しさん';
    }

    public function displayNameWithTrip(): string
    {
        return $this->trip ? $this->displayName() . '◆' . $this->trip : $this->displayName();
    }
}
