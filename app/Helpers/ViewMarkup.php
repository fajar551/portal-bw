<?php
namespace App\Helpers;

// Import Model Class here

// Import Package Class here
use App\Helpers\Cfg;

// Import Laravel Class here
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class ViewMarkup
{
	public function determineMarkupEditor($contentType = "", $definedEditor = "plain", $timestamp = NULL)
    {
        $markupEditor = "plain";
        $definableEditorContentTypes = array("ticket_msg", "ticket_reply", "ticket_note");
        if (in_array($contentType, $definableEditorContentTypes)) {
            if ($definedEditor == "markdown") {
                $markupEditor = "markdown";
            } else {
                $markupEditor = "bbcode";
            }
        } else {
            if ($timestamp && $this->isAfterMdeUpgrade($timestamp)) {
                $markupEditor = "markdown";
            }
        }
        return $markupEditor;
    }
	public function transform($text, $markupFormat = "plain", $emailFriendly = false)
    {
        $text = strip_tags($text);
        $text = \App\Helpers\Sanitize::decode($text);
        switch ($markupFormat) {
            case "markdown":
               /*  $markdown = new Markdown\Markdown();
                $markdown->email_friendly = $emailFriendly;
                $formattedText = $markdown->transform(\App\Helpers\Sanitize::decode($text)); */
                $formattedText = \Markdown::convertToHtml($text);
                break;
            case "bbcode":
                $text = \App\Helpers\Sanitize::encode($text);
                //$formattedText = Bbcode\Bbcode::transform($text);
                $formattedText = \App\Helpers\Bbcode::transform($text);
                $formattedText = $this->ticketAutoHyperlinks(nl2br($formattedText));
                break;
            case "plain":
            default:
                $text = \App\Helpers\Sanitize::encode($text);
                $formattedText = $this->ticketAutoHyperlinks(nl2br($text));
                break;
        }
        return $formattedText;
    }
    public function isAfterMdeUpgrade($timestamp)
    {
        $mdeFromTime = \WHMCS\Config\Setting::getValue("MDEFromTime");
        if ($mdeFromTime) {
            $mdeFromTime = \WHMCS\Carbon::createFromFormat("Y-m-d H:i:s", $mdeFromTime);
            return \WHMCS\Carbon::createFromFormat("Y-m-d H:i:s", $timestamp)->gte($mdeFromTime);
        }
        return false;
    }

    public function ticketAutoHyperlinks($string){
        $pecah = explode(" ", $string);
        for ($i=0; $i<=sizeof($pecah)-1; $i++)
        {
            if ((substr($pecah[$i], 0, 7) == 'http://') && ($pecah[$i] != 'http://')){
                $string = str_replace($pecah[$i], "<a href='".$pecah[$i]."'>".$pecah[$i]."</a>", $string);
            }
        }
        return  $string;
    }


}
