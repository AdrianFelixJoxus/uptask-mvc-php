<?php

namespace Controllers;
use MVC\Router;
use Model\Proyecto;
use Model\Tarea;

class TareaController {

    public static function index() {
        session_start();

        $proyectoId = $_GET["url"];
        if(!$proyectoId) {// Verificar que halla una url
            header("location: /dashboard");
        }

        // Verificar que el proyecto exista
        $proyecto = Proyecto::where("url", $proyectoId);
        

        if(!$proyecto || $proyecto->propietarioId !== $_SESSION["id"]) {
            header("Location: /404");
        }

        // traer las tareas asociadas al proyecto
        $tareas = Tarea::belongsTo("proyectoId", $proyecto->id);

        echo json_encode(["tareas" => $tareas]);


    }

    public static function crear() {
        if($_SERVER["REQUEST_METHOD"] === "POST") {
            //verificar o saber a cual proyecto esta ligada la tarea y si existe
            session_start();
            $proyectoUrl = $_POST["proyectoId"];
            $proyecto = Proyecto::where("url", $proyectoUrl);
            
            if(!$proyecto || $proyecto->propietarioId !== $_SESSION["id"]) {
                $respuesta = [
                    "tipo" => "error",
                    "mensaje" => "Hubo un error al agregar la tarea"
                ];
                echo json_encode($respuesta);
                return;
                
            }
            // instanciar y crear la tarea
            $tarea = new Tarea($_POST);
            $tarea->proyectoId = $proyecto->id;
            $resultado = $tarea->guardar();
            $respuesta = [
                "tipo" => "exito",
                "id" => $resultado["id"],
                "mensaje" => "Tarea creada correctamente",
                "proyectoId" => $proyecto->id
            ];
            echo json_encode($respuesta);
            
        }
    }

    public static function actualizar() {
        if($_SERVER["REQUEST_METHOD"] === "POST") {
            session_start();
            // validar que el proyecto exista
            $proyecto = Proyecto::where("url",$_POST["url"]);

            if(!$proyecto || $proyecto->propietarioId !== $_SESSION["id"]) {
                $respuesta = [
                    "tipo" => "error",
                    "mensaje" => "Hubo un Error al actualizar la tarea"
                ];
                echo json_encode($respuesta);
                return;
            }

            $tarea = new Tarea($_POST);
            $tarea->proyectoId = $proyecto->id;
            $resultado = $tarea->guardar();
            if($resultado) {
                $respuesta = [
                "tipo" => "exito",
                "id" => $tarea->id,
                "proyectoId" => $proyecto->id,
                "mensaje" => "Actualizado Correctamente"
                ];

                echo json_encode($respuesta);
            }
        }
    }

    public static function eliminar() {
        if($_SERVER["REQUEST_METHOD"] === "POST") {
            session_start();
            // validar que el proyecto exista
            $proyecto = Proyecto::where("url",$_POST["url"]);

            if(!$proyecto || $proyecto->propietarioId !== $_SESSION["id"]) {
                $respuesta = [
                    "tipo" => "error",
                    "mensaje" => "Hubo un Error al actualizar la tarea"
                ];
                echo json_encode($respuesta);
                return;
            }

            $tarea = new Tarea($_POST);
            $resultado = $tarea->eliminar();
            $resultado = [
                "resultado" => $resultado,
                "mensaje" => "Eliminado Correctamente",
                "tipo" => "exito"
            ];

            echo json_encode($resultado);
        }
    }
}