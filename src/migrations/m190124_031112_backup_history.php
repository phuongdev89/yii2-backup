<?php

use yii\db\Migration;
use yii\db\Schema;

class m190124_031112_backup_history extends Migration {

	public function safeUp() {
		$tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
		$this->createTable('{{%backup_history}}', [
			'id'          => Schema::TYPE_PK . '',
			'name'        => Schema::TYPE_STRING . '(255) NOT NULL',
			'size'        => Schema::TYPE_INTEGER . '(11) NOT NULL DEFAULT "0"',
			'created_at'  => Schema::TYPE_INTEGER . '(11)',
			'updated_at'  => Schema::TYPE_INTEGER . '(11)',
			'type'        => "enum('DATABASE','DIRECTORY')" . ' NOT NULL',
			'data'        => Schema::TYPE_TEXT . ' NULL',
			'status'      => Schema::TYPE_SMALLINT . '(1) NOT NULL DEFAULT "0"',
			'mail_status' => Schema::TYPE_SMALLINT . '(1) NOT NULL DEFAULT "0"',
			'ftp_status'  => Schema::TYPE_SMALLINT . '(1) DEFAULT "0"',
			's3_status'   => Schema::TYPE_SMALLINT . '(1) NOT NULL DEFAULT "0"',
		], $tableOptions);
	}

	public function safeDown() {
		$this->dropTable('{{%backup_history}}');
	}
}
