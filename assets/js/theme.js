document.addEventListener("DOMContentLoaded", () => {

    const btn = document.getElementById("themeToggle");

    /* =========================
       DARK MODE
    ========================= */
    if (btn) {
        const isDarkSaved = localStorage.getItem("theme") === "dark";

        if (isDarkSaved) {
            document.documentElement.classList.add("dark");
            btn.innerText = "☀️";
        } else {
            btn.innerText = "🌙";
        }

        btn.onclick = () => {
            const dark = document.documentElement.classList.toggle("dark");
            localStorage.setItem("theme", dark ? "dark" : "light");
            btn.innerText = dark ? "☀️" : "🌙";

            location.reload(); // 🔥 asegura que gráficas se actualicen correctamente
        };
    }

    /* =========================
       GRÁFICAS SEGURAS
    ========================= */
    const grafica1 = document.getElementById('graficaMensual');
    const grafica2 = document.getElementById('graficaTipos');

    const isDark = document.documentElement.classList.contains("dark");
    const colorText = isDark ? "#ffffff" : "#333";
    const colorGrid = isDark ? "rgba(255,255,255,0.1)" : "rgba(0,0,0,0.1)";

    // 🔥 SOLO si existen datos válidos
    if (
        grafica1 &&
        typeof Chart !== "undefined" &&
        typeof labelsMes !== "undefined" &&
        Array.isArray(labelsMes) &&
        labelsMes.length
    ) {
        new Chart(grafica1, {
            type: 'bar',
            data: {
                labels: labelsMes,
                datasets: [{
                    label: 'Asistencia',
                    data: typeof dataMes !== "undefined" ? dataMes : [],
                    backgroundColor: isDark ? '#17e1fc' : '#007bff',
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false }},
                scales: {
                    x: { ticks: { color: colorText }, grid: { color: colorGrid }},
                    y: { ticks: { color: colorText }, grid: { color: colorGrid }, beginAtZero: true }
                }
            }
        });
    }

    if (
        grafica2 &&
        typeof Chart !== "undefined" &&
        typeof labelsTipo !== "undefined" &&
        Array.isArray(labelsTipo) &&
        labelsTipo.length
    ) {
        new Chart(grafica2, {
            type: 'pie',
            data: {
                labels: labelsTipo,
                datasets: [{
                    data: typeof dataTipo !== "undefined" ? dataTipo : [],
                    backgroundColor: isDark
                        ? ['#17e1fc','#ff00ff','#00ff88','#ffaa00']
                        : ['#007bff','#ff6384','#28a745','#ffc107']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { labels: { color: colorText }}
                }
            }
        });
    }

    /* =========================
       BUSCADOR
    ========================= */
    const input = document.getElementById("buscador");

    if(input && typeof $ !== "undefined" && $.fn.DataTable){
        const tabla = $('#tablaJovenes').DataTable();

        input.addEventListener("keyup", function(){
            tabla.search(this.value).draw();
        });
    }

});