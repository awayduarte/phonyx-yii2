<?php

namespace common\models;

use Yii;
use yii\base\Model;

class LoginForm extends Model
{
    
    public $email;
    public $password;
    public $rememberMe = true;



    private ?User $_user = null;

   
    public function rules()
    {
        return [
            [['email', 'password'], 'required'],
            ['rememberMe', 'boolean'],
            ['password', 'validatePassword'],
        ];
    }

   
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

 
    protected function getUser(): ?User
    {
        if ($this->_user === null) {
            $this->_user = User::find()
                ->where(['email' => $this->email])  
                ->andWhere(['status' => 10])
                ->andWhere(['deleted_at' => null])
                ->one();
        }

        return $this->_user;
    }
}
