<?php
namespace App\Helpers;

// Import Model Class here
use \App\Models\Ticketpredefinedcat;
use \App\Models\Ticketpredefinedreply;

// Import Laravel Class here
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class Predefined {


   public static function genPredefinedCat($cat, $predefq = "")
   {
      $aInt = new \App\Helpers\Admin();
      $catscontent = "";
      $repliescontent = "";

         if (!$predefq) {
            if (!$cat) {
               $cat = 0;
            }
            $result = Ticketpredefinedcat::where('parentid', $cat)->orderBy('name', 'asc')->get();
            foreach ($result as $k => $v) {
               $id = $v->id;
               $name = $v->name;
               $catscontent .= "
               <div class=\"col-sm-12 col-md-4\">
                  <i class=\"fas fa-folder mr-1 text-warning\"></i>
                  <a href=\"#\" onclick=\"selectpredefcat('" . $id . "');return false\">" . $name . "</a>
               </div>";
            }
         }
         $where = $predefq ? array(['name', 'like', '%'.$predefq.'%']) : array('catid' => $cat);
         // $where2 = array('name', 'LIKE', "%" . $predefq . "%");
         // dd($where2);
         $result1 = Ticketpredefinedreply::where($where)->orderBy('name', 'asc')->get();
         foreach ($result1 as $k => $v) {
            $id = $v->id;
            $name = $v->name;
            $reply = strip_tags($v->reply);
            $shortreply = substr($reply, 0, 100) . "...";
            $shortreply = str_replace(chr(10), " ", $shortreply);
            $shortreply = str_replace(chr(13), " ", $shortreply);
            $repliescontent .= "
            <div class=\"col-12\">
               <i class=\"fas fa-file-alt\"></i>
               <a href=\"#\" onclick=\"selectpredefreply('" . $id . "');return false\">" . $name . "</a> - " . $shortreply .
               "</div>";
         }

      $content = "";
      if ($catscontent) {
         $content .= "
         <div class=\"col-sm-8 col-md-10 mb-1\">
         <h5>" . $aInt->lang("support", "categories") . "</h5>
         </div>
         " . $catscontent . "";
      }
      if ($repliescontent != '') {
         if ($predefq) {
            $content .= "
            <div class=\"col-12\">
               <h5>Search Result</h5>
            </div>". $repliescontent ;
         } else {
            $content .= "<div class=\"col-12 font-weight-bold font-size-14 my-2\">" . $aInt->lang("support", "replies") . "</div>" . $repliescontent;
         }
      }
      if (!$content) {
         if ($predefq) {
            $content .= "
            <div class=\"col-12\"><h5>" . $aInt->lang("", "searchresults") . "</h5></div>
            <div class=\"col-12\">" . $aInt->lang("", "nomatchesfound") . "</div>";
         } else {
            $content .= "
            <div class=\"col-12\" style=\"line-height:22px;\">"
               . $aInt->lang("support", "catempty") . 
            "</div>
            ";
         }
      }

      $result2 = Ticketpredefinedcat::select('parentid')->where(array('id' => $cat))->get();
      foreach ($result2 as $value) {
         $parentId = $value->parentid;
      }
      if (0 < $cat || $predefq) {
         $content .= "
         <div class=\"col-sm-12 col-md-2 mt-3\">
            <i class=\"fas fa-arrow-alt-circle-up mr-1 text-primary\"></i>
            <a href=\"#\" onclick=\"selectpredefcat('0');return false\">" . $aInt->lang("support", "toplevel") . "</a>
         </div>
         ";
      }
      if (0 < $cat) {
         $content .= "
         <div class=\"col-sm-12 col-md-2 mt-3\">
            <i class=\"fas fa-arrow-circle-left mr-1 text-primary\"></i>
            <a href=\"#\" onclick=\"selectpredefcat('". $parentId ."');return false\">" . $aInt->lang("support", "uponelevel") . "</a>
         </div>
         ";
      }

      return ResponseAPI::Success([
         'message' => "The data successfully loaded!",
         'data' => $content
      ]);
   }

   public static function genPredefinedReplies($id)
   {
      $result = Ticketpredefinedreply::where('id', $id)->get();
      foreach ($result as $data) {
         $reply = Sanitize::decode($data->reply);
      }
      return ResponseAPI::Success([
         'message' => "The data successfully loaded!",
         'data' => $reply
      ]);
   }
}