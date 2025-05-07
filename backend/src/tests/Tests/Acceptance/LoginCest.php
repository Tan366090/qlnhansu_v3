<?php

namespace Tests\Acceptance;

class LoginCest
{
    public function _before(\AcceptanceTester $I)
    {
        $I->amOnPage('/public/login.html');
    }

    public function loginPageWorks(\AcceptanceTester $I)
    {
        $I->see('Đăng nhập - Quản lý nhân sự');
        $I->seeElement('#username');
        $I->seeElement('#password');
        $I->seeElement('#login-btn');
        
        // Check placeholder text
        $I->seeInField('#username', '');
        $I->seeInField('#password', '');
    }

    public function loginWithEmptyCredentials(\AcceptanceTester $I)
    {
        $I->click('#login-btn');
        $I->see('Vui lòng nhập tên đăng nhập');
    }

    public function loginWithEmptyPassword(\AcceptanceTester $I)
    {
        $I->fillField('#username', 'admin');
        $I->click('#login-btn');
        $I->see('Vui lòng nhập mật khẩu');
    }

    public function loginWithEmptyUsername(\AcceptanceTester $I)
    {
        $I->fillField('#password', 'password');
        $I->click('#login-btn');
        $I->see('Vui lòng nhập tên đăng nhập');
    }

    public function loginSuccessfully(\AcceptanceTester $I)
    {
        $I->fillField('#username', 'admin');
        $I->fillField('#password', 'admin123');
        $I->click('#login-btn');
        $I->waitForElement('#user-profile', 10);
        $I->seeCurrentUrlMatches('~/dashboard.php~');
    }

    public function loginWithInvalidCredentials(\AcceptanceTester $I)
    {
        $I->fillField('#username', 'wrong');
        $I->fillField('#password', 'wrong');
        $I->click('#login-btn');
        $I->see('Tên đăng nhập hoặc mật khẩu không đúng');
    }

    public function checkLoadingState(\AcceptanceTester $I)
    {
        $I->fillField('#username', 'admin');
        $I->fillField('#password', 'admin123');
        $I->click('#login-btn');
        $I->seeElement('.loading-spinner');
        $I->waitForElementNotVisible('.loading-spinner', 10);
    }

    public function checkResponsiveDesign(\AcceptanceTester $I)
    {
        $I->resizeWindow(375, 667); // Mobile
        $I->seeElement('.login-form');
        
        $I->resizeWindow(768, 1024); // Tablet
        $I->seeElement('.login-form');
        
        $I->resizeWindow(1920, 1080); // Desktop
        $I->seeElement('.login-form');
    }
} 