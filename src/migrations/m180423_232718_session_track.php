<?php

use yii\db\Migration;

/**
 * Class m180423_232718_session_track
 */
class m180423_232718_session_track extends Migration
{
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {
        $this->createTable(
            'session_track',
            [
                'id' => $this->primaryKey(),
                'user_id' => $this->integer(),
                'session_id' => $this->string(64),
                'ip_address' => $this->string(32),
                'created_at' => $this->dateTime(),
                'updated_at' => $this->dateTime(),
            ]
        );
        $this->createIndex(
            'uni_sessionkey',
            'session_track',
            'session_id'
        );
        $this->createIndex(
            'idx_userid',
            'session_track',
            'user_id'
        );
    }

    public function down()
    {
        $this->dropTable(
            'session_track'
        );
    }

}
