<?php

namespace frontend\models;

use Yii;
use yii\base\Model;
use common\models\User;

class SignupForm extends Model
{
    // form fields
    public $username;
    public $email;
    public $password;
    public $confirm_password;

    // validation rules
    public function rules()
    {
        return [
            [['username', 'email', 'password', 'confirm_password'], 'required'],

            ['username', 'string', 'min' => 2, 'max' => 100],
            ['email', 'email'],
            ['password', 'string', 'min' => 6],
            ['confirm_password', 'compare', 'compareAttribute' => 'password', 'message' => 'Passwords do not match.'],

            ['username', 'unique', 'targetClass' => User::class],
            ['email', 'unique', 'targetClass' => User::class],
        ];
    }

    // create user account
    public function signup()
    {
        if (!$this->validate()) {
            return null;
        }

        $user = new User();
        $user->username = $this->username;
        $user->email = $this->email;
        $user->password = $this->password;
        $user->status = 10;
        $user->role = User::ROLE_USER;

        if (!$user->save()) {
            return null;
        }

        // auto login after signup
        Yii::$app->user->login($user);

        return $user;
    }
}
