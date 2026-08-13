"use strict";


/* ==========================================================
   ASIGNACIONES DE SEGUIMIENTO
   Control de selección y cancelación masiva
========================================================== */

document.addEventListener("DOMContentLoaded", () => {


    /* ======================================================
       ELEMENTOS
    ====================================================== */

    const formAsignar =
        document.getElementById(
            "formAsignarJovenes"
        );

    const checkTodos =
        document.getElementById(
            "checkTodos"
        );

    const checksJovenes =
        Array.from(
            document.querySelectorAll(
                ".check-joven"
            )
        );

    const botonSeleccionarTodos =
        document.getElementById(
            "selectTodos"
        );

    const botonAsignar =
        document.getElementById(
            "btnAsignarSeleccionados"
        );


    const formCancelar =
        document.getElementById(
            "formCancelarAsignaciones"
        );

    const checkAsignaciones =
        document.getElementById(
            "checkAsignaciones"
        );

    const checksAsignacion =
        Array.from(
            document.querySelectorAll(
                ".check-asignacion"
            )
        );

    const botonCancelar =
        document.getElementById(
            "btnCancelarSeleccionados"
        );

    const idsCancelar =
        document.getElementById(
            "idsCancelar"
        );


    /* ======================================================
       UTILIDADES
    ====================================================== */

    function actualizarCheckGeneral(
        checkPrincipal,
        checks
    ) {

        if (!checkPrincipal) {
            return;
        }


        if (checks.length === 0) {

            checkPrincipal.checked = false;

            checkPrincipal.indeterminate = false;

            return;
        }


        const marcados =
            checks.filter(
                check => check.checked
            ).length;


        checkPrincipal.checked =
            marcados === checks.length;


        checkPrincipal.indeterminate =
            marcados > 0 &&
            marcados < checks.length;
    }


    function marcarChecks(
        checks,
        valor
    ) {

        checks.forEach(
            check => {

                check.checked = valor;

            }
        );
    }


    function obtenerSeleccionados(
        checks
    ) {

        return checks
            .filter(
                check => check.checked
            )
            .map(
                check => check.value
            )
            .filter(
                value => value !== ""
            );
    }


    /* ======================================================
       TABLA SUPERIOR
       SELECCIONAR JÓVENES
    ====================================================== */

    if (checkTodos) {

        checkTodos.addEventListener(
            "change",
            () => {

                marcarChecks(
                    checksJovenes,
                    checkTodos.checked
                );


                checkTodos.indeterminate =
                    false;

            }
        );

    }


    checksJovenes.forEach(
        check => {

            check.addEventListener(
                "change",
                () => {

                    actualizarCheckGeneral(
                        checkTodos,
                        checksJovenes
                    );

                }
            );

        }
    );


    /* ======================================================
       BOTÓN "SELECCIONAR TODOS"
    ====================================================== */

    if (botonSeleccionarTodos) {

        botonSeleccionarTodos.addEventListener(
            "click",
            event => {

                event.preventDefault();


                if (checksJovenes.length === 0) {
                    return;
                }


                const seleccionados =
                    obtenerSeleccionados(
                        checksJovenes
                    );


                const todosMarcados =
                    seleccionados.length ===
                    checksJovenes.length;


                marcarChecks(
                    checksJovenes,
                    !todosMarcados
                );


                actualizarCheckGeneral(
                    checkTodos,
                    checksJovenes
                );


                botonSeleccionarTodos.innerHTML =
                    todosMarcados

                        ? `
                            <i class="fa-solid fa-square-check"></i>
                            Seleccionar todos
                          `

                        : `
                            <i class="fa-solid fa-square-minus"></i>
                            Desmarcar todos
                          `;
            }
        );

    }


    /* ======================================================
       VALIDAR ASIGNACIÓN
    ====================================================== */

    if (formAsignar) {

        formAsignar.addEventListener(
            "submit",
            event => {

                const seleccionados =
                    obtenerSeleccionados(
                        checksJovenes
                    );


                if (
                    seleccionados.length === 0
                ) {

                    event.preventDefault();


                    alert(
                        "Selecciona al menos un joven para asignarlo."
                    );

                    return;

                }


                /*
                 * Quitamos nombres antiguos para evitar
                 * duplicados si el formulario vuelve a enviarse.
                 */

                formAsignar
                    .querySelectorAll(
                        'input[data-generated="joven-id"]'
                    )
                    .forEach(
                        input => input.remove()
                    );


                /*
                 * Creamos los IDs seleccionados.
                 *
                 * No usamos los checkbox directamente
                 * como campos del formulario.
                 */

                seleccionados.forEach(
                    id => {

                        const input =
                            document.createElement(
                                "input"
                            );

                        input.type =
                            "hidden";

                        input.name =
                            "joven_ids[]";

                        input.value =
                            id;

                        input.dataset.generated =
                            "joven-id";

                        formAsignar.appendChild(
                            input
                        );

                    }
                );

            }
        );

    }


    /* ======================================================
       TABLA INFERIOR
       SELECCIÓN PARA CANCELAR
    ====================================================== */

    function actualizarCancelacion() {

        const seleccionados =
            obtenerSeleccionados(
                checksAsignacion
            );


        if (botonCancelar) {

            botonCancelar.disabled =
                seleccionados.length === 0;

        }


        if (!idsCancelar) {
            return;
        }


        idsCancelar.innerHTML = "";


        seleccionados.forEach(
            id => {

                const input =
                    document.createElement(
                        "input"
                    );

                input.type =
                    "hidden";

                input.name =
                    "asignacion_ids[]";

                input.value =
                    id;

                input.dataset.generated =
                    "asignacion-id";

                idsCancelar.appendChild(
                    input
                );

            }
        );

    }


    /* ======================================================
       CHECK GENERAL DE ASIGNACIONES
    ====================================================== */

    if (checkAsignaciones) {

        checkAsignaciones.addEventListener(
            "change",
            () => {

                marcarChecks(
                    checksAsignacion,
                    checkAsignaciones.checked
                );


                checkAsignaciones.indeterminate =
                    false;


                actualizarCancelacion();

            }
        );

    }


    checksAsignacion.forEach(
        check => {

            check.addEventListener(
                "change",
                () => {

                    actualizarCheckGeneral(
                        checkAsignaciones,
                        checksAsignacion
                    );


                    actualizarCancelacion();

                }
            );

        }
    );


    /* ======================================================
       VALIDAR CANCELACIÓN
    ====================================================== */

    if (formCancelar) {

        formCancelar.addEventListener(
            "submit",
            event => {

                const seleccionados =
                    obtenerSeleccionados(
                        checksAsignacion
                    );


                if (
                    seleccionados.length === 0
                ) {

                    event.preventDefault();


                    return;

                }


                /*
                 * Confirmación antes de cancelar.
                 */

                const confirmar =
                    window.confirm(
                        seleccionados.length === 1

                            ? "¿Deseas cancelar la asignación seleccionada?"

                            : `¿Deseas cancelar las ${seleccionados.length} asignaciones seleccionadas?`
                    );


                if (!confirmar) {

                    event.preventDefault();

                    return;

                }


                /*
                 * Sincronizar nuevamente los IDs
                 * por seguridad.
                 */

                if (idsCancelar) {

                    idsCancelar.innerHTML = "";


                    seleccionados.forEach(
                        id => {

                            const input =
                                document.createElement(
                                    "input"
                                );

                            input.type =
                                "hidden";

                            input.name =
                                "asignacion_ids[]";

                            input.value =
                                id;

                            idsCancelar.appendChild(
                                input
                            );

                        }
                    );

                }

            }
        );

    }


    /* ======================================================
       ESTADO INICIAL
    ====================================================== */

    actualizarCheckGeneral(
        checkTodos,
        checksJovenes
    );


    actualizarCancelacion();


});