<?php

namespace App\Http\Controllers\Admin\Setup;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Ticketdepartment;
use API;
use App\Helpers\Format;
use App\Helpers\LogActivity;
use App\Models\Invoice;
use App\Models\Paymentgateway;
use DB;
use Illuminate\Support\Str;

class GeneralSettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:admin']);
        $this->prefix = \Database::prefix();
    }
    public function GeneralSettingsOLD()
    {
        $themesData = \App\Helpers\ThemeManager::all();

        // client area themes
        $themes = collect($themesData)->where('name', '!=', 'admin')->where('vendor', config('themes-manager.composer.vendor'))->all();

        // orderform themes
        $orderformThemes = collect($themesData)->where('vendor', \App\Helpers\ThemeManager::orderformTheme())->all();

        $pfx = $this->prefix;

        //General Tab
        $getCompanyName = API::post('GetConfigurationValue', ['setting' => 'CompanyName']);
        $getCompanyName = json_decode(json_encode($getCompanyName));
        $getEmail = API::post('GetConfigurationValue', ['setting' => 'Email']);
        $getEmail = json_decode(json_encode($getEmail));
        $getDomain = API::post('GetConfigurationValue', ['setting' => 'Domain']);
        $getDomain = json_decode(json_encode($getDomain));
        $getLogo = API::post('GetConfigurationValue', ['setting' => 'LogoURL']);
        $getLogo = json_decode(json_encode($getLogo));
        $getPayTo = API::post('GetConfigurationValue', ['setting' => 'InvoicePayTo']);
        $getPayTo = json_decode(json_encode($getPayTo));
        $getSystemURL = API::post('GetConfigurationValue', ['setting' => 'SystemURL']);
        $getSystemURL = json_decode(json_encode($getSystemURL));
        $getTemplate = API::post('GetConfigurationValue', ['setting' => 'Template']);
        $getTemplate = json_decode(json_encode($getTemplate));
        $getActivityLimit = API::post('GetConfigurationValue', ['setting' => 'ActivityLimit']);
        $getActivityLimit = json_decode(json_encode($getActivityLimit));
        $getRecordPerPage = API::post('GetConfigurationValue', ['setting' => 'NumRecordstoDisplay']);
        $getRecordPerPage = json_decode(json_encode($getRecordPerPage));
        $getMaintenanceMode = Api::post('GetConfigurationValue', ['setting' => 'MaintenanceMode']);
        $getMaintenanceMode = json_decode(json_encode($getMaintenanceMode));
        $getMaintenanceModeMessage = Api::post('GetConfigurationValue', ['setting' => 'MaintenanceModeMessage']);
        $getMaintenanceModeMessage = json_decode(json_encode($getMaintenanceModeMessage));
        $getMaintenanceModeURL = Api::post('GetConfigurationValue', ['setting' => 'MaintenanceModeURL']);
        $getMaintenanceModeURL = json_decode(json_encode($getMaintenanceModeURL));
        $getFriendlyURL = Api::post('GetConfigurationValue', ['setting' => 'SEOfriendlyurls']);
        $getFriendlyURL = json_decode(json_encode($getFriendlyURL));

        //Localisation Tab
        $getCharset = API::post('GetConfigurationValue', ['setting' => 'Charset']);
        $getCharset = json_decode(json_encode($getCharset));
        $getDateFormat = API::post('GetConfigurationValue', ['setting' => 'DateFormat']);
        $getDateFormat = json_decode(json_encode($getDateFormat));
        $getClientDateFormat = API::post('GetConfigurationValue', ['setting' => 'ClientDateFormat']);
        $getClientDateFormat = json_decode(json_encode($getClientDateFormat));
        $getDefaultCountry = API::post('GetConfigurationValue', ['setting' => 'DefaultCountry']);
        $getDefaultCountry = json_decode(json_encode($getDefaultCountry));
        $getLanguage = API::post('GetConfigurationValue', ['setting' => 'Language']);
        $getLanguage = json_decode(json_encode($getLanguage));
        $getLanguageMenu = API::post('GetConfigurationValue', ['setting' => 'AllowLanguageChange']);
        $getLanguageMenu = json_decode(json_encode($getLanguageMenu));
        $getEnableTranslation = API::post('GetConfigurationValue', ['setting' => 'EnableTranslations']);
        $getEnableTranslation = json_decode(json_encode($getEnableTranslation));
        $getUtfCutOption = API::post('GetConfigurationValue', ['setting' => 'CutUtf8Mb4']);
        $getUtfCutOption = json_decode(json_encode($getUtfCutOption));
        $getPhoneNumberDropdown = API::post('GetConfigurationValue', ['setting' => 'PhoneNumberDropdown']);
        $getPhoneNumberDropdown = json_decode(json_encode($getPhoneNumberDropdown));

        //Ordering Tab
        $getOrderDaysGrace = API::post('GetConfigurationValue', ['setting' => 'OrderDaysGrace']);
        $getOrderDaysGrace = json_decode(json_encode($getOrderDaysGrace));
        $getOrderFormTemplate = API::post('GetConfigurationValue', ['setting' => 'OrderFormTemplate']);
        $getOrderFormTemplate = json_decode(json_encode($getOrderFormTemplate));
        //Template/Theme List
        // =============================
        $directory = 'D:\Qwords\CBMSAuto\cbms-auto-new\cbms-auto\themes\qwords';
        $sub_directories = array_map('basename', glob($directory . '/*', GLOB_ONLYDIR));

        $getTemplateList = array_splice($sub_directories, 1, count($sub_directories));
        // ==============================
        $getOrderFormSidebarToggle = API::post('GetConfigurationValue', ['setting' => 'OrderFormSidebarToggle']);
        $getOrderFormSidebarToggle = json_decode(json_encode($getOrderFormSidebarToggle));
        $getEnableTOSAccept = API::post('GetConfigurationValue', ['setting' => 'EnableTOSAccept']);
        $getEnableTOSAccept = json_decode(json_encode($getEnableTOSAccept));
        $getTermsOfService = API::post('GetConfigurationValue', ['setting' => 'TermsOfService']);
        $getTermsOfService = json_decode(json_encode($getTermsOfService));
        $getAutoRedirectoInvoice = API::post('GetConfigurationValue', ['setting' => 'AutoRedirectoInvoice']);
        $getAutoRedirectoInvoice = json_decode(json_encode($getAutoRedirectoInvoice));
        $getShowNotesFieldonCheckout = API::post('GetConfigurationValue', ['setting' => 'ShowNotesFieldonCheckout'] ?? "on");
        $getShowNotesFieldonCheckout = json_decode(json_encode($getShowNotesFieldonCheckout));
        $getProductMonthlyPricingBreakdown = API::post('GetConfigurationValue', ['setting' => 'ProductMonthlyPricingBreakdown']);
        $getProductMonthlyPricingBreakdown = json_decode(json_encode($getProductMonthlyPricingBreakdown));
        $getAllowDomainsTwice = API::post('GetConfigurationValue', ['setting' => 'AllowDomainsTwice']);
        $getAllowDomainsTwice = json_decode(json_encode($getAllowDomainsTwice));
        $getNoInvoiceEmailOnOrder = API::post('GetConfigurationValue', ['setting' => 'NoInvoiceEmailOnOrder']);
        $getNoInvoiceEmailOnOrder = json_decode(json_encode($getNoInvoiceEmailOnOrder));
        $getSkipFraud = API::post('GetConfigurationValue', ['setting' => 'SkipFraudForExisting']);
        $getSkipFraud = json_decode(json_encode($getSkipFraud));
        $getAutoProvision = API::post('GetConfigurationValue', ['setting' => 'AutoProvisionExistingOnly']);
        $getAutoProvision = json_decode(json_encode($getAutoProvision));
        $getGenerateRandomUsername = API::post('GetConfigurationValue', ['setting' => 'GenerateRandomUsername']);
        $getGenerateRandomUsername = json_decode(json_encode($getGenerateRandomUsername));
        $getProrataClientsAnniversaryDate = API::post('GetConfigurationValue', ['setting' => 'ProrataClientsAnniversaryDate']);
        $getProrataClientsAnniversaryDate = json_decode(json_encode($getProrataClientsAnniversaryDate));

        //Domain Tab
        $getAllowRegister = API::post('GetConfigurationValue', ['setting' => 'AllowRegister']);
        $getAllowRegister = json_decode(json_encode($getAllowRegister));
        $getAllowTransfer = API::post('GetConfigurationValue', ['setting' => 'AllowTransfer']);
        $getAllowTransfer = json_decode(json_encode($getAllowTransfer));
        $getAllowOwnDomain = API::post('GetConfigurationValue', ['setting' => 'AllowOwnDomain']);
        $getAllowOwnDomain = json_decode(json_encode($getAllowOwnDomain));
        $getEnableDomainRenew = API::post('GetConfigurationValue', ['setting' => 'EnableDomainRenewalOrders']);
        $getEnableDomainRenew = json_decode(json_encode($getEnableDomainRenew));
        $getAutoRenewOnPayment = API::post('GetConfigurationValue', ['setting' => 'AutoRenewDomainsonPayment']);
        $getAutoRenewOnPayment = json_decode(json_encode($getAutoRenewOnPayment));
        $getRenewRequireProduct = API::post('GetConfigurationValue', ['setting' => 'FreeDomainAutoRenewRequiresProduct']);
        $getRenewRequireProduct = json_decode(json_encode($getRenewRequireProduct));
        $getAutoRenewDefault = API::post('GetConfigurationValue', ['setting' => 'DomainAutoRenewDefault']);
        $getAutoRenewDefault = json_decode(json_encode($getAutoRenewDefault));
        $getDomainToDoListEntries = API::post('GetConfigurationValue', ['setting' => 'DomainToDoListEntries']);
        $getDomainToDoListEntries = json_decode(json_encode($getDomainToDoListEntries));
        $getAllowIDNDomains = API::post('GetConfigurationValue', ['setting' => 'AllowIDNDomains']);
        $getAllowIDNDomains = json_decode(json_encode($getAllowIDNDomains));
        $getGraceAndRedemptionFees = API::post('GetConfigurationValue', ['setting' => 'DisableDomainGraceAndRedemptionFees']);
        $getGraceAndRedemptionFees = json_decode(json_encode($getGraceAndRedemptionFees));
        $getDefaultNameserver1 = API::post('GetConfigurationValue', ['setting' => 'DefaultNameserver1']);
        $getDefaultNameserver1 = json_decode(json_encode($getDefaultNameserver1));
        $getDefaultNameserver2 = API::post('GetConfigurationValue', ['setting' => 'DefaultNameserver2']);
        $getDefaultNameserver2 = json_decode(json_encode($getDefaultNameserver2));
        $getDefaultNameserver3 = API::post('GetConfigurationValue', ['setting' => 'DefaultNameserver3']);
        $getDefaultNameserver3 = json_decode(json_encode($getDefaultNameserver3));
        $getDefaultNameserver4 = API::post('GetConfigurationValue', ['setting' => 'DefaultNameserver4']);
        $getDefaultNameserver4 = json_decode(json_encode($getDefaultNameserver4));
        $getDefaultNameserver5 = API::post('GetConfigurationValue', ['setting' => 'DefaultNameserver5']);
        $getDefaultNameserver5 = json_decode(json_encode($getDefaultNameserver5));
        $getClientsDetails = API::post('GetConfigurationValue', ['setting' => 'RegistrarAdminUseClientDetails']);
        $getClientsDetails = json_decode(json_encode($getClientsDetails));

        //Mail Tab
        $getMailType = API::post('GetConfigurationValue', ['setting' => 'MailType']);
        $getMailType = json_decode(json_encode($getMailType));
        $getMailEncoding = API::post('GetConfigurationValue', ['setting' => 'MailEncoding']);
        $getMailEncoding = json_decode(json_encode($getMailEncoding));
        $getSMTPPort = API::post('GetConfigurationValue', ['setting' => 'SMTPPort']);
        $getSMTPPort = json_decode(json_encode($getSMTPPort));
        $getSMTPHost = API::post('GetConfigurationValue', ['setting' => 'SMTPHost']);
        $getSMTPHost = json_decode(json_encode($getSMTPHost));
        $getSMTPUsername = API::post('GetConfigurationValue', ['setting' => 'SMTPUsername']);
        $getSMTPUsername = json_decode(json_encode($getSMTPUsername));
        $getSMTPPassword = API::post('GetConfigurationValue', ['setting' => 'SMTPPassword']);
        $getSMTPPassword = json_decode(json_encode($getSMTPPassword));
        $getSMTPSSL = API::post('GetConfigurationValue', ['setting' => 'SMTPSSL']);
        $getSMTPSSL = json_decode(json_encode($getSMTPSSL));
        $getMailSignature = API::post('GetConfigurationValue', ['setting' => 'Signature']);
        $getMailSignature = json_decode(json_encode($getMailSignature));
        $getEmailCSS = API::post('GetConfigurationValue', ['setting' => 'EmailCSS']);
        $getEmailCSS = json_decode(json_encode($getEmailCSS));
        $getEmailGlobalHeader = API::post('GetConfigurationValue', ['setting' => 'EmailGlobalHeader']);
        $getEmailGlobalHeader = json_decode(json_encode($getEmailGlobalHeader));
        $getEmailGlobalFooter = API::post('GetConfigurationValue', ['setting' => 'EmailGlobalFooter']);
        $getEmailGlobalFooter = json_decode(json_encode($getEmailGlobalFooter));
        $getSystemEmailsFromName = API::post('GetConfigurationValue', ['setting' => 'SystemEmailsFromName']);
        $getSystemEmailsFromName = json_decode(json_encode($getSystemEmailsFromName));
        $getSystemEmailsFromEmail = API::post('GetConfigurationValue', ['setting' => 'SystemEmailsFromEmail']);
        $getSystemEmailsFromEmail = json_decode(json_encode($getSystemEmailsFromEmail));
        $getBCCMessages = API::post('GetConfigurationValue', ['setting' => 'BCCMessages']);
        $getBCCMessages = json_decode(json_encode($getBCCMessages));
        $getContactFormDept = API::post('GetConfigurationValue', ['setting' => 'ContactFormDept']);
        $getContactFormDept = json_decode(json_encode($getContactFormDept));
        $getContactFormTo = API::post('GetConfigurationValue', ['setting' => 'ContactFormTo']);
        $getContactFormTo = json_decode(json_encode($getContactFormTo));

        //Support Tab
        $getSupportModule = API::post('GetConfigurationValue', ['setting' => 'SupportModule']);
        $getSupportModule = json_decode(json_encode($getSupportModule));
        $getTicketMask = API::post('GetConfigurationValue', ['setting' => 'TicketMask']);
        $getTicketMask = json_decode(json_encode($getTicketMask));
        $getTicketOrder = API::post('GetConfigurationValue', ['setting' => 'SupportTicketOrder']);
        $getTicketOrder = json_decode(json_encode($getTicketOrder));
        $getTicketLimit = API::post('GetConfigurationValue', ['setting' => 'TicketEmailLimit']);
        $getTicketLimit = json_decode(json_encode($getTicketLimit));
        $getShowClientOnlyDepts = API::post('GetConfigurationValue', ['setting' => 'ShowClientOnlyDepts']);
        $getShowClientOnlyDepts = json_decode(json_encode($getShowClientOnlyDepts));
        $getRequireLoginforClientTickets = API::post('GetConfigurationValue', ['setting' => 'RequireLoginforClientTickets']);
        $getRequireLoginforClientTickets = json_decode(json_encode($getRequireLoginforClientTickets));
        $getSupportTicketKBSuggestions = API::post('GetConfigurationValue', ['setting' => 'SupportTicketKBSuggestions']);
        $getSupportTicketKBSuggestions = json_decode(json_encode($getSupportTicketKBSuggestions));
        $getAttachmentThumbnails = API::post('GetConfigurationValue', ['setting' => 'AttachmentThumbnails']);
        $getAttachmentThumbnails = json_decode(json_encode($getAttachmentThumbnails));
        $getTicketRatingEnabled = API::post('GetConfigurationValue', ['setting' => 'TicketRatingEnabled']);
        $getTicketRatingEnabled = json_decode(json_encode($getTicketRatingEnabled));
        $getTicketCarbonRecipients = API::post('GetConfigurationValue', ['setting' => 'TicketAddCarbonCopyRecipients']);
        $getTicketCarbonRecipients = json_decode(json_encode($getTicketCarbonRecipients));
        $getPreventEmailReopening = API::post('GetConfigurationValue', ['setting' => 'PreventEmailReopening']);
        $getPreventEmailReopening = json_decode(json_encode($getPreventEmailReopening));
        $getUpdateLastReplyTimestamp = API::post('GetConfigurationValue', ['setting' => 'UpdateLastReplyTimestamp']);
        $getUpdateLastReplyTimestamp = json_decode(json_encode($getUpdateLastReplyTimestamp));
        $getEmailsLogging = API::post('GetConfigurationValue', ['setting' => 'DisableSupportTicketReplyEmailsLogging']);
        $getEmailsLogging = json_decode(json_encode($getEmailsLogging));
        $getTicketAllowedFileTypes = API::post('GetConfigurationValue', ['setting' => 'TicketAllowedFileTypes']);
        $getTicketAllowedFileTypes = json_decode(json_encode($getTicketAllowedFileTypes));
        $getNetworkIssuesRequireLogin = API::post('GetConfigurationValue', ['setting' => 'NetworkIssuesRequireLogin']);
        $getNetworkIssuesRequireLogin = json_decode(json_encode($getNetworkIssuesRequireLogin));
        $getDownloadsIncludeProductLinked = API::post('GetConfigurationValue', ['setting' => 'DownloadsIncludeProductLinked']);
        $getDownloadsIncludeProductLinked = json_decode(json_encode($getDownloadsIncludeProductLinked));

        //Invoice Tab
        $getInvoiceGeneration = API::post('GetConfigurationValue', ['setting' => 'ContinuousInvoiceGeneration']);
        $getInvoiceGeneration = json_decode(json_encode($getInvoiceGeneration));
        $getMetricInvoicing = API::post('GetConfigurationValue', ['setting' => 'MetricUsageInvoicing']);
        $getMetricInvoicing = json_decode(json_encode($getMetricInvoicing));
        $getPDFInvoice = API::post('GetConfigurationValue', ['setting' => 'EnablePDFInvoices']);
        $getPDFInvoice = json_decode(json_encode($getPDFInvoice));
        $getPDFPaper = API::post('GetConfigurationValue', ['setting' => 'PDFPaperSize']);
        $getPDFPaper = json_decode(json_encode($getPDFPaper));
        $getFontFamily = API::post('GetConfigurationValue', ['setting' => 'TCPDFFont']);
        $getFontFamily = json_decode(json_encode($getFontFamily));
        $getClientSnapshot = API::post('GetConfigurationValue', ['setting' => 'StoreClientDataSnapshotOnInvoiceCreation']);
        $getClientSnapshot = json_decode(json_encode($getClientSnapshot));
        $getMassPay = API::post('GetConfigurationValue', ['setting' => 'EnableMassPay']);
        $getMassPay = json_decode(json_encode($getMassPay));
        $getClientChangeGateway = API::post('GetConfigurationValue', ['setting' => 'AllowCustomerChangeInvoiceGateway']);
        $getClientChangeGateway = json_decode(json_encode($getClientChangeGateway));
        $getGroupSimItems = API::post('GetConfigurationValue', ['setting' => 'GroupSimilarLineItems']);
        $getGroupSimItems = json_decode(json_encode($getGroupSimItems));
        $getAutoCancel = API::post('GetConfigurationValue', ['setting' => 'AutoCancellationRequests']);
        $getAutoCancel = json_decode(json_encode($getAutoCancel));
        $getAutoSubs = API::post('GetConfigurationValue', ['setting' => 'AutoCancelSubscriptions']);
        $getAutoSubs = json_decode(json_encode($getAutoSubs));
        $getProformaInvoicing = API::post('GetConfigurationValue', ['setting' => 'EnableProformaInvoicing']);
        $getProformaInvoicing = json_decode(json_encode($getProformaInvoicing));
        $getSeqInvoiceNumbering = API::post('GetConfigurationValue', ['setting' => 'SequentialInvoiceNumbering']);
        $getSeqInvoiceNumbering = json_decode(json_encode($getSeqInvoiceNumbering));
        $getSeqInvoiceNumberFormat = API::post('GetConfigurationValue', ['setting' => 'SequentialInvoiceNumberFormat']);
        $getSeqInvoiceNumberFormat = json_decode(json_encode($getSeqInvoiceNumberFormat));
        $getSeqInvoiceNumberValue = API::post('GetConfigurationValue', ['setting' => 'SequentialInvoiceNumberValue']);
        $getSeqInvoiceNumberValue = json_decode(json_encode($getSeqInvoiceNumberValue));
        $getLateFeeType = API::post('GetConfigurationValue', ['setting' => 'LateFeeType']);
        $getLateFeeType = json_decode(json_encode($getLateFeeType));
        $getLateFeeAmount = API::post('GetConfigurationValue', ['setting' => 'InvoiceLateFeeAmount']);
        $getLateFeeAmount = json_decode(json_encode($getLateFeeAmount));
        $getLateFeeMinimum = API::post('GetConfigurationValue', ['setting' => 'LateFeeMinimum']);
        $getLateFeeMinimum = json_decode(json_encode($getLateFeeMinimum));
        $getCCIssueStart = API::post('GetConfigurationValue', ['setting' => 'ShowCCIssueStart']);
        $getCCIssueStart = json_decode(json_encode($getCCIssueStart));
        $getInvoiceIncrement = API::post('GetConfigurationValue', ['setting' => 'InvoiceIncrement']);
        $getInvoiceIncrement = json_decode(json_encode($getInvoiceIncrement));
        $getInvoiceStartNumber = DB::select(DB::raw("SELECT ${pfx}invoiceitems.invoiceid FROM ${pfx}invoiceitems ORDER BY {$pfx}invoiceitems.invoiceid DESC LIMIT 1"));
        $lastInvoiceId = 0;
        foreach ($getInvoiceStartNumber as $invoice) {
            $lastInvoiceId = $invoice->invoiceid;
        }
        // Multiple Data Selected
        $rawAcceptedCardList = API::post('GetConfigurationValue', ['setting' => 'AcceptedCardTypeList']);
        $rawAcceptedCardList = json_decode(json_encode($rawAcceptedCardList));
        $getAcceptedCardList = explode(",", $rawAcceptedCardList->value);
        $rawAcceptedCardActive = API::post('GetConfigurationValue', ['setting' => 'AcceptedCardTypes']);
        $rawAcceptedCardActive = json_decode(json_encode($rawAcceptedCardActive));
        $getAcceptedCardActive = explode(",", $rawAcceptedCardActive->value);
        // Multiple Data

        //Credit Tab
        $getAddFundsEnabled = API::post('GetConfigurationValue', ['setting' => 'AddFundsEnabled']);
        $getAddFundsEnabled = json_decode(json_encode($getAddFundsEnabled));
        $getAddFundsMinimum = API::post('GetConfigurationValue', ['setting' => 'AddFundsMinimum']);
        $getAddFundsMinimum = json_decode(json_encode($getAddFundsMinimum));
        $getAddFundsMaximum = API::post('GetConfigurationValue', ['setting' => 'AddFundsMaximum']);
        $getAddFundsMaximum = json_decode(json_encode($getAddFundsMaximum));
        $getAddFundsMaximumBalance = API::post('GetConfigurationValue', ['setting' => 'AddFundsMaximumBalance']);
        $getAddFundsMaximumBalance = json_decode(json_encode($getAddFundsMaximumBalance));
        $getAddFundsRequireOrder = API::post('GetConfigurationValue', ['setting' => 'AddFundsRequireOrder']);
        $getAddFundsRequireOrder = json_decode(json_encode($getAddFundsRequireOrder));
        $getNoAutoApplyCredit = API::post('GetConfigurationValue', ['setting' => 'NoAutoApplyCredit']);
        $getNoAutoApplyCredit = json_decode(json_encode($getNoAutoApplyCredit));
        $getCreditOnDowngrade = API::post('GetConfigurationValue', ['setting' => 'CreditOnDowngrade']);
        $getCreditOnDowngrade = json_decode(json_encode($getCreditOnDowngrade));

        //Affiliate Tab
        $getAffiliateEnabled = API::post('GetConfigurationValue', ['setting' => 'AffiliateEnabled']);
        $getAffiliateEnabled = json_decode(json_encode($getAffiliateEnabled));
        $getAffiliateEarningPercent = API::post('GetConfigurationValue', ['setting' => 'AffiliateEarningPercent']);
        $getAffiliateEarningPercent = json_decode(json_encode($getAffiliateEarningPercent));
        $getAffiliateBonusDeposit = API::post('GetConfigurationValue', ['setting' => 'AffiliateBonusDeposit']);
        $getAffiliateBonusDeposit = json_decode(json_encode($getAffiliateBonusDeposit));
        $getAffiliatePayout = API::post('GetConfigurationValue', ['setting' => 'AffiliatePayout']);
        $getAffiliatePayout = json_decode(json_encode($getAffiliatePayout));
        $getAffiliatesDelayCommission = API::post('GetConfigurationValue', ['setting' => 'AffiliatesDelayCommission']);
        $getAffiliatesDelayCommission = json_decode(json_encode($getAffiliatesDelayCommission));
        $getPayoutDepartment = Ticketdepartment::all()->pluck('name', 'id')->toArray();
        $getActivePayoutDept = API::post('GetConfigurationValue', ['setting' => 'AffiliateDepartment']);
        $getActivePayoutDept = json_decode(json_encode($getActivePayoutDept));
        $getAffiliateLinks = API::post('GetConfigurationValue', ['setting' => 'AffiliateLinks']);
        $getAffiliateLinks = json_decode(json_encode($getAffiliateLinks));

        //Security Tab
        $getEnableEmailVerification = API::post('GetConfigurationValue', ['setting' => 'EnableEmailVerification']);
        $getEnableEmailVerification = json_decode(json_encode($getEnableEmailVerification));
        $getCaptchaSetting = API::post('GetConfigurationValue', ['setting' => 'CaptchaSetting']);
        $getCaptchaSetting = json_decode(json_encode($getCaptchaSetting));
        $getCaptchaType = API::post('GetConfigurationValue', ['setting' => 'CaptchaType']);
        $getCaptchaType = json_decode(json_encode($getCaptchaType));
        $getCaptchaForms = API::post('GetConfigurationValue', ['setting' => 'CaptchaForms']);
        $getCaptchaForms = json_decode(json_encode($getCaptchaForms));
        $jsonCaptchaFormsDB = json_decode($getCaptchaForms->value, TRUE); //<----- Boolean Value for CaptchaForms
        $getReCaptchaPublicKey = API::post('GetConfigurationValue', ['setting' => 'ReCAPTCHAPublicKey']);
        $getReCaptchaPublicKey = json_decode(json_encode($getReCaptchaPublicKey));
        $getReCaptchaPrivateKey = API::post('GetConfigurationValue', ['setting' => 'ReCAPTCHAPrivateKey']);
        $getReCaptchaPrivateKey = json_decode(json_encode($getReCaptchaPrivateKey));
        $getAutoGeneratedPasswordFormat = API::post('GetConfigurationValue', ['setting' => 'AutoGeneratedPasswordFormat']);
        $getAutoGeneratedPasswordFormat = json_decode(json_encode($getAutoGeneratedPasswordFormat));
        $getRequiredPWStrength = API::post('GetConfigurationValue', ['setting' => 'RequiredPWStrength']);
        $getRequiredPWStrength = json_decode(json_encode($getRequiredPWStrength));
        $getInvalidLoginBanLength = API::post('GetConfigurationValue', ['setting' => 'InvalidLoginBanLength']);
        $getInvalidLoginBanLength = json_decode(json_encode($getInvalidLoginBanLength));
        $getDataWhitelistIP = API::post('GetConfigurationValue', ['setting' => 'WhitelistedIPs']);
        $getDataWhitelistIP = json_decode(json_encode($getDataWhitelistIP));
        $arrayWhitelistIP = $getDataWhitelistIP->value ? unserialize($getDataWhitelistIP->value) : []; //WhitelistIP to Array
        $getSendFailedLoginWhitelist = API::post('GetConfigurationValue', ['setting' => 'sendFailedLoginWhitelist']);
        $getSendFailedLoginWhitelist = json_decode(json_encode($getSendFailedLoginWhitelist));
        $getDisableAdminPWReset = API::post('GetConfigurationValue', ['setting' => 'DisableAdminPWReset']);
        $getDisableAdminPWReset = json_decode(json_encode($getDisableAdminPWReset));
        $getCCAllowCustomerDelete = API::post('GetConfigurationValue', ['setting' => 'CCAllowCustomerDelete']);
        $getCCAllowCustomerDelete = json_decode(json_encode($getCCAllowCustomerDelete));
        $getDisableSessionIPCheck = API::post('GetConfigurationValue', ['setting' => 'DisableSessionIPCheck']);
        $getDisableSessionIPCheck = json_decode(json_encode($getDisableSessionIPCheck));
        $getAllowSmartyPhpTags = API::post('GetConfigurationValue', ['setting' => 'AllowSmartyPhpTags']);
        $getAllowSmartyPhpTags = json_decode(json_encode($getAllowSmartyPhpTags));
        $getProxyHeader = API::post('GetConfigurationValue', ['setting' => 'proxyHeader']);
        $getProxyHeader = json_decode(json_encode($getProxyHeader));
        $getDataAPIAllowedIPs = API::post('GetConfigurationValue', ['setting' => 'APIAllowedIPs']);
        $getDataAPIAllowedIPs = json_decode(json_encode($getDataAPIAllowedIPs));
        $arrayDataAPIallowedIP = unserialize($getDataAPIAllowedIPs->value);
        $getLogAPIAuthentcation = API::post('GetConfigurationValue', ['setting' => 'LogAPIAuthentication']);
        $getLogAPIAuthentcation = json_decode(json_encode($getLogAPIAuthentcation));
        $getAllowAutoAuth = API::post('GetConfigurationValue', ['setting' => 'AllowAutoAuth']);
        $getAllowAutoAuth = json_decode(json_encode($getAllowAutoAuth));
        // dd($arrayWhitelistIP);

        //Social Tab
        $getTwitterUsername = API::post('GetConfigurationValue', ['setting' => 'TwitterUsername']);
        $getTwitterUsername = json_decode(json_encode($getTwitterUsername));
        $getAnnouncementsTweet = API::post('GetConfigurationValue', ['setting' => 'AnnouncementsTweet']);
        $getAnnouncementsTweet = json_decode(json_encode($getAnnouncementsTweet));
        $getFBRecommend = API::post('GetConfigurationValue', ['setting' => 'AnnouncementsFBRecommend']);
        $getFBRecommend = json_decode(json_encode($getFBRecommend));
        $getFBComment = API::post('GetConfigurationValue', ['setting' => 'AnnouncementsFBComments']);
        $getFBComment = json_decode(json_encode($getFBComment));

        //Other Tab
        $getEmailMarketingRequireOptIn = API::post('GetConfigurationValue', ['setting' => 'EmailMarketingRequireOptIn']);
        $getEmailMarketingRequireOptIn = json_decode(json_encode($getEmailMarketingRequireOptIn));
        $getAllowClientsEmailOptOut = API::post('GetConfigurationValue', ['setting' => 'AllowClientsEmailOptOut']);
        $getAllowClientsEmailOptOut = json_decode(json_encode($getAllowClientsEmailOptOut));
        $getEmailMarketingOptInMessage = API::post('GetConfigurationValue', ['setting' => 'EmailMarketingOptInMessage']);
        $getEmailMarketingOptInMessage = json_decode(json_encode($getEmailMarketingOptInMessage));
        $getClientDisplayFormat = API::post('GetConfigurationValue', ['setting' => 'ClientDisplayFormat']);
        $getClientDisplayFormat = json_decode(json_encode($getClientDisplayFormat));
        $getDefaultToClientArea = API::post('GetConfigurationValue', ['setting' => 'DefaultToClientArea']);
        $getDefaultToClientArea = json_decode(json_encode($getDefaultToClientArea));
        $getAllowClientRegister = API::post('GetConfigurationValue', ['setting' => 'AllowClientRegister']);
        $getAllowClientRegister = json_decode(json_encode($getAllowClientRegister));
        $getDisableClientEmailPreferences = API::post('GetConfigurationValue', ['setting' => 'DisableClientEmailPreferences']);
        $getDisableClientEmailPreferences = json_decode(json_encode($getDisableClientEmailPreferences));
        //--OptionalClientFields
        $getProfileOptionalFields = API::post('GetConfigurationValue', ['setting' => 'ClientsProfileOptionalFields']);
        $getProfileOptionalFields = json_decode(json_encode($getProfileOptionalFields));
        $jsonProfileOptionFromDB = json_decode($getProfileOptionalFields->value, TRUE);

        //--LockedClientFields
        $getProfileLockedFields = API::post('GetConfigurationValue', ['setting' => 'ClientsProfileUneditableFields']);
        $getProfileLockedFields = json_decode(json_encode($getProfileLockedFields));
        $jsonProfileLockedFromDB = json_decode($getProfileLockedFields->value, TRUE);
        // dd($jsonProfileLockedFromDB);
        $getClientDetailsNotify = API::post('GetConfigurationValue', ['setting' => 'SendEmailNotificationonUserDetailsChange']);
        $getClientDetailsNotify = json_decode(json_encode($getClientDetailsNotify));
        $getShowCancellationButton = API::post('GetConfigurationValue', ['setting' => 'ShowCancellationButton']);
        $getShowCancellationButton = json_decode(json_encode($getShowCancellationButton));
        $getSendAffiliateReportMonthly = API::post('GetConfigurationValue', ['setting' => 'SendAffiliateReportMonthly']);
        $getSendAffiliateReportMonthly = json_decode(json_encode($getSendAffiliateReportMonthly));
        $getBannedSubdomainPrefixes = API::post('GetConfigurationValue', ['setting' => 'BannedSubdomainPrefixes']);
        $getBannedSubdomainPrefixes = json_decode(json_encode($getBannedSubdomainPrefixes));
        $getEnableSafeInclude = API::post('GetConfigurationValue', ['setting' => 'EnableSafeInclude']);
        $getEnableSafeInclude = json_decode(json_encode($getEnableSafeInclude));
        $getDisplayErrors = API::post('GetConfigurationValue', ['setting' => 'DisplayErrors']);
        $getDisplayErrors = json_decode(json_encode($getDisplayErrors));
        $getLogErrors = API::post('GetConfigurationValue', ['setting' => 'LogErrors']);
        $getLogErrors = json_decode(json_encode($getLogErrors));
        $getSQLErrorReporting = API::post('GetConfigurationValue', ['setting' => 'SQLErrorReporting']);
        $getSQLErrorReporting = json_decode(json_encode($getSQLErrorReporting));
        $getHooksDebugMode = API::post('GetConfigurationValue', ['setting' => 'HooksDebugMode']);
        $getHooksDebugMode = json_decode(json_encode($getHooksDebugMode));


        //InstaInvoice Tab
        // $gateways =  \App\Helpers\Gateway::GetGatewaysArray();
        // // dd($gateways);
        // $invoice = new \App\Helpers\InvoiceClass(806);
        // $data = $invoice->getOutput();
        // $totalPay = Format::Currency($data['total']->toNumeric(), null, ['format' => $data['total']->getCurrency()['format']]);
        // // dd($totalPay);
        // $paymentbutton = $invoice->getData("status") == "Unpaid" && 0 < $invoice->getData("balance") ? $invoice->getPaymentLink() : "";
        // // dd($paymentbutton);

        return view('pages.setup.generalsettings.general.index', [
            //General Tab Parameters
            'companyName' => $getCompanyName->value ?? "",
            'email' => $getEmail->value ?? "",
            'domain' => $getDomain->value ?? "",
            'logoURL' => $getLogo->value ?? "",
            'payTo' => $getPayTo->value ?? "",
            'systemURL' => $getSystemURL->value ?? "",
            'templateGeneral' => $getTemplate->value ?? "",
            'activityLimit' => $getActivityLimit->value ?? "",
            'recordPerPage' => $getRecordPerPage->value ?? "",
            'maintenanceMode' => $getMaintenanceMode->value ?? "",
            'maintenanceMessage' => $getMaintenanceModeMessage->value ?? "",
            'maintenanceURL' => $getMaintenanceModeURL->value ?? "",
            'friendlyURL' => $getFriendlyURL->value ?? "",
            //Localisation Tab Parameters
            'systemCharset' => $getCharset->value ?? "",
            'dateFormat' => $getDateFormat->value ?? "",
            'clientDateFormat' => $getClientDateFormat->value ?? "",
            'defaultCountry' => $getDefaultCountry->value ?? "",
            'language' => $getLanguage->value ?? "",
            'languageChange' => $getLanguageMenu->value ?? "",
            'enableTranslation' => $getEnableTranslation->value ?? "",
            'utfCutOption' => $getUtfCutOption->value ?? "",
            'phoneNumberDropdown' => $getPhoneNumberDropdown->value ?? "",
            //Ordering Tab Parameters
            'orderDaysGrace' => $getOrderDaysGrace->value ?? "",
            'orderFormTemplate' => $getOrderFormTemplate->value ?? "",
            'templateList' => $getTemplateList,
            'orderFormSidebarToggle' => $getOrderFormSidebarToggle->value ?? "",
            'enableTOSAccept' => $getEnableTOSAccept->value ?? "",
            'termsOfService' => $getTermsOfService->value ?? "",
            'autoDirectOnCheckout' => $getAutoRedirectoInvoice->value ?? "",
            'noteOnCheckout' => $getShowNotesFieldonCheckout->value ?? "",
            'pricingBreakdown' => $getProductMonthlyPricingBreakdown->value ?? "",
            'allowDomainTwice' => $getAllowDomainsTwice->value ?? "",
            'noInvoiceEmail' => $getNoInvoiceEmailOnOrder->value ?? "",
            'skipFraud' => $getSkipFraud->value ?? "",
            'autoProvision' => $getAutoProvision->value ?? "",
            'generateRandomUsername' => $getGenerateRandomUsername->value ?? "",
            'prorataClientData' => $getProrataClientsAnniversaryDate->value ?? "",
            //Domain Tab Parameters
            'allowRegister' => $getAllowRegister->value ?? "",
            'allowTransfer' => $getAllowTransfer->value ?? "",
            'allowOwnDomain' => $getAllowOwnDomain->value ?? "",
            'enableRenewDomain' => $getEnableDomainRenew->value ?? "",
            'autoRenewOnPayment' => $getAutoRenewOnPayment->value ?? "",
            'renewRequireProd' => $getRenewRequireProduct->value ?? "",
            'autoRenewDefault' => $getAutoRenewDefault->value ?? "",
            'domainTodoList' => $getDomainToDoListEntries->value ?? "",
            'allowIDNDomains' => $getAllowIDNDomains->value ?? "",
            'graceRedemptionFee' => $getGraceAndRedemptionFees->value ?? "",
            'nameserver1' => $getDefaultNameserver1->value ?? "",
            'nameserver2' => $getDefaultNameserver2->value ?? "",
            'nameserver3' => $getDefaultNameserver3->value ?? "",
            'nameserver4' => $getDefaultNameserver4->value ?? "",
            'nameserver5' => $getDefaultNameserver5->value ?? "",
            'useClientDetails' => $getClientsDetails->value ?? "",
            //Mail Tab Parameters
            'mailType' => $getMailType->value ?? "",
            'mailEncoding' => $getMailEncoding->value ?? "",
            'smtpPort' => $getSMTPPort->value ?? "",
            'smtpHost' => $getSMTPHost->value ?? "",
            'smtpUsername' => $getSMTPUsername->value ?? "",
            'smtpPassword' => $getSMTPPassword->value ?? "",
            'smtpSSL' => $getSMTPSSL->value ?? "",
            'mailSignature' => $getMailSignature->value ?? "",
            'mailCSS' => $getEmailCSS->value ?? "",
            'globalHeader' => $getEmailGlobalHeader->value ?? "",
            'globalFooter' => $getEmailGlobalFooter->value ?? "",
            'emailFromName' => $getSystemEmailsFromName->value ?? "",
            'emailFromEmail' => $getSystemEmailsFromEmail->value ?? "",
            'bccMessage' => $getBCCMessages->value ?? "",
            'formDept' => $getContactFormDept->value ?? "",
            'formContact' => $getContactFormTo->value ?? "",
            //Support Tab Parameters
            'supportModule' => $getSupportModule->value ?? "",
            'ticketMask' => $getTicketMask->value ?? "",
            'ticketOrder' => $getTicketOrder->value ?? "",
            'ticketLimit' => $getTicketLimit->value ?? "",
            'clientOnlyDept' => $getShowClientOnlyDepts->value ?? "",
            'requireLoginClient' => $getRequireLoginforClientTickets->value ?? "",
            'knowledgebaseSuggestion' => $getSupportTicketKBSuggestions->value ?? "",
            'attachmentThumbnails' => $getAttachmentThumbnails->value ?? "",
            'ticketRatingEnabled' => $getTicketRatingEnabled->value ?? "",
            'ticketCarbon' => $getTicketCarbonRecipients->value ?? "",
            'preventEmailReopening' => $getPreventEmailReopening->value ?? "",
            'lastReplyTimestamp' => $getUpdateLastReplyTimestamp->value ?? "",
            'emailsLogging' => $getEmailsLogging->value ?? "",
            'allowedFileTypes' => $getTicketAllowedFileTypes->value ?? "",
            'networkIssueLogin' => $getNetworkIssuesRequireLogin->value ?? "",
            'downloadProductLinked' => $getDownloadsIncludeProductLinked->value ?? "",
            //Invoice Tab Parameters
            'acceptedCard' => $getAcceptedCardList,
            'activeCard' => $getAcceptedCardActive,
            'invoiceGeneration' => $getInvoiceGeneration->value ?? "",
            'metricInvoice' => $getMetricInvoicing->value ?? "",
            'pdfInvoiceEnable' => $getPDFInvoice->value ?? "",
            'pdfPaperSize' => $getPDFPaper->value ?? "",
            'pdfFontFamily' => $getFontFamily->value ?? "",
            'clientSnapshot' => $getClientSnapshot->value ?? "",
            'massPayment' => $getMassPay->value ?? "",
            'clientChangeGateway' => $getClientChangeGateway->value ?? "",
            'groupSimiliarItems' => $getGroupSimItems->value ?? "",
            'autoCancel' => $getAutoCancel->value ?? "",
            'autoSubs' => $getAutoSubs->value ?? "",
            'proformaInvoicing' => $getProformaInvoicing->value ?? "",
            'seqInvoiceNumbering' => $getSeqInvoiceNumbering->value ?? "",
            'seqInvoiceNumberFormat' => $getSeqInvoiceNumberFormat->value ?? "",
            'seqInvoiceNumberValue' => $getSeqInvoiceNumberValue->value ?? "",
            'lateFeeType' => $getLateFeeType->value ?? "",
            'lateFeeAmount' => $getLateFeeAmount->value ?? "",
            'lateFeeMinimum' => $getLateFeeMinimum->value ?? "",
            'ccIssueStart' => $getCCIssueStart->value ?? "",
            'invoiceIncrement' => $getInvoiceIncrement->value ?? "",
            'lastInvoiceId' => $lastInvoiceId,
            //Credit Tab Parameters
            'addFundsEnabled' => $getAddFundsEnabled->value ?? "",
            'addFundsMinimum' => $getAddFundsMinimum->value ?? "",
            'addFundsMaximum' => $getAddFundsMaximum->value ?? "",
            'addFundsMaximumBalance' => $getAddFundsMaximumBalance->value ?? "",
            'addFundsRequireOrder' => $getAddFundsRequireOrder->value ?? "",
            'noAutoApplyCredit' => $getNoAutoApplyCredit->value ?? "",
            'creditDowngrade' => $getCreditOnDowngrade->value ?? "",
            //Affiliate Tab
            'affiliateEnabled' => $getAffiliateEnabled->value ?? "",
            'affiliateEarningPercent' => $getAffiliateEarningPercent->value ?? "",
            'affiliateBonus' => $getAffiliateBonusDeposit->value ?? "",
            'affiliatePayout' => $getAffiliatePayout->value ?? "",
            'affiliateDelay' => $getAffiliatesDelayCommission->value ?? "",
            'affiliatePayoutDepartment' => $getPayoutDepartment,
            'activeAffDept' => $getActivePayoutDept->value ?? "",
            'affiliateLinks' => $getAffiliateLinks->value ?? "",
            //Security Tab
            'emailVerification' => $getEnableEmailVerification->value ?? "",
            'captchaSettings' => $getCaptchaSetting->value ?? "",
            'captchaType' => $getCaptchaType->value ?? "",
            'captchaForms' => $jsonCaptchaFormsDB,
            'captchaPublicKey' => $getReCaptchaPublicKey->value ?? "",
            'captchaPrivateKey' => $getReCaptchaPrivateKey->value ?? "",
            'generatedPasswordFormat' => $getAutoGeneratedPasswordFormat->value ?? "",
            'passStrength' => $getRequiredPWStrength->value ?? "",
            'loginBanLength' => $getInvalidLoginBanLength->value ?? "",
            'arrayWhitelistIP' => $arrayWhitelistIP,
            'sendFailedLoginWhitelist' => $getSendFailedLoginWhitelist->value ?? "",
            'disableAdminPWReset' => $getDisableAdminPWReset->value ?? "",
            'allowClientPayMethodRemoval' => $getCCAllowCustomerDelete->value ?? "",
            'disableSessionIP' => $getDisableSessionIPCheck->value ?? "",
            'smartyPHPtags' => $getAllowSmartyPhpTags->value ?? "",
            'proxyHeader' => $getProxyHeader->value ?? "",
            'arrayDataAPIallowedIP' => $arrayDataAPIallowedIP,
            'apiLogAuthentication' => $getLogAPIAuthentcation->value ?? "",
            'allowAutoAuth' => $getAllowAutoAuth->value ?? "",
            //Social Tab
            'twitterUsername' => $getTwitterUsername->value ?? "",
            'tweetAnnounce' => $getAnnouncementsTweet->value ?? "",
            'fbRecommend' => $getFBRecommend->value ?? "",
            'fbComment' => $getFBComment->value ?? "",
            //Other Tab
            'emailMarketing' => $getEmailMarketingRequireOptIn->value ?? "",
            'requireUserOpt' => $getAllowClientsEmailOptOut->value ?? "",
            'emailMarketingMessage' => $getEmailMarketingOptInMessage->value ?? "",
            'clientDisplayFormat' => $getClientDisplayFormat->value ?? "",
            'defaultToClientArea' => $getDefaultToClientArea->value ?? "",
            'allowClientRegister' => $getAllowClientRegister->value ?? "",
            'emailClientPreferences' => $getDisableClientEmailPreferences->value ?? "",
            'optionProfileFields' => $jsonProfileOptionFromDB,
            'lockedProfileFields' => $jsonProfileLockedFromDB,
            'clientDetailsNotify' => $getClientDetailsNotify->value ?? "",
            'showCancellation' => $getShowCancellationButton->value ?? "",
            'sendAffiliateReport' => $getSendAffiliateReportMonthly->value ?? "",
            'bannedSubdomain' => $getBannedSubdomainPrefixes->value ?? "",
            'enableSafeInclude' => $getEnableSafeInclude->value ?? "",
            'displayErrors' => $getDisplayErrors->value ?? "",
            'logErrors' => $getLogErrors->value ?? "",
            'sqlReporting' => $getSQLErrorReporting->value ?? "",
            'hooksDebugMode' => $getHooksDebugMode->value ?? "",

            //instaInvoice
            'gateways' => [],
            'paymentbutton' => "",
            'totalPay' => 0,

            // theme
            'themes' => $themes,
            'orderformThemes' => $orderformThemes,
        ]);
    }

    public function GeneralSettings(Request $request)
    {
        global $CONFIG;
        $aInt = new \App\Helpers\Admin("Configure General Settings", false);
        $route = "admin.pages.setup.generalsettings.general.index";
        $action = $request->input("action");
        $ipaddress = $request->input("ipaddress");
        $removeip = $request->input("removeip");
        $notes = $request->input("notes");
        if ($action == "addWhiteListIp") {
            if (defined("DEMO_MODE")) {
                // exit;
                return '';
            }
            $whitelistedips = \App\Helpers\Cfg::get("WhitelistedIPs");
            $whitelistedips = (new \App\Helpers\Client)->safe_unserialize($whitelistedips);
            $whitelistedips[] = array("ip" => $ipaddress, "note" => $notes);
            \App\Helpers\Cfg::set("WhitelistedIPs", (new \App\Helpers\Pwd)->safe_serialize($whitelistedips));
            \App\Helpers\AdminFunctions::logAdminActivity("General Settings Changed. Whitelisted IP Added: '" . $ipaddress . "'");
            \App\Models\Bannedip::where(array("ip" => $ipaddress))->delete();
            // exit;
            return '';
        }
        if ($action == "deletewhitelistip") {
            if (defined("DEMO_MODE")) {
                // exit;
                return '';
            }
            $removeip = explode(" - ", $removeip);
            $whitelistedips = \App\Helpers\Cfg::get("WhitelistedIPs");
            $whitelistedips = (new \App\Helpers\Client)->safe_unserialize($whitelistedips);
            foreach ($whitelistedips as $k => $v) {
                // if (Str::contains($v['ip'], $removeip[0])) {
                //     unset($whitelistedips[$k]);
                // }
                if ($v['ip'] == $removeip[0]) {
                    unset($whitelistedips[$k]);
                }
            }
            \App\Helpers\Cfg::set("WhitelistedIPs", (new \App\Helpers\Pwd)->safe_serialize($whitelistedips));
            DB::table("tblconfiguration")->where(array("setting" => "WhitelistedIPs"))->update(array("value" => (new \App\Helpers\Pwd)->safe_serialize($whitelistedips)));
            \App\Helpers\AdminFunctions::logAdminActivity("General Settings Changed. Whitelisted IP Removed: '" . $removeip[0] . "'");
            // exit;
            return '';
        }
        if ($action == "addApiIp") {
            if (defined("DEMO_MODE")) {
                // exit;
                return '';
            }
            $whitelistedips = \App\Helpers\Cfg::get("APIAllowedIPs");
            $whitelistedips = (new \App\Helpers\Client)->safe_unserialize($whitelistedips);
            $whitelistedips[] = array("ip" => $ipaddress, "note" => $notes);
            \App\Helpers\Cfg::set("APIAllowedIPs", (new \App\Helpers\Pwd)->safe_serialize($whitelistedips));
            \App\Helpers\AdminFunctions::logAdminActivity("General Settings Changed. API Allowed IP Added: '" . $ipaddress . "'");
            // exit;
            return '';
        }
        if ($action == "deleteapiip") {
            if (defined("DEMO_MODE")) {
                // exit;
                return '';
            }
            $removeip = explode(" - ", $removeip);
            $whitelistedips = \App\Helpers\Cfg::get("APIAllowedIPs");
            $whitelistedips = (new \App\Helpers\Client)->safe_unserialize($whitelistedips);
            foreach ($whitelistedips as $k => $v) {
                if ($v["ip"] == $removeip[0]) {
                    unset($whitelistedips[$k]);
                }
            }
            \App\Helpers\Cfg::set("APIAllowedIPs", (new \App\Helpers\Pwd)->safe_serialize($whitelistedips));
            \App\Helpers\AdminFunctions::logAdminActivity("General Settings Changed. API Allowed IP Removed: '" . $removeip[0] . "'");
            // exit;
            return '';
        }
        if ($action == "deletetrustedproxyip") {
            if (defined("DEMO_MODE")) {
                // exit;
                return '';
            }
            $removeip = explode(" - ", $removeip);
            $whitelistedips = \App\Helpers\Cfg::get("trustedProxyIps");
            $whitelistedips = json_decode($whitelistedips, true);
            $whitelistedips = is_array($whitelistedips) ? $whitelistedips : array();
            foreach ($whitelistedips as $k => $v) {
                if ($v["ip"] == $removeip[0]) {
                    unset($whitelistedips[$k]);
                }
            }
            \App\Helpers\Cfg::set("trustedProxyIps", json_encode($whitelistedips));
            
            return '';
        }
        $themesData = \App\Helpers\ThemeManager::all();
        // $clientLanguages = WHMCS\Language\ClientLanguage::getLanguages();
        $clientLanguages = [];
        $clientTemplates = array();
        $orderFormTemplates = array();

        // client area themes
        $clientTemplates = collect($themesData)->where('name', '!=', 'admin')->where('vendor', config('themes-manager.composer.vendor'));
        // orderform themes
        $orderFormTemplates = collect($themesData)->where('vendor', \App\Helpers\ThemeManager::orderformTheme());
        $frm1 = new \App\Helpers\Form();
        if ($action == "save") {
            if (defined("DEMO_MODE")) {
                // redir("demo=1");
                return redirect()->route($route, ['demo' => '1']);
            }
            DB::beginTransaction();
            try {
                $tab = $request->input("tab") ?? 0;
                $affiliatebonusdeposit = $request->input("affiliatebonusdeposit");
                $affiliatepayout = $request->input("affiliatepayout");
                $language = $request->input("language");
                $template = $request->input("template");
                $orderformtemplate = $request->input("orderformtemplate");
                $clientsprofoptional = $request->input("clientsprofoptional");
                $clientsprofuneditable = $request->input("clientsprofuneditable");
                $tcpdffont = $request->input("tcpdffont");
                $tcpdffontcustom = $request->input("tcpdffontcustom");
                $addfundsminimum = $request->input("addfundsminimum");
                $addfundsmaximum = $request->input("addfundsmaximum");
                $addfundsmaximumbalance = $request->input("addfundsmaximumbalance");
                $latefeeminimum = $request->input("latefeeminimum");
                $domain = $request->input("domain");
                $systemurl = $request->input("systemurl");
                $domphone = $request->input("domphone");
                $ShowNotesFieldonCheckout = $request->input("ShowNotesFieldonCheckout");
                
                // unset($_SESSION["Language"]);
                // unset($_SESSION["Template"]);
                // unset($_SESSION["OrderFormTemplate"]);
                session()->forget(["Language","Template","OrderFormTemplate"]);
                $existingConfig = \App\Models\Setting::allAsArray();

                // Tambahkan inisialisasi ShowNotesFieldonCheckout
                if (!isset($existingConfig["ShowNotesFieldonCheckout"])) {
                    $existingConfig["ShowNotesFieldonCheckout"] = "on";
                    
                    // Tambahkan ke database jika belum ada
                    $result = DB::table('tblconfiguration')
                        ->where('setting', 'ShowNotesFieldonCheckout')
                        ->first();

                    if (!$result) {
                        DB::table('tblconfiguration')->insert([
                            'setting' => 'ShowNotesFieldonCheckout',
                            'value' => 'on',
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                    }
                }

                // Tambahkan inisialisasi MailEncoding
                if (!isset($existingConfig["MailEncoding"])) {
                    $existingConfig["MailEncoding"] = "0"; 
                    
                    // Tambahkan ke database jika belum ada
                    $result = DB::table('tblconfiguration')
                        ->where('setting', 'MailEncoding')
                        ->first();

                    if (!$result) {
                        DB::table('tblconfiguration')->insert([
                            'setting' => 'MailEncoding',
                            'value' => '0',
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                    }
                }
                
                // Tambahkan inisialisasi SMTPPort
                if (!isset($existingConfig["SMTPPort"])) {
                    $existingConfig["SMTPPort"] = "25";
                    
                    // Tambahkan ke database jika belum ada
                    $result = DB::table('tblconfiguration')
                        ->where('setting', 'SMTPPort')
                        ->first();

                    if (!$result) {
                        DB::table('tblconfiguration')->insert([
                            'setting' => 'SMTPPort',
                            'value' => '25',
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                    }
                }

                // Tambahkan inisialisasi SMTPHost
                if (!isset($existingConfig["SMTPHost"])) {
                    $existingConfig["SMTPHost"] = "";
                    
                    // Tambahkan ke database jika belum ada
                    $result = DB::table('tblconfiguration')
                        ->where('setting', 'SMTPHost')
                        ->first();

                    if (!$result) {
                        DB::table('tblconfiguration')->insert([
                            'setting' => 'SMTPHost',
                            'value' => '',
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                    }
                }

                // Tambahkan inisialisasi SMTPUsername
                if (!isset($existingConfig["SMTPUsername"])) {
                    $existingConfig["SMTPUsername"] = "";
                    
                    // Tambahkan ke database jika belum ada
                    $result = DB::table('tblconfiguration')
                        ->where('setting', 'SMTPUsername')
                        ->first();

                    if (!$result) {
                        DB::table('tblconfiguration')->insert([
                            'setting' => 'SMTPUsername',
                            'value' => '',
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                    }
                }

                // Tambahkan inisialisasi SMTPSSL
                if (!isset($existingConfig["SMTPSSL"])) {
                    $existingConfig["SMTPSSL"] = "";
                    
                    // Tambahkan ke database jika belum ada
                    $result = DB::table('tblconfiguration')
                        ->where('setting', 'SMTPSSL')
                        ->first();

                    if (!$result) {
                        DB::table('tblconfiguration')->insert([
                            'setting' => 'SMTPSSL',
                            'value' => '',
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                    }
                }

                // Tambahkan inisialisasi SMTPPassword
                if (!isset($existingConfig["SMTPPassword"])) {
                    $existingConfig["SMTPSSL"] = "L8ban/115RFVw8rDN4KJ0GVZ72bnMitcHEiZmFQ8dg==";
                    
                    // Tambahkan ke database jika belum ada
                    $result = DB::table('tblconfiguration')
                        ->where('setting', 'SMTPPassword')
                        ->first();

                    if (!$result) {
                        DB::table('tblconfiguration')->insert([
                            'setting' => 'SMTPPassword',
                            'value' => 'L8ban/115RFVw8rDN4KJ0GVZ72bnMitcHEiZmFQ8dg==',
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                    }
                }

                $ticketEmailLimit = intval($request->input("ticketEmailLimit"));
                if (!$ticketEmailLimit) {
                    // redir("tab=" . $tab . "&error=limitnotnumeric");
                    return redirect()->route($route, ['tab' => $tab, 'error' => 'limitnotnumeric']);
                }
                if (!\App\Helpers\InvoiceClass::isValidCustomInvoiceNumberFormat(\App\Helpers\Sanitize::decode($request->input("sequentialinvoicenumberformat")))) {
                    // redir("tab=" . $tab . "&error=invalidCustomInvoiceNumber");
                    return redirect()->route($route, ['tab' => $tab, 'error' => 'invalidCustomInvoiceNumber']);
                }
                $affiliatebonusdeposit = number_format($affiliatebonusdeposit, 2, ".", "");
                $affiliatepayout = number_format($affiliatepayout, 2, ".", "");
                if (!in_array($language, $clientLanguages)) {
                    if (in_array("english", $clientLanguages)) {
                        $language = "english";
                    } else {
                        $language = $clientLanguages[0] ?? "";
                    }
                }
                if (!$clientTemplates->where('name', $template)->first()) {
                    $template = $clientTemplates->first()['name'];
                }
                if (!$orderFormTemplates->where('name', $orderformtemplate)->first()) {
                    $orderformtemplate = $orderFormTemplates->first()['name'];
                }
                $clientsprofoptional = $clientsprofoptional ? implode(",", $clientsprofoptional) : "";
                $clientsprofuneditable = $clientsprofuneditable ? implode(",", $clientsprofuneditable) : "";
                if ($tcpdffont == "custom" && $tcpdffontcustom) {
                    $tcpdffont = $tcpdffontcustom;
                }
                $addfundsminimum = \App\Helpers\Functions::format_as_currency($addfundsminimum);
                $addfundsmaximum = \App\Helpers\Functions::format_as_currency($addfundsmaximum);
                $addfundsmaximumbalance = \App\Helpers\Functions::format_as_currency($addfundsmaximumbalance);
                $latefeeminimum = \App\Helpers\Functions::format_as_currency($latefeeminimum);
                $domain = $this->cleanSystemURL($domain);
                $systemurl = $this->cleanSystemURL($systemurl);
                $domphone = \App\Helpers\Application::formatPostedPhoneNumber("domphone");
                // $captchaUtility = new WHMCS\Utility\Captcha();
                // TODO: $captchaFormsSettings = $captchaUtility->getForms();
                $captchaFormsSettings = [];
                $captchaFormsEnabled = $request->input("captchaform");
                if (!is_array($captchaFormsEnabled)) {
                    $captchaFormsEnabled = array();
                }
                foreach ($captchaFormsSettings as $form => $previousValue) {
                    if (!array_key_exists($form, $captchaFormsEnabled)) {
                        $captchaFormsSettings[$form] = false;
                    } else {
                        $captchaFormsSettings[$form] = true;
                    }
                }
                $captchaFormsSettings = json_encode($captchaFormsSettings);
                $save_arr = array(
                    "CompanyName" => \App\Helpers\Sanitize::decode($request->input('companyname')),
                    "Email" => $request->input('email'),
                    "Domain" => $request->input('domain'),
                    "LogoURL" => $request->input('logourl'),
                    "InvoicePayTo" => $request->input("invoicepayto"),

                    "BillingNotificationReceiver" => $request->input("billingnotificationreceiver"), 

                    "SystemURL" => $systemurl,
                    "Template" => $template,
                    "ActivityLimit" => (int) $request->input("activitylimit"),
                    "NumRecordstoDisplay" => (int) $request->input("numrecords"),
                    "MaintenanceMode" => $request->input("maintenancemode"),
                    "MaintenanceModeMessage" => $request->input("maintenancemodemessage"),
                    "MaintenanceModeURL" => $request->input('maintenancemodeurl'),
                    "Charset" => $request->input("charset"),
                    "DateFormat" => $request->input("dateformat"),
                    "ClientDateFormat" => $request->input('clientdateformat'),
                    "DefaultCountry" => $request->input("defaultcountry"),
                    "Language" => $request->input("language"),
                    "AllowLanguageChange" => $request->input("allowuserlanguage"),
                    "EnableTranslations" => (int) $request->input("enable_translations"),
                    "CutUtf8Mb4" => $request->input("cututf8mb4"),
                    "PhoneNumberDropdown" => (int) $request->input("tel-cc-input"),
                    "OrderDaysGrace" => (int) $request->input("orderdaysgrace"),
                    "OrderFormTemplate" => $orderformtemplate,
                    "OrderFormSidebarToggle" => (int) $request->input("orderfrmsidebartoggle"),
                    "EnableTOSAccept" => $request->input("enabletos"),
                    "TermsOfService" => $request->input("tos"),
                    "AutoRedirectoInvoice" => $request->input("autoredirecttoinvoice"),
                    "ShowNotesFieldonCheckout" => $request->input("shownotesfieldoncheckout"),
                    "ProductMonthlyPricingBreakdown" => $request->input("productmonthlypricingbreakdown"),
                    "AllowDomainsTwice" => $request->input("allowdomainstwice"),
                    "NoInvoiceEmailOnOrder" => $request->input("noinvoicemeailonorder"),
                    "SkipFraudForExisting" => $request->input("skipfraudforexisting"),
                    "AutoProvisionExistingOnly" => $request->input("autoprovisionexistingonly"),
                    "GenerateRandomUsername" => $request->input("generaterandomusername"),
                    "ProrataClientsAnniversaryDate" => $request->input("prorataclientsanniversarydate"),
                    "AllowRegister" => $request->input("allowregister"),
                    "AllowTransfer" => $request->input("allowtransfer"),
                    "AllowOwnDomain" => $request->input("allowowndomain"),
                    "EnableDomainRenewalOrders" => $request->input("enabledomainrenewalorders"),
                    "AutoRenewDomainsonPayment" => $request->input("autorenewdomainsonpayment"),
                    "FreeDomainAutoRenewRequiresProduct" => $request->input('freedomainautorenewrequiresproduct'),
                    "DomainAutoRenewDefault" => $request->input("domainautorenewdefault"),
                    "DomainToDoListEntries" => $request->input("domaintodolistentries"),
                    "AllowIDNDomains" => $request->input('allowidndomains'),
                    "DisableDomainGraceAndRedemptionFees" => (int) $request->input("disabledomaingrace"),
                    "DomainExpirationFeeHandling" => $request->input("domainExpiryFeeHandling"),
                    "DefaultNameserver1" => $request->input('ns1'),
                    "DefaultNameserver2" => $request->input('ns2'),
                    "DefaultNameserver3" => $request->input('ns3'),
                    "DefaultNameserver4" => $request->input('ns4'),
                    "DefaultNameserver5" => $request->input('ns5'),
                    "RegistrarAdminUseClientDetails" => $request->input('domuseclientsdetails'),
                    "RegistrarAdminFirstName" => $request->input('domfirstname'),
                    "RegistrarAdminLastName" => $request->input('domlastname'),
                    "RegistrarAdminCompanyName" => $request->input('domcompanyname'),
                    "RegistrarAdminEmailAddress" => $request->input('domemail'),
                    "RegistrarAdminAddress1" => $request->input('domaddress1'),
                    "RegistrarAdminAddress2" => $request->input('domaddress2'),
                    "RegistrarAdminCity" => $request->input('domcity'),
                    "RegistrarAdminStateProvince" => $request->input('domstate'),
                    "RegistrarAdminPostalCode" => $request->input('dompostcode'),
                    "RegistrarAdminCountry" => $request->input('domcountry'),
                    "RegistrarAdminPhone" => $request->input('domphone'),
                    "MailType" => $request->input("mailtype"),
                    "MailEncoding" => $request->input('mailencoding'),
                    "SMTPPort" => $request->input('smtpport'),
                    "SMTPHost" => $request->input('smtphost'),
                    "SMTPUsername" => $request->input('smtpusername'),
                    "SMTPSSL" => $request->input('smtpssl'),
                    "EmailCSS" => $request->input("emailcss"),
                    "Signature" => $request->input("signature"),
                    "EmailGlobalHeader" => $request->input('emailglobalheader'),
                    "EmailGlobalFooter" => $request->input('emailglobalfooter'),
                    "SystemEmailsFromName" => $request->input("systememailsfromname"),
                    "SystemEmailsFromEmail" => $request->input("systememailsfromemail"),
                    "BCCMessages" => $request->input('bccmessages'),
                    "ContactFormDept" => $request->input("contactformdept"),
                    "ContactFormTo" => $request->input('contactformto'),
                    "SupportModule" => $request->input("supportmodule"),
                    "TicketMask" => $request->input('ticketmask'),
                    "SupportTicketOrder" => $request->input("supportticketorder"),
                    "TicketEmailLimit" => $ticketEmailLimit,
                    "ShowClientOnlyDepts" => $request->input('showclientonlydepts'),
                    "RequireLoginforClientTickets" => $request->input("requireloginforclienttickets"),
                    "SupportTicketKBSuggestions" => $request->input("supportticketkbsuggestions"),
                    "AttachmentThumbnails" => $request->input('attachmentthumbnails'),
                    "TicketRatingEnabled" => $request->input("ticketratingenabled"),
                    "TicketAddCarbonCopyRecipients" => $request->input("ticket_add_cc"),
                    "PreventEmailReopening" => (bool) $request->input("preventEmailReopening") ? 1 : 0,
                    "UpdateLastReplyTimestamp" => $request->input('lastreplyupdate'),
                    "DisableSupportTicketReplyEmailsLogging" => $request->input("disablesupportticketreplyemailslogging"),
                    "TicketAllowedFileTypes" => $request->input("allowedfiletypes"),
                    "NetworkIssuesRequireLogin" => $request->input("networkissuesrequirelogin"),
                    "DownloadsIncludeProductLinked" => $request->input('dlinclproductdl'),
                    "ContinuousInvoiceGeneration" => $request->input("continuousinvoicegeneration"),
                    "EnablePDFInvoices" => $request->input("enablepdfinvoices"),
                    "PDFPaperSize" => $request->input('pdfpapersize'),
                    "TCPDFFont" => $tcpdffont,
                    "StoreClientDataSnapshotOnInvoiceCreation" => $request->input('invoiceclientdatasnapshot'),
                    "EnableMassPay" => $request->input("enablemasspay"),
                    "AllowCustomerChangeInvoiceGateway" => $request->input("allowcustomerchangeinvoicegateway"),
                    "GroupSimilarLineItems" => $request->input("groupsimilarlineitems"),
                    "CancelInvoiceOnCancellation" => $request->input('cancelinvoiceoncancel'),
                    "AutoCancelSubscriptions" => $request->input('autoCancelSubscriptions'),
                    "EnableProformaInvoicing" => $request->input('enableProformaInvoicing'),
                    "SequentialInvoiceNumbering" => $request->input("sequentialinvoicenumbering"),
                    "SequentialInvoiceNumberFormat" => $request->input("sequentialinvoicenumberformat"),
                    "LateFeeType" => $request->input("latefeetype"),
                    "InvoiceLateFeeAmount" => $request->input("invoicelatefeeamount"),
                    "LateFeeMinimum" => $request->input("latefeeminimum"),
                    "ShowCCIssueStart" => $request->input("showccissuestart"),
                    "InvoiceIncrement" => (int) $request->input("invoiceincrement"),
                    "AddFundsEnabled" => $request->input('addfundsenabled'),
                    "AddFundsMinimum" => $addfundsminimum,
                    "AddFundsMaximum" => $addfundsmaximum,
                    "AddFundsMaximumBalance" => $addfundsmaximumbalance,
                    "AddFundsRequireOrder" => $request->input("addfundsrequireorder"),
                    "NoAutoApplyCredit" => $request->input("noautoapplycredit") ? "" : "on",
                    "CreditOnDowngrade" => $request->input("creditondowngrade"),
                    "AffiliateEnabled" => $request->input('affiliateenabled'),
                    "AffiliateEarningPercent" => $request->input('affiliateearningpercent'),
                    "AffiliateBonusDeposit" => $affiliatebonusdeposit,
                    "AffiliatePayout" => $affiliatepayout,
                    "AffiliatesDelayCommission" => $request->input('affiliatesdelaycommission'),
                    "AffiliateDepartment" => $request->input('affiliatedepartment'),
                    "AffiliateLinks" => $request->input('affiliatelinks'),
                    "CaptchaSetting" => $request->input("captchasetting"),
                    "CaptchaType" => $request->input('captchatype'),
                    "ReCAPTCHAPublicKey" => $request->input('recaptchapublickey'),
                    "ReCAPTCHAPrivateKey" => $request->input('recaptchaprivatekey'),
                    "CaptchaForms" => $captchaFormsSettings,
                    "EnableEmailVerification" => (int) $request->input("enable_email_verification"),
                    "AutoGeneratedPasswordFormat" => $request->input('autogeneratedpwformat'),
                    "RequiredPWStrength" => (int) $request->input("requiredpwstrength"),
                    "InvalidLoginBanLength" => (int) $request->input("invalidloginsbanlength"),
                    "sendFailedLoginWhitelist" => $request->input('sendFailedLoginWhitelist') ? 1 : 0,
                    "DisableAdminPWReset" => $request->input('disableadminpwreset'),
                    "CCAllowCustomerDelete" => $request->input("ccallowcustomerdelete"),
                    "DisableSessionIPCheck" => $request->input("disablesessionipcheck"),
                    "AllowSmartyPhpTags" => $request->input('allowsmartyphptags'),
                    "proxyHeader" => (string) $request->input("proxyheader"),
                    "LogAPIAuthentication" => (int) $request->input('logapiauthentication'),
                    "TwitterUsername" => $request->input('twitterusername'),
                    "AnnouncementsTweet" => $request->input('announcementstweet'),
                    "AnnouncementsFBRecommend" => $request->input('announcementsfbrecommend'),
                    "AnnouncementsFBComments" => $request->input('announcementsfbcomments'),
                    "AllowClientsEmailOptOut" => (int) $request->input("allowclientsemailoptout"),
                    "EmailMarketingRequireOptIn" => (int) $request->input("marketingreqoptin"),
                    "EmailMarketingOptInMessage" => $request->input("marketingoptinmessage"),
                    "ClientDisplayFormat" => $request->input("clientdisplayformat"),
                    "DefaultToClientArea" => $request->input("defaulttoclientarea"),
                    "AllowClientRegister" => $request->input("allowclientregister"),
                    "ClientsProfileOptionalFields" => $clientsprofoptional,
                    "ClientsProfileUneditableFields" => $clientsprofuneditable,
                    "SendEmailNotificationonUserDetailsChange" => $request->input("sendemailnotificationonuserdetailschange"),
                    "ShowCancellationButton" => $request->input("showcancel"),
                    "SendAffiliateReportMonthly" => $request->input("affreport"),
                    "BannedSubdomainPrefixes" => $request->input('bannedsubdomainprefixes'),
                    "EnableSafeInclude" => $request->input("enablesafeinclude"),
                    "DisplayErrors" => $request->input("displayerrors"),
                    "LogErrors" => $request->input("logerrors"),
                    "SQLErrorReporting" => $request->input("sqlerrorreporting"),
                    "HooksDebugMode" => $request->input('hooksdebugmode'),
                );

                if (!isset($CONFIG["BillingNotificationReceiver"])) {
                    $CONFIG["BillingNotificationReceiver"] = "";
                }

                if (!isset($CONFIG["ShowNotesFieldonCheckout"])) {
            $CONFIG["ShowNotesFieldonCheckout"] = "on";
        }

        // Tambahkan ke database jika belum ada
            $result = DB::table('tblconfiguration')
                ->where('setting', 'ShowNotesFieldonCheckout')
                ->first();

            if (!$result) {
                DB::table('tblconfiguration')->insert([
                    'setting' => 'ShowNotesFieldonCheckout',
                    'value' => 'on',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

                if ($request->input("sequentialinvoicenumbervalue") && is_numeric($request->input("sequentialinvoicenumbervalue"))) {
                    $save_arr["SequentialInvoiceNumberValue"] = $request->input("sequentialinvoicenumbervalue");
                }
                $booleanKeys = array("MaintenanceMode", "AllowLanguageChange", "CutUtf8Mb4", "EnableTOSAccept", "ShowNotesFieldonCheckout", "ProductMonthlyPricingBreakdown", "AllowDomainsTwice", "NoInvoiceEmailOnOrder", "SkipFraudForExisting", "AutoProvisionExistingOnly", "GenerateRandomUsername", "ProrataClientsAnniversaryDate", "EnableTranslations", "CutUtf8Mb4", "PhoneNumberDropdown", "AllowRegister", "AllowTransfer", "AllowOwnDomain", "EnableDomainRenewalOrders", "AutoRenewDomainsonPayment", "FreeDomainAutoRenewRequiresProduct", "DomainAutoRenewDefault", "DomainToDoListEntries", "AllowIDNDomains", "RegistrarAdminUseClientDetails", "ShowClientOnlyDepts", "RequireLoginforClientTickets", "SupportTicketKBSuggestions", "TicketRatingEnabled", "TicketAddCarbonCopyRecipients", "PreventEmailReopening", "DisableSupportTicketReplyEmailsLogging", "NetworkIssuesRequireLogin", "DownloadsIncludeProductLinked", "ContinuousInvoiceGeneration", "EnablePDFInvoices", "StoreClientDataSnapshotOnInvoiceCreation", "EnableMassPay", "AllowCustomerChangeInvoiceGateway", "GroupSimilarLineItems", "CancelInvoiceOnCancellation", "AutoCancelSubscriptions", "EnableProformaInvoicing", "SequentialInvoiceNumbering", "ShowCCIssueStart", "AddFundsEnabled", "AddFundsRequireOrder", "CreditOnDowngrade", "AffiliateEnabled", "EnableEmailVerification", "sendFailedLoginWhitelist", "DisableAdminPWReset", "CCAllowCustomerDelete", "DisableSessionIPCheck", "AllowSmartyPhpTags", "LogAPIAuthentication", "AnnouncementsTweet", "AnnouncementsFBRecommend", "AnnouncementsFBComments", "AllowClientsEmailOptOut", "EmailMarketingRequireOptIn", "DefaultToClientArea", "AllowClientRegister", "SendEmailNotificationonUserDetailsChange", "ShowCancellationButton", "SendAffiliateReportMonthly", "EnableSafeInclude", "DisplayErrors", "LogErrors", "SQLErrorReporting", "HooksDebugMode");
                // $basicLoggingKeys = array("InvoicePayTo", "MaintenanceModeMessage", "EmailCSS", "Signature", "EmailGlobalHeader", "EmailGlobalFooter", "NoAutoApplyCredit", "AffiliateLinks", "ReCAPTCHAPublicKey", "ReCAPTCHAPrivateKey", "BannedSubdomainPrefixes");
                $basicLoggingKeys = array(
                    "InvoicePayTo", 
                    "MaintenanceModeMessage", 
                    "EmailCSS", 
                    "Signature", 
                    "EmailGlobalHeader", 
                    "EmailGlobalFooter", 
                    "NoAutoApplyCredit", 
                    "AffiliateLinks", 
                    "ReCAPTCHAPublicKey", 
                    "ReCAPTCHAPrivateKey", 
                    "BannedSubdomainPrefixes",
                    "ShowNotesFieldonCheckout",
                    "BillingNotificationReceiver", 
                );

                $secureKeys = array("SMTPPassword");
                $changes = array();
                $newPassword = trim($request->input("smtppassword"));
                $originalPassword = (new \App\Helpers\Pwd)->decrypt(\App\Helpers\Cfg::get("SMTPPassword"));
                $valueToStore = \App\Helpers\AdminFunctions::interpretMaskedPasswordChangeForStorage($newPassword, $originalPassword);
                if ($valueToStore !== false) {
                    $save_arr["SMTPPassword"] = $valueToStore;
                    if ($newPassword != $originalPassword) {
                        $changes[] = "SMTP Password Changed";
                    }
                }
                foreach ($save_arr as $k => $v) {
                    \App\Helpers\Cfg::setValue($k, trim($v));
                    if ($existingConfig[$k] != trim($v) && !in_array($k, $secureKeys)) {
                        $regEx = "/(?<=[a-z])(?=[A-Z])|(?<=[A-Z])(?=[A-Z][a-z])/x";
                        $friendlySettingParts = preg_split($regEx, $k);
                        $friendlySetting = implode(" ", $friendlySettingParts);
                        if (in_array($k, $booleanKeys)) {
                            if (!$v || $v === false || $v == "off") {
                                $changes[] = (string) $friendlySetting . " Disabled";
                            } else {
                                $changes[] = (string) $friendlySetting . " Enabled";
                                if ($k == "StoreClientDataSnapshotOnInvoiceCreation") {
                                    // TODO: this
                                    // $snapShot = new WHMCS\Billing\Invoice\Snapshot();
                                    // $snapShot->createTable();
                                }
                            }
                        } else {
                            if (in_array($k, $basicLoggingKeys)) {
                                $changes[] = (string) $friendlySetting . " Changed";
                            } else {
                                $changes[] = (string) $friendlySetting . " Changed from '" . $existingConfig[$k] . "' to '" . $v . "'";
                            }
                        }
                    }
                }
                $continuousinvoicegeneration = $request->input("continuousinvoicegeneration");
                if ($continuousinvoicegeneration == "on" && !$CONFIG["ContinuousInvoiceGeneration"]) {
                    DB::update("UPDATE tblhosting SET nextinvoicedate = nextduedate");
                    DB::update("UPDATE tbldomains SET nextinvoicedate = nextduedate");
                    DB::update("UPDATE tblhostingaddons SET nextinvoicedate = nextduedate");
                }
                // $token_manager =& getTokenManager();
                // $token_manager->processAdminHTMLSave($whmcs);
                $tokenNamespaces = \App\Helpers\Cfg::getValue("token_namespaces");
                if ($existingConfig["token_namespaces"] != $tokenNamespaces) {
                    $changes[] = "CSRF Token Settings changed";
                }
                $invoicestartnumber = (int) $request->input("invoicestartnumber");
                if (0 < $invoicestartnumber) {
                    $maxinvnum = \App\Models\Invoiceitem::orderBy("invoiceid", "DESC")->value("invoiceid") ?? 0;
                    if ($invoicestartnumber < $maxinvnum) {
                        if ($changes) {
                            \App\Helpers\AdminFunctions::logAdminActivity("General Settings Modified. Changes made: " . implode(". ", $changes) . ".");
                        }
                        // redir("tab=" . $tab . "&error=invnumtoosml");
                        return redirect()->route($route, ['tab' => $tab, 'error' => 'invnumtoosml']);
                    }
                    // full_query("ALTER TABLE tblinvoices AUTO_INCREMENT = " . (int) $invoicestartnumber);
                    DB::statement(DB::raw("ALTER TABLE tblinvoices AUTO_INCREMENT = " . (int) $invoicestartnumber));
                    $changes[] = "Invoice Starting Number Changed to " . $invoicestartnumber;
                }
                if ($changes) {
                    \App\Helpers\AdminFunctions::logAdminActivity("General Settings Modified. Changes made: " . implode(". ", $changes) . ".");
                }
                DB::commit();
                // redir("tab=" . $tab . "&success=true");
                return redirect()->route($route, ['tab' => $tab, 'success' => 'true']);
            } catch (\Exception $e) {
                dd($e);
                DB::rollback();
                return $e->getMessage();
            }
        }

        $infobox = "";
        if (defined("DEMO_MODE")) {
            $infobox = \App\Helpers\AdminFunctions::infoBox("Demo Mode", "Actions on this page are unavailable while in demo mode. Changes will not be saved.");
        }
        $success = $request->input("success");
        $error = $request->input("error");
        if (!empty($success)) {
            $infobox = \App\Helpers\AdminFunctions::infoBox($aInt->lang("general", "changesuccess"), $aInt->lang("general", "changesuccessinfo"));
        }
        if (isset($error)) {
            if ($error == "invnumtoosml") {
                $infobox = \App\Helpers\AdminFunctions::infoBox($aInt->lang("global", "validationerror"), $aInt->lang("general", "errorinvnumtoosml"), "error");
            } else {
                if ($error == "limitnotnumeric") {
                    $infobox = \App\Helpers\AdminFunctions::infoBox($aInt->lang("global", "validationerror"), $aInt->lang("general", "limitNotNumeric"), "error");
                } else {
                    if ($error == "invalidCustomInvoiceNumber") {
                        $infobox = \App\Helpers\AdminFunctions::infoBox($aInt->lang("general", "sequentialpaidformat") . " " . $aInt->lang("global", "validationerror"), $aInt->lang("general", "sequentialPaidNumberValidationFail"), "error");
                    }
                }
            }
        }
        $result = \App\Models\Configuration::all();
        foreach ($result->toArray() as $data) {
            $setting = $data["setting"];
            $value = $data["value"];
            $CONFIG[(string) $setting] = (string) $value;
        }
        $hasMbstring = extension_loaded("mbstring");
        $validMailEncodings = array("8bit", "7bit", "binary", "base64", "quoted-printable");
        $tcpdfDefaultFonts = array("courier", "freesans", "helvetica", "times", "dejavusans");
        $defaultFont = false;
        $activeFontName = \App\Helpers\Cfg::get("TCPDFFont");

        // tambahan
        if (!isset($CONFIG["CurrencySymbol"])) {
            $CONFIG["CurrencySymbol"] = "";
        }
        if (!isset($CONFIG["CaptchaType"])) {
            $CONFIG["CaptchaType"] = "";
        }
        if (!isset($CONFIG["ReCAPTCHAPublicKey"])) {
            $CONFIG["ReCAPTCHAPublicKey"] = "";
        }
        if (!isset($CONFIG["ReCAPTCHAPrivateKey"])) {
            $CONFIG["ReCAPTCHAPrivateKey"] = "";
        }
        

        return view("pages.setup.generalsettings.general.index", [
            "infobox" => $infobox,
            "clientTemplates" => $clientTemplates,
            "orderFormTemplates" => $orderFormTemplates,
            "hasMbstring" => $hasMbstring,
            "frm1" => $frm1,
            "validMailEncodings" => $validMailEncodings,
            "tcpdfDefaultFonts" => $tcpdfDefaultFonts,
            "activeFontName" => $activeFontName,
            "aInt" => $aInt,
        ]);
    }

    public function cleanSystemURL($url)
    {
        $prefix = request()->secure() ? "https" : "http";
        if ($url == "" || !preg_match("/\\b(?:(?:https?|ftp):\\/\\/|www\\.)[-a-z0-9+&@#\\/%?=~_|!:,.;]*[-a-z0-9+&@#\\/%=~_|]/i", $url)) {
            $url = $prefix . "://" . $_SERVER["SERVER_NAME"] . preg_replace("#/[^/]*\\.php\$#simU", "/", $_SERVER["PHP_SELF"]);
        } else {
            $url = str_replace("\\", "", trim($url));
            if (!preg_match("~^(?:ht)tps?://~i", $url)) {
                $url = $prefix . "://" . $url;
            }
            $url = preg_replace("~^https?://[^/]+\$~", "\$0/", $url);
        }
        if (substr($url, -1) != "/") {
            $url .= "/";
        }
        return str_replace("/" . env("ADMIN_ROUTE_PREFIX") . "/", "/", $url);
    }

    public function GeneralSettings_whitelistIP(Request $request)
    {
        $getAllowedIPExist = API::post('GetConfigurationValue', ['setting' => 'WhitelistedIPS']);
        $getAllowedIPExist = json_decode(json_encode($getAllowedIPExist));
        $arrayWhitelistIP = unserialize($getAllowedIPExist->value); //Get existing IP data from DB in array
        $newIPData = $request->only('ip', 'note'); //Raw data from input modal
        $mergedIPArray = array_merge($arrayWhitelistIP, array($newIPData)); //Previous array and new array is merged
        $newArrayData = serialize($mergedIPArray); //Merged array converted to serialize format and ready for post to DB
        // dd($newArrayDat);
        API::post('SetConfigurationValue', ['setting' => 'WhitelistedIPs', 'value' => $newArrayData]);
        return ('Whitelisted IP has been updated!');
    }

    public function GeneralSettings_whitelistIP_delete(Request $request)
    {
        $getAllowedIPExist = API::post('GetConfigurationValue', ['setting' => 'WhitelistedIPS']);
        $getAllowedIPExist = json_decode(json_encode($getAllowedIPExist));
        $arrayWhitelistIP = unserialize($getAllowedIPExist->value); //Get existing IP data from DB in array
        $data = $request->only('ip');
        $selectedIP = $data['ip'];

        foreach ($arrayWhitelistIP as $key => $value) {
            if ($value['ip'] == $selectedIP) {
                unset($arrayWhitelistIP[$key]);
            }
        }
        // print_r($selectedIP);
        $newArrayWithDeletedData = serialize($arrayWhitelistIP);
        API::post('SetConfigurationValue', ['setting' => 'WhitelistedIPs', 'value' => $newArrayWithDeletedData]);
        return ('IP has been deleted!');
    }

    public function GeneralSettings_APIAllowedIps(Request $request)
    {
        $getAPIAllowedData = API::post('GetConfigurationValue', ['setting' => 'APIAllowedIPs']);
        $getAPIAllowedData = json_decode(json_encode($getAPIAllowedData));
        $arrayAPIAllowedIPs = unserialize($getAPIAllowedData->value); //Get existing IP data from DB in array
        $newAllowedApiIPData = $request->only('ip', 'note'); //Raw data from input modal
        $mergedAllowedIPArray = array_merge($arrayAPIAllowedIPs, array($newAllowedApiIPData)); //Previous array and new array is merged
        $newArrayAPIAllowedIPsData = serialize($mergedAllowedIPArray); //Merged array converted to serialize format and ready for post to DB
        // dd($newArrayDat);
        API::post('SetConfigurationValue', ['setting' => 'APIAllowedIPs', 'value' => $newArrayAPIAllowedIPsData]);
        return ('Allowed IP has been updated!');
    }

    public function GeneralSettings_APIAllowedIps_delete(Request $request)
    {
        $getAPIAllowedData = API::post('GetConfigurationValue', ['setting' => 'APIAllowedIPs']);
        $getAPIAllowedData = json_decode(json_encode($getAPIAllowedData));
        $arrayAPIAllowedIPs = unserialize($getAPIAllowedData->value); //Get existing IP data from DB in array
        $data = $request->only('ip');
        $selectedIP = $data['ip'];

        foreach ($arrayAPIAllowedIPs as $key => $value) {
            if ($value['ip'] == $selectedIP) {
                unset($arrayAPIAllowedIPs[$key]);
            }
        }
        $newArrayWithDeletedData = serialize($arrayAPIAllowedIPs);
        API::post('SetConfigurationValue', ['setting' => 'APIAllowedIPs', 'value' => $newArrayWithDeletedData]);
        return ('Allowed IP has been deleted!');
    }

    public function GeneralSettings_update(Request $request)
    {

    }
    public function GeneralSettings_updateOLD(Request $request)
    {
        $pfx = $this->prefix;

        //Single Value Input
        $data = $request->except('APIAllowedIPs', 'WhitelistedIPs', 'AcceptedCardTypes', 'checkoutCompletion', 'domainChecker', 'registration', 'contactUs', 'submitTicket', 'login', 'optFirstName', 'optLastName', 'optAddress1', 'optCity', 'optStateRegion', 'optPostcode', 'optPhoneNumber', 'lockFirstName', 'lockLastName', 'lockCompanyName', 'lockEmailAddress', 'lockAddress1', 'lockAddress2', 'lockCity', 'lockStateRegion', 'lockPostcode', 'lockCountry', 'lockPhoneNumber', 'lockTaxID');
        foreach ($data as $key => $value) {
            if (in_array($key, ['EmailGlobalHeader', 'EmailGlobalFooter'])) {
                $value = htmlspecialchars($value);
            }
            Api::post('SetConfigurationValue', ['setting' => $key, 'value' => $value]);
        }

        //Multiple Value Input
        $multiValueInput = $request->only('AcceptedCardTypes');
        foreach ($multiValueInput as $key => $value) {
            $newStr = $value;
        }
        $cardTypeMultiple = implode(',', $newStr);
        Api::post('SetConfigurationValue', ['setting' => 'AcceptedCardTypes', 'value' => $cardTypeMultiple]);

        //Checked Input For CaptchaForms
        $captchaFormsData = $request->only('checkoutCompletion', 'domainChecker', 'registration', 'contactUs', 'submitTicket', 'login');
        $jsonDataCaptchaForms = json_encode($captchaFormsData);
        Api::post('SetConfigurationValue', ['setting' => 'CaptchaForms', 'value' => $jsonDataCaptchaForms]);

        //Checked Optional Client Profile Fields
        $checkedOptionClientFields = $request->only('optFirstName', 'optLastName', 'optAddress1', 'optCity', 'optStateRegion', 'optPostcode', 'optPhoneNumber');
        $jsonDataOptionFields = json_encode($checkedOptionClientFields);
        Api::post('SetConfigurationValue', ['setting' => 'ClientsProfileOptionalFields', 'value' => $jsonDataOptionFields]);

        //Checked Locked Client Profile Fields
        $checkedLockedClientFields = $request->only('lockFirstName', 'lockLastName', 'lockCompanyName', 'lockEmailAddress', 'lockAddress1', 'lockAddress2', 'lockCity', 'lockStateRegion', 'lockPostcode', 'lockCountry', 'lockPhoneNumber', 'lockTaxID');
        $jsonDataLockedFields = json_encode($checkedLockedClientFields);
        Api::post('SetConfigurationValue', ['setting' => 'ClientsProfileUneditableFields', 'value' => $jsonDataLockedFields]);

        //Invoice Start Number
        $changes = [];
        $invoiceNumber = (int)$request->InvoiceStartNumber;

        if (0 < $invoiceNumber) {
            $maxinvnum = DB::select(DB::raw("SELECT ${pfx}invoiceitems.invoiceid FROM ${pfx}invoiceitems ORDER BY {$pfx}invoiceitems.invoiceid DESC LIMIT 1"));
            foreach ($maxinvnum as $invoice) {
                $lastInvoiceId = $invoice->invoiceid;
            }
            if ($invoiceNumber < $lastInvoiceId) {
                if ($changes) {
                    LogActivity::Save("General Settings Modified. Changes made: " . implode(". ", $changes) . ".");
                }
                return redirect()->route('admin.pages.setup.generalsettings.general.index')->with(['error' => 'The number must be greater than ' . $lastInvoiceId]);
            }
            DB::statement("ALTER TABLE {$pfx}invoices AUTO_INCREMENT = " . (int)$invoiceNumber);
            $changes[] = "Invoice Starting Number Changed to " . $invoiceNumber;
        }

        if ($changes) {
            LogActivity::Save("General Settings Modified. Changes made: " . implode(". ", $changes) . ".");
        }

        return redirect()->route('admin.pages.setup.generalsettings.general.index')->with(['success' => 'Configuration has been successfully updated']);
    }

    public function postPaymentToInvoice(Request $request)
    {
        $arrMethods = explode(", ", $request->checkedMethods);
        return response()->json($arrMethods);
    }
    public function InstaInvoice_UpdatePayment(Request $request)
    {
        $invoiceId = $request->id;
        $paymentMethod = $request->paymentmethod;
        // dd($request->all());
        $invoicePayMethod = Invoice::findOrFail($invoiceId);
        $invoicePayMethod->paymentmethod = $paymentMethod;
        $invoicePayMethod->save();
        $updatedInvoice =  $invoicePayMethod->paymentmethod;
        $paymentLists = Paymentgateway::where('setting', 'name')->where('gateway', $updatedInvoice)->get();
        // dd($paymentLists);
        foreach ($paymentLists as $key => $payType) {
            $strPayment = $payType->value;
            $keyPayment = $payType->gateway;
        }
        $invoice = new \App\Helpers\InvoiceClass($invoiceId);
        $paymentbutton = $invoice->getData("status") == "Unpaid" && 0 < $invoice->getData("balance") ? $invoice->getPaymentLink() : "";
        $params = array();
        $params['name'] = $strPayment;
        $params['gateway'] = $keyPayment;
        $params['button'] = $paymentbutton;
        return response()->json($params);
    }
}
