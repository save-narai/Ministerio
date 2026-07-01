document.addEventListener("DOMContentLoaded", () => {

    /* ==========================================
       ANIMAR TARJETAS
    ========================================== */

    document
        .querySelectorAll(".perfil-stat-card")
        .forEach((card, index) => {

            card.style.opacity = "0";
            card.style.transform = "translateY(20px)";

            setTimeout(() => {

                card.style.transition =
                    "all .35s ease";

                card.style.opacity = "1";
                card.style.transform =
                    "translateY(0)";

            }, 120 * index);

        });

    /* ==========================================
       ANIMAR TIMELINE
    ========================================== */

    document
        .querySelectorAll(".timeline-item")
        .forEach((item, index) => {

            item.style.opacity = "0";
            item.style.transform =
                "translateX(20px)";

            setTimeout(() => {

                item.style.transition =
                    "all .4s ease";

                item.style.opacity = "1";
                item.style.transform =
                    "translateX(0)";

            }, 180 * index);

        });

    /* ==========================================
       TOOLTIPS
    ========================================== */

    document
        .querySelectorAll("[data-tooltip]")
        .forEach(el => {

            el.setAttribute(
                "title",
                el.dataset.tooltip
            );

        });

});