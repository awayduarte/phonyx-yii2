<?php

use yii\db\Migration;

class m251115_190305_create_initial_schema extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m251115_190305_create_initial_schema cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m251115_190305_create_initial_schema cannot be reverted.\n";

        return false;
    }
    */
}
