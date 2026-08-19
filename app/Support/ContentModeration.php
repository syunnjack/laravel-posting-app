<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ContentModeration
{
    /** 書き込みを止める語。 */
    private const NG_WORDS = ['死ね', '殺す', 'バカ', 'カス'];

    /**
     * NG語を部分に含むが、それ自体は普通の言葉。
     *
     * 日本語には単語の区切りが無いので、部分一致だけで判定すると
     * 「カスタム」「バカンス」まで弾いてしまう。先に取り除いてから判定する。
     */
    private const ALLOW_WORDS = [
        'カスタム', 'カスタマイズ', 'カスタマー', 'カスタネット', 'カスケード', 'カステラ',
        'カスパー', 'ダマスカス', 'オデュッセウス', 'バカンス', 'バカラ', 'バカボン', 'バカロレア',
    ];

    /** 1つの投稿に貼れるリンクの数。 */
    private const MAX_LINKS = 2;

    public static function containsNgWord(string $text): bool
    {
        // 誤爆する語を先に外す
        $text = str_ireplace(self::ALLOW_WORDS, '', $text);

        foreach (self::NG_WORDS as $word) {
            if ($word !== '' && mb_stripos($text, $word) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * 宣伝目的とみられる投稿か。
     *
     * 以前は 'http://' 自体をNG語にしていたため、ニュース板でも出典を貼れず、
     * しかもエラーは「不適切な表現」としか出なかった。リンクは許可したうえで、
     * 数が多いものと、本文がほぼリンクだけのものを止める。
     */
    public static function isLinkSpam(string $text): bool
    {
        $count = preg_match_all('#https?://|www\.#i', $text);

        if ($count > self::MAX_LINKS) {
            return true;
        }

        if ($count === 0) {
            return false;
        }

        // リンクを除いた本文が短すぎる（＝URLを貼っただけ）
        $withoutLinks = trim(preg_replace('#(https?://|www\.)\S*#i', '', $text));

        return mb_strlen($withoutLinks) < 10;
    }

    /** リンクの上限（画面の案内文で使う）。 */
    public static function maxLinks(): int
    {
        return self::MAX_LINKS;
    }

    public static function clientIp(Request $request): string
    {
        return $request->ip() ?? 'unknown';
    }

    public static function clientIpHash(Request $request): string
    {
        return hash('sha256', self::clientIp($request));
    }

    /**
     * 同一キーからの連投を防ぐ簡易クールダウン。
     * 初回呼び出し時はfalseを返しキーを記録、期間内の再呼び出しはtrueを返す。
     */
    public static function isTooSoon(string $key, int $seconds): bool
    {
        if (Cache::has($key)) {
            return true;
        }

        Cache::put($key, true, $seconds);

        return false;
    }
}
