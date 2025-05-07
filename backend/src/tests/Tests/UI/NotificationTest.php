<?php

namespace Tests\UI;

use PHPUnit\Framework\TestCase;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;

class NotificationTest extends TestCase
{
    protected $webDriver;

    protected function setUp(): void
    {
        $host = getenv('SELENIUM_HOST');
        $this->webDriver = RemoteWebDriver::create($host, DesiredCapabilities::chrome());
        
        // Login before each test
        $this->login();
    }

    protected function tearDown(): void
    {
        $this->webDriver->quit();
    }

    protected function login()
    {
        $this->webDriver->get(getenv('APP_URL') . '/public/login.html');
        $usernameField = $this->webDriver->findElement(WebDriverBy::name('username'));
        $usernameField->sendKeys('admin');
        $passwordField = $this->webDriver->findElement(WebDriverBy::name('password'));
        $passwordField->sendKeys('admin123');
        $loginButton = $this->webDriver->findElement(WebDriverBy::cssSelector('button[type="submit"]'));
        $loginButton->click();
        
        // Wait for dashboard to load
        $this->webDriver->wait(10)->until(
            WebDriverExpectedCondition::urlContains('dashboard')
        );
    }

    public function testSuccessNotification()
    {
        // Trigger a success notification (e.g., by creating a new user)
        $this->webDriver->get(getenv('APP_URL') . '/users/create');
        
        // Fill in user creation form
        $this->webDriver->findElement(WebDriverBy::name('username'))->sendKeys('testuser');
        $this->webDriver->findElement(WebDriverBy::name('password'))->sendKeys('password123');
        $this->webDriver->findElement(WebDriverBy::name('email'))->sendKeys('test@example.com');
        
        // Submit form
        $this->webDriver->findElement(WebDriverBy::cssSelector('button[type="submit"]'))->click();
        
        // Wait for success notification
        $notification = $this->webDriver->wait(10)->until(
            WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::className('notification-success'))
        );
        
        // Assert notification content
        $this->assertStringContainsString('User created successfully', $notification->getText());
        
        // Assert notification disappears after 5 seconds
        sleep(6);
        $notifications = $this->webDriver->findElements(WebDriverBy::className('notification-success'));
        $this->assertCount(0, $notifications);
    }

    public function testErrorNotification()
    {
        // Trigger an error notification (e.g., by trying to create a user with invalid data)
        $this->webDriver->get(getenv('APP_URL') . '/users/create');
        
        // Submit empty form to trigger validation error
        $this->webDriver->findElement(WebDriverBy::cssSelector('button[type="submit"]'))->click();
        
        // Wait for error notification
        $notification = $this->webDriver->wait(10)->until(
            WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::className('notification-error'))
        );
        
        // Assert notification content
        $this->assertStringContainsString('Please fill in all required fields', $notification->getText());
    }

    public function testWarningNotification()
    {
        // Trigger a warning notification (e.g., by accessing a restricted area)
        $this->webDriver->get(getenv('APP_URL') . '/admin/settings');
        
        // Wait for warning notification
        $notification = $this->webDriver->wait(10)->until(
            WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::className('notification-warning'))
        );
        
        // Assert notification content
        $this->assertStringContainsString('You need additional permissions', $notification->getText());
    }

    public function testInfoNotification()
    {
        // Trigger an info notification (e.g., by viewing system status)
        $this->webDriver->get(getenv('APP_URL') . '/system/status');
        
        // Wait for info notification
        $notification = $this->webDriver->wait(10)->until(
            WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::className('notification-info'))
        );
        
        // Assert notification content
        $this->assertStringContainsString('System status', $notification->getText());
    }

    public function testNotificationDismissal()
    {
        // Trigger a notification
        $this->webDriver->get(getenv('APP_URL') . '/system/status');
        
        // Wait for notification
        $notification = $this->webDriver->wait(10)->until(
            WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::className('notification'))
        );
        
        // Click close button
        $closeButton = $notification->findElement(WebDriverBy::className('notification-close'));
        $closeButton->click();
        
        // Assert notification is removed
        $this->webDriver->wait(10)->until(
            WebDriverExpectedCondition::invisibilityOfElementLocated(WebDriverBy::className('notification'))
        );
    }
} 