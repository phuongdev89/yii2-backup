<?php
/**
 * Created by PhpStorm.
 * User: lephuong
 * Date: 9/26/16
 * Time: 9:43 PM
 */

namespace phuongdev89\backup\commands;

use PharData;
use phuongdev89\backup\components\MysqlBackup;
use phuongdev89\backup\helpers\StringHelper;
use phuongdev89\backup\models\BackupConfig;
use phuongdev89\backup\models\BackupHistory;
use phuongdev89\backup\Module;
use Swift_TransportException;
use Throwable;
use Yii;
use yii\base\Action;
use yii\base\InvalidConfigException;
use yii\console\Controller;
use yii\console\Exception;
use yii\db\StaleObjectException;
use yii\helpers\Console;
use yii\helpers\Json;

/**
 * @property Module $this->module
 */
class BackupController extends Controller
{

    /**@var Module */
    public $module;

    /**
     * @param $id
     * @param $module
     * @param array $config
     */
    public function __construct($id, $module, array $config = [])
    {
        $this->module = Yii::$app->getModule('backup');
        parent::__construct($id, $this->module, $config);
    }

    /**
     * @param Action $action
     *
     * @return bool
     */
    public function beforeAction($action)
    {
        $this->module->clean();
        return parent::beforeAction($action);
    }

    /**
     * Run daemon based on "for cycle"
     *
     * @param int $loopLimit
     * @param int $chunkSize
     *
     * @throws \Exception
     * @throws Throwable
     */
    public function actionDaemon($loopLimit = 1000, $chunkSize = 100)
    {
        set_time_limit(0);
        for ($i = 1; $i < $loopLimit; $i++) {
            $this->runChunk($chunkSize);
            sleep(1);
        }
    }

    /**
     * Tries to run sendOne $chunkSize times
     *
     * @param int $chunkSize
     *
     * @return bool
     * @throws \Exception
     * @throws Throwable
     */
    protected function runChunk($chunkSize = 100)
    {
        for ($i = 0; $i < $chunkSize; $i++) {
            $r = $this->actionRunOne();
            if (!$r) {
                return false;
            }
        }
        return true;
    }

    /**
     * @throws \yii\base\Exception
     * @throws \yii\db\Exception
     * @throws Throwable
     */
    public function actionRunOne()
    {
        $this->clean();
        $lastBackupHistory = BackupHistory::find()->orderBy(['created_at' => SORT_DESC])->one();
        if ($lastBackupHistory !== null) {
            if (time() - $lastBackupHistory->created_at < BackupConfig::getCronjob('runEvery')) {
                exit(0);
            }
        }
        $this->database();
        $this->directory();
    }

    /**
     * Backup directory
     *
     * @throws Exception
     * @throws \yii\base\Exception
     * @throws InvalidConfigException
     */
    protected function directory()
    {
        if ($this->module->directories != null) {
            $config = BackupConfig::findOne(['name' => 'directory_config']);
            if ($config === null) {
                throw new Exception('Config has not been executed.');
            }
            $paths = Json::decode($config->value);
            foreach ($paths as $path => $isEnable) {
                $folder = Yii::getAlias($path);
                if (file_exists($folder)) {
                    $archiveFile = BackupConfig::getCronjob('backupPath') . DIRECTORY_SEPARATOR . Module::TYPE_DIRECTORY . '_' . StringHelper::removeSign($path) . '_' . date('Y.m.d_H.i.s') . '.tar';
                    $backupHistory = new BackupHistory();
                    $backupHistory->name = basename($archiveFile);
                    $backupHistory->type = BackupHistory::TYPE_DIRECTORY;
                    $backupHistory->status = BackupHistory::STATUS_DRAFT;
                    $backupHistory->data = $archiveFile;
                    $archive = new PharData($archiveFile);
                    $archive->buildFromDirectory($folder);
                    if ((int)BackupConfig::getConfig('email_enable') == 1) {
                        if (filesize($archiveFile) < 20 * 1024 * 1024) {
                            try {
                                $this->module->mail->setFile($archiveFile)->setType(Module::TYPE_DIRECTORY)->send();
                                $backupHistory->mail_status = BackupHistory::STATUS_DONE;
                            } catch (Swift_TransportException $e) {
                                Console::output('Can not send email. ' . $e->getMessage());
                            }
                        } else {
                            Console::output('Can not send file, file size is too big!');
                        }
                    }
                    if ((int)BackupConfig::getConfig('ftp_enable') == 1) {
                        $this->module->ftp->setFile($archiveFile)->push();
                        $backupHistory->ftp_status = BackupHistory::STATUS_DONE;
                    }
                    $backupHistory->size = filesize($archiveFile);
                    $backupHistory->status = BackupHistory::STATUS_DONE;
                    $backupHistory->save();
                    Console::output('Folder "' . $folder . '" backed up!');
                } else {
                    Console::output('Folder "' . $folder . '" does not exists');
                }
            }
        }
    }

