<?php

namespace frontend\models;

use Yii;
use yii\base\Model;
use common\models\User;

class LoginForm extends Model
{
    public $login;      
    public $password;
    public $rememberMe = true;

    private $_user = false;

    public function rules()
    {
        return [
            [['login', 'password'], 'required'],
            [['login'], 'string', 'max' => 255],
            ['rememberMe', 'boolean'],
            ['password', 'validatePassword'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'login' => 'Username ou Email',
            'password' => 'Palavra-passe',
            'rememberMe' => 'Lembrar-me',
        ];
    }

    public function validatePassword($attribute, $params)
    {
        if ($this->hasErrors()) return;

        $user = $this->getUser();
        if (!$user || !$user->validatePassword($this->password)) {
            $this->addError($attribute, 'Username/Email ou palavra-passe inválidos.');
        }
    }

    public function login()
    {
        if ($this->validate()) {
            return Yii::$app->user->login($this->getUser(), $this->rememberMe ? 3600 * 24 * 30 : 0);
        }
        return false;
    }

    protected function getUser()
    {
        if ($this->_user === false) {
            $value = trim((string)$this->login);
            $this->_user = User::findByUsernameOrEmail($value);
        }

        return $this->_user;
    }
}
