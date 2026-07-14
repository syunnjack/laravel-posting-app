<?php

namespace App\Support;

class TripCode
{
    /**
     * 名前欄の「#secret」部分からトリップ文字列を生成する。
     * 古典的な5chのDES-crypt方式そのものではないが、同じ用途
     * （合言葉が一致する投稿者だけが同じ表示になる）を満たす簡易実装。
     */
    public static function generate(string $secret): string
    {
        $hash = hash('sha256', $secret . config('app.key'), true);

        return substr(base64_encode($hash), 0, 10);
    }
}
