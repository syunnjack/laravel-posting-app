<?php

namespace App\Support;

class PostName
{
    /**
     * 投稿者名を「表示名」と「トリップ」に分ける。
     *
     * 「名無し#合言葉」のように # 以降をトリップの種にする。
     *
     * 以前は preg_split の戻り値をそのまま配列として扱っていたため、
     * 不正なバイト列を含む名前が来ると 500 になっていた。
     * /u 修飾子つきのパターンは、対象が正しい UTF-8 でないと false を返す。
     * 実際、文字化けした名前を送ったときに
     * 「Trying to access array offset on false」で落ちていた。
     *
     * @return array{0: ?string, 1: ?string} [表示名, トリップ]
     */
    public static function parse(?string $raw): array
    {
        $raw = trim((string) $raw);
        if ($raw === '') {
            return [null, null];
        }

        // 不正なバイト列は捨てる。ここで弾いておけば以降の処理が落ちない。
        if (! mb_check_encoding($raw, 'UTF-8')) {
            $raw = trim(mb_convert_encoding($raw, 'UTF-8', 'UTF-8'));
            if ($raw === '') {
                return [null, null];
            }
        }

        $parts = preg_split('/[#＃]/u', $raw, 2);

        // それでも失敗したときは、名前だけ使ってトリップは付けない。
        if ($parts === false) {
            return [$raw, null];
        }

        $name = ($parts[0] ?? '') !== '' ? $parts[0] : null;
        $trip = isset($parts[1]) && $parts[1] !== '' ? TripCode::generate($parts[1]) : null;

        return [$name, $trip];
    }
}
