<?php

namespace frontend\tests\unit;

use common\models\User;

class UserValidationTest extends \Codeception\Test\Unit
{
    public function testInvalidEmail()
    {
        $user = new User();
        $user->email = 'email_invalido';
        $this->assertFalse($user->validate(['email']));
    }
}
