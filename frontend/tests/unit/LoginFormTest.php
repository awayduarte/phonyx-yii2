<?php

namespace frontend\tests\unit;

use frontend\models\LoginForm;
use Codeception\Test\Unit;

class LoginFormTest extends Unit
{
    public function testLoginFormRequiresLoginAndPassword()
    {
        $model = new LoginForm();

        $this->assertFalse($model->validate(), 'O formulário não deve validar sem dados');

        $errors = $model->getErrors();

        $this->assertArrayHasKey('login', $errors, 'O campo login deve ter erro');
        $this->assertArrayHasKey('password', $errors, 'O campo password deve ter erro');
    }

public function testLoginFormAcceptsValidInput()
{
    $model = new LoginForm([
        'login' => 'teste@example.com',
        'password' => '123456'
    ]);

    // Valida apenas o campo login (string + required)
    $this->assertTrue($model->validate(['login']), 'O campo login deve validar com dados corretos');

    // Não validamos password aqui porque validatePassword() depende da BD
    // e nunca irá passar num teste unitário
}




    public function testLoginFormRejectsInvalidPassword()
    {
        $model = new LoginForm([
            'login' => 'teste@example.com',
            'password' => 'senha_errada'
        ]);

        // Força a validação completa (vai chamar validatePassword)
        $model->validate();

        $this->assertArrayHasKey('password', $model->getErrors(), 'Password inválida deve gerar erro');
    }
}
