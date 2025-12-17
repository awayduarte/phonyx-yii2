<?php

use yii\db\Migration;

class m251212_192624_init_rbac_roles extends Migration
{
    public function safeUp()
    {
        $auth = Yii::$app->authManager;

        // ROLES
        $admin  = $auth->createRole('admin');
        $artist = $auth->createRole('artist');
        $user   = $auth->createRole('user');

        $auth->add($admin);
        $auth->add($artist);
        $auth->add($user);

        // PERMISSIONS
        $manageBackend = $auth->createPermission('manageBackend');
        $manageBackend->description = 'Full backend access';
        $auth->add($manageBackend);

        $manageArtist = $auth->createPermission('manageArtistPanel');
        $manageArtist->description = 'Artist dashboard access';
        $auth->add($manageArtist);

        $basicUser = $auth->createPermission('basicUser');
        $basicUser->description = 'Basic user access';
        $auth->add($basicUser);

        // ASSIGN PERMISSIONS TO ROLES
        $auth->addChild($admin, $manageBackend);
        $auth->addChild($artist, $manageArtist);
        $auth->addChild($user, $basicUser);

        // ASSIGN ROLES TO INITIAL USERS
        $auth->assign($admin, 1);
        $auth->assign($artist, 2);
        $auth->assign($user, 3);
    }

    public function safeDown()
    {
        echo "RBAC init cannot be reverted.\n";
        return false;
    }
}
