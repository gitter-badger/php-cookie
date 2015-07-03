<?php
/**
 * Created by PhpStorm.
 * User: ec
 * Date: 03.07.2015
 * Time: 23:28
 */

namespace bpteam\Cookie;

use \PHPUnit_Framework_TestCase;
use \ReflectionClass;

class HtmlCookieTest extends PHPUnit_Framework_TestCase
{
    public static $name;

    public static function setUpBeforeClass()
    {
        self::$name = 'unit_test';
    }
    /**
     * @param        $name
     * @param string $className
     * @return \ReflectionMethod
     */
    protected static function getMethod($name, $className = 'bpteam\SimpleHttp\SimpleHttp')
    {
        $class = new ReflectionClass($className);
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }

    /**
     * @param        $name
     * @param string $className
     * @return \ReflectionProperty
     */
    protected static function getProperty($name, $className = 'bpteam\SimpleHttp\SimpleHttp')
    {
        $class = new ReflectionClass($className);
        $property = $class->getProperty($name);
        $property->setAccessible(true);
        return $property;
    }

    public function testOpen()
    {
        $cookie = new HtmlCookie();
        $cookie->open(self::$name);
        $this->assertFileExists($cookie->getFileName());
    }

    public function testAdd()
    {
        $cookie = new HtmlCookie();
        $cookie->open(self::$name);
        $cookie->add('testName', 'testValue', '.test1.ru', '/lib3', date('l, d-M-y H:i:s e', time() + 43200), false, false, true);
        $cookie->add('2testName', '2testValue', '.test1.ru', '/', date('l, d-M-y H:i:s e',63200), true, true, true);
        $cookies = $cookie->get('http://www.test1.ru/blablalba/22');
        $this->assertTrue(is_array($cookies));
        $this->assertArrayHasKey('testName', $cookies);
        $this->assertArrayHasKey('value', $cookies['testName']);
        $this->assertEquals('testValue', $cookies['testName']['value']);

        $this->assertArrayHasKey('2testName', $cookies);
        $this->assertArrayHasKey('value', $cookies['2testName']);
        $this->assertEquals('2testValue', $cookies['2testName']['value']);
    }
}