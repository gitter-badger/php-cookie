<?php
/**
 * Created by PhpStorm.
 * User: ec
 * Date: 23.06.15
 * Time: 9:40
 * Project: cookie
 * @author: Evgeny Pynykh bpteam22@gmail.com
 */

namespace bpteam\Cookie;

use \PHPUnit_Framework_TestCase;
use \ReflectionClass;

class PhantomJsCookieTest extends PHPUnit_Framework_TestCase
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
        $cookie = new PhantomJsCookie();
        $cookie->open(uniqid(self::$name));
        $this->assertFileExists($cookie->getFileName());
    }

    public function testAdd()
    {
        $cookie = new PhantomJsCookie();
        $cookie->open(uniqid(self::$name));
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

    public function testTo()
    {
        $cookie = new PhantomJsCookie();
        $cookie->open(uniqid(self::$name));
        $cookie->add('testName', 'testValue', '.test1.ru', '/lib3', date('l, d-M-y H:i:s e', time() + 43200), false, false, true);
        $cookie->add('2testName', '2testValue', '.test1.ru', '/', date('l, d-M-y H:i:s e',63200), true, true, true);
        $testCookie = $cookie->get('test1.ru');
        $testPhantomCookie = $cookie->to($testCookie['2testName']);
        $regEx = '%^2testName=2testValue; expires=[^;]*; secure; HttpOnly; domain=.test1.ru; path=/$%';
        $this->assertRegExp($regEx, $testPhantomCookie);
    }

    public function testFrom()
    {
        $phantomJsCookie = '[General]
cookies="@Variant(\0\0\0\x7f\0\0\0\x16QList<QNetworkCookie>\0\0\0\0\x1\0\0\0\x2\0\0\0SPHPSESSID=asdf123465; expires=Sat, 25-Jan-14 16:57:33 GMT; domain=.test1.ru; path=/\0\0\0QtestName=testValue; expires=Sat, 25-Jan-14 16:57:33 GMT; domain=.test1.ru; path=/)"
';
        $cookies = PhantomJsCookie::from($phantomJsCookie);
        $this->assertArrayHasKey('testName', $cookies);
        $this->assertArrayHasKey('value', $cookies['testName']);
        $this->assertEquals('testValue',$cookies['testName']['value']);

        $this->assertArrayHasKey('PHPSESSID', $cookies);
        $this->assertArrayHasKey('value', $cookies['PHPSESSID']);
        $this->assertEquals('asdf123465',$cookies['PHPSESSID']['value']);
    }

    public function testToFile()
    {
        $cookie = new PhantomJsCookie();
        $cookie->open(uniqid(self::$name));
        $cookie->add('PHPSESSID', 'asdf123465', '.test1.ru', '/', date('D, d-M-y H:i:s', time()+100000) . ' GMT', false, false, true);
        $cookie->add('testName', 'testValue', '.test1.ru', '/', date('D, d-M-y H:i:s', time()+100000) . ' GMT', false, false, true);
        $cookie->toFile($cookie->get('test1.ru'));
        $testCookie = $cookie->fromFile($cookie->getFileFormName());

        $this->assertArrayHasKey('testName', $testCookie);
        $this->assertArrayHasKey('value', $testCookie['testName']);
        $this->assertEquals('testValue',$testCookie['testName']['value']);

        $this->assertArrayHasKey('PHPSESSID', $testCookie);
        $this->assertArrayHasKey('value', $testCookie['PHPSESSID']);
        $this->assertEquals('asdf123465',$testCookie['PHPSESSID']['value']);
    }
}