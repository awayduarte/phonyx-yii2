<?php
namespace backend\tests\functional;

use backend\tests\FunctionalTester;

class BackendAccessDeniedCest
{
    public function backendProtectedForGuests(FunctionalTester $I)
    {
       
        $I->amOnPage('/user/index');

   
        $I->seeInCurrentUrl('login');
        $I->see('Login');
    }
}
