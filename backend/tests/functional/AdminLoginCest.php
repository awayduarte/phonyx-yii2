<?php

namespace backend\tests\functional;

use backend\tests\FunctionalTester;

class AdminLoginCest
{
    public function loginAsAdmin(FunctionalTester $I)
    {
        $I->amOnPage('/site/login');

        $I->submitForm('#login-form', [
            'LoginForm[email]' => 'admin@phonyx.com',
            'LoginForm[password]' => '123456',
        ]);

        $I->see('Dashboard');
    }
}
