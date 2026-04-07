<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminResetPasswordNotification extends Notification
{
    use Queueable;

    private $token;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($token = '')
    {
        //
        $this->token = $token;
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
        $url = route('admin.password.reset', ['token' => $this->token, 'email' => $notifiable->email]);
        $expire = config('auth.passwords.admins.expire');

        return (new MailMessage)
                    ->from(env("MAIL_FROM_ADDRESS"), env("MAIL_FROM_NAME"))
                    ->subject("Reset Password Notification for Administrator")
                    ->greeting("Dear {$notifiable->full_name}")
                    ->line("You are receiving this email because we received a password reset request for your account.")
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
