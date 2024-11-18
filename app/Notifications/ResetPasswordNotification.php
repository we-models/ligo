<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\DB;

class ResetPasswordNotification extends ResetPassword
{
    public bool $is_app = false;
    public $token = "";
    public $code = "";
    public function __construct( $token )
    {
        $this->token = $token;
        // Generar un código aleatorio de 6 dígitos
        $this->code = rand(100000, 999999);
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

        return url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
            'locale' => app()->getLocale()
        ], false), ['origin' => false]);
    }

    /**
     * Get the reset code for the given notifiable.
     *
     * @param  mixed  $notifiable
     * @return string
     */
    protected function resetCode($notifiable)
    {
        // Guardar el código en la base de datos
        DB::table('password_resets')->updateOrInsert(
            ['email' => $notifiable->getEmailForPasswordReset()],
            [
                'token' => bcrypt($this->code), // Almacenar el código encriptado como token
                'created_at' => now(),
            ]
        );

        return $this->code;
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $code = $this->resetCode($notifiable);

        return (new MailMessage)
            ->line('Estás recibiendo este correo porque solicitaste un restablecimiento de contraseña.')
            ->line('Tu código de verificación es: ' . $code)
            ->line('Si no solicitaste un restablecimiento de contraseña, no se requiere ninguna acción adicional.');
    }
}
