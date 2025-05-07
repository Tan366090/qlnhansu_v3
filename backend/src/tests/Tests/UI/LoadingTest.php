<?php

namespace Tests\UI;

use PHPUnit\Framework\TestCase;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;

class LoadingTest extends TestCase
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

    public function testFormLoading()
    {
        // Navigate to user creation form
        $this->webDriver->get(getenv('APP_URL') . '/users/create');
        
        // Get the form element
        $form = $this->webDriver->findElement(WebDriverBy::id('user-create-form'));
        
        // Submit form to trigger loading state
        $form->submit();
        
        // Assert loading overlay is visible
        $loadingOverlay = $this->webDriver->wait(10)->until(
            WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::className('loading-overlay'))
        );
        $this->assertTrue($loadingOverlay->isDisplayed());
        
        // Wait for loading to complete
        $this->webDriver->wait(10)->until(
            WebDriverExpectedCondition::invisibilityOfElementLocated(WebDriverBy::className('loading-overlay'))
        );
    }

    public function testAjaxLoading()
    {
        // Navigate to user list page
        $this->webDriver->get(getenv('APP_URL') . '/users');
        
        // Click refresh button to trigger AJAX loading
        $refreshButton = $this->webDriver->findElement(WebDriverBy::id('refresh-users'));
        $refreshButton->click();
        
        // Assert loading spinner is visible
        $loadingSpinner = $this->webDriver->wait(10)->until(
            WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::className('loading-spinner'))
        );
        $this->assertTrue($loadingSpinner->isDisplayed());
        
        // Wait for loading to complete
        $this->webDriver->wait(10)->until(
            WebDriverExpectedCondition::invisibilityOfElementLocated(WebDriverBy::className('loading-spinner'))
        );
    }

    public function testFileUploadLoading()
    {
        // Navigate to file upload page
        $this->webDriver->get(getenv('APP_URL') . '/files/upload');
        
        // Find file input and upload a file
        $fileInput = $this->webDriver->findElement(WebDriverBy::name('file'));
        $fileInput->sendKeys(__DIR__ . '/test-file.txt');
        
        // Assert loading progress bar is visible
        $progressBar = $this->webDriver->wait(10)->until(
            WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::className('loading-progress'))
        );
        $this->assertTrue($progressBar->isDisplayed());
        
        // Wait for upload to complete
        $this->webDriver->wait(10)->until(
            WebDriverExpectedCondition::invisibilityOfElementLocated(WebDriverBy::className('loading-progress'))
        );
    }

    public function testBatchProcessingLoading()
    {
        // Navigate to batch processing page
        $this->webDriver->get(getenv('APP_URL') . '/batch-process');
        
        // Start batch process
        $startButton = $this->webDriver->findElement(WebDriverBy::id('start-batch'));
        $startButton->click();
        
        // Assert loading message is visible
        $loadingMessage = $this->webDriver->wait(10)->until(
            WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::className('loading-message'))
        );
        $this->assertTrue($loadingMessage->isDisplayed());
        
        // Assert progress updates
        $this->webDriver->wait(10)->until(
            WebDriverExpectedCondition::textToBePresentInElement(
                $loadingMessage,
                'Processing item'
            )
        );
        
        // Wait for batch process to complete
        $this->webDriver->wait(30)->until(
            WebDriverExpectedCondition::invisibilityOfElementLocated(WebDriverBy::className('loading-message'))
        );
    }

    public function testLoadingCancellation()
    {
        // Navigate to long-running process page
        $this->webDriver->get(getenv('APP_URL') . '/long-process');
        
        // Start process
        $startButton = $this->webDriver->findElement(WebDriverBy::id('start-process'));
        $startButton->click();
        
        // Wait for loading overlay and cancel button
        $loadingOverlay = $this->webDriver->wait(10)->until(
            WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::className('loading-overlay'))
        );
        $cancelButton = $this->webDriver->findElement(WebDriverBy::className('loading-cancel'));
        
        // Click cancel button
        $cancelButton->click();
        
        // Assert loading overlay is removed
        $this->webDriver->wait(10)->until(
            WebDriverExpectedCondition::invisibilityOfElementLocated(WebDriverBy::className('loading-overlay'))
        );
        
        // Assert cancellation message
        $message = $this->webDriver->findElement(WebDriverBy::className('process-status'));
        $this->assertStringContainsString('Process cancelled', $message->getText());
    }
} 