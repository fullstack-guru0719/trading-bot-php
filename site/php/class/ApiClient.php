<?php

class ApiClient
{
    private static $ch = null;

    public static function makeRequest($url, $get_vars = array(), $post_vars = array(), $headers = array(), $is_post_request = false, $return_raw = false, $custom_request = null)
    {
        if (self::$ch === null) {
            self::$ch = curl_init();
        }
        curl_setopt(self::$ch, CURLOPT_URL, $url . '?' . http_build_query($get_vars));
        curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, 1);
        if ($is_post_request == true) {
            curl_setopt(self::$ch, CURLOPT_POST, 1);
            curl_setopt(self::$ch, CURLOPT_POSTFIELDS, http_build_query($post_vars));
        }
        curl_setopt(self::$ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        curl_setopt(self::$ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt(self::$ch, CURLOPT_SSL_VERIFYHOST, 0);
        if (count($headers) > 0) {
            curl_setopt(self::$ch, CURLOPT_HTTPHEADER, $headers);
        }
        if ($custom_request !== null) {
            curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, $custom_request);
        }
        $response = curl_exec(self::$ch);

        if ($return_raw == true) {
            return $response;
        }

        $return_array = null;
        $r            = json_decode($response, true);
        if ( ! is_null($r)) {
            $return_array = $r;
        }

        return $return_array;
    }

    public static function makePostRequest($url, $get_vars = array(), $post_vars = array(), $headers = array(), $return_raw = false)
    {
        return self::makeRequest($url, $get_vars, $post_vars, $headers, true, $return_raw);
    }

}