    /**
     * Backup db
     *
     * @throws \yii\base\Exception
     * @throws \yii\db\Exception
     */
    protected function database()
    {
        if ($this->module->databases != null) {
            $config = BackupConfig::findOne(['name' => 'database_config']);
            if ($config === null) {
                throw new Exception('Config has not been executed.');
            }
            $dbs = Json::decode($config->value);
            Console::output('Starting backup database.');
            foreach ($dbs as $db => $tables) {
                $backupHistory = new BackupHistory();
                $backupHistory->type = BackupHistory::TYPE_DATABASE;
                $backupHistory->status = BackupHistory::STATUS_DRAFT;
                $totalProgress = count($tables, true) - count($tables);
                Console::output('Backing up `' . $db . '`');
                Console::startProgress(0, $totalProgress);
                $sql = new MysqlBackup(['db' => $db]);
                if (!$sql->StartBackup()) {
                    die;
                }
                $progress = 0;
                foreach ($tables as $tableName => $export) {
                    if (in_array($tableName, $sql->getTables())) {
                        if (isset($export['schema']) && $export['schema'] == 1) {
                            $sql->getColumns($tableName);
                            $progress++;
                            Console::updateProgress($progress, $totalProgress);
                        }
                        if (isset($export['data']) && $export['data'] == 1) {
                            $sql->getData($tableName);
                            $progress++;
                            Console::updateProgress($progress, $totalProgress);
                        }
                    }
                }
                $sqlFile = $sql->EndBackup();
                $backupHistory->data = Json::encode($tables);
                $backupHistory->status = BackupHistory::STATUS_DONE;
                $backupHistory->name = basename($sqlFile);
                $backupHistory->size = filesize($sqlFile);
                Console::updateProgress($totalProgress, $totalProgress);
                Console::endProgress(true, false);
                Console::output('Backed up `' . $db . '`');
                if ((int)BackupConfig::getConfig('email_enable') == 1) {
                    if (filesize($sqlFile) < 20 * 1024 * 1024) {
                        try {
                            $this->module->mailTransport->setFile($sqlFile)->setType(Module::TYPE_DATABASE)->send();
                            $backupHistory->mail_status = BackupHistory::STATUS_DONE;
                            Console::output('Sent `' . $db . '` to email ' . BackupConfig::getConfig('email_to'));
                        } catch (Swift_TransportException $e) {
                            Console::output('Can not send email. ' . $e->getMessage());
                        }
                    } else {
                        Console::output('Can not attach filesize > 20MB.');
                    }
                }
                if ((int)BackupConfig::getConfig('ftp_enable') == 1) {
                    $this->module->ftpTransport->setFile($sqlFile)->push();
                    $backupHistory->ftp_status = BackupHistory::STATUS_DONE;
                    Console::output('Sent `' . $db . '` to FTP ' . BackupConfig::getConfig('ftp_host'));
                }
                if ((int)BackupConfig::getConfig('s3_enable') == 1) {
                    $this->module->s3Transport->setFile($sqlFile)->push();
                    $backupHistory->s3_status = BackupHistory::STATUS_DONE;
                    Console::output('Sent `' . $db . '` to S3 ' . BackupConfig::getConfig('s3_endpoint'));
                }
                $backupHistory->save();
            }
        } else {
            echo 'DB Backup not enable!' . PHP_EOL;
        }
    }

    /**
     * Remove files was created x days ago
     *
     * @throws Throwable
     * @throws StaleObjectException
     */
    protected function clean()
    {
        $backups = BackupHistory::find()->andWhere([
            '<',
            'created_at',
            (time() - (BackupConfig::getCronjob('cleanAfterDays') * 3600 * 24)),
        ])->all();
        foreach ($backups as $backup) {
            $backup->delete();
        }
    }
}
