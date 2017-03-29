<?php

namespace poloniex\api\tools;

/**
 * HTTP requests support class.
 *
 * @category Poloniex API
 * @author Dmytro Zarezenko <dmytro.zarezenko@gmail.com>
 * @copyright (c) 2017, Dmytro Zarezenko
 *
 * @git https://github.com/dzarezenko/poloniex-api
 * @license http://opensource.org/licenses/MIT
 */
class Request {

    private $apiKey = "";
    private $apiSecret = "";

    public function __construct($apiKey, $apiSecret) {
        $this->apiKey = $apiKey;
        $this->apiSecret = $apiSecret;
    }

    public function exec(array $req = []) {
        usleep(1000000);

        // API settings
        $key = $this->apiKey;
        $secret = $this->apiSecret;
        $mt = explode(' ', microtime());
        $req['nonce'] = $mt[1] . substr($mt[0], 2, 6);

        // generate the POST data string
        $post_data = http_build_query($req, '', '&');
        $sign = hash_hmac('sha512', $post_data, $secret);

        // generate the extra headers
        $headers = [
            'Key: ' . $key,
            'Sign: ' . $sign,
        ];

        // curl handle (initialize if required)
        static $ch = null;
        if (is_null($ch)) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt(
                $ch,
                CURLOPT_USERAGENT,
                'Mozilla/4.0 (compatible; Poloniex PHP API; ' . php_uname('a') . '; PHP/' . phpversion() . ')'
            );
        }
        curl_setopt($ch, CURLOPT_URL, \poloniex\api\PoloniexAPIConf::URL_TRADING);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        // run the query
        $res = curl_exec($ch);
        if ($res === false) {
            throw new \Exception('Curl error: ' . curl_error($ch));
        }

        $json = json_decode($res, true);

        return $json;
    }

    public static function json($url) {
        $opts = [
            'http' => [
                'method' => 'GET',
                'timeout' => 10
            ]
        ];
        $context = stream_context_create($opts);
        $feed = file_get_contents($url, false, $context);
        $json = json_decode($feed, true);

        return $json;
    }

}