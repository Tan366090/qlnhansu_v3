<?php

namespace Tests\UI;

use PHPUnit\Framework\TestCase;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;

class LoginTest extends TestCase
{
    protected $webDriver;

    protected function setUp(): void
    {
        $host = getenv('SELENIUM_HOST');
        $this->webDriver = RemoteWebDriver::create($host, DesiredCapabilities::chrome());
    }

    protected function tearDown(): void
    {
        $this->webDriver->quit();
    }

    public function testLoginPageLoads()
    {
        $this->webDriver->get(getenv('APP_URL') . '/public/login.html');
        $this->assertStringContainsString('Login', $this->webDriver->getTitle());
    }

    public function testLoginWithValidCredentials()
    {
        $this->webDriver->get(getenv('APP_URL') . '/public/login.html');
        
        // Find and fill in username field
        $usernameField = $this->webDriver->findElement(WebDriverBy::name('username'));
        $usernameField->sendKeys('admin');

        // Find and fill in password field
        $passwordField = $this->webDriver->findElement(WebDriverBy::name('password'));
        $passwordField->sendKeys('admin123');

        // Click login button
        $loginButton = $this->webDriver->findElement(WebDriverBy::cssSelector('button[type="submit"]'));
        $loginButton->click();

        // Wait for redirect and check if we're on the dashboard
        $this->webDriver->wait(10)->until(
            WebDriverExpectedCondition::urlContains('dashboard')
        );

        // Assert we're on the dashboard
        $this->assertStringContainsString('dashboard', $this->webDriver->getCurrentURL());
    }

    public function testLoginWithInvalidCredentials()
    {
        $this->webDriver->get(getenv('APP_URL') . '/public/login.html');
        
        // Find and fill in username field with invalid credentials
        $usernameField = $this->webDriver->findElement(WebDriverBy::name('username'));
        $usernameField->sendKeys('wronguser');

        // Find and fill in password field with invalid credentials
        $passwordField = $this->webDriver->findElement(WebDriverBy::name('password'));
        $passwordField->sendKeys('wrongpass');

        // Click login button
        $loginButton = $this->webDriver->findElement(WebDriverBy::cssSelector('button[type="submit"]'));
        $loginButton->click();

        // Wait for error message
        $errorMessage = $this->webDriver->wait(10)->until(
            WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::className('error-message'))
        );

        // Assert error message is displayed
        $this->assertNotEmpty($errorMessage->getText());
    }
} 