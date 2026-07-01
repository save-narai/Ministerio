/* =====================================================
   DASHBOARD
===================================================== */

document.addEventListener("DOMContentLoaded", () => {

    animarCards();

    animarContadores();

});

/* =====================================================
   APARICIÓN
===================================================== */

function animarCards(){

    const cards = document.querySelectorAll(
        ".dashboard__card"
    );

    cards.forEach((card,index)=>{

        card.style.opacity="0";
        card.style.transform="translateY(20px)";

        setTimeout(()=>{

            card.style.transition=
                "all .45s ease";

            card.style.opacity="1";
            card.style.transform="translateY(0)";

        },index*70);

    });

}

/* =====================================================
   CONTADORES
===================================================== */

function animarContadores(){

    const valores=document.querySelectorAll(
        ".dashboard__card-value"
    );

    valores.forEach(item=>{

        const texto=item.textContent.trim();

        const numero=parseInt(texto);

        if(isNaN(numero)) return;

        let actual=0;

        const incremento=Math.max(
            1,
            Math.ceil(numero/40)
        );

        const intervalo=setInterval(()=>{

            actual+=incremento;

            if(actual>=numero){

                actual=numero;

                clearInterval(intervalo);

            }

            item.textContent=
                texto.includes("%")
                ? actual+"%"
                : actual;

        },20);

    });

}