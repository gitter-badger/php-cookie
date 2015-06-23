<?php
/**
 * Created by PhpStorm.
 * Project: cookie
 * @author: Evgeny Pynykh bpteam22@gmail.com
 */

namespace bpteam\Cookie;


interface iCookie
{
    /**
     * @param string      $name
     * @param string      $value
     * @param string      $domain
     * @param string      $path
     * @param bool|string $expires
     * @param bool        $httpOnly Send only in HTTP headers
     * @param bool        $secure
     * @param bool        $tailMatch full match on domain name
     */
    public function add($name, $value, $domain, $path = '/', $expires = false, $httpOnly = false, $secure = false, $tailMatch = true);
    public function delete($name, $domain);
    public function get($domain);
    public function open($name);
    public function close();
    public function getActive($domain);
    public function getAll();
}