<?php
session_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Login - Remanente</title>

<link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;600&display=swap" rel="stylesheet">

<style>

*{
margin:0;
padding:0;
box-sizing:border-box;
font-family:'Sora', sans-serif;
}

body{
overflow:hidden;
background:black;
}

/* ESCENA */
.scene{
position:relative;
width:100vw;
height:100vh;
overflow:hidden;
}

/* IMAGEN */
.scene img{
position:absolute;
width:100%;
height:100%;
object-fit:cover;
z-index:1;
}

/* CAPA OSCURA */
.overlay{
position:absolute;
top:0;
left:0;
width:100%;
height:100%;
background:linear-gradient(
rgba(0,0,0,0.7),
rgba(0,0,0,0.4),
rgba(0,0,0,0.8)
);
z-index:2;
}

/* CANVAS (NEBULOSA) */
canvas{
position:absolute;
top:0;
left:0;
width:100%;
height:100%;
z-index:3;
pointer-events:none;
mix-blend-mode:screen;
}

/* CONTROLES */
.controls{
position:absolute;
top:20px;
left:20px;
z-index:20;
background:rgba(0,0,0,0.4);
backdrop-filter:blur(10px);
padding:12px 15px;
border-radius:12px;
color:white;
font-size:14px;
box-shadow:0 0 15px rgba(0, 255, 255, 0.06);
}

.controls label{
display:block;
margin-bottom:5px;
opacity:0.8;
}

.controls select{
width:120px;
padding:6px;
border:none;
border-radius:8px;
background:rgba(118, 224, 243, 0.25);
color:black;
outline:none;
cursor:pointer;
}

/* hover glow */
.controls select:hover{
box-shadow:0 0 10px #00ffff;
}

/* FORMULARIO */
.login-box{
position:absolute;
top:50%;
left:50%;
transform:translate(-50%,-50%);
z-index:10;

width:350px;
padding:40px 30px;

background:rgba(255,255,255,0.08);
backdrop-filter:blur(15px);

border-radius:20px;
box-shadow:0 0 30px rgba(0,255,255,0.2);

animation:float 4s ease-in-out infinite;
}

/* ANIMACIÓN */
@keyframes float{
0%{ transform:translate(-50%,-50%) translateY(0);}
50%{ transform:translate(-50%,-50%) translateY(-10px);}
100%{ transform:translate(-50%,-50%) translateY(0);}
}

/* TITULO */
.title{
font-size:32px;
font-weight:600;
text-align:center;

background:linear-gradient(90deg,#ff00ff,#00ffff,#ffff00,#ff6600);
-webkit-background-clip:text;
color:transparent;

margin-bottom:10px;
letter-spacing:2px;
}

/* SUB */
.subtitle{
text-align:center;
color:#ccc;
margin-bottom:25px;
}

/* INPUTS */
input{
width:100%;
padding:12px;
margin-bottom:15px;

border:none;
border-radius:10px;

background:rgba(255,255,255,0.1);
color:white;

outline:none;
}

/* BOTÓN */
button{
width:100%;
padding:12px;

border:none;
border-radius:10px;

background:linear-gradient(90deg,#00ffff,#ff00ff);
color:white;

font-weight:bold;
cursor:pointer;

transition:0.3s;
}

button:hover{
transform:scale(1.05);
box-shadow:0 0 20px #00ffff;
}

.error{
color:#ff4d4d;
text-align:center;
margin-bottom:10px;
}

</style>
</head>

<body>

<div class="scene">

<img src="assets/img/1396974.png">

<div class="overlay"></div>

<canvas id="fx"></canvas>

<!-- CONTROLES -->
<div class="controls">
<label>Velocidad</label>

<select id="speed">
  <option value="0.3">Lenta 🌙</option>
  <option value="1" selected>Normal ⚡</option>
  <option value="2">Rápida 🔥</option>
  <option value="3">Ultra 🚀</option>
</select>
</div>

<div class="login-box">

<h1 class="title">REMANENTE</h1>
<p class="subtitle">Access Portal</p>

<?php if(isset($_SESSION["error"])): ?>
<p class="error"><?= htmlspecialchars($_SESSION["error"]) ?></p>
<?php unset($_SESSION["error"]); ?>
<?php endif; ?>

<form action="controllers/authController.php" method="POST">

<input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

<input type="text" name="usuario" placeholder="Usuario" required>
<input type="password" name="password" placeholder="Contraseña" required>

<button type="submit">Ingresar</button>

</form>

</div>

</div>

<script>

const canvas = document.getElementById("fx");
const ctx = canvas.getContext("2d");

function resize(){
canvas.width = window.innerWidth;
canvas.height = window.innerHeight;
}
resize();
window.addEventListener("resize", resize);

let speed = 1;

const speedInput = document.getElementById("speed");
if(speedInput){
speedInput.oninput = e=>{
speed = e.target.value;
};
}

class Cloud{
constructor(){
this.reset();
}

reset(){
this.x = Math.random()*canvas.width;
this.y = Math.random()*canvas.height;

this.size = Math.random()*250+150;

this.vx = (Math.random()*0.4+0.1);
this.vy = (Math.random()*0.2-0.1);

this.alpha = Math.random()*0.25+0.1;

this.color = [
"0,150,255",
"255,0,150",
"255,200,0",
"0,255,150",
"255,255,255",
"150,0,255"
][Math.floor(Math.random()*6)];
}

update(){
this.x += this.vx * speed;
this.y += this.vy * speed;

if(this.x > canvas.width+300 || this.y > canvas.height+300 || this.y < -300){
this.reset();
this.x = -200;
}
}

draw(){
let g = ctx.createRadialGradient(
this.x,this.y,0,
this.x,this.y,this.size
);

g.addColorStop(0,`rgba(${this.color},${this.alpha})`);
g.addColorStop(1,"transparent");

ctx.fillStyle = g;

ctx.beginPath();
ctx.arc(this.x,this.y,this.size,0,Math.PI*2);
ctx.fill();
}
}

let clouds = [];
for(let i=0;i<20;i++){
clouds.push(new Cloud());
}

function lightning(){
if(Math.random()<0.004){
ctx.strokeStyle="rgba(120,200,255,0.8)";
ctx.lineWidth=2;

ctx.beginPath();

let x=Math.random()*canvas.width;
let y=0;

ctx.moveTo(x,y);

for(let i=0;i<8;i++){
x += Math.random()*50-25;
y += canvas.height/8;
ctx.lineTo(x,y);
}

ctx.stroke();
}
}

function animate(){
ctx.clearRect(0,0,canvas.width,canvas.height);

clouds.forEach(c=>{
c.update();
c.draw();
});

lightning();

requestAnimationFrame(animate);
}

animate();

</script>

</body>
</html>