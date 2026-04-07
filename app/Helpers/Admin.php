<?php
namespace App\Helpers;

// Import Model Class here

// Import Package Class here

// Import Laravel Class here
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Log;

class Admin
{
	public $loginRequired = true;
    public $requiredPermission = "";
    public $title = "";
    public $sidebar = "";
    public $icon = "";
    public $helplink = "";
    public $jscode = "";
    public $internaljquerycode = array();
    public $jquerycode = "";
    public $template = "";
    public $content = "";
    public $templatevars = array();
    public $filename = "";
    public $rowLimit = 50;
    public $tablePagination = true;
    public $adminTemplate = self::DEFAULT_ADMIN_TEMPLATE;
    public $exitmsg = "";
    public $language = "english";
    public $extrajscode = array();
    public $headOutput = array();
    public $chartFunctions = array();
    public $sortableTableCount = 0;
    protected $tabPrefix = "";
    public $smarty = "";
    protected $notificationContent = "";
    protected $bodyContent = "";
    protected $headerContent = "";
    protected $footerContent = "";
    protected $responseType = self::RESPONSE_HTML;
    protected $translateJqueryDefined = false;
    protected $standardVariablesLoaded = false;
    protected $tabCount = 1;
    protected $defaultTabOpen = false;
    private $adminRoleId = NULL;
    protected $topBarNotifications = array();
    const DEFAULT_ADMIN_TEMPLATE = "blend";
    const RESPONSE_JSON_MODAL_MESSAGE = "JSON_MODAL_MESSAGE";
    // const RESPONSE_JSON_MESSAGE = Http\Message\ResponseFactory::RESPONSE_TYPE_JSON;
    const RESPONSE_JSON = "JSON";
    // const RESPONSE_HTML_MESSAGE = Http\Message\ResponseFactory::RESPONSE_TYPE_HTML;
    const RESPONSE_HTML = "HTML";
	public function __construct($reqpermission = '', $releaseSession = true)
	{
		
	}

