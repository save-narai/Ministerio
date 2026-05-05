document.addEventListener("DOMContentLoaded", () => {

    const btn = document.getElementById("themeToggle");

    /* =========================
       DARK MODE
    ========================= */

    if (btn) {

        const isDarkSaved =
            localStorage.getItem("theme") === "dark";

        if (isDarkSaved) {

            document.documentElement.classList.add("dark");
            btn.innerText = "☀️";

        } else {

            btn.innerText = "🌙";

        }

        btn.onclick = () => {

            const dark =
                document.documentElement.classList.toggle("dark");

            localStorage.setItem(
                "theme",
                dark ? "dark" : "light"
            );

            btn.innerText = dark ? "☀️" : "🌙";

            location.reload();

        };

    }

    /* =========================
       GRAFICAS
    ========================= */

    const grafica1 =
        document.getElementById("graficaMensual");

    const grafica2 =
        document.getElementById("graficaTipos");

    const isDark =
        document.documentElement.classList.contains("dark");

    const colorText =
        isDark ? "#ffffff" : "#333";

    const colorGrid =
        isDark
        ? "rgba(255,255,255,0.1)"
        : "rgba(0,0,0,0.1)";

    if (
        grafica1 &&
        typeof Chart !== "undefined" &&
        typeof labelsMes !== "undefined"
    ) {

        new Chart(grafica1, {

            type: "bar",

            data: {

                labels: labelsMes,

                datasets: [{

                    label: "Asistencia",

                    data: dataMes,

                    backgroundColor:
                        isDark ? "#17e1fc" : "#007bff",

                    borderRadius: 8

                }]

            },

            options: {

                responsive: true,

                maintainAspectRatio: false,

                plugins: {
                    legend: {
                        display: false
                    }
                },

                scales: {

                    x: {
                        ticks: { color: colorText },
                        grid: { color: colorGrid }
                    },

                    y: {
                        ticks: { color: colorText },
                        grid: { color: colorGrid },
                        beginAtZero: true
                    }

                }

            }

        });

    }

    if (
        grafica2 &&
        typeof Chart !== "undefined" &&
        typeof labelsTipo !== "undefined"
    ) {

        new Chart(grafica2, {

            type: "pie",

            data: {

                labels: labelsTipo,

                datasets: [{

                    data: dataTipo,

                    backgroundColor: isDark
                        ? ['#17e1fc','#ff00ff','#00ff88','#ffaa00']
                        : ['#007bff','#ff6384','#28a745','#ffc107']

                }]

            },

            options: {

                responsive: true,

                maintainAspectRatio: false,

                plugins: {

                    legend: {
                        labels: {
                            color: colorText
                        }
                    }

                }

            }

        });

    }



    /* =========================
       SIDEBAR
    ========================= */

    const sidebar =
        document.getElementById("sidebar");

    const main =
        document.getElementById("mainContent");

    if (sidebar) {

        sidebar.addEventListener("mouseenter", () => {

            sidebar.classList.add("expand");

            if (main) {
                main.classList.add("expand");
            }

        });

        sidebar.addEventListener("mouseleave", () => {

            sidebar.classList.remove("expand");

            if (main) {
                main.classList.remove("expand");
            }

        });

    }

});


/* =========================
       formulario
    ========================= */


document.addEventListener("DOMContentLoaded", () => {

    const nombre = document.getElementById("nombre");
    const telefono = document.getElementById("telefono");

    // 🔥 MAYÚSCULAS AUTOMÁTICAS
    if(nombre){
        nombre.addEventListener("input", () => {
            nombre.value = nombre.value.toUpperCase();
        });
    }

    // 🔥 SOLO NÚMEROS EN TELÉFONO
    if(telefono){
        telefono.addEventListener("input", () => {
            telefono.value = telefono.value.replace(/\D/g, "");
        });
    }

});