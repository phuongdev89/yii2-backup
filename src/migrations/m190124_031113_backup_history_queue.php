<?php

use yii\db\Migration;
use yii\db\Schema;

class m190124_031113_backup_history_queue extends Migration {

	public function safeUp() {
		$tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
		$this->createTable('{{%backup_history_queue}}', [
			'id'                => Schema::TYPE_PK . '',
			'backup_history_id' => Schema::TYPE_INTEGER . '(11) NOT NULL',
			'status'            => Schema::TYPE_SMALLINT . '(1) NOT NULL DEFAULT "0"',
			'description'       => Schema::TYPE_TEXT . ' NOT NULL',
		], $tableOptions);
		$this->createIndex('backup_history_id', '{{%backup_history_queue}}', 'backup_history_id', 1);
	}

	public function safeDown() {
		$this->dropIndex('backup_history_id', '{{%backup_history_queue}}');
		$this->dropTable('{{%backup_history_queue}}');
	}
}
