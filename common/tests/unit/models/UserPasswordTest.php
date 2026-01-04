<?php
namespace common\tests\unit\models;

use Codeception\Test\Unit;
use common\models\User;

class UserPasswordTest extends Unit
{
    public function testSetAndValidatePassword()
    {
        $user = new User();
        $user->setPassword('Password123!');

        $this->assertNotEmpty($user->password_hash, 'Password hash should be generated');
        $this->assertTrue($user->validatePassword('Password123!'), 'Password should validate');
        $this->assertFalse($user->validatePassword('errada'), 'Wrong password should not validate');
    }
}
