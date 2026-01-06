<?php

namespace frontend\tests\functional;

use frontend\tests\FunctionalTester;

class LoginCest
{
    public function loginWrongPassword(FunctionalTester $I)
    {
        $I->amOnPage('/site/login');

    $I->submitForm('#loginForm', [
        'LoginForm[email]' => 'naoexiste@phonyx.com',
        'LoginForm[password]' => '123456',
    ]);


        $I->see('Incorrect email or password.');
    }
}
