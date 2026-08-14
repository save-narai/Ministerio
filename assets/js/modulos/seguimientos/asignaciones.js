document.addEventListener("DOMContentLoaded", () => {

    /* =====================================================
       UTILIDADES
    ===================================================== */

    const dataTableDisponible =
        typeof initDataTable === "function";



    /* =====================================================
       TABLA:
       JÓVENES PENDIENTES
    ===================================================== */

    const tablaPendientes =
        document.getElementById(
            "tablaAsignaciones"
        );

    let dtPendientes = null;


    if (
        dataTableDisponible &&
        tablaPendientes
    ) {

        dtPendientes =
            initDataTable(
                "#tablaAsignaciones",
                {
                    pageLength: 8,

                    order: [
                        [1, "asc"]
                    ],

                    columnDefs: [
                        {
                            targets: 0,
                            orderable: false,
                            searchable: false
                        }
                    ]
                }
            );

    }



    /* =====================================================
       TABLA:
       ASIGNACIONES DEL PERÍODO
    ===================================================== */

    const tablaActuales =
        document.getElementById(
            "tablaAsignacionesActuales"
        );

    let dtActuales = null;


    if (
        dataTableDisponible &&
        tablaActuales
    ) {

        dtActuales =
            initDataTable(
                "#tablaAsignacionesActuales",
                {
                    pageLength: 8,

                    order: [
                        [1, "asc"]
                    ],

                    columnDefs: [
                        {
                            targets: 0,
                            orderable: false,
                            searchable: false
                        },

                        {
                            targets: 6,
                            orderable: false,
                            searchable: false
                        }
                    ]
                }
            );

    }



    /* =====================================================
       BUSCADOR
       JÓVENES PENDIENTES
    ===================================================== */

    const buscador =
        document.getElementById(
            "buscarAsignaciones"
        );


    if (
        buscador &&
        dtPendientes
    ) {

        buscador.addEventListener(
            "input",
            () => {

                dtPendientes
                    .search(
                        buscador.value
                    )
                    .draw();

            }
        );

    }



    /* =====================================================
       SELECCIÓN:
       JÓVENES PENDIENTES
    ===================================================== */

    const checkTodos =
        document.getElementById(
            "checkTodos"
        );

    const botonTodos =
        document.getElementById(
            "selectTodos"
        );


    /*
     * Guardamos las selecciones por ID.
     * Esto permite mantenerlas aunque
     * DataTables cambie de página.
     */

    const seleccionPendientes =
        new Set();



    function obtenerChecksPendientes() {

        if (!dtPendientes) {

            return $();

        }

        return dtPendientes
            .rows()
            .nodes()
            .to$()
            .find(
                ".check-joven"
            );

    }



    function obtenerIdsPendientes() {

        if (!dtPendientes) {

            return [];

        }

        const ids = [];


        dtPendientes
            .rows()
            .nodes()
            .to$()
            .find(
                ".check-joven"
            )
            .each(
                function () {

                    ids.push(
                        String(
                            this.value
                        )
                    );

                }
            );


        return ids;

    }



    function aplicarSeleccionPendientes() {

        const checks =
            obtenerChecksPendientes();


        checks.each(
            function () {

                const id =
                    String(
                        this.value
                    );


                this.checked =
                    seleccionPendientes.has(
                        id
                    );

            }
        );


        sincronizarCheckPendientes();

    }



    function sincronizarCheckPendientes() {

        if (!checkTodos) {

            return;

        }


        const ids =
            obtenerIdsPendientes();


        if (!ids.length) {

            checkTodos.checked =
                false;

            return;

        }


        checkTodos.checked =
            ids.every(
                id =>
                    seleccionPendientes.has(
                        id
                    )
            );

    }



    function seleccionarTodosPendientes(
        estado
    ) {

        const ids =
            obtenerIdsPendientes();


        ids.forEach(
            id => {

                if (estado) {

                    seleccionPendientes.add(
                        id
                    );

                } else {

                    seleccionPendientes.delete(
                        id
                    );

                }

            }
        );


        aplicarSeleccionPendientes();

    }



    /*
     * Check principal
     */

    checkTodos?.addEventListener(
        "change",
        () => {

            seleccionarTodosPendientes(
                checkTodos.checked
            );

        }
    );



    /*
     * Botón seleccionar todos
     */

    botonTodos?.addEventListener(
        "click",
        () => {

            const ids =
                obtenerIdsPendientes();


            if (!ids.length) {

                return;

            }


            const todosSeleccionados =
                ids.every(
                    id =>
                        seleccionPendientes.has(
                            id
                        )
                );


            seleccionarTodosPendientes(
                !todosSeleccionados
            );

        }
    );



    /*
     * Check individual.
     *
     * Delegado porque DataTables
     * reconstruye las filas.
     */

    $("#tablaAsignaciones tbody")
        .on(
            "change",
            ".check-joven",
            function () {

                const id =
                    String(
                        this.value
                    );


                if (this.checked) {

                    seleccionPendientes.add(
                        id
                    );

                } else {

                    seleccionPendientes.delete(
                        id
                    );

                }


                sincronizarCheckPendientes();

            }
        );



    /*
     * Cada vez que DataTables
     * redibuja la tabla,
     * recuperamos las selecciones.
     */

    if (dtPendientes) {

        dtPendientes.on(
            "draw",
            () => {

                aplicarSeleccionPendientes();

            }
        );

    }



    /*
     * Antes de enviar,
     * eliminamos duplicados creados
     * anteriormente y generamos
     * nuevamente los hidden.
     */

    const formularioAsignar =
        document.getElementById(
            "formAsignarJovenes"
        );


    if (formularioAsignar) {

        formularioAsignar.addEventListener(
            "submit",
            () => {

                formularioAsignar
                    .querySelectorAll(
                        ".js-joven-seleccionado"
                    )
                    .forEach(
                        input =>
                            input.remove()
                    );


                seleccionPendientes.forEach(
                    jovenId => {

                        const input =
                            document.createElement(
                                "input"
                            );


                        input.type =
                            "hidden";


                        input.name =
                            "joven_ids[]";


                        input.value =
                            jovenId;


                        input.className =
                            "js-joven-seleccionado";


                        formularioAsignar.appendChild(
                            input
                        );

                    }
                );

            }
        );

    }



    /* =====================================================
       SELECCIÓN:
       ASIGNACIONES DEL PERÍODO
    ===================================================== */

    const checkTodasAsignaciones =
        document.getElementById(
            "checkTodasAsignaciones"
        );

    const botonTodasAsignaciones =
        document.getElementById(
            "selectTodasAsignaciones"
        );

    const contadorAsignaciones =
        document.getElementById(
            "contadorAsignacionesSeleccionadas"
        );

    const botonCancelarSeleccionadas =
        document.getElementById(
            "cancelarSeleccionadas"
        );


    /*
     * Set principal.
     *
     * Aquí se guardarán los IDs
     * realmente seleccionados.
     */

    const seleccionAsignaciones =
        new Set();



    function obtenerChecksAsignaciones() {

        if (!dtActuales) {

            return $();

        }

        return dtActuales
            .rows()
            .nodes()
            .to$()
            .find(
                ".check-asignacion:not(:disabled)"
            );

    }



    function obtenerIdsAsignaciones() {

        if (!dtActuales) {

            return [];

        }

        const ids = [];


        dtActuales
            .rows()
            .nodes()
            .to$()
            .find(
                ".check-asignacion:not(:disabled)"
            )
            .each(
                function () {

                    ids.push(
                        String(
                            this.value
                        )
                    );

                }
            );


        return ids;

    }



    function actualizarChecksAsignaciones() {

        const checks =
            obtenerChecksAsignaciones();


        checks.each(
            function () {

                const id =
                    String(
                        this.value
                    );


                this.checked =
                    seleccionAsignaciones.has(
                        id
                    );

            }
        );


        sincronizarCheckTodasAsignaciones();

        actualizarContadorAsignaciones();

    }



    function sincronizarCheckTodasAsignaciones() {

        if (
            !checkTodasAsignaciones
        ) {

            return;

        }


        const ids =
            obtenerIdsAsignaciones();


        if (!ids.length) {

            checkTodasAsignaciones.checked =
                false;

            return;

        }


        checkTodasAsignaciones.checked =
            ids.every(
                id =>
                    seleccionAsignaciones.has(
                        id
                    )
            );

    }



    function actualizarContadorAsignaciones() {

        const cantidad =
            seleccionAsignaciones.size;


        if (
            contadorAsignaciones
        ) {

            contadorAsignaciones.textContent =
                `${cantidad} seleccionadas`;

        }


        if (
            botonCancelarSeleccionadas
        ) {

            botonCancelarSeleccionadas.disabled =
                cantidad === 0;

        }

    }



    function seleccionarTodasAsignaciones(
        estado
    ) {

        const ids =
            obtenerIdsAsignaciones();


        ids.forEach(
            id => {

                if (estado) {

                    seleccionAsignaciones.add(
                        id
                    );

                } else {

                    seleccionAsignaciones.delete(
                        id
                    );

                }

            }
        );


        actualizarChecksAsignaciones();

    }



    /*
     * Check principal
     */

    checkTodasAsignaciones?.addEventListener(
        "change",
        () => {

            seleccionarTodasAsignaciones(
                checkTodasAsignaciones.checked
            );

        }
    );



    /*
     * Botón seleccionar todos
     */

    botonTodasAsignaciones?.addEventListener(
        "click",
        () => {

            const ids =
                obtenerIdsAsignaciones();


            if (!ids.length) {

                return;

            }


            const todasSeleccionadas =
                ids.every(
                    id =>
                        seleccionAsignaciones.has(
                            id
                        )
                );


            seleccionarTodasAsignaciones(
                !todasSeleccionadas
            );

        }
    );



    /*
     * Checkbox individual.
     */

    $("#tablaAsignacionesActuales tbody")
        .on(
            "change",
            ".check-asignacion",
            function () {

                const id =
                    String(
                        this.value
                    );


                if (this.checked) {

                    seleccionAsignaciones.add(
                        id
                    );

                } else {

                    seleccionAsignaciones.delete(
                        id
                    );

                }


                sincronizarCheckTodasAsignaciones();

                actualizarContadorAsignaciones();

            }
        );



    /*
     * Restaurar checks después
     * de cada draw de DataTables.
     */

    if (dtActuales) {

        dtActuales.on(
            "draw",
            () => {

                actualizarChecksAsignaciones();

            }
        );

    }



    /* =====================================================
       CANCELACIÓN MÚLTIPLE
    ===================================================== */

    const formularioCancelar =
        document.getElementById(
            "formCancelarAsignaciones"
        );


    if (formularioCancelar) {

        formularioCancelar.addEventListener(
            "submit",
            event => {

                if (
                    seleccionAsignaciones.size === 0
                ) {

                    event.preventDefault();

                    return;

                }


                formularioCancelar
                    .querySelectorAll(
                        ".js-asignacion-seleccionada"
                    )
                    .forEach(
                        input =>
                            input.remove()
                    );


                seleccionAsignaciones.forEach(
                    asignacionId => {

                        const input =
                            document.createElement(
                                "input"
                            );


                        input.type =
                            "hidden";


                        input.name =
                            "ids[]";


                        input.value =
                            asignacionId;


                        input.className =
                            "js-asignacion-seleccionada";


                        formularioCancelar.appendChild(
                            input
                        );

                    }
                );

            }
        );

    }



    /* =====================================================
       CANCELACIÓN INDIVIDUAL
    ===================================================== */

    /*
     * Delegamos el evento para que
     * DataTables pueda reconstruir
     * los botones sin perder el evento.
     */

    $("#tablaAsignacionesActuales tbody")
        .on(
            "click",
            ".btn-cancelar-asignacion",
            function () {

                const id =
                    this.dataset.id;


                const input =
                    document.getElementById(
                        "cancelarAsignacionId"
                    );


                const formulario =
                    document.getElementById(
                        "formCancelarIndividual"
                    );


                if (
                    !id ||
                    !input ||
                    !formulario
                ) {

                    return;

                }


                input.value =
                    id;


                formulario.submit();

            }
        );



    /* =====================================================
       ESTADO INICIAL
    ===================================================== */

    if (dtPendientes) {

        aplicarSeleccionPendientes();

    }


    if (dtActuales) {

        actualizarChecksAsignaciones();

    }


});