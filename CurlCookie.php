<?php
/**
 * Created by PhpStorm.
 * Project: cookie
 * @author: Evgeny Pynykh bpteam22@gmail.com
 */

namespace bpteam\Cookie;


class CurlCookie extends Cookie implements iCookie
{
    public function getFileName(){
        return parent::getFileName() . '-curl';
    }

    /**
     * @param string $text
     * @return array
     */
    public static function from($text){
        $lines = explode("\n", $text);
        $count = count($lines);
        $cookies = [];
        for($i = 4 ; $i < $count ; $i++){
            $fields = array_map('trim', explode("\t", $lines[$i]));
            if(is_array($fields) && isset($fields[5])){
                $cookie['name']     = $fields[5];
                $cookie['value']    = $fields[6];
                $cookie['tailmatch']= $fields[1] == 'TRUE';
                $cookie['domain']   = preg_replace('%^\#HttpOnly_%ims', '', $fields[0]);
                $cookie['path']     = $fields[2];
                $cookie['expires']  = date('D, d-M-y H:i:s', $fields[4] - date('Z')) . " GMT";
                $cookie['httponly'] = (bool)preg_match('%^\#HttpOnly_%ims', $lines[$i]);
                $cookie['secure']   = $fields[3] == 'TRUE';
                $cookies[$cookie['name']] = $cookie;
            }
        }
        return $cookies;
    }

    public function fromFile($fileName = false){
        $text = file_get_contents($fileName ? $fileName : $this->getFileName());
        return self::from($text);
    }

    /**
     * @param $cookie
     * @return string
     */
    public static function to($cookie){
        return ($cookie['httponly']?'#HttpOnly_':'') .
        $cookie['domain'] . "\t" .
        (@$cookie['tailmatch'] ? 'TRUE' : 'FALSE') . "\t" .
        $cookie['path'] . "\t" .
        ($cookie['secure'] ? 'TRUE' : 'FALSE') . "\t" .
        ($cookie['expires'] ? strtotime($cookie['expires']) : 1) . "\t" .
        $cookie['name'] . "\t" .
        $cookie['value'];
    }

    public function toFile($cookies){
        $str = "\n\n\n\n";
        $cookiesLines = [];
        foreach($cookies as $cookie){
            $cookiesLines[] = $this->to($cookie);
        }
        $str .= implode("\n",$cookiesLines);
        return file_put_contents($this->getFileName(), $str);
    }
}