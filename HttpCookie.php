<?php
/**
 * Created by PhpStorm.
 * Project: cookie
 * @author: Evgeny Pynykh bpteam22@gmail.com
 */

namespace bpteam\Cookie;


class HttpCookie extends Cookie implements iCookie
{

    protected $ext = '_http.cookie';

    function __construct($path = null)
    {
        parent::__construct($path);
    }

    function __destruct()
    {
        parent::__destruct();
    }

    /**
     * @param string $text
     * @return array
     */
    public static function from($text)
    {
        $cookies = [];
        if (preg_match_all('%Set-Cookie:\s*(?<name>\w+)\s*=\s*(?<value>[^;]+)(?<cookie>.*)%i', $text, $matches)) {
            foreach ($matches['cookie'] as $cookieLine) {
                $cookie = self::parsCookieString($cookieLine);
                if ($cookie) {
                    $cookies[$cookie['name']] = $cookie;
                }
            }
        }
        return $cookies;
    }

    /**
     * @param $cookie
     * @return string
     */
    public static function to($cookie)
    {
        return $cookie['name'] . '=' . $cookie['value'];
    }

    public static function toHeader($cookies)
    {
        $cookieLines = [];
        foreach ($cookies as $cookie) {
            $cookieLines[] = self::to($cookie);
        }
        return 'Cookie: ' . implode('; ', $cookieLines) . "\n";
    }
}