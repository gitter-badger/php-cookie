<?php
/**
 * Created by PhpStorm.
 * Project: cookie
 * @author: Evgeny Pynykh bpteam22@gmail.com
 */

namespace bpteam\Cookie;

use bpteam\BigList\JsonList;

class Cookie extends JsonList implements iCookie
{

    protected $ext = 'cookie';

    function __construct($path = NULL)
    {
        parent::__construct($path ?: (__DIR__ . '/data'));
    }

    function __destruct()
    {
        parent::__destruct();
        $this->deleteFile();
    }

    public function add($name, $value, $domain, $path = '/', $expires = false, $httpOnly = false, $secure = false, $tailMatch = true)
    {
        $cookie['name']     = $name;
        $cookie['value']    = $value;
        $cookie['tailmatch']= (bool)$tailMatch;
        $cookie['domain']   = $domain;
        $cookie['path']     = $path;
        $cookie['expires']  = $expires ? $expires : date('D, d-M-y H:i:s', time() + 86400 - date('Z')) . ' GMT';
        $cookie['httponly'] = (bool)$httpOnly;
        $cookie['secure']   = (bool)$secure;
        $domain = cStringWork::getDomainName($domain);
        $this->write($cookie, $name, $domain);
    }

    public function adds($cookies){
        foreach($cookies as $cookie){
            $this->add(
                $cookie['name'],
                $cookie['value'],
                $cookie['domain'],
                isset($cookie['path']) ? $cookie['path'] : '/',
                isset($cookie['expires']) ? $cookie['expires'] : false,
                isset($cookie['httponly']) ? (bool)$cookie['httponly'] : false,
                isset($cookie['secure']) ? (bool)$cookie['secure'] : false,
                isset($cookie['tailmatch']) ? (bool)$cookie['tailmatch'] : true
            );
        }
    }

    public function delete($name, $domain)
    {
        $data =& $this->find($name, $this->find(cStringWork::getDomainName($domain)));
        $data = NULL;
    }

    public function deleteFile()
    {
        $this->deleteList();
    }

    public function deleteOldFiles($storageTime = 86400)
    {
        foreach (glob($this->path . "/*." . $this->ext) as $value) {
            if (file_exists($value)) {
                $fileInfo = stat($value);
                if ($fileInfo['ctime'] < time() - $storageTime) {
                    unlink($value);
                }
            }
        }
    }

    public function get($domain)
    {
        return $this->find(cStringWork::getDomainName($domain));
    }

    public function getActive($domain)
    {
        $cookies = $this->get($domain);
        foreach($cookies as $key => $cookie){
            if(!$this->checkExpires($cookie['expires'])){
                unset($cookies[$key]);
            }
        }
        return $cookies;
    }

    protected function checkExpires($date){
        return (time() > strtotime($date));
    }

    public function getAll()
    {
        return $this->read();
    }

    /**
     * @param string $text
     * @return array
     */
    public static function fromHttp($text){
        $cookies = [];
        if(preg_match_all('%Set-Cookie:\s*(?<name>\w+)\s*=\s*(?<value>[^;]+)(?<cookie>.*)%i', $text, $matches)){
            foreach($matches['cookie'] as $cookieLine){
                $cookie = self::parsCookieString($cookieLine);
                if ($cookie) {
                    $cookies[$cookie['name']] = $cookie;
                }
            }
        }
        return $cookies;
    }

    /**
     * @param string $text
     * @return array
     */
    public static function fromMetaTeg($text){
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

    public static function parsCookieString($text){
        $parameters = ['expires', 'domain', 'path'];
        if(preg_match('%(?<name>\w+)\s*=\s*(?<value>[^;]+)%ims', $text, $match)){
            $cookie['name'] = trim($match['name']);
            $cookie['value'] = trim($match['value']);
        } else {
            return false;
        }
        foreach($parameters as $param){
            if(preg_match('%' . $param . '\s*=\s*(?<val>[^;]+)%i', $text, $match)){
                $cookie[$param] = trim($match['val']);
            }
        }
        $cookie['secure'] = (bool)preg_match('%;\s*secure\s*(;|$)%i', $text);
        $cookie['httponly'] = (bool)preg_match('%;\s*httponly\s*(;|$)%i', $text);
        return $cookie;
    }
}