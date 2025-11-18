<?php

namespace frontend\models;

use yii\base\Model;
use common\models\User;
use Yii;

class RegisterForm extends Model
{
    public $email;
    public $username;
    public $password;
    public $confirm_password;

    public function rules()
    {
        return [
            [['email', 'username', 'password', 'confirm_password'], 'required'],
            ['email', 'email'],
            ['email', 'string', 'max' => 255],
            ['username', 'string', 'max' => 120],
            ['email', 'unique', 'targetClass' => User::class, 'message' => 'Este email já está registado.'],
            ['username', 'unique', 'targetClass' => User::class, 'message' => 'Este username já está registado.'],

            ['password', 'string', 'min' => 6],

            ['confirm_password', 'compare', 'compareAttribute' => 'password', 'message' => 'As passwords não coincidem.'],
        ];
    }

    public function register()
    {
        if (!$this->validate()) {
            return null;
        }

        $user = new User();
        $user->email = $this->email;
        $user->username = $this->username;
        $user->password_plain = $this->password;
        $user->status = 0; // ACTIVE
        $user->role = 'user';

        if ($user->save()) {
            return $user;
        }

        return null;
    }
}