	public function sortableTable($columns, $tabledata, $formurl = "", $formbuttons = "", $topbuttons = "")
    {
        global $orderby;
        global $order;
        global $numrows;
        global $page;
        $pages = ceil($numrows / $this->rowLimit);
        if ($pages == 0) {
            $pages = 1;
        }
        $content = "";
        if ($this->tablePagination) {
            $varsrecall = "";
            foreach (Request::all() as $key => $value) {
                if (!in_array($key, array("orderby", "page", "PHPSESSID", "token")) && $value) {
                    if (is_array($value)) {
                        foreach ($value as $k => $v) {
                            if ($v) {
                                $varsrecall .= "<input type=\"hidden\" name=\"" . $key . "[" . $k . "]\" value=\"" . $v . "\" />" . "\n";
                            }
                        }
                    } else {
                        $varsrecall .= "<input type=\"hidden\" name=\"" . $key . "\" value=\"" . $value . "\" />" . "\n";
                    }
                }
            }
            if ($varsrecall) {
                $varsrecall = "\n" . $varsrecall;
            }
            $content .= "<form method=\"post\" action=\"" . url()->current() . "\">" . $varsrecall . "\n<table class=\"\" width=\"100%\" border=\"0\" cellpadding=\"3\" cellspacing=\"0\"><tr>\n<td width=\"50%\" align=\"left\">" . $numrows . " " . $this->lang("", "recordsfound") . ", " . $this->lang("", "page") . " " . ($page + 1) . " " . $this->lang("", "of") . " " . $pages . "</td>\n<td width=\"50%\" align=\"right\">" . $this->lang("", "jumppage") . ": <select name=\"page\" onchange=\"submit()\">";
            for ($i = 1; $i <= $pages; $i++) {
                $newpage = $i - 1;
                $content .= "<option value=\"" . $newpage . "\"";
                if ($page == $newpage) {
                    $content .= " selected";
                }
                $content .= ">" . $i . "</option>";
            }
            $content .= "</select> <input type=\"submit\" value=\"" . $this->lang("", "go") . "\" class=\"btn btn-xs btn-secondary\" /></td>\n</tr></table>\n</form>\n";
        }
        if ($formurl) {
            $content .= "<form method=\"post\" action=\"" . $formurl . "\">" . $varsrecall;
        }
        if ($topbuttons) {
            $content .= "<div style=\"padding-bottom:2px;\">" . $this->lang("", "withselected") . ": " . $formbuttons . "</div>";
        }
        $content .= "\n<div class=\"tablebg\">\n<table id=\"sortabletbl" . $this->sortableTableCount . "\" class=\"datatable display table table-borderless dt-responsive w-100\" width=\"100%\" border=\"0\" cellspacing=\"1\" cellpadding=\"3\">\n<tr>";
        foreach ($columns as $column) {
            if (is_array($column)) {
                $sortableheader = true;
                list($columnid, $columnname, $width) = $column;
                if (!$columnid) {
                    $sortableheader = false;
                }
            } else {
                $sortableheader = false;
                $columnid = $width = "";
                $columnname = $column;
            }
            if (!$columnname) {
                $content .= "<th width=\"20\"></th>";
            } else {
                if ($columnname == "checkall") {
                    $this->internaljquerycode[] = "\$(\"#checkall" . $this->sortableTableCount . "\").click(function () {\n    \$(\"#sortabletbl" . $this->sortableTableCount . " .checkall\").prop(\"checked\",this.checked);\n});";
                    $content .= "<th width=\"20\"><input type=\"checkbox\" id=\"checkall" . $this->sortableTableCount . "\"></th>";
                } else {
                    $width = $width ? " width=\"" . $width . "\"" : "";
                    $content .= "<th" . $width . ">";
                    if ($sortableheader) {
                        $content .= "<a href=\"" . url()->current() . "?";
                        foreach (Request::all() as $key => $value) {
                            if ($key != "orderby" && $key != "PHPSESSID" && $value) {
                                $content .= "" . $key . "=" . $value . "&";
                            }
                        }
                        $content .= "orderby=" . $columnid . "\">";
                    }
                    $content .= $columnname;
                    if ($sortableheader) {
                        $content .= "</a>";
                        if ($orderby == $columnid) {
                            $content .= " <img src=\"images/" . strtolower($order) . ".gif\" class=\"absmiddle\" />";
                        }
                    }
                    $content .= "</th>";
                }
            }
        }
        $content .= "</tr>\n";
        $totalcols = count($columns);
        if (is_array($tabledata) && count($tabledata)) {
            foreach ($tabledata as $tablevalues) {
                if ($tablevalues[0] == "dividingline") {
                    $content .= "<tr><td colspan=\"" . $totalcols . "\" style=\"background-color:#efefef;\"><div align=\"left\"><b>" . $tablevalues[1] . "</b></div></td></tr>\n";
                } else {
                    $content .= "<tr>";
                    foreach ($tablevalues as $tablevalue) {
                        $content .= "<td>" . $tablevalue . "</td>";
                    }
                    $content .= "</tr>\n";
                }
            }
        } else {
            $content .= "<tr><td colspan=\"" . $totalcols . "\">" . $this->lang("", "norecordsfound") . "</td></tr>\n";
        }
        $content .= "</table>\n</div>\n";
        if ($formbuttons) {
            $content .= "" . $this->lang("", "withselected") . ": " . $formbuttons;
        }
        if ($formurl) {
            $content .= "</form>";
        }
        if ($this->tablePagination) {
            $content .= "<ul class=\"pager\">";
            if (0 < $page) {
                $prevoffset = $page - 1;
                $content .= "<li class=\"previous\"><a href=\"" . url()->current() . "?";
                foreach (Request::all() as $key => $value) {
                    if ($key != "orderby" && $key != "page" && $key != "PHPSESSID" && $value) {
                        if (is_array($value)) {
                            foreach ($value as $k => $v) {
                                if ($v) {
                                    $content .= $key . "[" . $k . "]=" . $v . "&";
                                }
                            }
                        } else {
                            $content .= (string) $key . "=" . $value . "&";
                        }
                    }
                }
                $content .= "page=" . $prevoffset . "\">&laquo; " . $this->lang("", "previouspage") . "</a></li>";
            } else {
                $content .= "<li class=\"previous disabled\"><a href=\"#\">&laquo; " . $this->lang("", "previouspage") . "</a></li>";
            }
            if (($page * $this->rowLimit + $this->rowLimit) / $this->rowLimit == $pages) {
                $content .= "<li class=\"next disabled\"><a href=\"#\">" . $this->lang("", "nextpage") . " &raquo;</a></li>";
            } else {
                $newoffset = $page + 1;
                $content .= "<li class=\"next\"><a href=\"" . url()->current() . "?";
                foreach (Request::all() as $key => $value) {
                    if ($key != "orderby" && $key != "page" && $key != "PHPSESSID" && $value) {
                        if (is_array($value)) {
                            foreach ($value as $k => $v) {
                                if ($v) {
                                    $content .= $key . "[" . $k . "]=" . $v . "&";
                                }
                            }
                        } else {
                            $content .= (string) $key . "=" . $value . "&";
                        }
                    }
                }
                $content .= "page=" . $newoffset . "\">" . $this->lang("", "nextpage") . " &raquo;</a></li>";
            }
            $content .= "</ul>";
        }
        return $content;
    }

