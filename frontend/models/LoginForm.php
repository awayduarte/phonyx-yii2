<?php

namespace frontend\models;

use Yii;
use yii\base\Model;
use common\models\User;

class LoginForm extends Model
{
    // form fields
    public $email;
    public $password;
    public $rememberMe = true;

    // cached user
    private ?User $_user = null;

    public function rules()
    {
        return [
            [['email', 'password'], 'required'],
            ['email', 'email'],
            ['rememberMe', 'boolean'],
            ['password', 'validatePassword'],
        ];
    }

    // validate password
    public function validatePassword($attribute, $params)
    {
        if ($this->hasErrors()) {
            return;
        }

        $user = $this->getUser();

        if (!$user || !$user->validatePassword($this->password)) {
            $this->addError($attribute, 'Incorrect email or password.');
        }
    }

    // login user
    public function login()
    {
        if (!$this->validate()) {
            return false;
        }

        return Yii::$app->user->login(
            $this->getUser(),
            $this->rememberMe ? 3600 * 24 * 30 : 0
        );
    }

    // get active user from model
    protected function getUser(): ?User
    {
        if ($this->_user === null) {
            $this->_user = User::findByEmail($this->email);
        }

        return $this->_user;
    }
}
