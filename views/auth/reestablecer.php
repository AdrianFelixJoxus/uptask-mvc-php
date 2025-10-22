<div class="contenedor reestablecer">
   <?php include_once __DIR__ . "/../templates/nombre-sitio.php"; ?>

    <div class="contenedor-sm">
        <p class="descripcion-pagina">Coloca tu nuevo password</p>

        <?php include_once __DIR__ . "/../templates/alertas.php"; 
            if($alertas) {
                return;
            }
        ?>

        <form class="formulario" method="post">
            <div class="campo">
                <label for="password">Password</label>
                <input 
                type="password"
                id="password"
                name="password"
                placeholder="Tu Password"
                >
            </div>

            <input type="submit" class="boton" value="Guardar Password">
        </form>

        <div class="acciones">
            <a href="/crear">¿Aun no tienes una cuenta? Obtener Una</a>
            <a href="/">¿Ya tienes una cuenta? Inicia sesión</a>
        </div>
    </div><!--.contenedor-sm -->
</div>