<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Board extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'position',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function threads(): HasMany
    {
        return $this->hasMany(Thread::class);
    }

    /**
     * 板の一覧に出す「最後に書き込まれたスレッド」。
     *
     * 一覧の中で1板ずつ引くと板の数だけ問い合わせが増えるので、
     * まとめて読み込めるように関連として持たせる。
     */
    public function latestThread(): HasOne
    {
        return $this->hasOne(Thread::class)->latestOfMany('last_posted_at');
    }
}
