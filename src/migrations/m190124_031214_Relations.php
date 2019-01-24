<?php

use yii\db\Migration;

class m190124_031214_Relations extends Migration {

	public function safeUp() {
		$this->addForeignKey('fk_backup_history_queue_backup_history_id', '{{%backup_history_queue}}', 'backup_history_id', 'backup_history', 'id', 'CASCADE', 'CASCADE');
	}

	public function safeDown() {

		$this->dropForeignKey('fk_backup_history_queue_backup_history_id', '{{%backup_history_queue}}');
	}
}
