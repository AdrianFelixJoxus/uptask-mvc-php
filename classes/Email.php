<?php

namespace Classes;
use PHPMailer\PHPMailer\PHPMailer;
use Exception;

class Email {
    protected $email;
    protected $nombre;
    protected $token;

    function __construct($email, $nombre, $token)
    {
        $this->email = $email;
        $this->nombre = $nombre;
        $this->token = $token;
    }

    public function enviarConfirmacion() {

        try {
            // Crear el objeto de email
            $mail = new PHPMailer();
            $mail->isSMTP();
            $mail->Host = $_ENV['EMAIL_HOST'];
            $mail->SMTPAuth = true;
            $mail->Port = $_ENV['EMAIL_PORT'];
            $mail->Username = $_ENV['EMAIL_USER'];
            $mail->Password = $_ENV['EMAIL_PASS'];

            $mail->setFrom("cuentas@uptask.com");
            $mail->addAddress("cuentas@uptask.com", "UpTask.com");
            $mail->Subject = "Confirma tu cuenta";

            // Set HTML
            $mail->isHTML(true);
            $mail->CharSet = "UTF-8";

            $contenido = "<html>";
            $contenido .= "<p><strong>Hola {$this->nombre}</strong> Has creado tu cuenta en UpTask
            solo debes confirmarla presionando el siguiente enlace</p>";
            $contenido .= "<p>Presiona aquí: <a href='" . $_ENV['HOST'] . "/confirmar?token=" . $this->token . "'>Confirmar Cuenta</a>";
            $contenido .= "<p> Si tu no solicitastes esta cuenta, puedes ignorar el mensaje</p>";
            $contenido .= "</html>";
            $mail->Body = $contenido;
            $mail->send();

        } catch (Exception $th) {
           debuguear("{$th} $mail->ErrorInfo");
        }
       
        

       
    }

    public function enviarInstrucciones() {
        try {
            // Crear el objeto de email
            $mail = new PHPMailer();
            $mail->isSMTP();
            $mail->Host = $_ENV['EMAIL_HOST'];
            $mail->SMTPAuth = true;
            $mail->Port = 2525;
            $mail->Port = $_ENV['EMAIL_PORT'];
            $mail->Username = $_ENV['EMAIL_USER'];
            $mail->Password = $_ENV['EMAIL_PASS'];

            $mail->setFrom("cuentas@uptask.com");
            $mail->addAddress("cuentas@uptask.com", "UpTask.com");
            $mail->Subject = "Reestablece tu password";

            // Set HTML
            $mail->isHTML(true);
            $mail->CharSet = "UTF-8";

            $contenido = "<html>";
            $contenido .= "<p><strong>Hola {$this->nombre}</strong> Has solicitado resetear tu password en UpTask
            solo debes confirmar presionando el siguiente enlace para reestablecerla</p>";
            $contenido .= "<p>Presiona aquí: <a href='" . $_ENV['HOST'] . "/reestablecer?token=" . $this->token . "'>Reestablecer Password</a>";
            $contenido .= "<p> Si tu no solicitastes restablecer tu password, puedes ignorar el mensaje</p>";
            $contenido .= "</html>";
            $mail->Body = $contenido;
            $mail->send();

        } catch (Exception $th) {
           debuguear("{$th} $mail->ErrorInfo");
        }
    }
}