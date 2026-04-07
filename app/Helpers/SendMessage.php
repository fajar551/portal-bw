<?php
namespace App\Helpers;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use App\Helpers\Mail;

class SendMessage
{

	public function __construct()
	{
	//	$this->sendMail();
	}

	public function sendMail(){

		$html="<table>
					<tr>
						<td>Haloo</td>
						<td>Testing</td>
					</tr>
		
				</table>";
		try{

			/*  Mail::raw($html, function($message) {
				$message->from('cbmsauto@ujiproduk.com','CBMS Auto');
				$message->to('andiw@qwords.co.id');
				$message->subject('testing cbms');
				//$message->setBody( '<html><h1>5% off its awesome</h1><p>Go get it now !</p></html>', 'text/html' );
				//$message->addPart("5% off its awesome\n\nGo get it now!", 'text/plain');
			});  */

			/* Mail::send(['html' => $html], [], function (Message $message) use ($html) {
				$message->to('andiw@qwords.co.id');
				$message->subject('my subject');
				$message->from('cbmsauto@ujiproduk.com');
				$message->setBody($html, 'text/html');
				});
 			*/

			$html='<p>Dear {$client_name},</p><p align="center"><strong>PLEASE READ THIS EMAIL IN FULL AND PRINT IT FOR YOUR RECORDS</strong></p><p>Thank you for your order from us! Your hosting account has now been setup and this email contains all the information you will need in order to begin using your account.</p><p>If you have requested a domain name during sign up, please keep in mind that your domain name will not be visible on the internet instantly. This process is called propagation and can take up to 48 hours. Until your domain has propagated, your website and email will not function, we have provided a temporary url which you may use to view your website and upload files in the meantime.</p><p><strong>New Account Information</strong></p><p>Hosting Package: {$service_product_name}<br />Domain: {$service_domain}<br />First Payment Amount: {$service_first_payment_amount}<br />Recurring Amount: {$service_recurring_amount}<br />Billing Cycle: {$service_billing_cycle}<br />Next Due Date: {$service_next_due_date}</p><p><strong>Login Details</strong></p><p>Username: {$service_username}<br />Password: {$service_password}</p><p>Control Panel URL: <a href="http://{$service_server_ip}:2082/">http://{$service_server_ip}:2082/</a><br />Once your domain has propagated, you may also use <a href="http://www.{$service_domain}:2082/">http://www.{$service_domain}:2082/</a></p><p><strong>Server Information</strong></p><p>Server Name: {$service_server_name}<br />Server IP: {$service_server_ip}</p><p>If you are using an existing domain with your new hosting account, you will need to update the nameservers to point to the nameservers listed below.</p><p>Nameserver 1: {$service_ns1} ({$service_ns1_ip})<br />Nameserver 2: {$service_ns2} ({$service_ns2_ip}){if $service_ns3}<br />Nameserver 3: {$service_ns3} ({$service_ns3_ip}){/if}{if $service_ns4}<br />Nameserver 4: {$service_ns4} ({$service_ns4_ip}){/if}</p><p><strong>Uploading Your Website</strong></p><p>Temporarily you may use one of the addresses given below to manage your web site:</p><p>Temporary FTP Hostname: {$service_server_ip}<br />Temporary Webpage URL: <a href="http://{$service_server_ip}/~{$service_username}/">http://{$service_server_ip}/~{$service_username}/</a></p><p>And once your domain has propagated you may use the details below:</p><p>FTP Hostname: {$service_domain}<br />Webpage URL: <a href="http://www.{$service_domain}">http://www.{$service_domain}</a></p><p><strong>Email Settings</strong></p><p>For email accounts that you setup, you should use the following connection details in your email program:</p><p>POP3 Host Address: mail.{$service_domain}<br />SMTP Host Address: mail.{$service_domain}<br />Username: The email address you are checking email for<br />Password: As specified in your control panel</p><p>Thank you for choosing us.</p><p>{$signature}</p>';
				$client_name='andi wijang prasetyo';
			$html=str_replace('{$client_name}',$client_name,$html);
			
			 $sent=new Mail('andiw@qwords.co.id','CBMS auto Testing','awp',$html);
			dd($sent);

		} catch (\Exception $e) {
			return ResponseApi::Error(['message' => $e->getMessage()]);
		}
		$html='<b>Ini pesan</b>';


		/* Mail::raw([], function($message) use($html, $plain, $to, $subject, $formEmail, $formName){
			$message->from($fromEmail, $fromName);
			$message->to($to);
			$message->subject($subject);
			$message->setBody($html, 'text/html' ); // dont miss the '<html></html>' or your spam score will increase !
			$message->addPart($plain, 'text/plain');
		}); */


	}

	

}
