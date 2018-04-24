<?php

use yii\db\Migration;

/**
 * Class m180423_232719_session_track_page
 */
class m180423_232719_session_track_page extends Migration
{
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {
        $this->createTable(
            'session_track_page',
            [
                'id' => $this->primaryKey(),
                'session_track_id' => $this->integer(),
                'created_at' => $this->dateTime(),
                'request_type' => $this->string(6),
                'request_path' => $this->string(),
                'request_params' => $this->text(),
            ]
        );
        $this->createIndex(
            'idx_sesstrackid',
            'session_track_page',
            'session_track_id'
        );
    }

    public function down()
    {
        $this->dropTable(
            'session_track_page'
        );
    }

}
