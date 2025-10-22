<?php

namespace Model;

class Usuario extends ActiveRecord {
    protected static $tabla = "usuarios";
    protected static $columnasDB = ["id", "nombre", "email", "password", "token", "confirmado"];

    public $id;
    public $nombre;
    public $email;
    public $password;
    public $token;
    public $confirmado;
    public $password2;
    public $password_actual;
    public $password_nuevo;

    public function __construct($args = [])
    {
        $this->id = $args["id"] ?? null;
        $this->nombre = $args["nombre"] ?? "";
        $this->email = $args["email"] ?? "";
        $this->password = $args["password"] ?? "";
        $this->password2 = $args["password2"] ?? "";
        $this->password_actual = $args["password_actual"] ?? "";
        $this->password_nuevo = $args["password_nuevo"] ?? "";
        $this->token = $args["token"] ?? "";
        $this->confirmado = $args["confirmado"] ?? 0;
    }

    // Validacion para cuentas nuevas
    public function validarCuentaNueva() {
        if(!$this->nombre) {
            self::$alertas["error"][] = "El Nombre del usuario es obligatorio";
        }
        if(!$this->email) {
            self::$alertas["error"][] = "El Email del usuario es obligatorio";
        }
        if(!$this->password | strlen($this->password) < 6) {
            self::$alertas["error"][] = "El Password no puede ir vacio y requiere un minimo de 6 caracteres";
        }
        if($this->password !== $this->password2) {
            self::$alertas["error"][] = "Los password son diferentes";
        }

        return self::$alertas;
    }

    public function validarPerfil() {
        if(!$this->nombre) {
            self::$alertas["error"][] = "El Nombre es obligatorio";
        }
        if(!$this->email) {
            self::$alertas["error"][] = "El Email es obligatorio";
        }
        
        return self::$alertas;
    }

    public function nuevoPassword() : array {
        if(!$this->password_actual) {
            self::$alertas["error"][] = "El password Actual no puede ir vacio";
        }
        if(!$this->password_nuevo) {
            self::$alertas["error"][] = "El password Nuevo no puede ir vacio";
        }
        if(strlen($this->password_nuevo) < 6) {
            self::$alertas["error"][] = "El password Debe contener al menos 6 caracteres";
        }

        return self::$alertas;
    }

    public function comprobarPassword() : bool {
        return password_verify($this->password_actual, $this->password);
    }

    public function hashPassword() : void {
        $this->password = password_hash($this->password, PASSWORD_BCRYPT);
    }

    public function crearToken() : void {
        $this->token = uniqid();
    }


    public function validarEmail() {
        if(!$this->email) {
            self::$alertas["error"][] = "El email es obligatorio";
        }
        if(!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            self::$alertas["error"][] = "Email no valido";
        }

        return self::$alertas;
    }

    public function validarPassword() {
        if(!$this->password || strlen($this->password) < 6) {
            self::$alertas["error"][] = "El password es obligatorio y debe tener al menos 6 caracteres";
        }

        return self::$alertas;
    }
    
    public function validarLogin() {
        if(!$this->email) {
            self::$alertas["error"][] = "El email es obligatorio";
        }
        if(!filter_var($this->email,FILTER_VALIDATE_EMAIL)) {
            self::$alertas["error"][] = "Email no valido";
        }
        if(!$this->password) {
            self::$alertas["error"][] = "El password no puede ir vacio";
        }

        return self::$alertas;
    }

   
    

    // public function validarPassword($password = "") {
    //     if($this->password !== $password) {
    //         self::$alertas["error"][] = "Los passwords son diferentes";
    //     }

    //     return self::$alertas;
    // }
}