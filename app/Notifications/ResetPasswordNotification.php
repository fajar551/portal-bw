<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    private $token;
    private $data = [];

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data = (object) $data;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $url = url(config('app.url', request()->root()) . route('password.reset', [
            'token' => $this->data->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));
        $expire = config('auth.passwords.users.expire');

        // Notes: Add env MAIL_FROM_ADDRESS, MAIL_FROM_NAME
        return (new MailMessage)
                    ->greeting("Dear {$this->data->name}")
                    ->from(env("MAIL_FROM_ADDRESS", "admin@cbms.qwords.co.id"), env("MAIL_FROM_NAME", "Admin CBMS")) //param 1 = mail adress, param 2 = name
                    ->subject("Your new password for {$this->data->companyName}")
                    ->line("You are receiving this email because we received a password reset request for your account. Follow the link below to set a new password:")
                    ->action("Reset Password", $url)
                    ->line("This password reset link will expire in $expire minutes.")
                    ->line("If you don't wish to reset your password, disregard this email and no action will be taken.");
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
