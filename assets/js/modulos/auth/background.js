/* ==========================================================
   BACKGROUND
   Movimiento suave del fondo
========================================================== */

document.addEventListener("DOMContentLoaded", () => {

    const page = document.querySelector(".login-page");
    const image = document.querySelector(".login-background-image");

    if (!page || !image) return;

    let targetX = 0;
    let targetY = 0;

    let currentX = 0;
    let currentY = 0;

    /* ======================================================
       MOUSE MOVE
    ====================================================== */

    page.addEventListener("mousemove", (event) => {

        const x =
            (event.clientX / window.innerWidth - .5) * 16;

        const y =
            (event.clientY / window.innerHeight - .5) * 16;

        targetX = x;
        targetY = y;

    });

    /* ======================================================
       MOUSE LEAVE
    ====================================================== */

    page.addEventListener("mouseleave", () => {

        targetX = 0;
        targetY = 0;

    });

    /* ======================================================
       ANIMATION
    ====================================================== */

    function animate() {

        currentX += (targetX - currentX) * 0.05;
        currentY += (targetY - currentY) * 0.05;

        image.style.transform = `
            scale(1.06)
            translate(${currentX}px, ${currentY}px)
        `;

        requestAnimationFrame(animate);

    }

    animate();

});