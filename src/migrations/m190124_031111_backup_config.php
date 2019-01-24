<?php

use yii\db\Migration;
use yii\db\Schema;

class m190124_031111_backup_config extends Migration {

	public function safeUp() {
		$tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
		$this->createTable('{{%backup_config}}', [
			'id'    => Schema::TYPE_PK . '',
			'name'  => Schema::TYPE_STRING . '(255) NOT NULL',
			'value' => Schema::TYPE_TEXT . ' NULL',
			'type'  => "enum('DATABASE','DIRECTORY','TRANSPORT')" . ' NOT NULL DEFAULT "DATABASE"',
		], $tableOptions);
	}

	public function safeDown() {
		$this->dropTable('{{%backup_config}}');
	}
}
