(function() {
    document.addEventListener("DOMContentLoaded", function() {
        iniciarApp();
    });

    function iniciarApp() {

        obtenerTareas();

        let tareas = [];
        let filtradas = [];

        // Boton para mostrar modal de agregar tarea
        const nuevaTareaBtn = document.querySelector("#agregar-tarea");
        nuevaTareaBtn.addEventListener("click", function() {
            mostrarFormulario();
        });

        // Filtros de busqueda
        const filtros = document.querySelectorAll("#filtros input[type='radio'");
        filtros.forEach(function(filtro) {
            filtro.addEventListener("input", filtrarTareas);
        });

        function filtrarTareas(e) {
            const filtro = e.target.value;
            if(filtro !== "") {
                filtradas = tareas.filter(function(tarea) {
                    if(tarea.estado === filtro) {
                        return tarea;
                    }
                });
            } else {
                filtradas = [];
            }

            mostrarTareas();
        }

        async function obtenerTareas() {
            try {
                const url = `http://localhost:3000/api/tareas?url=${obtenerProyecto()}`;
                const respuesta = await fetch(url);
                const resultado = await respuesta.json();
                
                
                tareas = resultado.tareas;
                mostrarTareas();
            } catch (error) {
                console.log(error);
            }
        }

        function mostrarTareas() {
            limpiarTareas();
            totalPendientes();
            totalCompletas();

            const arrayTareas = filtradas.length ? filtradas : tareas;

            if(arrayTareas.length === 0) {
                const contenedor = document.querySelector("#listado-tareas");
                const textoNoTareas = document.createElement("LI");
                textoNoTareas.textContent = "No hay tareas";
                textoNoTareas.classList.add("no-tareas");
                contenedor.appendChild(textoNoTareas);
                return;
            }

            const estados = {
                0: "Pendiente",
                1: "Completa"
            }

            arrayTareas.forEach(tarea => {
                const contenedorTarea = document.createElement("LI");
                contenedorTarea.dataset.tareaId = tarea.id;
                contenedorTarea.classList.add("tarea");

                const nombreTarea = document.createElement("P");
                nombreTarea.textContent = tarea.nombre;
                nombreTarea.ondblclick = function() {
                    mostrarFormulario(true, {...tarea});
                }

                const opcionesDiv = document.createElement("DIV");
                opcionesDiv.classList.add("opciones");

                // Botones
                const btnEstadoTarea = document.createElement("BUTTON");
                btnEstadoTarea.classList.add("estado-tarea");
                btnEstadoTarea.classList.add(`${estados[tarea.estado].toLowerCase()}`);
                btnEstadoTarea.textContent = estados[tarea.estado];
                btnEstadoTarea.dataset.estadoTarea = tarea.estado;
                btnEstadoTarea.ondblclick = function() {
                    cambiarEstadoTarea({...tarea});//realizo un spread operator para no mutar o modificar el objeto o el arreglo original
                }

                const btnEliminarTarea = document.createElement("BUTTON");
                btnEliminarTarea.classList.add("eliminar-tarea");
                btnEliminarTarea.dataset.idTarea = tarea.id;
                btnEliminarTarea.textContent = "Eliminar";
                btnEliminarTarea.ondblclick = function() {
                    confirmarEliminarTarea({...tarea});
                }

                opcionesDiv.appendChild(btnEstadoTarea);
                opcionesDiv.appendChild(btnEliminarTarea);

                contenedorTarea.appendChild(nombreTarea);
                contenedorTarea.appendChild(opcionesDiv);

                const listadoTareas = document.querySelector("#listado-tareas");

                listadoTareas.appendChild(contenedorTarea);

                
            });
        }

        function totalPendientes() {
            const totalPendientes = tareas.filter(function(tarea) {
                if(tarea.estado === "0") {
                    return tarea;
                }
            });
            const pendientesRadio = document.querySelector("#pendientes");
            if(totalPendientes.length === 0) {
                pendientesRadio.disabled = true;
            } else {
                pendientesRadio.disabled = false;
            }
        }

        function totalCompletas() {
            const totalCompletas = tareas.filter( tarea => tarea.estado === "1");
            const completasRadio = document.querySelector("#completadas");
            if(totalCompletas.length === 0) {
                completasRadio.disabled = true;
            } else {
                completasRadio.disabled = false;
            }
        }

        function mostrarFormulario(editar = false, tarea = {}) {
            
            const modal = document.createElement("DIV");
            modal.classList.add("modal");
            modal.innerHTML = `
                <form class="formulario nueva-tarea">
                    <legend>${editar ? "Editar Tarea" : "Agrega una nueva tarea"}</legend>
                    <div class="campo">
                        <label>Tarea</label>
                        <input 
                            type="text"
                            name="tarea"
                            placeholder="${tarea.nombre ? "Edita la Tarea" : "Agregar Tarea al Proyecto Actual"}"
                            id="tarea"
                            value="${tarea.nombre ? tarea.nombre : ""}"
                        />
                    </div>
                    <div class="opciones">
                        <input
                            type="submit"
                            class="submit-nueva-tarea"
                            value="${editar ? "Guardar Cambios" : "Agregar Tarea"}"
                        />
                        <button type="button" class="cerrar-modal">Cancelar</button>
                    </div>
                </form
            `;
           
             setTimeout(() => {
                const formulario = document.querySelector(".formulario");
                formulario.classList.add("animar");
            }, 0);

            modal.addEventListener("click", function(e) {
                e.preventDefault();
                
                if(e.target.classList.contains("cerrar-modal")) {
                    const formulario = document.querySelector(".formulario");
                    formulario.classList.add("cerrar");
                    setTimeout(() => {
                        modal.remove();
                    }, 500);
                }
                if(e.target.classList.contains("submit-nueva-tarea")) {
                    const nombreTarea = document.querySelector("#tarea").value.trim();

                    if(nombreTarea === "") {
                        // Mostrar una alerta de error
                        mostrarAlerta("El nombre de la tarea es obligatorio","error", document.querySelector(".formulario legend"));
                        return;
                    }

                    if(editar) {
                        tarea.nombre = nombreTarea;
                        actualizarTarea(tarea);
                    } else {
                        agregarTarea(nombreTarea);
                    }

                    
                }
                
            })

            document.querySelector(".dashboard").appendChild(modal);

        }

        // Consultar el servidor para agregar una nueva tarea al proyecto actual
        async function agregarTarea(tarea) {
            // contruir la peticion
            const datos = new FormData();
            datos.append("nombre", tarea);
            datos.append("proyectoId", obtenerProyecto());
            

            try {
                const url = "http://localhost:3000/api/tarea";
                const respuesta = await fetch(url, {
                    method: "post",
                    body: datos
                });
                const resultado = await respuesta.json();
                mostrarAlerta(resultado.mensaje, resultado.tipo,document.querySelector(".formulario legend"));

                if(resultado.tipo === "exito") {
                    const modal = document.querySelector(".modal");
                    setTimeout(() => {
                       modal.remove(); 
                    }, 2000);

                    // Agregar el objeto de tarea al global de tareas
                    const tareaObj = {
                        id: String(resultado.id),
                        nombre: tarea,
                        estado: "0",
                        proyectoId: resultado.proyectoId
                    }

                    tareas = [...tareas, tareaObj];
                    mostrarTareas();
                }
            } catch (error) {
                console.log(error);
            }
        }

        function cambiarEstadoTarea(tarea) {
            //Ternario
            const nuevoEstado = tarea.estado === "1" ? "0" : "1";
            tarea.estado = nuevoEstado;
            actualizarTarea(tarea);
        }

        async function actualizarTarea(tarea) {
            const {id, estado, nombre, proyectoId} = tarea;
            
            const datos = new FormData();
            datos.append("id", id);
            datos.append("nombre", nombre);
            datos.append("estado", estado);
            datos.append("url", obtenerProyecto());

            // for(let valor of datos.values()) {
            //     console.log(valor);
            // } para ver los datos que se estan registrando en el formdata

            try {
                const url = "http://localhost:3000/api/tarea/actualizar";

                const respuesta = await fetch(url, {
                    method: "POST",
                    body: datos
                })
                const resultado = await respuesta.json();
                
                if(resultado.tipo === "exito") {
                    Swal.fire(
                        "Actualizado!",
                        resultado.mensaje,
                        "success"
                    )
                    const modal = document.querySelector(".modal");
                    if(modal) {
                        modal.remove();
                    }

                    tareas = tareas.map(function(tareaEnMemoria) {
                        if(tareaEnMemoria.id === id) {
                            tareaEnMemoria.estado = estado;
                            tareaEnMemoria.nombre = nombre;
                        }

                        return tareaEnMemoria;
                    });

                    mostrarTareas();
                }
            } catch (error) {
                console.log(error);
            }
            

        }

        function confirmarEliminarTarea(tarea) {
            Swal.fire({
            title: "¿Eliminar Tarea?",
            showCancelButton: true,
            confirmButtonText: "Si",
            cancelButtonText: "No"
            }).then((result) => {
            if (result.isConfirmed) {
                eliminarTarea(tarea)
            }

            });
        }

        async function eliminarTarea(tarea) {
            const {id, estado, nombre} = tarea;
            
            const datos = new FormData();
            datos.append("id", id);
            datos.append("nombre", nombre);
            datos.append("estado", estado);
            datos.append("url", obtenerProyecto());

            
            try {
                const url = "http://localhost:3000/api/tarea/eliminar";
                const respuesta = await fetch(url, {
                    method: "POST",
                    body: datos
                })
                const resultado = await respuesta.json();
                if(resultado.resultado) {
                    // mostrarAlerta(
                    //     resultado.mensaje, 
                    //     resultado.tipo,
                    //     document.querySelector(".contenedor-nueva-tarea")
                    // );
                    Swal.fire("Eliminado!", resultado.mensaje, "success");

                    tareas = tareas.filter(function(tareaMemoria) {
                        if(tareaMemoria.id !== id) {// Trae todo lo que sea diferente
                           return tareaMemoria;
                        }
                    });

                    mostrarTareas();
                }
            } catch (error) {
                console.log(error)
            }
        }

        function obtenerProyecto() {
            const proyectoParams = new URLSearchParams(window.location.search);
            const proyecto = Object.fromEntries(proyectoParams.entries());
            return proyecto.url;
        }

        function limpiarTareas() {
            const listadoTareas = document.querySelector("#listado-tareas");
            
            while(listadoTareas.firstChild) {
                listadoTareas.removeChild(listadoTareas.firstChild);
            }
        }

       

        // Muestra un mensaje en la interfaz
        function mostrarAlerta(mensaje, tipo, referencia) {
            // Previene la creacion de multiples alertas
            const alertaPrevia = document.querySelector(".alerta");
            if(alertaPrevia) {
                alertaPrevia.remove();
            }

            const alerta = document.createElement("DIV");
            alerta.classList.add("alerta", tipo);
            alerta.textContent = mensaje;
            referencia.parentElement.insertBefore(alerta, referencia.nextElementSibling);

            setTimeout(() => {
                alerta.remove();
            }, 3000);
        }


    }
})();







