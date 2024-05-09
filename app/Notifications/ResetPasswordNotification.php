<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\URL;

class ResetPasswordNotification extends ResetPassword
{
    public bool $is_app = false;
    public $token = "";
    public function __construct($isApp, $token )
    {
        $this->is_app = $isApp;
        $this->token = $token;
    }

    /**
     * Get the reset URL for the given notifiable.
     *
     * @param  mixed  $notifiable
     * @return string
     */
    protected function resetUrl($notifiable)
    {
        if (static::$createUrlCallback) {
            return call_user_func(static::$createUrlCallback, $notifiable, $this->token);
        }
        if($this->is_app){
            return Url::to(APP_URL .'/#/reset-password?token='. $this->token. '&email='.  $notifiable->getEmailForPasswordReset());
        }

        return url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
            'locale' => app()->getLocale()
        ], false), ['origin' => $this->is_app]);
    }
}
