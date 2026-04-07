<?php

namespace App\Helpers\Schedule;

use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Helpers\LogActivity;
use Database;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Helpers\Cfg;
use Exception;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;

class DatabaseBackup
{
   protected static $zipFile = NULL;
   public static function run(){
        \Artisan::call('backup:run');
        DatabaseBackup::doCpanel();
        DatabaseBackup::doFtpAndEmail();
        \Artisan::call('backup:clean');
   }
   protected static function doCpanel()
    {
        $complete = false;
        if (DatabaseBackup::isBackupSystemActive("cpanel")) {
            try {
               DatabaseBackup::requestCPanelBackup();
               LogActivity::save("Cron Job: Remote cPanel Backup Requested");
               $complete = true;
            } catch (\Exception $e) {
               LogActivity::save("Cron Job: cPanel Remote Backup Failed" . $e->getMessage());
            }
        }
        return $complete;
   }

   protected static function doFtpAndEmail()
   {
      $complete = false;
    
      if(DatabaseBackup::isBackupSystemActive("email") || DatabaseBackup::isBackupSystemActive("ftp")) {
            try {
                //dd(DatabaseBackup::getFile());
                $getfile=Storage::disk('beckup')->files('CBMS-Backup');
                $file=array();
                if($getfile){
                    $file=Storage::path('beckup/'.$getfile[0]);
                   
                    if(DatabaseBackup::isBackupSystemActive("email")){
                        DatabaseBackup::emailZip($file);
                        LogActivity::save("Cron Job: Email Backup - Sent Successfully");
                    }
                    if (DatabaseBackup::isBackupSystemActive("ftp")){
                        //$remoteFile = Cfg::getValue("FTPBackupDestination") . $attachmentName;
                        DatabaseBackup::ftpZip($file);
                        LogActivity::Save("Cron Job: FTP Backup - Completed Successfully");
                    }
                    $msg = "Backup Complete";
                        unlink($file);
                    //$this->output("completed")->write(1);
                    $complete = true;
                }else{
                    throw new Exception("Backup File Generation Failed");
                }
            }catch(Exception $e){
               $msg = "Database Backup Sending Failed - PHPMailer Exception" . " - " . $e->getMessage() . "(Subject: WHMCS Database Backup)";
               //$this->output("completed")->write(0);

            }catch (\Exception $e) {
               //$this->output("completed")->write(0);
               $msg = $e->getMessage();
            }

            //unlink($file);
      }else{
         //$this->output("completed")->write(0);
         $msg = "Database Backup requested but backups are not configured.";
      }
      LogActivity::Save("Cron Job: " . $msg);
      return $complete;
   }
   protected static function ftpZip($file)
    {
        Storage::disk('ftp')->put('cbms_beckup'.date('Y-m-d_h:i:sa').'.zip',$file);
        return true;
    }

   protected static function emailZip($file)
   {    
        if(!Cfg::getValue("DailyEmailBackup")) {
            throw new Exception("No Daily Email Address Configured");
        }
        $param=[
            'form'  => [Cfg::get("SystemEmailsFromEmail"),Cfg::get("SystemEmailsFromName")],
            'to'    => Cfg::getValue("DailyEmailBackup"),
            'file'  => $file
        ];
     
            Mail::send([], [], function ($message) use ($param) {
                $message->from($param['form'][0], $param['form'][1]);
                $message->to($param['to']);
                $message->subject('CBMS Email Backup'.date('Y-m-d h:i:sa'));
                $message->setBody('<p>CBMS Auto Beckup Database</p>', 'text/html');
                $message->attach($param['file']); 
            });
        return true;
   }

   protected static function isBackupSystemActive($system)
    {
        $activeBackupSystems = Cfg::getValue("ActiveBackupSystems");
        //dd( $activeBackupSystems);
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
    protected static function requestCPanelBackup()
    {
        $server = new \App\Module\Server();
        $server->load("cpanel");
        $server->call("request_backup",   [
                                                "serverip" => "",
                                                "serverhostname" => Cfg::getValue("CpanelBackupHostname"),
                                                "serverusername" => Cfg::getValue("CpanelBackupWHMUsername"),
                                                "serveraccesshash" => decrypt(Cfg::getValue("CpanelBackupAPIToken")),
                                                "serverhttpprefix" => "https",
                                                "serverport" => "2087",
                                                "serversecure" => true,
                                                "dest" => Cfg::getValue("CpanelBackupDestination"),
                                                "hostname" => Cfg::getValue("CpanelBackupDestinationHostname"),
                                                "user" => Cfg::getValue("CpanelBackupDestinationUser"),
                                                "pass" => decrypt(Cfg::getValue("CpanelBackupDestinationPassword")),
                                                "email" => Cfg::getValue("CpanelBackupNotifyEmail"),
                                                "port" => Cfg::getValue("CpanelBackupDestinationPort"),
                                                "rdir" => Cfg::getValue("CpanelBackupDestinationDirectory"),
                                                "username" => Cfg::getValue("CpanelBackupUsername") 
                                          ]);
        return true;
    }

    

}