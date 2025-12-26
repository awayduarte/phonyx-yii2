<?php

namespace frontend\models;

use yii\base\Model;
use common\models\User;
use yii\web\UploadedFile;


class EditProfileForm extends Model
{
    /** @var UploadedFile|null */
    public $profileFile;

    public $username;
    public $email;

    private User $_user;

    public function __construct(User $user, $config = [])
    {
        $this->_user = $user;

        $this->username = $user->username;
        $this->email = $user->email;

        parent::__construct($config);
    }

    public function rules()
    {
        return [
            [['username', 'email'], 'required'],
            [['username'], 'string', 'min' => 3, 'max' => 255],
            ['email', 'email'],
            ['email', 'string', 'max' => 255],

            [
                'username',
                'unique',
                'targetClass' => User::class,
                'filter' => ['not', ['id' => $this->_user->id]],
            ],
            [
                'email',
                'unique',
                'targetClass' => User::class,
                'filter' => ['not', ['id' => $this->_user->id]],
            ],
        ];
    }

    public function attributeLabels()
    {
        return [
            'username' => 'Nome de utilizador',
            'email' => 'Email',
        ];
    }

    public function applyToUser(User $user): void
    {
        $user->username = $this->username;
        $user->email = $this->email;
    }
}
