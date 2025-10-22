<?php

namespace Controllers;

use Model\Usuario;
use MVC\Router;
use Classes\Email;

class LoginController {

    public static function login(Router $router) {
        $alertas = Usuario::getAlertas();
        if($_SERVER["REQUEST_METHOD"] === "POST") {
            $auth = new Usuario($_POST);
            $alertas = $auth->validarLogin();

            if(empty($alertas)) {
                // Verificar que el usuario exista
                $usuario = Usuario::where("email", $auth->email);
                if(!$usuario || !$usuario->confirmado) {
                    Usuario::setAlerta("error","El usuario no existe o no esta confirmado");
                } else {
                    // Verificar que el password este correcto
                    if(password_verify($auth->password, $usuario->password)) {
                        // Iniciar la sesion del usuario
                        session_start();
                        $_SESSION["id"] = $usuario->id;
                        $_SESSION["nombre"] = $usuario->nombre;
                        $_SESSION["email"] = $usuario->email;
                        $_SESSION["login"] = true;

                        // Redireccionar 
                        header("Location: /dashboard");
                    } else {
                        Usuario::setAlerta("error", "Password Incorrecto");
                    }
                }
            }

        }

        $alertas = Usuario::getAlertas();
        // Renderizar vista
        $router->render("auth/login",[
            "titulo" => "Iniciar sesión",
            "alertas" => $alertas
        ]);
    }

    public static function logout() {
        session_start();
        $_SESSION = [];
        header("Location: /");
    }

    public static function crear(Router $router) {

        $usuario = new Usuario;
        $alertas = Usuario::getAlertas();
        if($_SERVER["REQUEST_METHOD"] === "POST") {
            $usuario->sincronizar($_POST);
            $usuario->sanitizarAtributos();
            $alertas = $usuario->validarCuentaNueva();
            if(empty($alertas)) {
                $existeUsuario = Usuario::where("email", $usuario->email);
                if($existeUsuario) {
                    Usuario::setAlerta("error", "El usuario ya esta registrado");
                } else {    
                    // Hashear el password
                    $usuario->hashPassword();

                    // Eliminar Password2
                    unset($usuario->password2);

                    // Generar un token
                    $usuario->crearToken();

                    // Enviar Email
                    $email = new Email($usuario->email, $usuario->nombre, $usuario->token);
                    $email->enviarConfirmacion();

                    // Crear un nuevo usuario
                    $resultado = $usuario->guardar();
                    if($resultado) {
                        header("Location: /mensaje");
                    }
                }

            }

        }

        $alertas = Usuario::getAlertas();
        // Renderizar vista
        $router->render("auth/crear",[
            "titulo" => "Crear tu cuenta en UpTask",
            "usuario" => $usuario,
            "alertas" => $alertas
        ]);
    }

    public static function olvide(Router $router) {
        // Importar arreglo vacio en caso de errores mostrar en pantalla
        $alertas = Usuario::getAlertas();
        if($_SERVER["REQUEST_METHOD"] === "POST") {
            // validar que el campo mandado por el usuario no este vacio
            $usuario = new Usuario($_POST);
            $alertas = $usuario->validarEmail();
            if(empty($alertas)) {
                // verificar que el usuario exista o no este confirmado
                $usuario = Usuario::where("email", $usuario->email);
                if(empty($usuario) || !$usuario->confirmado) {
                    Usuario::setAlerta("error", "El usuario no existe o no esta confirmado");
                }
                
                if($usuario->confirmado) {
                    //generar un nuevo token
                    $usuario->crearToken();

                    // Resetear el confirmado
                    $usuario->confirmado = 0;

                    // Eliminar el campo temporal
                    unset($usuario->password2);

                    // Enviar email
                    $email = new Email($usuario->email, $usuario->nombre, $usuario->token);
                    $email->enviarInstrucciones();

                    // guardar los cambios
                    $resultado = $usuario->guardar();
            
                    if($resultado) {
                        Usuario::setAlerta("exito", "Hemos enviado las instrucciones a tu email");
                    }
                }
            }
        }

        $alertas = Usuario::getAlertas();
        $router->render("auth/olvide",[
            "titulo" => "Recuperar Cuenta de UpTask",
            "alertas" => $alertas
        ]);
    }

    public static function reestablecer(Router $router) {
        // Importar las alertas
        $alertas = Usuario::getAlertas();
        // Validar que el token sea valido y exista el usuario con el token enviado
        $token = s($_GET["token"]);
        if(!$token) {
            header("Location: /");
        }

        $existeUsuario = Usuario::where("token", $token);
        if(empty($existeUsuario)) {
            // Si no existe usuario segun el token mandar mensaje de error
            Usuario::setAlerta("error", "Token no Valido");
        }

        if($_SERVER["REQUEST_METHOD"] === "POST") {
            // agregar el nuevo password
            $existeUsuario->password = s($_POST["password"]);
            // validar que el password no se envie vacio
           $alertas = $existeUsuario->validarPassword();
           if(empty($alertas)) {
            //hashear el nuevo password
            $existeUsuario->hashPassword();

            // Eliminar el token
            $existeUsuario->token = "";

            // Modificar el confirmado
            $existeUsuario->confirmado = 1;

            // Eliminar el campo password2 temporal
            unset($existeUsuario->password2);

            // Guardar los cambios en BD
           $resultado = $existeUsuario->guardar();

            // Redireccionar
            if($resultado) {
                header("Location: /");
            }
           }
            
        }

        $alertas = Usuario::getAlertas();
        $router->render("auth/reestablecer",[
            "titulo" => "Reestablecer Password",
            "alertas" => $alertas
        ]);
    }

    public static function mensaje(Router $router) {
        
        $router->render("auth/mensaje",[
            "titulo" => "Cuenta Creada Exitosamente"
        ]);
    }

    public static function confirmar(Router $router) {
        $alertas = Usuario::getAlertas(); // Importar arreglo vacio para las alertas
        // Validar que el token sea valido
        $token = s($_GET["token"]);
        if(!$token) {
            header("Location: /");
        }
        // Buscar el usuario relacionado al token
        $usuario = Usuario::where("token", $token);
        if(empty($usuario)) {
            Usuario::setAlerta("error", "Token no valido");
        } else {
            // Modificar a usuario a confirmado y eliminar el token
            $usuario->confirmado = 1;
            $usuario->token = "";
            unset($usuario->password2);
            $usuario->guardar();
            Usuario::setAlerta("exito", "Usuario Registrado Correctamente");
        }
        
        $alertas = Usuario::getAlertas();
        $router->render("auth/confirmar",[
            "titulo" => "Confirma tu cuenta UpTask",
            "alertas" => $alertas
        ]);
    }
}