<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth, DB;

class DownloadController extends Controller
{
    //
    public function index(Request $request)
    {
        define("CLIENTAREA", true);
        global $_LANG;
        $auth = Auth::guard('web')->user();
        $authadmin = Auth::guard('admin')->user();
        $uid = $auth ? $auth->id : 0;
        $adminid = $authadmin ? $authadmin->id : 0;
        $type = $request->input("type");
        $viewpdf = $request->input("viewpdf");
        $i = (int) $request->input("i");
        $id = (int) $request->input("id");
        $storage = NULL;
        $allowedtodownload = "";
        $file_name = $display_name = "";
        $allowedtodownload = "";
        if ($type == "i") {
            $result = \App\Models\Invoice::where(array("id" => $id));
            $data = $result;
            $invoiceid = $data->value("id");
            $invoicenum = $data->value("invoicenum");
            $userid = $data->value("userid");
            $status = $data->value("status");
            if (!$invoiceid) {
                // redir("", "clientarea.php");
                return redirect()->route('home');
            }
            if ($authadmin) {
                if (!\App\Helpers\AdminFunctions::checkPermission("Manage Invoice", true)) {
                    exit("You do not have the necessary permissions to download PDF invoices. If you feel this message to be an error, please contact the system administrator.");
                }
            } else {
                if ($uid == $userid) {
                    if ($status == "Draft") {
                        // redir("", "clientarea.php");
                        return redirect()->route('home');
                    }
                } else {
                    $this->downloadLogin();
                }
            }
            if (!$invoicenum) {
                $invoicenum = $invoiceid;
            }
            $pdfdata = \App\Helpers\Invoice::pdfInvoice($id);
            $filenameSuffix = preg_replace("|[\\\\/]+|", "-", $invoicenum);
            header("Pragma: public");
            header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
            header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0, private");
            header("Cache-Control: private", false);
            header("Content-Type: application/pdf");
            header("Content-Disposition: " . ($viewpdf ? "inline" : "attachment") . "; filename=\"" . $_LANG["invoicefilename"] . $filenameSuffix . ".pdf\"");
            header("Content-Transfer-Encoding: binary");
            header("Content-Length: " . strlen($pdfdata));
            echo $pdfdata;
            exit;
        }
        switch ($type) {
            case 'a':
            case 'ar':
            case 'an':
                $useridOfMasterTicket = $useridOfReply = 0;
                $adminOnly = false;
                $ticketid = "";
                switch ($type) {
                    case "an":
                        $noteData = DB::table("tblticketnotes")->find($id, array("ticketid", "attachments"));
                        if ($noteData) {
                            $attachments = $noteData->attachments;
                            $ticketid = $noteData->ticketid;
                            $adminOnly = true;
                        }
                        break;
                    case "ar":
                        $replyData = DB::table("tblticketreplies")->find($id, array("tid", "userid", "attachment"));
                        if ($replyData) {
                            $attachments = $replyData->attachment;
                            $ticketid = $replyData->tid;
                            $useridOfReply = $replyData->userid;
                            $useridOfMasterTicket = \App\Models\Ticket::where(array("id" => $ticketid))->value("userid") ?? 0;
                        }
                        break;
                    default:
                        $ticketData = DB::table("tbltickets")->find($id, array("id", "userid", "attachment"));
                        if ($ticketData) {
                            $attachments = $ticketData->attachment;
                            $ticketid = $ticketData->id;
                            $useridOfMasterTicket = $ticketData->userid;
                        }
                }
                if (!$ticketid) {
                    exit("Ticket ID Not Found");
                }
                if ($authadmin) {
                    if (!\App\Helpers\AdminFunctions::checkPermission("View Support Ticket", true)) {
                        exit("You do not have the necessary permissions to View Support Tickets. " . "If you feel this message to be an error, please contact the system administrator.");
                    }
                    $access = \App\Helpers\Ticket::validateAdminTicketAccess($ticketid);
                    if ($access) {
                        exit("Access Denied. You do not have the required permissions to view this ticket.");
                    }
                } else {
                    if (!$adminOnly) {
                        if ($useridOfMasterTicket) {
                            if ($useridOfMasterTicket != $uid) {
                                $this->downloadLogin();
                                exit;
                            }
                        } else {
                            if ($useridOfReply) {
                                if ($useridOfReply != $uid) {
                                    $this->downloadLogin();
                                    exit;
                                }
                            } else {
                                $AccessedTicketIDs = session("AccessedTicketIDs");
                                $AccessedTicketIDsArray = explode(",", $AccessedTicketIDs);
                                if (!in_array($ticketid, $AccessedTicketIDsArray)) {
                                    exit("Ticket Attachments cannot be accessed directly. " . "Please try again using the download link provided within the ticket. " . "If you are registered and have an account with us, you can access your tickets " . "from our client area. Otherwise, please use the link to view the ticket which you " . "should have received via email when the ticket was originally opened or last responded to.");
                                }
                            }
                        }
                    }
                }
                $storage = \Storage::disk('attachments');
                $files = explode("|", $attachments);
                $file_name = $files[$i];
                $display_name = substr($file_name, 7);
            break;

            case 'd':
                $data = \App\Models\Download::where(array("id" => $id));
                $downloadID = $data->value("id");
                $filename = $data->value("location");
                $clientsonly = $data->value("clientsonly");
                $productdownload = $data->value("productdownload");
                if (!$downloadID) {
                    exit("Invalid Download Requested");
                }
                $userID = (int) $uid;
                if (!$userID && ($clientsonly || $productdownload)) {
                    $this->downloadLogin();
                }
                if ($productdownload) {
                    $serviceID = (int) $request->input("serviceid");
                    if ($serviceID) {
                        $servicesWhere = array("tblhosting.id" => $serviceID, "userid" => $userID, "tblhosting.domainstatus" => "Active");
                        $addonsWhere = array("tblhostingaddons.hostingid" => $serviceID, "tblhosting.userid" => $userID, "tblhostingaddons.status" => "Active");
                    } else {
                        $servicesWhere = array("userid" => $userID, "tblhosting.domainstatus" => "Active");
                        $addonsWhere = array("tblhosting.userid" => $userID, "tblhostingaddons.status" => "Active");
                    }
                    $allowAccess = false;
                    $supportAndUpdatesAddons = array();
                    $result = \App\Models\Hosting::selectRaw("tblhosting.id,tblproducts.id AS productid,tblproducts.servertype,tblproducts.configoption7")
                    ->where($servicesWhere)
                    ->join("tblproducts", "tblproducts.id","=","tblhosting.packageid")
                    ->get();
                    foreach ($result->toArray() as $data) {
                        $productServiceID = $data["id"];
                        $productModule = $data["servertype"];
                        $supportAndUpdatesAddon = $data["configoption7"];
                        $productDownloadsArray = \App\Models\Product::find($data["productid"])->getDownloadIds();
                        if (is_array($productDownloadsArray) && in_array($downloadID, $productDownloadsArray)) {
                            if ($productModule == "licensing" && $supportAndUpdatesAddon && $supportAndUpdatesAddon != "0|None") {
                                $parts = explode("|", $supportAndUpdatesAddon);
                                $requiredAddonID = (int) $parts[0];
                                if ($requiredAddonID) {
                                    $supportAndUpdatesAddons[$productServiceID] = $requiredAddonID;
                                }
                            } else {
                                $allowAccess = true;
                            }
                        }
                    }
                    if (!$allowAccess) {
                        $result = \App\Models\Hostingaddon::selectRaw("DISTINCT tbladdons.id,tbladdons.downloads")
                        ->where($addonsWhere)
                        ->join("tbladdons", "tbladdons.id","=","tblhostingaddons.addonid")
                        ->join("tblhosting", "tblhosting.id","=","tblhostingaddons.hostingid")
                        ->get();
                        foreach ($result->toArray() as $data) {
                            $addondownloads = $data["downloads"];
                            $addondownloads = explode(",", $addondownloads);
                            if (in_array($downloadID, $addondownloads)) {
                                $allowAccess = true;
                            }
                        }
                    }
                    if (!$allowAccess && count($supportAndUpdatesAddons)) {
                        foreach ($supportAndUpdatesAddons as $productServiceID => $requiredAddonID) {
                            $requiredAddonName = \App\Models\Addon::where(array("id" => $requiredAddonID))->value("name") ?? "";
                            $where = "tblhosting.userid='" . $userID . "' AND tblhostingaddons.status='Active' AND (tblhostingaddons.name='" . \App\Helpers\Database::db_escape_string($requiredAddonName) . "' OR tblhostingaddons.addonid='" . $requiredAddonID . "')";
                            if ($serviceID) {
                                $where .= " AND tblhosting.id='" . $serviceID . "'";
                            }
                            $addonCount = \App\Models\Hostingaddon::whereRaw($where)->join("tblhosting", "tblhosting.id","=","tblhostingaddons.hostingid")->count();
                            if ($addonCount) {
                                $allowAccess = true;
                            }
                        }
                        if (!$allowAccess) {
                            if ($serviceID) {
                                $productServiceID = $serviceID;
                                $requiredAddonID = $supportAndUpdatesAddons[$serviceID];
                            }
                            $pagetitle = $_LANG["downloadstitle"];
                            $breadcrumbnav = "<a href=\"" . $CONFIG["SystemURL"] . "/index.php\">" . $_LANG["globalsystemname"] . "</a> > <a href=\"\">" . $_LANG["downloadstitle"] . "</a>";
                            $pageicon = "";
                            $displayTitle = \Lang::get("client.supportAndUpdatesExpired");
                            $tagline = "";
                            // initialiseClientArea($pagetitle, $displayTitle, $tagline, $pageicon, $breadcrumbnav);
                            $smartyvalues["reason"] = "supportandupdates";
                            $smartyvalues["serviceid"] = $productServiceID;
                            $smartyvalues["licensekey"] = \App\Models\Hosting::where(array("id" => $productServiceID))->value("domain");
                            $smartyvalues["addonid"] = $requiredAddonID;
                            // outputClientArea("downloaddenied");
                            return view("downloaddenied", $smartyvalues);
                        }
                    }
                    if (!$allowAccess) {
                        $pagetitle = $_LANG["downloadstitle"];
                        $breadcrumbnav = "<a href=\"" . $CONFIG["SystemURL"] . "/index.php\">" . $_LANG["globalsystemname"] . "</a> > <a href=\"" . routePath("download-index") . "\">" . $_LANG["downloadstitle"] . "</a>";
                        $pageicon = "";
                        $displayTitle = \Lang::get("client.accessdenied");
                        $tagline = "";
                        // initialiseClientArea($pagetitle, $displayTitle, $tagline, $pageicon, $breadcrumbnav);
                        if ($serviceID) {
                            $productsWithMatchingDownload = \App\Models\Product::whereHas("productDownloads", function ($query) use($downloadID) {
                                $download = new \App\Models\Download();
                                $query->where($download->getTable() . ".id", $downloadID);
                            })->whereHas("services", function ($query) use($serviceID) {
                                $service = new \App\Models\Hosting();
                                $query->where($service->getTable() . ".id", $serviceID);
                            })->get();
                        } else {
                            $productsWithMatchingDownload = \App\Models\Product::whereHas("productDownloads", function ($query) use($downloadID) {
                                $download = new \App\Models\Download();
                                $query->where($download->getTable() . ".id", $downloadID);
                            })->orderBy("hidden")->orderBy("order")->get();
                        }
                        $smartyvalues["pid"] = "";
                        $smartyvalues["prodname"] = "";
                        if (!$productsWithMatchingDownload->isEmpty()) {
                            $smartyvalues["pid"] = $productsWithMatchingDownload->first()->id;
                            $smartyvalues["prodname"] = $productsWithMatchingDownload->first()->name;
                        }
                        $smartyvalues["aid"] = "";
                        $smartyvalues["addonname"] = "";
                        $result = \App\Models\Addon::where("downloads", "!=", "")->get();
                        foreach ($result->toArray() as $data) {
                            $downloads = $data["downloads"];
                            $downloads = explode(",", $downloads);
                            if (in_array($downloadID, $downloads)) {
                                $smartyvalues["aid"] = $data["id"];
                                $smartyvalues["addonname"] = $data["name"];
                                break;
                            }
                        }
                        if (!$smartyvalues["prodname"] && !$smartyvalues["addonname"]) {
                            $smartyvalues["prodname"] = "Unable to Determine Required Product. Please contact support.";
                        }
                        $smartyvalues["reason"] = "accessdenied";
                        // outputClientArea("downloaddenied");
                        return view("downloaddenied", $smartyvalues);
                        exit;
                    }
                }
                \App\Models\Download::where(array("id" => $id))->increment("downloads");
                $storage = Storage::disk('downloads');
                $file_name = $filename;
                $display_name = $filename;
            break;
            
            case 'f':
                $result = \App\Models\Clientsfile::where(array("id" => $id));
                $data = $result;
                $userid = $data->value("userid");
                $file_name = $data->value("filename");
                $adminonly = $data->value("adminonly");
                $display_name = substr($file_name, 11);
                $storage = Storage::disk('client_files');
                if ($userid != $uid && !$adminid) {
                    $this->downloadLogin();
                }
                if (!$adminid && $adminonly) {
                    exit("Permission Denied");
                }
            break;

            case 'q':
                if (!$uid && !$adminid) {
                    $this->downloadLogin();
                }
                $result = \App\Models\Quote::where(array("id" => $id));
                $data = $result;
                $id = $data->value("id");
                $userid = $data->value("userid");
                if ($userid != $uid && !$adminid) {
                    exit("Permission Denied");
                }
                $pdfdata = \App\Helpers\Quote::genQuotePDF($id);
                header("Pragma: public");
                header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
                header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
                header("Cache-Control: must-revalidate, post-check=0, pre-check=0, private");
                header("Cache-Control: private", false);
                header("Content-Type: application/pdf");
                header("Content-Disposition: " . ($viewpdf ? "inline" : "attachment") . "; filename=\"" . $_LANG["quotefilename"] . $id . ".pdf\"");
                header("Content-Transfer-Encoding: binary");
                echo $pdfdata;
                exit;
            break;
            
            default:
                # code...
            break;
        }

        if (is_null($storage) || !trim($file_name)) {
            // redir("", "index.php");
            return redirect()->route('home');
        }
        try {
            $fileSize = $storage->size($file_name);
        } catch (\Exception $e) {
            if (Auth::guard('admin')->check()) {
                $extraMessage = "This could indicate that the file is missing or that <a href=\"\" target=\"_blank\">storage configuration settings" . "</a> are misconfigured. " . "<a href=\"https://docs.whmcs.com/Storage_Settings#Troubleshooting_a_File_Not_Found_Error\" target=\"_blank\">" . "Learn more</a>";
            } else {
                $extraMessage = "Please contact support.";
            }
            throw new \App\Exceptions\Fatal("File not found. " . $extraMessage);
        }
        \App\Helpers\Hooks::run_hook("FileDownload", array());
        header("Pragma: public");
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0, private");
        header("Content-Type: application/octet-stream");
        header("Content-Disposition: attachment; filename=\"" . $display_name . "\"");
        header("Content-Transfer-Encoding: binary");
        header("Content-Length: " . $fileSize);
        $stream = $storage->readStream($file_name);
        echo stream_get_contents($stream);
        fclose($stream);
    }

    public function downloadLogin()
    {
        return view("auth.login");
    }
}
