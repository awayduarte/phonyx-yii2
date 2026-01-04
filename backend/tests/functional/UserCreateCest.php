<?php
namespace backend\tests\functional;

use backend\tests\FunctionalTester;

class UserCreateCest
{
    public function createUser(FunctionalTester $I)
    {
       
        $I->amOnPage('/site/login');
        $I->submitForm('#login-form', [
            'LoginForm[email]' => 'admin@phonyx.com',
            'LoginForm[password]' => '123456',
        ]);
        
        // Verificar se o login foi bem-sucedido (não deve estar mais na página de login)
        $I->dontSeeInCurrentUrl('login');

      
        $I->amOnPage('/user/create');
        $I->see('Create');

        $username = 'user_' . time();

       
        $I->fillField('User[username]', $username);
        $I->fillField('User[email]', $username . '@test.com');

        

        $I->click('Save');

   
        $I->amOnPage('/user/index');
        $I->see($username);
    }
}
