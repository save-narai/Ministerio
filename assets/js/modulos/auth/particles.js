/* ==========================================================
   LOGIN PARTICLES
========================================================== */

document.addEventListener("DOMContentLoaded", () => {

    const canvas = document.getElementById("fx");

    if (!canvas) return;

    const ctx = canvas.getContext("2d");

    let particles = [];

    let width = 0;
    let height = 0;

    /* ======================================================
       RESIZE
    ====================================================== */

    function resize() {

        width = canvas.width = window.innerWidth;
        height = canvas.height = window.innerHeight;

    }

    resize();

    window.addEventListener("resize", resize);

    /* ======================================================
       PARTICLE
    ====================================================== */

    class Particle {

        constructor() {

            this.reset();

            this.y = Math.random() * height;

        }

        reset() {

            this.x = Math.random() * width;

            this.y = height + Math.random() * 200;

            this.size = Math.random() * 2 + 1;

            this.speed = Math.random() * .35 + .15;

            this.opacity = Math.random() * .35 + .08;

            this.swing = Math.random() * 2;

            this.offset = Math.random() * Math.PI * 2;

        }

        update() {

            this.y -= this.speed;

            this.x += Math.sin(this.y * .01 + this.offset) * this.swing;

            if (this.y < -20) {

                this.reset();

            }

        }

        draw() {

            ctx.beginPath();

            ctx.arc(

                this.x,

                this.y,

                this.size,

                0,

                Math.PI * 2

            );

            ctx.fillStyle = `rgba(255,190,90,${this.opacity})`;

            ctx.fill();

        }

    }

    /* ======================================================
       CREATE
    ====================================================== */

    const TOTAL = 55;

    for (let i = 0; i < TOTAL; i++) {

        particles.push(new Particle());

    }

    /* ======================================================
       ANIMATE
    ====================================================== */

    function animate() {

        ctx.clearRect(0, 0, width, height);

        particles.forEach(particle => {

            particle.update();

            particle.draw();

        });

        requestAnimationFrame(animate);

    }

    animate();

});