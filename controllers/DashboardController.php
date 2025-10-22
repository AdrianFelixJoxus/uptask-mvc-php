<?php

namespace Controllers;

use MVC\Router;
use Model\Proyecto;
use Model\Usuario;

class DashboardController {

    public static function index(Router $router) {
        session_start();
        isAuth();
        $proyectos = Proyecto::belongsTo("propietarioId", $_SESSION["id"]);


        $router->render("dashboard/index",[
            "titulo" => "Proyectos",
            "proyectos" => $proyectos
        ]);
    }

    public static function crear_proyecto(Router $router) {
        session_start();
        isAuth();
        $alertas = [];

        if($_SERVER["REQUEST_METHOD"] === "POST") {
            $proyecto = new Proyecto($_POST);
            
            //validacion
            $alertas = $proyecto->validarProyecto();

            if(empty($alertas)) {
                // Generar una URL unica
                $hash = md5(uniqid());
                $proyecto->url = $hash;

                // Almacenar el creador del proyecto
                $proyecto->propietarioId = $_SESSION["id"];

                // Guardar el proyecto
                $proyecto->guardar();

                // redireccionar
                header("Location: /proyecto?url={$proyecto->url}");
            }
        }
        $router->render("dashboard/crear-proyecto",[
            "titulo" => "Crear Proyecto",
            "alertas" => $alertas
        ]);
    }

    public static function proyecto(Router $router) {
        session_start();
        isAuth();
        $url = $_GET["url"];
        if(!$url) {
            header("Location: /dashboard");
        }

        // Revisar que la persona que visita el proyecto, es quien lo creo
        $proyecto = Proyecto::where("url", $url);
        if($proyecto->propietarioId !== $_SESSION["id"]) {
            header("Location: /proyectos");
        }

        
        $router->render("dashboard/proyecto",[
            "titulo" => $proyecto->proyecto
        ]);
    }

    public static function perfil(Router $router) {
        session_start();
        isAuth();
        $alertas = Usuario::getAlertas();
        $usuario = Usuario::find($_SESSION["id"]);

       if($_SERVER["REQUEST_METHOD"] === "POST") {
            $usuario->sincronizar($_POST);
            $alertas = $usuario->validarPerfil();

            if(empty($alertas)) {
                // Verificar que el email no exista o se repita
                $existeUsuario = Usuario::where("email", $usuario->email);
                if($existeUsuario && $existeUsuario->id !== $usuario->id) {
                    Usuario::setAlerta("error", "Email no valido ya pertenece a otra cuenta");
                } else {
                    // Guardar usuario
                    $usuario->guardar();

                    Usuario::setAlerta("exito", "Guardado Correctamente");
                    // Asignar el nombre nuevo a la barra
                    $_SESSION["nombre"] = $usuario->nombre;
                }

                
            }
       }

        $alertas = Usuario::getAlertas();
        $router->render("dashboard/perfil",[
            "titulo" => "Perfil",
            "alertas" => $alertas,
            "usuario" => $usuario
        ]);
    }

    public static function cambiar_password(Router $router) {
        session_start();
        isAuth();
        $alertas = Usuario::getAlertas();

        if($_SERVER["REQUEST_METHOD"] === "POST") {
            $usuario = Usuario::find($_SESSION["id"]);
            $usuario->sincronizar($_POST);
            $alertas = $usuario->nuevoPassword();

            if(empty($alertas)) {
                $resultado = $usuario->comprobarPassword();
                if($resultado) {
                    // asignar el nuevo password
                    $usuario->password = $usuario->password_nuevo;

                    // Eliminar propiedades no necesarias
                    unset($usuario->password_actual);
                    unset($usuario->password_nuevo);

                    // hasear el nuevo password
                    $usuario->hashPassword();

                    // Actualizar
                    $resultado = $usuario->guardar();
                    if($resultado) {
                        Usuario::setAlerta("exito", "Password Guardado Correctamente");
                    }
                } else {
                    Usuario::setAlerta("error", "Password Incorrecto");
                }
            }
            // debuguear($usuario);
        }

        $alertas = Usuario::getAlertas();
        $router->render("dashboard/cambiar-password",[
            "titulo" => "Cambiar Password",
            "alertas" => $alertas
        ]);
    }


}