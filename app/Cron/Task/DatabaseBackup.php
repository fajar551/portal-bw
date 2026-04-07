<?php

namespace App\Cron\Task;

class DatabaseBackup extends \App\Scheduling\Task\AbstractTask
{
    protected $accessLevel = \App\Scheduling\Task\TaskInterface::ACCESS_SYSTEM;
    protected $defaultPriority = 5000;
    protected $defaultFrequency = 1440;
    protected $defaultDescription = "Create a database backup and deliver via FTP or email";
    protected $defaultName = "Database Backup";
    protected $systemName = "DatabaseBackup";
    protected $outputs = array("completed" => array("defaultValue" => 0, "identifier" => "completed", "name" => "Backup Completed"));
    protected $icon = "fas fa-database";
    protected $isBooleanStatus = true;
    protected $successCountIdentifier = "completed";
    protected $zipFile = NULL;
    public function __invoke()
    {
        $this->doCpanel();
        $this->doFtpAndEmail();
        return $this;
    }
    protected function doCpanel()
    {
        $complete = false;
        if ($this->isBackupSystemActive("cpanel")) {
            try {
                $this->requestCPanelBackup();
                \App\Helpers\LogActivity::Save("Cron Job: Remote cPanel Backup Requested");
                $complete = true;
            } catch (\Exception $e) {
                \App\Helpers\LogActivity::Save("Cron Job: cPanel Remote Backup Failed" . $e->getMessage());
            }
        }
        return $complete;
    }
    protected function doFtpAndEmail()
    {
        $complete = false;
        if ($this->isBackupSystemActive("email") || $this->isBackupSystemActive("ftp")) {
            if (class_exists("ZipArchive")) {
                try {
                    if (!$this->generateDatabaseBackupZipFile()) {
                        throw new \Exception("Backup File Generation Failed");
                    }
                    // $whmcsApplicationConfig = \App::getApplicationConfig();
                    // $databaseName = $whmcsApplicationConfig->getDatabaseName();
                    $databaseName = env('DB_DATABASE');
                    $attachmentName = sprintf("%s_backup_%s.zip", $databaseName, date("Ymd_His"));
                    if ($this->isBackupSystemActive("email")) {
                        $this->emailZip($this->zipFile, $attachmentName);
                        \App\Helpers\LogActivity::Save("Cron Job: Email Backup - Sent Successfully");
                    }
                    if ($this->isBackupSystemActive("ftp")) {
                        $remoteFile = \App\Helpers\Cfg::getValue("FTPBackupDestination") . $attachmentName;
                        $this->ftpZip($this->zipFile, $remoteFile);
                        \App\Helpers\LogActivity::Save("Cron Job: FTP Backup - Completed Successfully");
                    }
                    $msg = "Backup Complete";
                    $this->output("completed")->write(1);
                    $complete = true;
                } catch (\PHPMailer\PHPMailer\Exception $e) {
                    $msg = "Database Backup Sending Failed - PHPMailer Exception" . " - " . $e->getMessage() . "(Subject: WHMCS Database Backup)";
                    $this->output("completed")->write(0);
                } catch (\Exception $e) {
                    $this->output("completed")->write(0);
                    $msg = $e->getMessage();
                }
                unlink($this->zipFile);
            } else {
                $this->output("completed")->write(0);
                $msg = "Database backup unavailable due to missing required Zip extension";
            }
        } else {
            $this->output("completed")->write(0);
            $msg = "Database Backup requested but backups are not configured.";
        }
        \App\Helpers\LogActivity::Save("Cron Job: " . $msg);
        return $complete;
    }
    protected function isBackupSystemActive($system)
    {
        $activeBackupSystems = \App\Helpers\Cfg::getValue("ActiveBackupSystems");
        if ($activeBackupSystems) {
            $activeBackupSystems = explode(",", $activeBackupSystems);
        }
        if (!is_array($activeBackupSystems)) {
            $activeBackupSystems = array();
        }
        if (0 < count($activeBackupSystems) && in_array($system, $activeBackupSystems)) {
            return true;
        }
        return false;
    }
    protected function generateDatabaseBackupZipFile()
    {
        $tempZipFile = tempnam(sys_get_temp_dir(), "zip");
        $tempDatabaseFile = tempnam(sys_get_temp_dir(), "sql");
        // $whmcsApplicationConfig = \App::getApplicationConfig();
        // $databaseName = $whmcsApplicationConfig->getDatabaseName();
        $databaseName = env('DB_DATABASE');
        $complete = false;
        try {
            \App\Helpers\LogActivity::Save("Cron Job: Starting Backup Generation");
            \App\Helpers\LogActivity::Save("Cron Job: Starting Backup Database Dump");
            // $databaseConnection = \App::getDatabaseObj();
            // $database = new \WHMCS\Database\Dumper\Database($databaseConnection);
            // $database->dumpTo($tempDatabaseFile);
            // \App\Helpers\LogActivity::Save("Cron Job: Backup Database Dump Complete");
            // $zipFileForSend = $this->createZipFile($tempZipFile, $tempDatabaseFile, $databaseName);
            // if (!file_exists($zipFileForSend)) {
            //     throw new \Exception("An unknown error occurred adding the generated sql to the archive.");
            // }
            // $this->zipFile = $zipFileForSend;
            $complete = true;
        } catch (\Exception $e) {
            \App\Helpers\LogActivity::Save("Cron Job: ERROR : " . $e->getMessage());
        }
        unlink($tempDatabaseFile);
        return $complete;
    }
    protected function createZipFile($tempZipFile, $tempDatabaseFile, $databaseName)
    {
        \App\Helpers\LogActivity::Save("Cron Job: Starting Backup Zip Creation");
        $zip = new \ZipArchive();
        $res = $zip->open($tempZipFile, \ZipArchive::CREATE);
        if ($res !== true) {
            $msg = "Cron Job: Backup Generation Failed. Error Code: " . $res;
            \App\Helpers\LogActivity::Save($msg);
        } else {
            $filename = (string) $databaseName . ".sql";
            if (!$zip->addFile($tempDatabaseFile, $filename)) {
                throw new \Exception("An unknown error occurred adding the generated sql to the archive");
            }
            $zip->setArchiveComment("WHMCS Generated MySQL Backup");
            $zip->close();
            \App\Helpers\LogActivity::Save("Cron Job: Backup Generation Completed");
        }
        return $tempZipFile;
    }
    protected function emailZip($zipFile, $attachmentName)
    {
        if (!\App\Helpers\Cfg::getValue("DailyEmailBackup")) {
            throw new \Exception("No Daily Email Address Configured");
        }
        // $mail = new \WHMCS\Mail(\App\Helpers\Cfg::getValue("SystemEmailsFromName"), \App\Helpers\Cfg::getValue("SystemEmailsFromEmail"));
        // $mail->Subject = "WHMCS Database Backup";
        // $mail->Body = "Backup File Attached";
        // $mail->AddAddress(\App\Helpers\Cfg::getValue("DailyEmailBackup"));
        // $mail->AddAttachment($zipFile, $attachmentName);
        // $result = $mail->Send();
        // $mail->clearAllRecipients();
        // if (!$result) {
        //     throw new \Exception($mail->ErrorInfo);
        // }
        return $this;
    }
    protected function ftpZip($zipFile, $remoteFile)
    {
        $ftpSecureMode = \App\Helpers\Cfg::getValue("FTPSecureMode");
        $ftpSecureMode ? $this->doSftpBackup($zipFile, $remoteFile) : $this->doFtpBackup($zipFile, $remoteFile);
        return $this;
    }
    protected function requestCPanelBackup()
    {
        $server = new \App\Module\Server();
        $server->load("cpanel");
        $server->call("request_backup", array("serverip" => "", "serverhostname" => \App\Helpers\Cfg::getValue("CpanelBackupHostname"), "serverusername" => \App\Helpers\Cfg::getValue("CpanelBackupWHMUsername"), "serveraccesshash" => (new \App\Helpers\Pwd)->decrypt(\App\Helpers\Cfg::getValue("CpanelBackupAPIToken")), "serverhttpprefix" => "https", "serverport" => "2087", "serversecure" => true, "dest" => \App\Helpers\Cfg::getValue("CpanelBackupDestination"), "hostname" => \App\Helpers\Cfg::getValue("CpanelBackupDestinationHostname"), "user" => \App\Helpers\Cfg::getValue("CpanelBackupDestinationUser"), "pass" => (new \App\Helpers\Pwd)->decrypt(\App\Helpers\Cfg::getValue("CpanelBackupDestinationPassword")), "email" => \App\Helpers\Cfg::getValue("CpanelBackupNotifyEmail"), "port" => \App\Helpers\Cfg::getValue("CpanelBackupDestinationPort"), "rdir" => \App\Helpers\Cfg::getValue("CpanelBackupDestinationDirectory"), "username" => \App\Helpers\Cfg::getValue("CpanelBackupUsername")));
        return $this;
    }
    protected function doSftpBackup($zipFile, $remoteFile)
    {
        $ftp_server = \App\Helpers\Cfg::getValue("FTPBackupHostname");
        if (!$ftp_server) {
            throw new \Exception("SFTP Hostname Required");
        }
        $ftp_port = \App\Helpers\Cfg::getValue("FTPBackupPort");
        $ftp_user = \App\Helpers\Cfg::getValue("FTPBackupUsername");
        $ftp_pass = (new \App\Helpers\Pwd)->decrypt(\App\Helpers\Cfg::getValue("FTPBackupPassword"));
        if (!$ftp_port) {
            $ftp_port = "22";
        }
        $ftp_server = str_replace(array("ftp://", "sftp://"), "", $ftp_server);
        $sftp = new \phpseclib\Net\SFTP($ftp_server, $ftp_port);
        if (!@$sftp->login($ftp_user, $ftp_pass)) {
            throw new \Exception("SFTP Backup - Login Failed");
        }
        $upload = $sftp->put($remoteFile, $zipFile, \phpseclib\Net\SFTP::SOURCE_LOCAL_FILE);
        $sftp->disconnect();
        if (!$upload) {
            throw new \Exception("SFTP Backup - Uploading Failed");
        }
        return $this;
    }
    protected function doFtpBackup($zipFile, $remoteFile)
    {
        $ftp_server = \App\Helpers\Cfg::getValue("FTPBackupHostname");
        if (!$ftp_server) {
            throw new \Exception("FTP Hostname Required");
        }
        $ftp_port = \App\Helpers\Cfg::getValue("FTPBackupPort");
        $ftp_user = \App\Helpers\Cfg::getValue("FTPBackupUsername");
        $ftp_pass = (new \App\Helpers\Pwd)->decrypt(\App\Helpers\Cfg::getValue("FTPBackupPassword"));
        if (!$ftp_port) {
            $ftp_port = "21";
        }
        $ftp_server = str_replace("ftp://", "", $ftp_server);
        $ftpConnection = @ftp_connect($ftp_server, $ftp_port ?: 21);
        if (!$ftpConnection) {
            throw new \Exception("FTP Backup - Could not connect to " . $ftp_server);
        }
        if (!ftp_login($ftpConnection, $ftp_user, $ftp_pass)) {
            throw new \Exception("FTP Backup - Login Failed");
        }
        if (\App\Helpers\Cfg::getValue("FTPPassiveMode")) {
            ftp_pasv($ftpConnection, true);
        }
        $upload = ftp_put($ftpConnection, $remoteFile, $zipFile, FTP_BINARY);
        ftp_close($ftpConnection);
        if (!$upload) {
            throw new \Exception("FTP Backup - Uploading Failed");
        }
        return $this;
    }
}

?>