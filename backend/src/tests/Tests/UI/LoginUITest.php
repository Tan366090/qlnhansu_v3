<?php

namespace Tests\UI;

use PHPUnit\Framework\TestCase;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverDimension;

class LoginUITest extends TestCase
{
    protected $driver;
    protected $baseUrl = 'http://localhost:3000';

    protected function setUp(): void
    {
        $host = 'http://localhost:4444/wd/hub';
        $capabilities = DesiredCapabilities::chrome();
        $capabilities->setCapability('goog:chromeOptions', ['args' => ['--headless', '--disable-gpu']]);
        $this->driver = RemoteWebDriver::create($host, $capabilities);
    }

    protected function tearDown(): void
    {
        if ($this->driver) {
            $this->driver->quit();
        }
    }

    public function testLoginPageUI()
    {
        $this->driver->get($this->baseUrl . '/login.html');
        
        $this->assertStringContainsString('Đăng Nhập', $this->driver->getTitle());
        
        $this->assertTrue($this->driver->findElement(WebDriverBy::id('username'))->isDisplayed());
        $this->assertTrue($this->driver->findElement(WebDriverBy::id('password'))->isDisplayed());
        $this->assertTrue($this->driver->findElement(WebDriverBy::className('login-button'))->isDisplayed());
        
        $this->assertEquals('', $this->driver->findElement(WebDriverBy::id('username'))->getAttribute('value'));
        $this->assertEquals('', $this->driver->findElement(WebDriverBy::id('password'))->getAttribute('value'));
    }

    public function testLoginFormValidation()
    {
        $this->driver->get($this->baseUrl . '/login.html');

        // Test empty form submission
        $this->driver->findElement(WebDriverBy::className('login-button'))->click();
        $this->assertStringContainsString(
            'Vui lòng nhập tên đăng nhập',
            $this->driver->findElement(WebDriverBy::id('errorMessage'))->getText()
        );

        // Test username only
        $this->driver->findElement(WebDriverBy::id('username'))->sendKeys('admin');
        $this->driver->findElement(WebDriverBy::className('login-button'))->click();
        $this->assertStringContainsString(
            'Vui lòng nhập mật khẩu',
            $this->driver->findElement(WebDriverBy::id('errorMessage'))->getText()
        );

        // Test password only
        $this->driver->findElement(WebDriverBy::id('username'))->clear();
        $this->driver->findElement(WebDriverBy::id('password'))->sendKeys('password');
        $this->driver->findElement(WebDriverBy::className('login-button'))->click();
        $this->assertStringContainsString(
            'Vui lòng nhập tên đăng nhập',
            $this->driver->findElement(WebDriverBy::id('errorMessage'))->getText()
        );
    }

    public function testSuccessfulLogin()
    {
        $this->driver->get($this->baseUrl . '/login.html');

        $this->driver->findElement(WebDriverBy::id('username'))->sendKeys('admin');
        $this->driver->findElement(WebDriverBy::id('password'))->sendKeys('admin123');
        $this->driver->findElement(WebDriverBy::className('login-button'))->click();

        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::urlContains('/admin/dashboard.html')
        );
    }

    public function testFailedLogin()
    {
        $this->driver->get($this->baseUrl . '/login.html');

        $this->driver->findElement(WebDriverBy::id('username'))->sendKeys('wrong');
        $this->driver->findElement(WebDriverBy::id('password'))->sendKeys('wrong');
        $this->driver->findElement(WebDriverBy::className('login-button'))->click();

        $this->assertStringContainsString(
            'Tên đăng nhập hoặc mật khẩu không đúng',
            $this->driver->findElement(WebDriverBy::id('errorMessage'))->getText()
        );
    }

    public function testLoadingStates()
    {
        $this->driver->get($this->baseUrl . '/login.html');

        $this->driver->findElement(WebDriverBy::id('username'))->sendKeys('admin');
        $this->driver->findElement(WebDriverBy::id('password'))->sendKeys('admin123');
        $this->driver->findElement(WebDriverBy::className('login-button'))->click();

        $this->assertTrue($this->driver->findElement(WebDriverBy::id('loading'))->isDisplayed());
        
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::invisibilityOfElementLocated(WebDriverBy::id('loading'))
        );
    }
} 