<?php

use PHPUnit\Framework\TestCase;

class LoginUITest extends TestCase
{
    private $driver;

    protected function setUp(): void
    {
        // Initialize Selenium WebDriver
        $host = 'http://localhost:4444/wd/hub'; // URL of the Selenium server
        $capabilities = \Facebook\WebDriver\Remote\DesiredCapabilities::chrome();
        $this->driver = \Facebook\WebDriver\Remote\RemoteWebDriver::create($host, $capabilities);
    }

    protected function tearDown(): void
    {
        // Quit the WebDriver
        $this->driver->quit();
    }

    public function testLoginWithValidCredentials()
    {
        // Navigate to the login page
        $this->driver->get('http://localhost/QLNhanSu_version1/public/admin/login.html');

        // Find and fill the username field
        $usernameField = $this->driver->findElement(\Facebook\WebDriver\WebDriverBy::id('username'));
        $usernameField->sendKeys('admin@example.com');

        // Find and fill the password field
        $passwordField = $this->driver->findElement(\Facebook\WebDriver\WebDriverBy::id('password'));
        $passwordField->sendKeys('123456');

        // Find and click the login button
        $loginButton = $this->driver->findElement(\Facebook\WebDriver\WebDriverBy::id('loginButton'));
        $loginButton->click();

        // Wait for the dashboard to load
        $this->driver->wait()->until(
            \Facebook\WebDriver\WebDriverExpectedCondition::urlContains('dashboard.html')
        );

        // Assert that the URL contains 'dashboard.html'
        $this->assertStringContainsString('dashboard.html', $this->driver->getCurrentURL());
    }

    public function testLoginWithInvalidCredentials()
    {
        // Navigate to the login page
        $this->driver->get('http://localhost/QLNhanSu_version1/public/admin/login.html');

        // Find and fill the username field
        $usernameField = $this->driver->findElement(\Facebook\WebDriver\WebDriverBy::id('username'));
        $usernameField->sendKeys('invalid@example.com');

        // Find and fill the password field
        $passwordField = $this->driver->findElement(\Facebook\WebDriver\WebDriverBy::id('password'));
        $passwordField->sendKeys('wrongpassword'); // Test invalid password

        // Find and click the login button
        $loginButton = $this->driver->findElement(\Facebook\WebDriver\WebDriverBy::id('loginButton'));
        $loginButton->click();

        // Wait for the error message to appear
        $this->driver->wait()->until(
            \Facebook\WebDriver\WebDriverExpectedCondition::presenceOfElementLocated(
                \Facebook\WebDriver\WebDriverBy::id('errorMessage')
            )
        );

        // Assert that the error message is displayed
        $errorMessage = $this->driver->findElement(\Facebook\WebDriver\WebDriverBy::id('errorMessage'))->getText();
        $this->assertStringContainsString('Invalid username or password', $errorMessage);
    }
}
