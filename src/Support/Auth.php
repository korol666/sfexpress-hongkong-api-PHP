<?php

namespace korol666\SFHKAPI\Support;


class Auth {
    /**
     * 計算驗證碼
     * data 是拼接完整的報文XML
     * checkword 是順豐給的接入碼
     *
     * @param string $data
     * @param string $checkword
     * @return string
     */
    public static function sign( $data , $checkword ) {
        $string = trim($data) . trim($checkword);
        $md5 = md5(mb_convert_encoding($string , 'UTF-8' , mb_detect_encoding($string)) , true);
        $sign = base64_encode($md5);

        return $sign;
    }
}