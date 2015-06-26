<?php
/**
 * Created by PhpStorm.
 * Project: cookie
 * @author: Evgeny Pynykh bpteam22@gmail.com
 */

namespace bpteam\Cookie;


class HtmlCookie extends Cookie implements iCookie
{
    /**
     * @param string $text
     * @return array
     */
    public static function from($text)
    {
        $cookies = [];
        if(preg_match_all('%(?<cookie><meta[^>]*Set-Cookie[^>]*>)%i', $text, $matches)){
            foreach($matches['cookie'] as $cookieLine){
                if(!preg_match('%content\s*=\s*(\'|")(?<cookieLine>.*))%i', $cookieLine, $match)){
                    continue;
                }
                $cookie = self::parsCookieString($match['cookieLine']);
                if($cookie){
                    $cookies[$cookie['name']] = $cookie;
                }
            }
        }
        return $cookies;
    }

    public static function to($cookie)
    {
        $cookieLine = [];
        if (!$cookie['name'] || !$cookie['value']) return false;
        $cookieLine[] = $cookie['name'] . '=' . $cookie['value'];
        if ($cookie['domain']) $cookieLine[] = 'domain=' . $cookie['domain'];
        if ($cookie['expires']) $cookieLine[] = 'expires=' . $cookie['expires'];
        if ($cookie['path']) $cookieLine[] = 'path=' . $cookie['path'];
        if ($cookie['secure']) $cookieLine[] = 'secure';
        if ($cookie['httponly']) $cookieLine[] = 'httponly';
        return '<meta http-equiv="Set-Cookie" content="' . implode('; ', $cookieLine) . '" />';
    }

    public static function toTag($cookies)
    {
        $cookieLine = [];
        foreach ($cookies as $cookie) {
            $cookieLine[] = self::to($cookie);
        }
        return implode("\n", $cookieLine);
    }
}