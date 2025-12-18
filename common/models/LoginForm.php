<?php

namespace common\models;

use Yii;
use yii\base\Model;

class LoginForm extends Model
{
    // form fields
    public $email;
    public $username;
    public $password;
    public $rememberMe = true;


    // cached user
    private ?User $_user = null;

    // validation rules
    public function rules()
    {
        return [
            [['email', 'password'], 'required'],
            ['email', 'email'],
            ['rememberMe', 'boolean'],
            ['password', 'validatePassword'],
        ];
    }

    // password validation
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

    // find active and non-deleted user
    protected function getUser(): ?User
    {
        if ($this->_user === null) {
            $this->_user = User::find()
                ->where(['email' => $this->email])
                ->andWhere(['username' => $this->username])
                ->andWhere(['status' => 10])
                ->andWhere(['deleted_at' => null])
                ->one();
        }

        return $this->_user;
    }
}
