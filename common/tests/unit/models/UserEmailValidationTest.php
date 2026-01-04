<?php

namespace common\tests\unit\models;

use common\models\User;

class UserEmailValidationTest extends \Codeception\Test\Unit
{
    public function testEmailRequiredAndValid()
{
    $user = new User();
    $user->setScenario('create'); // scenario


    $user->username = 'user_' . uniqid();
    $user->password = '123456';
    $user->role = 'user';
    $user->status = 10;


    $user->email = 'invalid-email';
    $this->assertFalse($user->validate(['email']));


    $user->email = 'valid_' . uniqid() . '@example.com';
    $this->assertTrue($user->validate(['email']));
}

}