	public function lang($section, $var, $escape = "")
    {
        $translated = \Lang::get("admin.".(string) $section . $var);
        if ($escape) {
            return addslashes($translated);
        }
        if ($translated == (string) $section . "." . $var) {
            if (defined("DEVMODE")) {
                return "Missing Language Var \"" . $section . "." . $var . "\"";
            }
            return "";
        }
        return $translated;
    }

    public function addHeadJqueryCode($code)
    {
        return $this->internaljquerycode[] = $code;
    }

    public function addMarkdownEditor($jsVariable = "openTicketMDE", $uniqueId = "ticket_open", $elementId = "replymessage", $addFilesToHead = true)
    {
        $locale = preg_replace("/[^a-zA-Z0-9_\\-]*/", "", 'en');
        $locale = $locale == "locale" ? "en" : substr($locale, 0, 2);
        $phpSelf = URL::current();
        $token = csrf_token();
        return $this->addHeadJqueryCode("var element = jQuery(\"#" . $elementId . "\"),\n    counter = 0;\nvar " . $jsVariable . " = element.markdown(\n    {\n        footer: '<div id=\"" . $elementId . "-footer\" class=\"markdown-editor-status\"></div>',\n        autofocus: false,\n        savable: false,\n        resize: 'vertical',\n        iconlibrary: 'fa',\n        language: '" . $locale . "',\n        onShow: function(e){\n            var content = '',\n                save_enabled = false;\n            if(typeof(Storage) !== \"undefined\") {\n                // Code for localStorage/sessionStorage.\n                content = localStorage.getItem(\"" . $uniqueId . "\");\n                save_enabled = true;\n                if (content && typeof(content) !== \"undefined\") {\n                    e.setContent(content);\n                }\n            }\n            jQuery(\"#" . $elementId . "-footer\").html(parseMdeFooter(content, save_enabled, 'saved'));\n        },\n        onChange: function(e){\n            var content = e.getContent(),\n                save_enabled = false;\n            if(typeof(Storage) !== \"undefined\") {\n                counter = 3;\n                save_enabled = true;\n                localStorage.setItem(\"" . $uniqueId . "\", content);\n                doCountdown();\n            }\n            jQuery(\"#" . $elementId . "-footer\").html(parseMdeFooter(content, save_enabled));\n        },\n        onPreview: function(e){\n            var originalContent = e.getContent(),\n                parsedContent;\n\n            jQuery.ajax({\n                url: '" . $phpSelf . "',\n                async: false,\n                data: {token: '" . $token . "', action: 'parseMarkdown', content: originalContent},\n                dataType: 'json',\n                success: function (data) {\n                    parsedContent = data;\n                }\n            });\n\n            return parsedContent.body ? parsedContent.body : '';\n        },\n        additionalButtons: [\n            [{\n                name: \"groupCustom\",\n                data: [{\n                    name: \"cmdHelp\",\n                    title: \"Help\",\n                    hotkey: \"Ctrl+F1\",\n                    btnClass: \"btn open-modal\",\n                    icon: {\n                        glyph: 'fas fa-question-circle',\n                        fa: 'fas fa-question-circle',\n                        'fa-3': 'icon-question-sign'\n                    },\n                    callback: function(e) {\n                        e.\$editor.removeClass(\"md-fullscreen-mode\");\n                    }\n                }]\n            }]\n        ],\n        hiddenButtons: [\n            'cmdImage'\n        ]\n    }\n);\n\njQuery('button[data-handler=\"bootstrap-markdown-cmdHelp\"]')\n    .attr('data-modal-title', 'Markdown Guide')\n    .attr('data-modal-size', 'modal-lg')\n    .attr('href', 'supporttickets.php?action=markdown');\n\nelement.closest(\"form\").bind({\n    submit: function() {\n        if(typeof(Storage) !== \"undefined\") {\n            // Code for localStorage/sessionStorage.\n            if (jQuery(this).attr('data-no-clear') == \"false\") {\n                localStorage.removeItem(\"" . $uniqueId . "\");\n            }\n        }\n    }\n});");
        if ($addFilesToHead) {
            $this->addHeadJqueryCode("function parseMdeFooter(content, auto_save, saveText)\n{\n    if (typeof saveText == 'undefined') {\n        saveText = 'autosaving';\n    }\n    var pattern = /[^\\s]+/g,\n        m = [],\n        word_count = 0,\n        line_count = 0;\n    if (content) {\n        m = content.match(pattern);\n        line_count = content.split(/\\r\\n|\\r|\\n/).length;\n    }\n    if (m) {\n        for(var i = 0; i < m.length; i++) {\n            if(m[i].charCodeAt(0) >= 0x4E00) {\n                word_count += m[i].length;\n            } else {\n                word_count += 1;\n            }\n        }\n    }\n    return '<div class=\"smallfont\">lines: ' + line_count\n        + '&nbsp;&nbsp;&nbsp;words: ' + word_count + ''\n        + (auto_save ? '&nbsp;&nbsp;&nbsp;<span class=\"markdown-save\">' + saveText + '</span>' : '')\n        + '</div>';\n}\n\nfunction doCountdown()\n{\n    if (counter >= 0) {\n        if (counter == 0) {\n            jQuery(\"span.markdown-save\").html('saved');\n        }\n        counter--;\n        setTimeout(doCountdown, 1000);\n    }\n}");
        }
    }
    public function modal($name, $title, $message, array $buttons = array(), $size = "", $panelType = "primary")
    {
        switch ($size) {
            case "small":
                $dialogClass = "modal-dialog modal-sm";
                break;
            case "large":
                $dialogClass = "modal-dialog modal-lg";
                break;
            default:
                $dialogClass = "modal-dialog";
        }
        switch ($panelType) {
            case "default":
            case "primary":
            case "success":
            case "info":
            case "warning":
            case "danger":
                $panel = "panel-" . $panelType;
                break;
            default:
                $panel = "panel-primary";
        }
        $buttonsOutput = "";
        foreach ($buttons as $button) {
            $id = \App\Helpers\ViewHelper::generateCssFriendlyId($name, $button["title"]);
            $onClick = isset($button["onclick"]) ? "onclick='" . $button["onclick"] . "'" : "data-dismiss=\"modal\"";
            $class = isset($button["class"]) ? $button["class"] : "btn-secondary";
            $type = isset($button["type"]) ? $button["type"] : "button";
            $buttonsOutput .= "<button type=\"" . $type . "\" id=\"" . $id . "\" class=\"btn " . $class . "\" " . $onClick . ">\n    " . $button["title"] . "\n</button>";
        }
        $modalOutput = "<div class=\"modal fade\" id=\"modal" . $name . "\" role=\"dialog\" aria-labelledby=\"" . $name . "Label\" aria-hidden=\"true\">\n    <div class=\"" . $dialogClass . "\">\n        <div class=\"modal-content panel " . $panel . "\">\n            <div id=\"modal" . $name . "Heading\" class=\"modal-header panel-heading\">\n<h4 class=\"modal-title\" id=\"" . $name . "Label\">" . $title . "</h4>\n<button type=\"button\" class=\"close\" data-dismiss=\"modal\">\n                    <span aria-hidden=\"true\">&times;</span>\n                    <span class=\"sr-only\">" . $this->lang("global", "close") . "</span>\n                </button>\n            </div>\n            <div id=\"modal" . $name . "Body\" class=\"modal-body panel-body\">\n                " . $message . "\n            </div>\n            <div id=\"modal" . $name . "Footer\" class=\"modal-footer panel-footer\">\n                " . $buttonsOutput . "\n            </div>\n        </div>\n    </div>\n</div>";
        return $modalOutput;
    }
}
