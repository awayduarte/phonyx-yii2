<?php

use yii\db\Migration;

class m251117_153040_init_rbac_roles extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $auth = Yii::$app->authManager;

        // Criar ROLES
        $admin = $auth->createRole('admin');
        $admin->description = 'Administrador';
        $auth->add($admin);

        $artist = $auth->createRole('artist');
        $artist->description = 'Artista';
        $auth->add($artist);

        $user = $auth->createRole('user');
        $user->description = 'Utilizador';
        $auth->add($user);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $auth = Yii::$app->authManager;

        $auth->remove($auth->getRole('admin'));
        $auth->remove($auth->getRole('artist'));
        $auth->remove($auth->getRole('user'));
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m251117_153040_init_rbac_roles cannot be reverted.\n";

        return false;
    }
    */
}
