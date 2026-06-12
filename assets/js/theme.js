document.addEventListener("DOMContentLoaded", () => {

    /* =====================================================
       THEME TOGGLE
    ===================================================== */

    const btn =
        document.getElementById("themeToggle");

    if (btn) {

        const darkSaved =
            localStorage.getItem("theme") === "dark";

        if (darkSaved) {

            document.documentElement
                .classList.add("dark");

            btn.innerHTML =
                '<i class="fa-solid fa-sun"></i>';

        } else {

            btn.innerHTML =
                '<i class="fa-solid fa-moon"></i>';
        }

        btn.addEventListener("click", () => {

            const dark =
                document.documentElement
                    .classList.toggle("dark");

            localStorage.setItem(
                "theme",
                dark ? "dark" : "light"
            );

            btn.innerHTML = dark
                ? '<i class="fa-solid fa-sun"></i>'
                : '<i class="fa-solid fa-moon"></i>';
        });
    }

    /* =====================================================
       SIDEBAR ACTIVE
    ===================================================== */

    const currentUrl =
        window.location.pathname;

    document
        .querySelectorAll(".sidebar a")
        .forEach(link => {

            if (
                currentUrl.includes(
                    link.getAttribute("href")
                )
            ) {

                link.classList.add("active");
            }
        });

    

    /* =====================================================
       INPUT MAYUSCULAS
    ===================================================== */

    document
        .querySelectorAll("[data-uppercase]")
        .forEach(input => {

            input.addEventListener("input", () => {

                input.value =
                    input.value.toUpperCase();
            });
        });

    /* =====================================================
       SOLO NUMEROS
    ===================================================== */

    document
        .querySelectorAll("[data-numbers]")
        .forEach(input => {

            input.addEventListener("input", () => {

                input.value =
                    input.value.replace(/\D/g, "");
            });
        });

});



/* =====================================================
   SIDEBAR TOGGLE
===================================================== */

const app =
    document.querySelector(".app");

const sidebarToggle =
    document.getElementById("sidebarToggle");

/* =====================================================
   SAVED STATE
===================================================== */

const sidebarCollapsed =
    localStorage.getItem("sidebarCollapsed");

if(sidebarCollapsed === "true"){

    app?.classList.add("collapsed");
}

/* =====================================================
   TOGGLE
===================================================== */

if(sidebarToggle){

    sidebarToggle.addEventListener("click", () => {

        app.classList.toggle("collapsed");

        localStorage.setItem(
            "sidebarCollapsed",
            app.classList.contains("collapsed")
        );
    });
}