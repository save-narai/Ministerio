/* ==========================================================
   LOGIN UI
========================================================== */

document.addEventListener("DOMContentLoaded", () => {

    initAlerts();

    initInputEffects();

    initCardHover();

});

/* ==========================================================
   ALERTS
========================================================== */

function initAlerts(){

    const alert = document.querySelector(".login-alert");

    if(!alert) return;

    setTimeout(() => {

        alert.style.opacity = "0";

        alert.style.transform = "translateY(-10px)";

        setTimeout(() => {

            alert.remove();

        },300);

    },6000);

}

/* ==========================================================
   INPUT EFFECTS
========================================================== */

function initInputEffects(){

    const inputs = document.querySelectorAll(".login-input");

    inputs.forEach(input => {

        input.addEventListener("focus", () => {

            input.parentElement.classList.add("active");

        });

        input.addEventListener("blur", () => {

            if(input.value.trim() === ""){

                input.parentElement.classList.remove("active");

            }

        });

    });

}

/* ==========================================================
   CARD EFFECT
========================================================== */

function initCardHover(){

    const card = document.querySelector(".login-card");

    if(!card) return;

    card.addEventListener("mousemove",(e)=>{

        const rect = card.getBoundingClientRect();

        const x = e.clientX - rect.left;

        const y = e.clientY - rect.top;

        card.style.setProperty("--mouse-x",`${x}px`);

        card.style.setProperty("--mouse-y",`${y}px`);

    });

}