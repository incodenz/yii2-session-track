<?php

use yii\db\Migration;

/**
 * Class m180704_145719_page_time
 */
class m180704_145719_page_time extends Migration
{
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {
        $this->addColumn(
            'session_track_page',
            'request_time',
            $this->decimal(6,4)
        );
    }

    public function down()
    {
        $this->dropColumn(
            'session_track_page',
            'request_time'
        );
    }

}
