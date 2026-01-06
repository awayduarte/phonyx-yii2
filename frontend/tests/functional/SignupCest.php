<?php

namespace frontend\tests\functional;

use frontend\tests\FunctionalTester;
use common\models\User;

class SignupCest
{
    protected $formId = '#form-signup';

    public function _before(FunctionalTester $I)
    {
        $I->amOnRoute('site/signup');
    }

    public function signupWithEmptyFields(FunctionalTester $I)
    {
        $I->see('Criar Conta', 'h1');

        $I->submitForm($this->formId, []);

        // Mensagens REAIS do Yii2 (em inglês)
        $I->see('Username cannot be blank.');
        $I->see('Email cannot be blank.');
        $I->see('Password cannot be blank.');
        $I->see('Confirm Password cannot be blank.');
    }

    public function signupWithWrongEmail(FunctionalTester $I)
    {
        $I->submitForm($this->formId, [
            'SignupForm[username]' => 'tester',
            'SignupForm[email]' => 'ttttt',
            'SignupForm[password]' => 'tester_password',
            'SignupForm[confirm_password]' => 'tester_password',
        ]);

        // Não aparecem erros de campos vazios
        $I->dontSee('Username cannot be blank.');
        $I->dontSee('Password cannot be blank.');
        $I->dontSee('Confirm Password cannot be blank.');

        // Erro real do Yii2
        $I->see('Email is not a valid email address.');
    }

    public function signupSuccessfully(FunctionalTester $I)
    {
        $I->submitForm($this->formId, [
            'SignupForm[username]' => 'tester',
            'SignupForm[email]' => 'tester.email@example.com',
            'SignupForm[password]' => 'tester_password',
            'SignupForm[confirm_password]' => 'tester_password',
        ]);

        // O teu signup cria o user com status = 10
        $I->seeRecord(User::class, [
            'username' => 'tester',
            'email' => 'tester.email@example.com',
            'status' => 10,
        ]);

        // Após signup, o user é redirecionado para a HOME
        $I->see('PHONYX'); // texto que existe na homepage
    }
}
