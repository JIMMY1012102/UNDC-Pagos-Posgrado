@extends('layouts.market')
@section('title', 'CREA TU CUENTA · UNDC MARKET')
@section('marketPage', 'register')
@section('bodyClass', 'commerce-view market-login-view')
@section('content')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Sora:wght@500;600;700;800&display=swap" rel="stylesheet">
<style>

body.market-login-view{margin:0;font-family:'Inter',system-ui,sans-serif;background:#031c13;color:#fff}
.emerald-login *{box-sizing:border-box}
.emerald-login a{color:inherit;text-decoration:none}
.emerald-login button,.emerald-login input{font-family:inherit}
.sora{font-family:'Sora','Inter',sans-serif}
.btn{transition:transform .25s ease,box-shadow .25s ease;cursor:pointer}
.btn:hover{transform:translateY(-3px)}
.btn:active{transform:translateY(0) scale(.98)}
.btn:focus-visible,.field:focus-within,.eye:focus-visible,.lnk:focus-visible{outline:3px solid #b6f36b;outline-offset:3px}
.field{display:flex;align-items:center;gap:12px;height:62px;padding:0 16px;border-radius:18px;background:rgba(255,255,255,.06);border:1.5px solid rgba(255,255,255,.16);transition:border-color .25s,background .25s,box-shadow .25s}
.field:hover{border-color:rgba(255,255,255,.32)}
.field:focus-within{border-color:#b6f36b;background:rgba(182,243,107,.08);box-shadow:0 0 0 5px rgba(182,243,107,.14),0 12px 30px -12px rgba(182,243,107,.5)}
.inp{flex:1;min-width:0;height:100%;border:0;background:transparent;color:#fff;font-size:16px;outline:0}
.inp::placeholder{color:#85bda3}
.eye{border:0;background:transparent;color:#cfeee0;width:40px;height:40px;border-radius:12px;display:flex;align-items:center;justify-content:center;cursor:pointer;transition:background .2s}
.eye:hover{background:rgba(255,255,255,.12)}
.lnk{color:#b6f36b;font-weight:600;transition:color .2s}
.lnk:hover{color:#e6ffb3}

.knob{width:20px;height:20px;border-radius:50%;background:#fff;transition:transform .25s cubic-bezier(.3,1.4,.5,1);box-shadow:0 2px 6px rgba(0,0,0,.35)}
.aur{position:absolute;border-radius:50%;filter:blur(100px);pointer-events:none}
.a1{width:640px;height:640px;background:#0f9a64;opacity:.55;left:-180px;top:-200px;animation:drift1 16s ease-in-out infinite}
.a2{width:520px;height:520px;background:#b6f36b;opacity:.2;right:-140px;bottom:-160px;animation:drift2 19s ease-in-out infinite}
.a3{width:420px;height:420px;background:#12c28a;opacity:.3;left:38%;top:30%;animation:drift3 22s ease-in-out infinite}
.gridbg{position:absolute;inset:0;background-image:linear-gradient(rgba(255,255,255,.04) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.04) 1px,transparent 1px);background-size:56px 56px;-webkit-mask-image:radial-gradient(ellipse at 40% 45%,#000 20%,transparent 75%);mask-image:radial-gradient(ellipse at 40% 45%,#000 20%,transparent 75%)}
.dot{position:absolute;bottom:-20px;width:8px;height:8px;border-radius:50%;background:#b6f36b;box-shadow:0 0 14px #b6f36b;opacity:0;animation:rising 12s linear infinite}
.d1{left:6%;animation-delay:0s}.d2{left:14%;animation-delay:3s;width:5px;height:5px}.d3{left:23%;animation-delay:7s}.d4{left:33%;animation-delay:1s;width:6px;height:6px}.d5{left:44%;animation-delay:5s}.d6{left:55%;animation-delay:9s;width:5px;height:5px}.d7{left:64%;animation-delay:2s}.d8{left:73%;animation-delay:6s;width:6px;height:6px}.d9{left:84%;animation-delay:10s}.d10{left:93%;animation-delay:4s;width:5px;height:5px}
.stage{perspective:1400px}
.rig{transform-style:preserve-3d;transition:transform .2s ease-out}
.plate{position:absolute;left:50%;top:80%;width:360px;height:360px;margin:-180px 0 0 -180px;border-radius:60px;transform:rotateX(90deg) translateZ(-10px);background:radial-gradient(circle,rgba(182,243,107,.55),rgba(16,185,129,.25) 45%,transparent 70%);filter:blur(6px)}
.pl2{position:absolute;left:50%;top:80%;width:300px;height:300px;margin:-150px 0 0 -150px;border-radius:50%;border:2px solid rgba(182,243,107,.6);transform:rotateX(90deg);animation:pulse 3.4s ease-out infinite}
.pl3{animation-delay:1.7s}
.cap{position:absolute;left:50%;top:44%;width:0;height:0;transform-style:preserve-3d;animation:capspin 14s linear infinite}
.bd{position:absolute;left:-120px;top:-120px;width:240px;height:240px;border-radius:14px;transform:rotateX(90deg) rotateZ(45deg)}
.bd1{background:#0a4d33}.bd2{background:#0c5c3d}.bd3{background:#0e6b47}.bd4{background:#117a52}
.bd5{background:linear-gradient(135deg,#17a56d,#0b6b46);box-shadow:0 0 0 3px #b6f36b inset,0 0 60px rgba(182,243,107,.55)}
.base{position:absolute;left:-62px;top:6px;width:124px;height:84px;border-radius:0 0 62px 62px;background:linear-gradient(180deg,#0a4d33,#06301f);transform:translateZ(0)}
.base2{transform:rotateY(90deg)}
.cord{position:absolute;left:92px;top:2px;width:5px;height:96px;border-radius:3px;background:#b6f36b;transform:translateZ(0)}
.tas{position:absolute;left:84px;top:92px;width:22px;height:48px;border-radius:6px 6px 11px 11px;background:linear-gradient(180deg,#e6ffb3,#8be04e);box-shadow:0 0 24px rgba(182,243,107,.7);transform-origin:50% 0;animation:swing 3s ease-in-out infinite}
.btn3d{position:absolute;left:-9px;top:-14px;width:18px;height:18px;border-radius:50%;background:#e6ffb3}
.botw{position:absolute;left:50%;top:52%;width:0;height:0;transform-style:preserve-3d;transform:scale(.9)}
.bot{position:absolute;left:0;top:0;width:0;height:0;transform-style:preserve-3d;animation:botsway 9s ease-in-out infinite}
.ly{position:absolute;left:-200px;top:-240px;width:400px;height:480px;pointer-events:none}
.ly svg{display:block;width:100%;height:100%;overflow:visible}
.lflame{transform:translateZ(-70px);animation:flame 1.3s ease-in-out infinite}
.lbody.market-login-view{transform:translateZ(-30px);filter:drop-shadow(0 24px 22px rgba(0,0,0,.35))}
.larm{transform:translateZ(-14px);transform-origin:118px 300px;animation:armL 4s ease-in-out infinite;filter:drop-shadow(0 10px 10px rgba(0,0,0,.3))}
.lhead{transform:translateZ(20px);filter:drop-shadow(0 16px 18px rgba(0,0,0,.3))}
.lcap{transform:translateZ(56px);filter:drop-shadow(0 10px 10px rgba(0,0,0,.35))}
.larmr{transform:translateZ(62px);transform-origin:282px 300px;animation:armR 1.4s ease-in-out infinite;filter:drop-shadow(0 10px 10px rgba(0,0,0,.3))}
.eyeg{transform-box:fill-box;transform-origin:center;animation:blink 5s infinite}
.tass{transform-origin:312px 52px;animation:swing 3s ease-in-out infinite}
.brs{animation:bar 1.6s ease-in-out infinite}
.orb{position:absolute;left:50%;top:52%;width:0;height:0;transform-style:preserve-3d}
.orbit{position:absolute;left:-230px;top:-230px;width:460px;height:460px;border-radius:50%;border:1.5px dashed rgba(110,240,185,.45);transform:rotateX(76deg);transform-style:preserve-3d}
.spin{position:absolute;inset:0;transform-style:preserve-3d;animation:orbitspin 12s linear infinite}
.sat{position:absolute;left:50%;top:-14px;width:28px;height:28px;margin-left:-14px;border-radius:50%;background:radial-gradient(circle at 30% 30%,#e6ffb3,#8be04e);box-shadow:0 0 24px rgba(182,243,107,.9);animation:counter 12s linear infinite}
.sat2{top:auto;bottom:-14px;background:radial-gradient(circle at 30% 30%,#a7f3d0,#10b981);box-shadow:0 0 24px rgba(52,211,153,.9)}
.float{position:absolute;border-radius:20px;background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.26);backdrop-filter:blur(14px);-webkit-backdrop-filter:blur(14px);box-shadow:0 30px 60px rgba(0,0,0,.4);padding:14px 18px}
.fa{left:0;top:40px;animation:floata 7s ease-in-out infinite}
.fb{right:0;top:120px;animation:floatb 8s ease-in-out infinite}
.fc{left:40px;bottom:50px;animation:floata 9s ease-in-out infinite reverse}
.gb{position:relative;border-radius:38px;padding:2px;overflow:hidden;transition:transform .2s ease-out;box-shadow:0 60px 120px -20px rgba(0,0,0,.6),0 0 90px rgba(16,185,129,.25)}
.gb:before{content:"";position:absolute;left:50%;top:50%;width:200%;padding-bottom:200%;transform:translate(-50%,-50%);background:conic-gradient(from 0deg,transparent 0 55%,#34d399 70%,#b6f36b 85%,transparent 100%);animation:borderspin 5s linear infinite}
.gbin{position:relative;border-radius:36px;padding:40px 38px;background:linear-gradient(160deg,#0a3a28,#052a1c);overflow:hidden}
.shine:after{content:"";position:absolute;top:-50%;left:-60%;width:35%;height:200%;background:linear-gradient(90deg,transparent,rgba(255,255,255,.1),transparent);transform:rotate(18deg);animation:sweep 6s ease-in-out infinite;pointer-events:none}
.spinner{width:22px;height:22px;border-radius:50%;border:3px solid rgba(4,40,28,.25);border-top-color:#04281c;animation:rot .7s linear infinite}
.in1{animation:rise .9s .05s both cubic-bezier(.2,.8,.2,1)}
.in2{animation:rise .9s .2s both cubic-bezier(.2,.8,.2,1)}
.in3{animation:rise .9s .35s both cubic-bezier(.2,.8,.2,1)}
@keyframes rise{from{opacity:0;transform:translateY(34px)}to{opacity:1;transform:none}}
@keyframes drift1{0%,100%{transform:translate(0,0)}50%{transform:translate(120px,90px)}}
@keyframes drift2{0%,100%{transform:translate(0,0)}50%{transform:translate(-140px,-80px)}}
@keyframes drift3{0%,100%{transform:translate(0,0) scale(1)}50%{transform:translate(-90px,70px) scale(1.25)}}
@keyframes rising{0%{transform:translateY(0);opacity:0}10%{opacity:.9}90%{opacity:.7}100%{transform:translateY(-900px);opacity:0}}
@keyframes pulse{0%{transform:rotateX(90deg) scale(.4);opacity:.9}100%{transform:rotateX(90deg) scale(1.15);opacity:0}}
@keyframes botsway{0%,100%{transform:translateY(0) rotateY(-15deg)}50%{transform:translateY(-18px) rotateY(15deg)}}
@keyframes blink{0%,92%,100%{transform:scaleY(1)}95%{transform:scaleY(.08)}}
@keyframes bar{0%,100%{opacity:.35}50%{opacity:1}}
@keyframes armL{0%,100%{transform:translateZ(-14px) rotate(0deg)}50%{transform:translateZ(-14px) rotate(7deg)}}
@keyframes armR{0%,100%{transform:translateZ(62px) rotate(-142deg)}50%{transform:translateZ(62px) rotate(-168deg)}}
@keyframes flame{0%,100%{transform:translateZ(-70px) scale(.9);opacity:.75}50%{transform:translateZ(-70px) scale(1.12);opacity:1}}
@keyframes capspin{0%{transform:translateY(0) rotateY(0)}50%{transform:translateY(-26px) rotateY(180deg)}100%{transform:translateY(0) rotateY(360deg)}}
@keyframes swing{0%,100%{transform:rotate(7deg)}50%{transform:rotate(-7deg)}}
@keyframes orbitspin{to{transform:rotateZ(360deg)}}
@keyframes counter{to{transform:rotateZ(-360deg)}}
@keyframes floata{0%,100%{transform:translateZ(130px) rotateY(14deg) translateY(0)}50%{transform:translateZ(160px) rotateY(10deg) translateY(-16px)}}
@keyframes floatb{0%,100%{transform:translateZ(110px) rotateY(-14deg) translateY(0)}50%{transform:translateZ(140px) rotateY(-9deg) translateY(14px)}}
@keyframes borderspin{to{transform:translate(-50%,-50%) rotate(360deg)}}
@keyframes sweep{0%,60%{left:-60%}100%{left:160%}}
@keyframes rot{to{transform:rotate(360deg)}}
@media (prefers-reduced-motion:reduce){*{animation:none!important;transition:none!important}}

body.market-login-view{background:#031c13;color:#fff;font-family:Inter,system-ui,sans-serif}
.market-login-view > header,.market-login-view > .footer{display:none}
.market-login-view main.wrap{max-width:none;width:100%;margin:0;padding:0}
.emerald-login{position:relative;isolation:isolate;overflow:hidden;min-height:100svh;padding:0!important;background:#031c13}
.emerald-layout{position:relative;max-width:var(--container-max,1440px);margin:auto;padding:28px;display:flex;align-items:center;gap:56px;min-height:100svh}
.emerald-intro{flex:1 1 540px!important}.emerald-panel{flex:1 1 440px;min-width:0;max-width:520px;width:100%;margin:auto}
.emerald-toolbar{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:20px}
.emerald-toolbar a,.emerald-toolbar button{padding:10px 16px;border:1px solid #639c7e;border-radius:14px;background:transparent;color:#d7ff9a;font-size:14px;cursor:pointer}
.emerald-login .market-login-card{position:relative;top:auto;max-width:none;width:100%;padding:40px 38px;margin:0;border:0;border-radius:36px;background:linear-gradient(160deg,#0a3a28,#052a1c);box-shadow:none;color:#fff}
.emerald-login .market-login-heading{display:flex;flex-direction:column;gap:0;padding:0;margin:0 0 30px;text-align:center}
.emerald-login .market-login-symbol{width:68px;height:68px;border-radius:22px;background:linear-gradient(135deg,#b6f36b,#34d399);color:#04281c;display:flex;align-items:center;justify-content:center;transform:rotate(-6deg)}
.emerald-login .market-login-symbol svg{width:32px;height:32px}
.emerald-login h1{font:800 34px Sora,Inter,sans-serif;color:#fff;margin:18px 0 8px;letter-spacing:-.5px}
.emerald-login .market-login-heading p{font-size:15px;color:#cfeee0;margin:0}
.emerald-login input::placeholder{color:#85bda3}.emerald-login input:autofill{box-shadow:0 0 0 1000px #103f2e inset;-webkit-text-fill-color:#fff}
.emerald-login .market-login-submit{width:100%;height:62px;border-radius:18px;border:0;background:linear-gradient(90deg,#b6f36b,#6ee7b7);color:#04281c;font-size:18px;font-weight:700;box-shadow:0 20px 44px -10px rgb(182 243 107 / 35%)}
.emerald-login .market-login-register{margin:24px 0 0;color:#cfeee0;font-size:15px;display:flex;flex-wrap:wrap;gap:6px;justify-content:center}.emerald-login .market-login-register a{color:#b6f36b;font-weight:700}
.emerald-login .eye{flex-shrink:0}.emerald-login a:focus-visible,.emerald-login button:focus-visible{outline:3px solid #b6f36b;outline-offset:4px}
@media(max-width:1000px){.emerald-layout{gap:28px}.emerald-intro .stage{transform:scale(.8);margin:-40px -50px}.emerald-intro h1{font-size:40px!important}.emerald-intro .float{padding:10px}.emerald-intro .sora{font-size:24px!important}}
@media(max-width:760px){.emerald-layout{flex-direction:column;padding:24px 16px;gap:28px}.emerald-intro{flex:auto!important;width:100%;gap:20px!important}.emerald-intro .stage{display:none}.emerald-intro h1{font-size:30px!important}.emerald-intro h1 br{display:none}.emerald-panel{flex:auto;max-width:520px}.emerald-login .market-login-card{padding:28px 22px}.emerald-intro .sora{font-size:24px!important}.gb{border-radius:30px}}


/* Compact viewport layout: shrink the content instead of clipping the form. */
@media(min-width:761px){
.emerald-layout{max-width:var(--container-max,1440px);min-height:100svh;padding:16px 28px;gap:40px}
.emerald-intro{align-self:center!important;justify-content:center!important;gap:12px!important}
.emerald-intro .stage{height:340px!important;transform:none;margin:0}
.emerald-intro .rig{inset:-55px -40px;transform:scale(.72) rotateX(16deg) rotateY(-6deg)!important}
.emerald-intro h1{font-size:38px!important;line-height:1.1!important}
.emerald-panel{max-width:440px}
.emerald-toolbar{margin-bottom:12px}
.emerald-toolbar a,.emerald-toolbar button{padding:8px 14px;min-height:40px}
.emerald-login .market-login-card{padding:24px 28px;border-radius:28px}
.emerald-login .gb{border-radius:30px}
.emerald-login .market-login-heading{margin-bottom:16px}
.emerald-login .market-login-symbol{width:48px;height:48px;border-radius:16px}
.emerald-login .market-login-symbol svg{width:26px;height:26px}
.emerald-login h1{font-size:28px;margin:12px 0 6px}
.emerald-login .market-login-heading p{font-size:14px}
.emerald-login .market-login-submit{height:48px;border-radius:14px;font-size:16px}
.emerald-login .market-login-register{margin:16px 0 0;font-size:14px}
}
@media(min-width:761px) and (max-height:700px){
.emerald-layout{padding-top:10px;padding-bottom:10px}
.emerald-intro .stage{height:290px!important}
.emerald-intro .rig{inset:-75px -50px;transform:scale(.62) rotateX(16deg) rotateY(-6deg)!important}
.emerald-intro h1{font-size:32px!important}
.emerald-login .market-login-card{padding:18px 24px}
.emerald-login .market-login-heading{margin-bottom:10px}
.emerald-login .market-login-symbol{width:40px;height:40px}
.emerald-login h1{font-size:26px;margin-top:8px}
.emerald-login .market-login-register{margin-top:12px}
}
body.light-mode .emerald-login{background:linear-gradient(125deg,#d6f5e5 0%,#ebfaf2 52%,#c3ead4 100%);color:#124e39}
body.light-mode .emerald-login .aur{opacity:.1}
body.light-mode .emerald-login .gridbg{background-image:linear-gradient(rgb(13 105 70 / 6%) 1px,transparent 1px),linear-gradient(90deg,rgb(13 105 70 / 6%) 1px,transparent 1px)}
body.light-mode .emerald-intro>.in1{color:#124e39}
body.light-mode .emerald-intro>.in1>span:last-child>span:last-child{color:#376751!important}
body.light-mode .emerald-intro .sora span{color:#07835a!important}
body.light-mode .emerald-intro>h1{color:#124e39}
body.light-mode .emerald-intro>h1 span{background:none!important;color:#087b53!important}
body.light-mode .emerald-login .float{background:rgb(255 255 255 / 88%);border-color:#91cdb0;color:#174f3b;box-shadow:0 12px 28px rgb(23 92 60 / 12%)}
body.light-mode .emerald-toolbar a,body.light-mode .emerald-toolbar button{background:#f2fcf6;color:#155c40;border-color:#7fbea0}
body.light-mode .emerald-login .gb{background:#b8dfcb;box-shadow:0 20px 55px rgb(23 92 60 / 15%)}
body.light-mode .emerald-login .gb::before{display:none}
body.light-mode .emerald-login .market-login-card{background:#fff;color:#174f3b}
body.light-mode .emerald-login .market-login-heading h1{color:#124e39}
body.light-mode .emerald-login .market-login-heading p,body.light-mode .emerald-login .market-login-register{color:#4b6f5f}
body.light-mode .emerald-login .market-login-submit{background:#087c53;color:#fff;box-shadow:0 8px 20px rgb(8 124 83 / 18%)}
body.light-mode .emerald-login .market-login-register a{color:#08764e}


/* Reference composition: stationary cards around the robot and centered caption. */
.emerald-university-emblem{width:64px;height:76px;object-fit:contain;flex-shrink:0;filter:drop-shadow(0 5px 8px rgb(0 70 45 / 12%))}
.emerald-intro>h1{text-align:center;align-self:center;width:100%}
.emerald-login .float{animation:none;transform:none;max-width:190px;padding:14px 16px}
.emerald-login .float>div{gap:12px!important}
.emerald-login .float>div>span:first-child{flex-shrink:0}
.emerald-login .float>div>span:last-child{font-size:16px!important;line-height:1.25;white-space:normal}
.emerald-login .fa{left:-28px;top:0}
.emerald-login .fb{right:-28px;top:225px}
.emerald-login .fc{left:-28px;bottom:55px}
@media(min-width:761px) and (max-width:1000px){
.emerald-login .fa,.emerald-login .fc{left:0}
.emerald-login .fb{right:0}
.emerald-login .float{max-width:155px;padding:12px}
.emerald-login .float>div>span:last-child{font-size:14px!important}
}
@media(max-width:760px){.emerald-university-emblem{width:48px;height:58px}}


/* Accessibility palettes must not fill the transparent SVG scene layers. */
body[data-accessibility-color] .emerald-login .stage,
body[data-accessibility-color] .emerald-login .stage div:not(.float),
body[data-accessibility-color] .emerald-login .stage .float div,
body[data-accessibility-color] .emerald-login .stage .float span{
background:transparent!important;box-shadow:none!important;
}
body[data-accessibility-color] .emerald-login .aur,
body[data-accessibility-color] .emerald-login .gridbg,
body[data-accessibility-color] .emerald-login .plate,
body[data-accessibility-color] .emerald-login .pl2,
body[data-accessibility-color] .emerald-login .orb{display:none}
body[data-accessibility-color] .emerald-login .gb::before{display:none}
body[data-accessibility-color] .emerald-login .gb{overflow:visible;border:1px solid var(--a11y-border);padding:1px}
body[data-accessibility-color] .emerald-login .float{background:var(--a11y-surface)!important;border:1px solid var(--a11y-border)!important}
body[data-accessibility-color] .emerald-login .market-login-heading h1,
body[data-accessibility-color] .emerald-login .emerald-intro h1,
body[data-accessibility-color] .emerald-login .emerald-intro h1 span{
color:var(--a11y-ink)!important;-webkit-text-fill-color:var(--a11y-ink)!important;background:none!important;
}


/* Larger left composition, with a height-aware compact variant. */
@media(min-width:1001px){
.emerald-layout{max-width:var(--container-max,1440px);gap:48px}
.emerald-university-emblem{width:76px;height:88px}
.emerald-intro>.in1 .sora{font-size:34px!important}
.emerald-intro>.in1>span:last-child>span:last-child{font-size:17px!important}
.emerald-intro{gap:16px!important}
.emerald-intro .stage{height:380px!important}
.emerald-intro .rig{inset:-20px 0;transform:scale(.84)!important}
.emerald-intro>h1{font-size:40px!important}
.emerald-login .float{max-width:185px;padding:15px 17px}
.emerald-login .float>div>span:last-child{font-size:17px!important}
.emerald-login .fa{left:0;top:65px}
.emerald-login .fc{left:0;bottom:90px}
.emerald-login .fb{right:0;top:180px}
}
@media(min-width:1001px) and (max-height:700px){
.emerald-intro{gap:12px!important}
.emerald-university-emblem{width:68px;height:78px}
.emerald-intro>.in1 .sora{font-size:32px!important}
.emerald-intro .stage{height:330px!important}
.emerald-intro .rig{inset:-40px 0;transform:scale(.76)!important}
.emerald-intro>h1{font-size:36px!important}
}


@media(min-width:761px){
.emerald-intro>.in1{position:relative;top:-24px}
}

.emerald-layout{max-width:var(--container-max,1440px)}
.market-login-view .market-login-layout{width:min(calc(100% - 56px),var(--container-max,1440px));max-width:var(--container-max,1440px);margin-inline:auto}
@media(min-width:1600px){.emerald-layout{gap:clamp(48px,4vw,72px)}.emerald-panel{max-width:500px}}
@media(min-width:1920px){.emerald-layout{max-width:var(--container-max,1680px)}.emerald-panel{max-width:540px}.emerald-intro h1{font-size:clamp(48px,3.2vw,64px)}}
@media(min-width:2560px){.emerald-panel{max-width:560px}}


.emerald-register{overflow:clip}
.emerald-register .field:focus-within{outline:0;box-shadow:none;background:transparent}
.emerald-register   .emerald-layout{max-width:var(--container-max,1440px);align-items:center  !important;padding-top:28px !important;padding-bottom:28px !important;}
.emerald-register .emerald-panel{flex:0 1 500px;max-width:500px;min-width:0}
.emerald-register .market-login-card{width:100%;max-width:none}
.emerald-register .market-login-heading{margin-bottom:20px}
.emerald-register .market-login-heading h1{text-transform:none}
.emerald-register .market-login-heading p{min-height:1.4em}
.emerald-register .back{display:inline-flex;align-items:center;min-height:42px;margin:0 0 16px;padding:8px 14px;border:1px solid #639c7e;border-radius:12px;background:transparent;color:#d7ff9a;font:600 14px Inter,system-ui,sans-serif;cursor:pointer}
.emerald-register .emerald-register-fields h2{margin:0 0 14px;color:#b6f36b;font:700 13px Sora,Inter,sans-serif;letter-spacing:.04em}
.emerald-register .form-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px 16px}
.emerald-register fieldset{min-width:0;margin:0;padding:0;border:0}
.emerald-register .register-profile-fields{display:contents}
.emerald-register .field{display:block;min-width:0;height:auto;margin:0;padding:0;border:0;border-radius:0;background:transparent}
.emerald-register .field label{display:block;margin:0 0 7px;color:#d3f3e4;font-size:13px;font-weight:600}
.emerald-register .field input,.emerald-register .field select,.emerald-register .register-password-control{display:block;width:100%;min-width:0;height:48px;padding:0 14px;border:1.5px solid rgb(255 255 255 / 18%);border-radius:14px;background:rgb(255 255 255 / 6%);color:#fff;font:15px Inter,system-ui,sans-serif;outline:0;transition:border-color .2s,box-shadow .2s,background .2s}
.emerald-register .field select{color-scheme:dark}
.emerald-register .field input:focus,.emerald-register .field select:focus,.emerald-register .register-password-control:focus-within{border-color:#b6f36b;box-shadow:0 0 0 4px rgb(182 243 107 / 14%);background:rgb(182 243 107 / 8%)}
.emerald-register .field input::placeholder{color:#85bda3}
.emerald-register .field input:is(:autofill,:-webkit-autofill){box-shadow:0 0 0 1000px #103f2e inset;-webkit-text-fill-color:#fff}
.emerald-register .register-phone-row{grid-column:1/-1;display:grid;grid-template-columns:minmax(104px, .34fr) minmax(0,1fr);gap:14px}
.emerald-register .register-password-control{display:flex;align-items:center;gap:8px;padding-right:5px}
.emerald-register .register-password-control input{height:100%;padding:0;border:0;background:transparent;box-shadow:none!important}
.emerald-register .register-password-control .eye{flex:0 0 38px;width:38px;height:38px;color:#cfeee0}
.emerald-register .register-progress{display:flex;align-items:center;gap:12px;margin:0 0 20px;color:#cfeee0;font-size:12px;font-weight:700}
.emerald-register .register-progress-dots{display:flex;align-items:center;gap:7px}
.emerald-register .register-progress-dots span{width:8px;height:8px;border-radius:50%;background:rgb(255 255 255 / 22%);transition:background .2s,transform .2s}
.emerald-register .register-progress-dots span[aria-current="step"]{background:#b6f36b;transform:scale(1.2)}
.emerald-register .register-step-actions{display:flex;align-items:center;gap:12px;margin-top:24px}
.emerald-register .register-step-actions .market-login-submit{flex:1;margin:0}
.emerald-register .register-step-actions .back{margin:0}
.emerald-register .register-step-next{width:100%;height:54px;border:0;border-radius:16px;background:linear-gradient(90deg,#b6f36b,#6ee7b7);color:#04281c;font:700 17px Inter,system-ui,sans-serif;box-shadow:0 16px 34px -12px rgb(182 243 107 / 35%)}
.emerald-register .market-login-submit{height:54px;border-radius:16px;font-size:16px}
.emerald-register .market-login-register{margin-top:20px}
body.light-mode .emerald-register .back{background:#f2fcf6;color:#155c40;border-color:#7fbea0}
body.light-mode .emerald-register .emerald-register-fields h2{color:#087b53}
body.light-mode .emerald-register .field label{color:#367864}
body.light-mode .emerald-register .field input,body.light-mode .emerald-register .field select,body.light-mode .emerald-register .register-password-control{border-color:#a7d1ba;background:#f3faf6;color:#163e2e;color-scheme:light}
body.light-mode .emerald-register .field input:is(:autofill,:-webkit-autofill){box-shadow:0 0 0 1000px #f3faf6 inset;-webkit-text-fill-color:#163e2e}
body.light-mode .emerald-register .register-password-control .eye{color:#197950}
body.light-mode .emerald-register .register-progress{color:#367864}
body.light-mode .emerald-register .register-progress-dots span{background:#c5dfd0}
body.light-mode .emerald-register .register-progress-dots span[aria-current="step"]{background:#087b53}
@media(max-width:1000px) and (min-width:761px){}
@media(max-width:760px){.emerald-register .emerald-panel{flex:auto;width:100%;max-width:none}.emerald-register .form-grid{grid-template-columns:minmax(0,1fr);gap:12px}.emerald-register .register-phone-row{grid-template-columns:minmax(100px,.38fr) minmax(0,1fr);gap:10px}.emerald-register .market-login-card{padding:26px 20px}.emerald-register .market-login-heading{margin-bottom:18px}.emerald-register .register-step-actions{flex-direction:column-reverse;align-items:stretch}.emerald-register .register-step-actions .back{justify-content:center}}
@media(min-width:761px) and (max-height:700px){.emerald-register .market-login-card{padding:22px 26px}.emerald-register .form-grid{gap:10px 14px}.emerald-register .field input,.emerald-register .field select,.emerald-register .register-password-control{height:42px}}

/* El atributo hidden debe prevalecer también cuando el layout móvil usa display:flex. */
.emerald-register .register-step[hidden]{display:none!important}

/* Registro en tres pasos: altura constante y composición sin scroll en escritorio. */
@media (min-width:768px){
  body.market-login-view{height:100dvh;overflow:hidden}
  .emerald-register{height:100dvh;min-height:0;overflow:hidden}
  .emerald-register .emerald-layout{
    height:100dvh;min-height:0;overflow:hidden;align-items:center!important;
    padding:clamp(8px,1.8vh,18px) clamp(20px,2.2vw,36px)!important;
    gap:clamp(24px,3vw,52px)
  }
  .emerald-register .emerald-intro{
    min-height:0;align-self:stretch!important;justify-content:space-between!important;
    gap:8px!important;padding-block:clamp(4px,1vh,12px)
  }
  .emerald-register .emerald-intro>.in1{top:0}
  .emerald-register .emerald-intro .stage{
    height:clamp(230px,42dvh,430px)!important;min-height:0;flex:0 1 auto;
    margin:0!important;overflow:visible
  }
  .emerald-register .emerald-intro .rig{
    inset:-55px -40px;transform:scale(clamp(.52,calc(.40 + .00013 * 100vw),.68)) rotateX(16deg) rotateY(-6deg)!important
  }
  .emerald-register .emerald-intro>h1{
    align-self:center;margin:0!important;font-size:clamp(30px,2.7vw,48px)!important;
    line-height:1.04!important;text-align:center
  }
  .emerald-register .emerald-panel{
    height:min(calc(100dvh - 28px),700px);max-height:calc(100dvh - 28px);
    min-height:0;display:flex;flex-direction:column;align-self:center
  }
  .emerald-register .emerald-toolbar{flex:0 0 auto;margin:0 0 8px}
  .emerald-register .emerald-toolbar a{min-height:38px;padding:7px 13px}
  .emerald-register .emerald-panel>.gb{flex:1;min-height:0;display:flex}
  .emerald-register .account-data-card{
    width:100%;height:100%;min-height:0;display:flex;flex-direction:column;
    overflow:hidden;padding:clamp(14px,2.2vh,22px) clamp(18px,2vw,26px)!important
  }
  .emerald-register .market-login-heading{
    flex:0 0 auto;display:flex;flex-direction:row;align-items:center;justify-content:center;
    gap:10px;margin:0 0 8px!important;text-align:left
  }
  .emerald-register .market-login-symbol{
    width:38px!important;height:38px!important;flex:0 0 38px;border-radius:12px!important
  }
  .emerald-register .market-login-symbol svg{width:21px!important;height:21px!important}
  .emerald-register .market-login-heading h1{font-size:clamp(20px,2vw,24px)!important;line-height:1.15;margin:0 0 2px!important}
  .emerald-register .market-login-heading p{font-size:12px!important;line-height:1.2;min-height:0;white-space:nowrap}
  .emerald-register .register-progress{flex:0 0 auto;margin:0 0 8px;font-size:11px}
  .emerald-register .register-progress-dots{gap:8px}
  .emerald-register .register-progress-dots span{width:7px;height:7px}
  .emerald-register .account-data-card>form{display:flex;flex:1;min-height:0;flex-direction:column}
  .emerald-register .emerald-register-fields{display:flex;flex:1;min-height:0}
  .emerald-register .register-step{height:100%;min-height:0;flex:1;display:flex;flex-direction:column;overflow:visible}
  .emerald-register .register-step .form-grid{flex:1;min-height:0;align-content:start}
  .emerald-register .register-step-actions{position:relative;z-index:1;flex:0 0 auto}
  .emerald-register .register-password-control{position:relative;z-index:3}
  .emerald-register .register-password-control .eye{position:relative;z-index:4;pointer-events:auto}
  .emerald-register .register-step[hidden]{display:none!important}
  .emerald-register .emerald-register-fields h2{margin:0 0 8px;font-size:12px}
  .emerald-register .form-grid{gap:10px 12px}
  .emerald-register .field label{margin:0 0 4px;font-size:11px;line-height:1.2}
  .emerald-register .field input,.emerald-register .field select,
  .emerald-register .register-password-control{height:40px;padding:0 11px;border-radius:11px;font-size:13px}
  .emerald-register .register-phone-row{gap:12px}
  .emerald-register .register-password-control{padding-right:4px}
  .emerald-register .register-password-control .eye{width:34px;height:34px;flex-basis:34px}
  .emerald-register .register-password-control .eye svg{width:18px;height:18px}
  .emerald-register .password-help{display:block;margin:3px 0 0;color:#a9d9c1;font-size:10px;line-height:1.1}
  .emerald-register .register-step-actions{flex:0 0 auto;margin-top:auto;padding-top:10px;gap:10px}
  .emerald-register .register-step-actions .back{min-height:40px;margin:0;padding:7px 12px}
  .emerald-register .register-step-next,.emerald-register .market-login-submit{
    height:42px;min-height:42px;border-radius:12px;font-size:14px
  }
  .emerald-register .market-login-register{flex:0 0 auto;margin:8px 0 0;font-size:12px}
  .emerald-register .field-error{display:block;margin:3px 0 0;color:#ffb4a8;font-size:10px;line-height:1.15}
  .emerald-register .field [aria-invalid="true"],
  .emerald-register .register-password-control:has([aria-invalid="true"]){border-color:#ff8d80}
}
@media(max-width:767px){
  body.market-login-view{height:auto;min-height:100dvh;overflow:auto}
  .emerald-register{height:auto;min-height:100dvh;overflow-x:clip;overflow-y:visible}
  .emerald-register .emerald-layout{
    height:auto;min-height:100dvh;overflow:visible;flex-direction:column;
    align-items:stretch;padding:18px 14px 28px!important;gap:14px
  }
  .emerald-register .emerald-intro{flex:auto!important;width:100%;gap:8px!important}
  .emerald-register .emerald-intro .stage{display:none!important}
  .emerald-register .emerald-intro>h1{font-size:26px!important;text-align:left}
  .emerald-register .emerald-panel{height:auto;max-height:none;min-height:0;width:100%;max-width:none}
  .emerald-register .emerald-toolbar{margin-bottom:8px}
  .emerald-register .emerald-panel>.gb{display:block;overflow:hidden}
  .emerald-register .account-data-card{display:flex;flex-direction:column;height:min(620px,calc(100dvh - 150px));min-height:440px;max-height:calc(100dvh - 150px);overflow-y:auto;padding:24px 18px!important}
  .emerald-register .account-data-card>form{display:flex;flex:1;min-height:0;flex-direction:column}
  .emerald-register .emerald-register-fields{display:flex;flex:1;min-height:0}
  .emerald-register .register-step{height:100%;min-height:0;flex:1;display:flex;flex-direction:column}
  .emerald-register .register-step .form-grid{flex:1;min-height:0;align-content:start}
  .emerald-register .register-step-actions{position:relative;z-index:1;flex:0 0 auto}
  .emerald-register .register-password-control{position:relative;z-index:3}
  .emerald-register .register-password-control .eye{position:relative;z-index:4;pointer-events:auto}
  .emerald-register .market-login-heading{margin-bottom:16px!important}
  .emerald-register .market-login-symbol{width:42px;height:42px}
  .emerald-register .market-login-heading h1{font-size:24px}
  .emerald-register .register-progress{margin-bottom:14px}
  .emerald-register .form-grid{grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}
  .emerald-register .field label{margin-bottom:5px;font-size:12px}
  .emerald-register .field input,.emerald-register .field select,
  .emerald-register .register-password-control{height:42px;font-size:14px}
  .emerald-register .register-step-actions{margin-top:18px}
  .emerald-register .register-step-actions .back{margin:0}
  .emerald-register .market-login-register{margin-top:14px}
}
@media(max-width:480px){
  .emerald-register .form-grid{grid-template-columns:minmax(0,1fr)}
  .emerald-register .register-phone-row{grid-template-columns:minmax(100px,.38fr) minmax(0,1fr);gap:10px}
}
body.light-mode .emerald-register .password-help{color:#537e68}


.emerald-register .gb.in3{padding:1px;border:1px solid #d8e9df;border-radius:25px;background:#fff;box-shadow:0 18px 48px rgb(22 111 73 / 14%)}
.emerald-register .gb.in3::before{display:none}
.emerald-register .account-data-card{border:0!important;border-radius:24px!important;background:#fff!important;color:#173f30!important;box-shadow:inset 0 0 0 1px rgb(15 91 58 / 5%)!important;padding:28px 30px!important}
.emerald-register .market-login-heading{justify-content:flex-start!important;gap:12px!important;margin:0 0 16px!important;text-align:left!important}
.emerald-register .market-login-symbol{width:42px!important;height:42px!important;flex:0 0 42px;border-radius:13px!important;transform:none!important}
.emerald-register .market-login-heading h1{margin:0!important;color:#173f30!important;font:700 22px/1.2 Sora,Inter,sans-serif!important;letter-spacing:-.25px}
.emerald-register .market-login-heading p{display:none!important}
.emerald-register .emerald-register-fields h2{margin:0 0 16px!important;color:#087b53!important;font-size:17px!important;letter-spacing:0!important}
.emerald-register .register-progress{display:flex;flex-direction:column;align-items:stretch;gap:8px;margin:0 0 18px!important;color:#315e49!important;font-size:13px!important}
.emerald-register .register-progress-count{font-size:12px;font-weight:700;color:#527363}
.emerald-register .register-progress-segments{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;list-style:none;margin:0;padding:0}
.emerald-register .register-progress-segments li{display:flex;min-width:0;flex-direction:column;gap:7px;color:#87998f;font-size:13px;font-weight:600}
.emerald-register .register-progress-track{display:block;width:100%;height:6px;border-radius:99px;background:#e5ece8;transition:background .18s ease}
.emerald-register .register-progress-name{white-space:nowrap}
.emerald-register .register-progress-segments li[data-state="current"]{color:#087b53}
.emerald-register .register-progress-segments li[data-state="current"] .register-progress-track,.emerald-register .register-progress-segments li[data-state="complete"] .register-progress-track{background:#087b53}
.emerald-register .register-progress-segments li[data-state="complete"] .register-progress-name::before{content:"✓ ";font-weight:800;color:#087b53}
.emerald-register .register-step{animation:register-step-in .18s ease both}
@keyframes register-step-in{from{opacity:0;transform:translateY(5px)}to{opacity:1;transform:translateY(0)}}
.emerald-register .field label{margin:0 0 6px!important;color:#315b47!important;font-size:13px!important;font-weight:600!important;line-height:1.25}
.emerald-register .field input,.emerald-register .field select,.emerald-register .register-password-control{height:48px!important;padding:0 13px!important;border:1.5px solid #a9c8b6!important;border-radius:12px!important;background:#fff!important;color:#173f30!important;font:500 15px Inter,system-ui,sans-serif!important;box-shadow:inset 0 1px 2px rgb(18 74 48 / 4%)!important;color-scheme:light}
.emerald-register .field input::placeholder{color:#789385!important}
.emerald-register .field input:focus,.emerald-register .field select:focus,.emerald-register .register-password-control:focus-within{border-color:#087b53!important;outline:0!important;box-shadow:0 0 0 3px rgb(8 123 83 / 17%)!important;background:#fff!important}
.emerald-register .field:focus-within{outline:0!important;box-shadow:none!important;background:transparent!important}
.emerald-register .field [aria-invalid="true"],.emerald-register .register-password-control:has([aria-invalid="true"]){border-color:#d97770!important;background:#fffafa!important;box-shadow:0 0 0 2px rgb(217 119 112 / 10%)!important}
.emerald-register .field-error{display:block;margin:5px 0 0;color:#b94b46;font-size:12px;line-height:1.3}
.emerald-register .register-step-help{margin:16px 0 0;color:#617c6d;font-size:12px;line-height:1.35}
.emerald-register .password-help{display:block;margin:7px 0 0;color:#617c6d;font-size:12px;line-height:1.3}
.emerald-register .register-password-control{position:relative!important;display:block!important;padding:0 48px 0 13px!important}
.emerald-register .register-password-control input{display:block;width:100%;height:100%!important;padding:0!important;border:0!important;border-radius:0!important;background:transparent!important;box-shadow:none!important;outline:0!important;color:#173f30!important}
.emerald-register .register-password-control .eye{position:absolute!important;z-index:5;right:5px;top:50%;transform:translateY(-50%);display:flex;align-items:center;justify-content:center;width:38px!important;height:38px!important;border:0;border-radius:9px;background:transparent;color:#287653;cursor:pointer}
.emerald-register .register-password-control .eye:hover{background:#edf7f1}
.emerald-register .register-password-control .eye:focus-visible{outline:3px solid #087b53!important;outline-offset:1px}
.emerald-register .password-strength{display:flex;align-items:center;gap:9px;margin:7px 0 0;color:#617c6d;font-size:12px;line-height:1.2}
.emerald-register .password-strength-bars{display:flex;gap:3px;width:72px;flex:0 0 72px}
.emerald-register .password-strength-bars i{height:4px;flex:1;border-radius:5px;background:#dfe9e3}
.emerald-register .password-strength[data-level="1"] .password-strength-bars i:nth-child(-n+1){background:#d97770}
.emerald-register .password-strength[data-level="2"] .password-strength-bars i:nth-child(-n+2){background:#d6a64b}
.emerald-register .password-strength[data-level="3"] .password-strength-bars i:nth-child(-n+3){background:#83a94d}
.emerald-register .password-strength[data-level="4"] .password-strength-bars i{background:#087b53}
.emerald-register .register-step-actions{gap:12px!important;margin-top:auto!important;padding-top:18px!important}
.emerald-register .register-step-next,.emerald-register .market-login-submit{display:inline-flex;align-items:center;justify-content:center;width:100%;height:48px!important;min-height:48px!important;border:1px solid #087b53!important;border-radius:12px!important;background:#087b53!important;color:#fff!important;font:700 15px Inter,system-ui,sans-serif!important;box-shadow:0 8px 18px rgb(8 123 83 / 16%)!important;transition:background .18s,border-color .18s,box-shadow .18s,transform .18s}
.emerald-register .register-step-next:hover,.emerald-register .market-login-submit:hover{background:#066744!important;border-color:#066744!important;transform:translateY(-1px)}
.emerald-register .register-step-next:focus-visible,.emerald-register .market-login-submit:focus-visible{outline:3px solid #69bd91!important;outline-offset:3px}
.emerald-register .register-step-next:disabled,.emerald-register .market-login-submit:disabled{background:#a6b9ad!important;border-color:#a6b9ad!important;box-shadow:none!important;cursor:not-allowed;transform:none}
.emerald-register .register-step-actions .back{display:inline-flex;align-items:center;justify-content:center;min-height:48px!important;padding:0 18px!important;margin:0!important;border:1.5px solid #8eb6a0!important;border-radius:12px!important;background:#fff!important;color:#176044!important;font-size:14px;font-weight:700}
.emerald-register .register-step-actions .back:hover{background:#f0f8f3!important;border-color:#087b53!important}
.emerald-register .register-step-actions .back:focus-visible{outline:3px solid #69bd91;outline-offset:3px}
.emerald-register .market-login-register{margin:14px 0 0!important;color:#526f60!important;font-size:13px!important;gap:6px}
.emerald-register .market-login-register a{color:#087b53!important;text-decoration:underline;text-underline-offset:3px}
.emerald-register .market-login-register a:hover{color:#055c3d!important}
.emerald-register .register-step .form-grid{align-content:center!important;row-gap:14px!important}
@media (min-width:768px){.emerald-register .account-data-card{padding:28px 30px!important}.emerald-register .register-step .form-grid{flex:1;align-content:center!important;row-gap:18px!important}.emerald-register .register-step-actions{margin-top:auto!important;padding-top:18px!important}.emerald-register .register-progress{margin-bottom:18px!important}.emerald-register .register-step-help{margin-top:12px}}
@media (max-height:700px) and (min-width:768px){.emerald-register .account-data-card{padding:24px 28px!important}.emerald-register .market-login-heading{margin-bottom:12px!important}.emerald-register .register-progress{margin-bottom:12px!important}.emerald-register .register-step .form-grid{row-gap:12px!important}.emerald-register .register-step-actions{padding-top:12px!important}}
@media (max-width:767px){.emerald-register .account-data-card{padding:26px 22px!important}.emerald-register .register-progress{margin-bottom:16px!important}.emerald-register .register-step .form-grid{align-content:start!important;row-gap:13px!important}.emerald-register .register-step-actions{margin-top:18px!important;padding-top:8px!important}.emerald-register .register-step-help{margin-top:12px}}
@media (max-width:480px){.emerald-register .register-progress-name{font-size:12px}.emerald-register .field input,.emerald-register .field select,.emerald-register .register-password-control{height:46px!important}}
@media (min-width:768px){.emerald-register .emerald-intro .stage{position:relative}.emerald-register .emerald-intro .float{max-width:190px;z-index:4}.emerald-register .emerald-intro .fa{left:0;right:auto;top:12px;bottom:auto}.emerald-register .emerald-intro .fb{left:auto;right:0;top:calc(50% - 30px);bottom:auto}.emerald-register .emerald-intro .fc{left:0;right:auto;top:auto;bottom:12px}}
@media (prefers-reduced-motion:reduce){.emerald-register .register-step,.emerald-register .register-progress-track{animation:none!important;transition:none!important}}


/* Corrección de geometría del registro: encabezado, cuerpo y acciones son filas independientes. */
@media (min-width:768px){
.emerald-register .emerald-panel{height:min(calc(100dvh - 24px),700px);max-height:calc(100dvh - 24px)}
.emerald-register .account-data-card{padding:20px 22px!important}
.emerald-register .account-data-card>form{display:flex;flex:1 1 auto;min-height:0;flex-direction:column}
.emerald-register .market-login-heading{flex:0 0 34px;min-height:34px;margin:0 0 6px!important;gap:9px!important}
.emerald-register .market-login-symbol{width:32px!important;height:32px!important;flex-basis:32px}
.emerald-register .market-login-heading h1{font-size:19px!important;line-height:1.1!important}
.emerald-register .register-progress{flex:0 0 auto;display:grid;grid-template-columns:auto minmax(0,1fr);align-items:center;gap:12px;margin:0 0 10px!important;font-size:11px!important;line-height:1.1}
.emerald-register .register-progress-segments{grid-template-columns:repeat(3,minmax(0,1fr));gap:8px}
.emerald-register .register-progress-segments li{display:flex;flex-direction:row;align-items:center;gap:5px;font-size:11px;white-space:nowrap}
.emerald-register .register-progress-track{height:4px;flex:1;min-width:8px}
.emerald-register .emerald-register-fields{display:flex;flex:1 1 auto;min-height:0;overflow-y:auto;overflow-x:hidden}
.emerald-register .register-step{display:flex;flex:1 1 auto;flex-direction:column;min-height:0;height:auto;overflow:visible}
.emerald-register .register-step>h2{flex:0 0 auto;margin:0 0 8px!important;font-size:13px!important;line-height:1.15}
.emerald-register .register-step .form-grid{flex:1 1 auto;min-height:0;align-content:start;align-items:start;gap:10px 12px!important}
.emerald-register .register-step .field label{margin:0 0 4px!important;font-size:12px!important;line-height:1.15}
.emerald-register .register-step .field input,.emerald-register .register-step .field select,.emerald-register .register-step .register-password-control{height:41px!important;min-height:41px!important;padding:0 10px!important;font-size:13px!important;border-radius:10px!important}
.emerald-register .register-step .register-phone-row{grid-column:auto!important;grid-template-columns:minmax(92px,.42fr) minmax(0,1fr);gap:8px;align-self:start}
.emerald-register .register-step-actions{position:static!important;z-index:auto!important;flex:0 0 44px;min-height:44px;margin:8px 0 0!important;padding:0!important;gap:10px!important}
.emerald-register .register-step-actions .back{flex:0 0 104px;width:104px;min-width:104px;height:42px;min-height:42px!important;padding:0 10px!important;white-space:nowrap;word-break:keep-all;overflow-wrap:normal;line-height:1!important;font-size:13px!important}
.emerald-register .register-step-actions .register-step-next,.emerald-register .register-step-actions .market-login-submit{flex:1 1 auto;height:42px!important;min-height:42px!important}
.emerald-register .market-login-register{flex:0 0 auto;margin:6px 0 0!important;font-size:11px}
.emerald-register .register-step-help{margin:8px 0 0;font-size:11px}
}
@media (min-width:768px) and (max-height:620px){
.emerald-register .account-data-card{padding:14px 20px!important}
.emerald-register .market-login-heading{flex-basis:30px;min-height:30px;margin-bottom:4px!important}
.emerald-register .market-login-symbol{width:28px!important;height:28px!important;flex-basis:28px}
.emerald-register .market-login-heading h1{font-size:18px!important}
.emerald-register .register-progress{margin-bottom:6px!important}
.emerald-register .register-step>h2{margin-bottom:5px!important}
.emerald-register .register-step .form-grid{gap:8px 10px!important}
.emerald-register .register-step .field label{font-size:11px!important}
.emerald-register .register-step .field input,.emerald-register .register-step .field select,.emerald-register .register-step .register-password-control{height:38px!important;min-height:38px!important}
.emerald-register .register-step-actions{margin-top:5px!important}
.emerald-register .market-login-register{margin-top:3px!important}
}
@media (max-width:767px){
.emerald-register .register-progress{display:grid;grid-template-columns:auto minmax(0,1fr);align-items:center;gap:8px}
.emerald-register .register-progress-segments{gap:6px}
.emerald-register .register-progress-segments li{font-size:11px;gap:4px}
.emerald-register .register-progress-track{height:4px}
.emerald-register .register-step-actions .back{min-width:104px;white-space:nowrap;word-break:keep-all}
}
</style>
<section class="screen visible emerald-login emerald-register" data-screen="register" aria-labelledby="marketLoginTitle">
<div aria-hidden="true"><div class="aur a1"></div><div class="aur a2"></div><div class="aur a3"></div><div class="gridbg"></div></div>
<div class="emerald-layout market-login-layout">

<section  class="emerald-intro" style="flex:1 1  540px;min-width:0;display:flex;flex-direction:column;gap:8px;align-self:stretch;justify-content:space-between">
<div class="in1" style="display:flex;align-items:center;gap:16px;padding-top:8px">
<img class="emerald-university-emblem" src="{{ asset('logo-undc-128.png') }}" srcset="{{ asset('logo-undc-128.png') }} 1x, {{ asset('logo-undc-256.png') }} 2x" alt="Emblema de la Universidad Nacional de Cañete" width="64" height="64">
<span><span class="sora" style="display:block;font-weight:800;font-size:30px;letter-spacing:.5px">UNDC <span style="color:#6ee7b7">MARKET</span></span><span style="display:block;margin-top:2px;color:#cfeee0;font-size:15px">Tienda virtual de pagos de la UNDC</span></span>
</div>

<div class="stage" style="position:relative;height:500px">
<div class="rig" style="position:absolute;inset:0;transform:rotateX(16deg) rotateY(-6deg)">
<div class="plate"></div>
<div class="pl2"></div><div class="pl2 pl3"></div>
<div class="orb" aria-hidden="true"><div class="orbit"><div class="spin"><span class="sat"></span><span class="sat sat2"></span></div></div></div>
<div class="botw" aria-hidden="true">
<svg width="0" height="0" style="position:absolute" focusable="false"><defs>
<radialGradient id="gShellR" cx=".3" cy=".22" r=".95"><stop offset="0" stop-color="#ffffff"></stop><stop offset=".5" stop-color="#e3f8ee"></stop><stop offset="1" stop-color="#9ccfb8"></stop></radialGradient>
<radialGradient id="gGreenR" cx=".32" cy=".28" r=".85"><stop offset="0" stop-color="#b8f5d8"></stop><stop offset=".55" stop-color="#10b981"></stop><stop offset="1" stop-color="#065f46"></stop></radialGradient>
<linearGradient id="gGreen" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#34d399"></stop><stop offset="1" stop-color="#0b7a4f"></stop></linearGradient>
<linearGradient id="gDark" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#12865a"></stop><stop offset="1" stop-color="#04281c"></stop></linearGradient>
<linearGradient id="gVisor" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#0d5238"></stop><stop offset="1" stop-color="#021a12"></stop></linearGradient>
<radialGradient id="gLime" cx=".35" cy=".3" r=".85"><stop offset="0" stop-color="#f6ffd6"></stop><stop offset=".5" stop-color="#b6f36b"></stop><stop offset="1" stop-color="#58bf30"></stop></radialGradient>
<filter id="fGlow" x="-60%" y="-60%" width="220%" height="220%"><feGaussianBlur stdDeviation="7"></feGaussianBlur></filter>
</defs></svg>
<div class="bot">

<div class="ly lflame"><svg viewBox="0 0 400 480"><ellipse cx="200" cy="462" rx="92" ry="22" fill="url(#gLime)" filter="url(#fGlow)" opacity=".9"></ellipse><ellipse cx="200" cy="460" rx="48" ry="9" fill="#f6ffd6" opacity=".9"></ellipse></svg></div>

<div class="ly lbody"><svg viewBox="0 0 400 480">
<path d="M138 262 C138 240 262 240 262 262 C284 300 294 350 282 392 C272 430 238 446 200 446 C162 446 128 430 118 392 C106 350 116 300 138 262 Z" fill="url(#gShellR)" stroke="#ffffff" stroke-opacity=".85" stroke-width="2"></path>
<ellipse cx="200" cy="430" rx="64" ry="14" fill="#7fbfa3" opacity=".45"></ellipse>
<path d="M126 402 C152 424 248 424 274 402" fill="none" stroke="url(#gGreen)" stroke-width="8" stroke-linecap="round"></path>
<rect x="154" y="296" width="92" height="92" rx="32" fill="url(#gDark)" stroke="#17a56d" stroke-width="2.5"></rect>
<circle cx="200" cy="342" r="30" fill="url(#gLime)" filter="url(#fGlow)" opacity=".75"></circle>
<circle cx="200" cy="342" r="28" fill="url(#gLime)"></circle>
<ellipse cx="190" cy="330" rx="12" ry="7" fill="#ffffff" opacity=".55"></ellipse>
<text x="200" y="353" text-anchor="middle" font-family="Sora,sans-serif" font-weight="800" font-size="32" fill="#04281c">U</text>
<rect class="brs" x="178" y="374" width="12" height="5" rx="2.5" fill="#b6f36b"></rect><rect class="brs" style="animation-delay:.25s" x="194" y="374" width="12" height="5" rx="2.5" fill="#b6f36b"></rect><rect class="brs" style="animation-delay:.5s" x="210" y="374" width="12" height="5" rx="2.5" fill="#b6f36b"></rect>
<path d="M136 284 C128 322 130 362 142 392" fill="none" stroke="#ffffff" stroke-opacity=".75" stroke-width="7" stroke-linecap="round"></path>
</svg></div>

<div class="ly larm"><svg viewBox="0 0 400 480"><g transform="rotate(14 118 300)">
<rect x="101" y="294" width="34" height="86" rx="17" fill="url(#gShellR)" stroke="#ffffff" stroke-opacity=".85" stroke-width="2"></rect>
<circle cx="118" cy="300" r="21" fill="url(#gGreen)" stroke="#b6f36b" stroke-width="3"></circle>
<circle cx="118" cy="394" r="24" fill="url(#gGreenR)" stroke="#b6f36b" stroke-width="3"></circle>
<ellipse cx="110" cy="384" rx="8" ry="5" fill="#ffffff" opacity=".6"></ellipse>
</g></svg></div>

<div class="ly lhead"><svg viewBox="0 0 400 480">
<circle cx="74" cy="170" r="34" fill="url(#gDark)"></circle><circle cx="74" cy="170" r="24" fill="url(#gGreenR)" stroke="#b6f36b" stroke-width="4"></circle><circle cx="68" cy="163" r="6" fill="#ffffff" opacity=".7"></circle>
<circle cx="326" cy="170" r="34" fill="url(#gDark)"></circle><circle cx="326" cy="170" r="24" fill="url(#gGreenR)" stroke="#b6f36b" stroke-width="4"></circle><circle cx="320" cy="163" r="6" fill="#ffffff" opacity=".7"></circle>
<ellipse cx="200" cy="262" rx="60" ry="13" fill="url(#gDark)"></ellipse>
<rect x="80" y="66" width="240" height="196" rx="86" fill="url(#gShellR)" stroke="#ffffff" stroke-opacity=".9" stroke-width="2.5"></rect>
<rect x="87" y="73" width="226" height="182" rx="80" fill="none" stroke="#04281c" stroke-opacity=".07" stroke-width="3"></rect>
<ellipse cx="142" cy="96" rx="48" ry="14" fill="#ffffff" opacity=".7" transform="rotate(-18 142 96)"></ellipse>
<rect x="106" y="102" width="188" height="128" rx="54" fill="url(#gVisor)" stroke="#17a56d" stroke-width="3.5"></rect>
<rect x="112" y="108" width="176" height="116" rx="48" fill="none" stroke="#b6f36b" stroke-opacity=".28" stroke-width="2"></rect>
<path d="M128 130 C152 112 226 106 272 116 C244 126 176 134 128 156 Z" fill="#ffffff" opacity=".13"></path>
</svg></div>

<div class="ly" style="transform:translateZ(36px) translate(0px,0px)"><svg viewBox="0 0 400 480">
<g class="eyeg"><ellipse cx="162" cy="156" rx="18" ry="23" fill="#b6f36b" filter="url(#fGlow)"></ellipse><ellipse cx="162" cy="156" rx="17" ry="22" fill="url(#gLime)"></ellipse><circle cx="156" cy="147" r="5.5" fill="#ffffff" opacity=".9"></circle></g>
<g class="eyeg"><ellipse cx="238" cy="156" rx="18" ry="23" fill="#b6f36b" filter="url(#fGlow)"></ellipse><ellipse cx="238" cy="156" rx="17" ry="22" fill="url(#gLime)"></ellipse><circle cx="232" cy="147" r="5.5" fill="#ffffff" opacity=".9"></circle></g>
<path d="M182 194 Q200 212 218 194" fill="none" stroke="#b6f36b" stroke-width="7" stroke-linecap="round" filter="url(#fGlow)" opacity=".8"></path>
<path d="M182 194 Q200 212 218 194" fill="none" stroke="#d7ff9a" stroke-width="4.5" stroke-linecap="round"></path>
<ellipse cx="132" cy="190" rx="11" ry="6" fill="#b6f36b" opacity=".28"></ellipse><ellipse cx="268" cy="190" rx="11" ry="6" fill="#b6f36b" opacity=".28"></ellipse>
</svg></div>

<div class="ly lcap"><svg viewBox="0 0 400 480">
<path d="M142 66 L142 94 C152 112 248 112 258 94 L258 66 Z" fill="#06301f"></path>
<polygon points="82,48 200,82 200,94 82,60" fill="#06301f"></polygon>
<polygon points="200,82 318,48 318,60 200,94" fill="#0a4d33"></polygon>
<polygon points="200,12 318,48 200,82 82,48" fill="url(#gDark)" stroke="#b6f36b" stroke-width="2.5" stroke-linejoin="round"></polygon>
<polygon points="200,20 290,48 200,74 110,48" fill="none" stroke="#b6f36b" stroke-opacity=".25" stroke-width="1.5"></polygon>
<ellipse cx="200" cy="48" rx="10" ry="6" fill="url(#gLime)"></ellipse>
<path d="M200 48 Q268 42 312 52" fill="none" stroke="#b6f36b" stroke-width="3" stroke-linecap="round"></path>
<g class="tass"><line x1="312" y1="52" x2="312" y2="98" stroke="#b6f36b" stroke-width="3" stroke-linecap="round"></line><rect x="303" y="96" width="18" height="38" rx="6" fill="url(#gLime)"></rect></g>
</svg></div>

<div class="ly larmr"><svg viewBox="0 0 400 480">
<rect x="265" y="294" width="34" height="86" rx="17" fill="url(#gShellR)" stroke="#ffffff" stroke-opacity=".85" stroke-width="2"></rect>
<circle cx="282" cy="300" r="21" fill="url(#gGreen)" stroke="#b6f36b" stroke-width="3"></circle>
<circle cx="282" cy="394" r="24" fill="url(#gGreenR)" stroke="#b6f36b" stroke-width="3"></circle>
<ellipse cx="274" cy="384" rx="8" ry="5" fill="#ffffff" opacity=".6"></ellipse>
</svg></div>

</div></div>
<div class="float fa"><div style="display:flex;align-items:center;gap:12px"><span style="width:38px;height:38px;border-radius:12px;background:#b6f36b;color:#04281c;display:flex;align-items:center;justify-content:center"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="5" width="20" height="14" rx="3"></rect><path d="M2 10h20"></path></svg></span><span style="font-weight:700;font-size:15px">Organiza tus trámites</span></div></div>
<div class="float fb"><div style="display:flex;align-items:center;gap:12px"><span style="width:38px;height:38px;border-radius:12px;background:#34d399;color:#04281c;display:flex;align-items:center;justify-content:center"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3l8 3v6c0 5-3.5 8-8 9-4.5-1-8-4-8-9V6z"></path><path d="M8.5 12l2.5 2.5 4.5-5"></path></svg></span><span style="font-weight:700;font-size:15px">Acceso a tu perfil</span></div></div>
<div class="float fc"><div style="display:flex;align-items:center;gap:12px"><span style="width:38px;height:38px;border-radius:12px;background:#d7ff9a;color:#04281c;display:flex;align-items:center;justify-content:center"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"></circle><path d="M12 7v5l3 2"></path></svg></span><span style="font-weight:700;font-size:15px">Consulta en línea</span></div></div>
</div>
</div>

<h1 class="sora in2" style="margin:0;font-size:52px;line-height:1.06;font-weight:800;letter-spacing:-1.5px">Tus trámites,<br><span style="background:linear-gradient(90deg,#6ee7b7,#b6f36b);-webkit-background-clip:text;background-clip:text;color:transparent">a un solo clic.</span></h1>
</section>


<section class="emerald-panel emerald-register-panel">
  <div class="emerald-toolbar"><a href="{{ route('tienda.login') }}">← Regresar</a></div>
  <div class="gb in3">
    <div class="market-login-card account-data-card">
      <div class="market-login-heading">
        <span class="market-login-symbol"><svg aria-hidden="true" viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg></span>
        <div><h1 id="marketLoginTitle" tabindex="-1">Crea tu cuenta</h1></div>
      </div>
      @include('modules.acceso.partials.errors')
      <form method="post" action="{{ route('tienda.registro.store') }}" data-register-form data-error-fields='@json($errors->keys())' data-error-messages='@json($errors->messages())'>
        @csrf
        <div class="register-progress" data-register-progress role="group" aria-label="Progreso del registro" aria-live="polite">
          <span class="register-progress-count" data-register-progress-label aria-label="Paso 1 de 3">Paso 1 de 3</span>
          <ol class="register-progress-segments" aria-label="Etapas del registro">
            <li data-register-progress-segment="1" data-state="current" aria-current="step"><span class="register-progress-track"></span><span class="register-progress-name">Identidad</span></li>
            <li data-register-progress-segment="2" data-state="pending"><span class="register-progress-track"></span><span class="register-progress-name">Contacto</span></li>
            <li data-register-progress-segment="3" data-state="pending"><span class="register-progress-track"></span><span class="register-progress-name">Acceso</span></li>
          </ol>
        </div>
        <div class="emerald-register-fields">
          <fieldset class="register-step" data-register-step="1">
            <h2>Identidad</h2>
            <div class="form-grid">
              <div class="register-profile-fields" data-register-profile-fields>@include('modules.acceso.partials.profile-fields')</div>
              <div class="field"><label for="account_email">Correo electrónico</label><input id="account_email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" maxlength="254" required></div>
            </div>
            <p class="register-step-help">Usa los datos tal como figuran en tu documento.</p>
            <div class="register-step-actions"><button type="button" class="btn register-step-next" data-register-next>Continuar →</button></div>
          </fieldset>
          <fieldset class="register-step" data-register-step="2" hidden>
            <h2>Contacto y ubicación</h2>
            <div class="form-grid" data-register-contact-grid></div>
            <div class="register-step-actions">
              <button type="button" class="back" data-register-back>← Atrás</button>
              <button type="button" class="btn register-step-next" data-register-next>Continuar →</button>
            </div>
          </fieldset>
          <fieldset class="register-step" data-register-step="3" hidden>
            <h2>Acceso</h2>
            <div class="form-grid">
              <div class="field">
                <label for="account_password">Contraseña</label>
                <div class="register-password-control">
                  <input id="account_password" name="password" type="password" minlength="12" maxlength="128" autocomplete="new-password" required>
                  <button class="eye" type="button" data-toggle-password="account_password" aria-controls="account_password" aria-label="Mostrar contraseña" aria-pressed="false"><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg></button>
                </div>
                <small class="password-help">Mínimo 12 caracteres</small>
                <div class="password-strength" data-password-strength aria-live="polite">
                  <span class="password-strength-bars" aria-hidden="true"><i></i><i></i><i></i><i></i></span>
                  <span data-password-strength-label>Fortaleza de contraseña</span>
                </div>
              </div>
              <div class="field">
                <label for="account_password_confirmation">Confirmar contraseña</label>
                <div class="register-password-control">
                  <input id="account_password_confirmation" name="password_confirmation" type="password" minlength="12" maxlength="128" autocomplete="new-password" required>
                  <button class="eye" type="button" data-toggle-password="account_password_confirmation" aria-controls="account_password_confirmation" aria-label="Mostrar contraseña" aria-pressed="false"><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg></button>
                </div>
              </div>
            </div>
            <div class="register-step-actions">
              <button type="button" class="back" data-register-back>← Atrás</button>
              <button type="submit" class="btn market-login-submit">Crear cuenta</button>
            </div>
          </fieldset>
        </div>
      </form>
      <div class="market-login-register"><span>¿Ya tienes una cuenta?</span><a href="{{ route('tienda.login') }}">Inicia sesión</a></div>
    </div>
  </div>
</section></div></section><script>
document.querySelectorAll('[data-toggle-password]').forEach(function(button){
  button.addEventListener('click',function(){
    const input=document.getElementById(this.dataset.togglePassword);
    const show=input.type==='password';
    input.type=show?'text':'password';
    this.setAttribute('aria-pressed',String(show));
    this.setAttribute('aria-label',show?'Ocultar contraseña':'Mostrar contraseña');
  });
});
(function(){
  const form=document.querySelector('[data-register-form]');
  if(!form)return;
  form.noValidate=true;
  const card=form.closest('.account-data-card');
  const steps=[...form.querySelectorAll('[data-register-step]')];
  const progress=form.querySelector('[data-register-progress-label]');
  const segments=form.querySelectorAll('[data-register-progress-segment]');
  const contactGrid=form.querySelector('[data-register-contact-grid]');
  const addressNames=['line_1','line_2','city','state','country'];
  const byName=name=>form.querySelector('[name="'+name+'"]');
  const phoneCountry=byName('phone_country_code');
  const phone=byName('phone');
  if(phoneCountry&&!phoneCountry.value.trim())phoneCountry.value='+51';
  if(phoneCountry&&phone){
    const phoneRow=document.createElement('div');
    phoneRow.className='register-phone-row';
    phoneRow.append(phoneCountry.closest('.field'),phone.closest('.field'));
    contactGrid.append(phoneRow);
  }
  const email=byName('email');
  if(email)contactGrid.append(email.closest('.field'));
  addressNames.forEach(name=>{
    const field=byName(name)?.closest('.field');
    if(field)contactGrid.append(field);
  });
  const allFields=[...form.querySelectorAll('input,select,textarea')];
  const touchedFields=new WeakSet();
  const passwordField=byName('password');
  const strength=card.querySelector('[data-password-strength]');
  const strengthLabel=card.querySelector('[data-password-strength-label]');
  function updatePasswordStrength(){
    if(!passwordField||!strength||!strengthLabel)return;
    const value=passwordField.value;
    let score=0;
    if(value.length>=12)score++;
    if(/[a-z]/.test(value)&&/[A-Z]/.test(value))score++;
    if(/\d/.test(value))score++;
    if(/[^A-Za-z0-9]/.test(value))score++;
    strength.dataset.level=String(score);
    strengthLabel.textContent=value.length===0?'Fortaleza de contraseña':['Por mejorar','Básica','Intermedia','Buena','Fuerte'][score];
  }
  passwordField?.addEventListener('input',updatePasswordStrength);
  updatePasswordStrength();
  allFields.forEach(field=>{
    field.dataset.registerInitiallyRequired=field.required?'true':'false';
    field.addEventListener('input',()=>{
      if(field.id==='account_password')updateFieldError(byName('password_confirmation'));
      if(touchedFields.has(field))updateFieldError(field);
    });
    field.addEventListener('change',()=>{if(touchedFields.has(field))updateFieldError(field);});
    field.addEventListener('blur',()=>{
      if(field.value.trim()!=='')touchedFields.add(field);
      if(touchedFields.has(field))updateFieldError(field);
    });
  });
  function updateFieldError(field,force=false){
    if(!field||(!force&&!touchedFields.has(field)))return;
    if(field.id==='account_password_confirmation'){
      const password=byName('password')?.value||'';
      field.setCustomValidity(field.value&&field.value!==password?'Las contraseñas no coinciden.':'');
    }
    const wrapper=field.closest('.field');
    if(!wrapper)return;
    let error=wrapper.querySelector('.field-error');
    const message=field.validity.valid?'':(
      field.validity.valueMissing?'Este campo es obligatorio.':
      field.validity.typeMismatch?'Ingresa un correo electrónico válido.':
      field.validity.tooShort?'Debe tener al menos '+field.minLength+' caracteres.':
      field.validationMessage||'Revisa este campo.'
    );
    if(message){
      if(!error){error=document.createElement('span');error.className='field-error';error.id=field.id+'-error';error.setAttribute('aria-live','polite');wrapper.append(error);}
      error.textContent=message;
      field.setAttribute('aria-invalid','true');
      field.setAttribute('aria-describedby',error.id);
    }else{
      error?.remove();
      field.removeAttribute('aria-invalid');
      field.removeAttribute('aria-describedby');
    }
  }
  function validateStep(step){
    let firstInvalid=null;
    step.querySelectorAll('input,select,textarea').forEach(field=>{
      updateFieldError(field,true);
      if(!field.validity.valid&&!firstInvalid)firstInvalid=field;
    });
    if(firstInvalid)firstInvalid.focus({preventScroll:true});
    return !firstInvalid;
  }
  function showStep(number,moveFocus=true){
    steps.forEach((step,index)=>{
      const active=index===number-1;
      step.hidden=!active;
      step.setAttribute('aria-hidden',String(!active));
      step.querySelectorAll('input,select,textarea').forEach(field=>{
        field.required=active&&field.dataset.registerInitiallyRequired==='true';
      });
    });
    progress.textContent='Paso '+number+' de 3';
    progress.setAttribute('aria-label','Paso '+number+' de 3');
    segments.forEach((segment,index)=>{
      const state=index<number-1?'complete':index===number-1?'current':'pending';
      segment.dataset.state=state;
      if(state==='current')segment.setAttribute('aria-current','step');
      else segment.removeAttribute('aria-current');
      segment.setAttribute('aria-label',['Identidad','Contacto','Acceso'][index]+(state==='complete'?', completado':state==='current'?', paso actual':', pendiente'));
    });
    if(moveFocus){
      const first=steps[number-1].querySelector('input:not([type="hidden"]),select,textarea');
      first?.focus({preventScroll:true});
    }
  }
  form.querySelectorAll('[data-register-next]').forEach((button,index)=>{
    button.addEventListener('click',()=>{
      if(validateStep(steps[index]))showStep(index+2);
    });
  });
  form.querySelectorAll('[data-register-back]').forEach(button=>{
    button.addEventListener('click',()=>{
      const current=steps.findIndex(step=>!step.hidden);
      showStep(Math.max(1,current));
    });
  });
  form.addEventListener('submit',event=>{
    for(let index=0;index<steps.length;index++){
      const step=steps[index];
      step.querySelectorAll('input,select,textarea').forEach(field=>{
        field.required=field.dataset.registerInitiallyRequired==='true';
      });
      if(!validateStep(step)){
        event.preventDefault();
        showStep(index+1);
        validateStep(step);
        return;
      }
    }
  });
  const errorKeys=JSON.parse(form.dataset.errorFields||'[]');
  const errorMessages=JSON.parse(form.dataset.errorMessages||'{}');
  const groups=[
    ['first_names','last_names','document_type','document_number'],
    ['phone_country_code','phone','email','contact_email','line_1','line_2','city','state','country'],
    ['password','password_confirmation']
  ];
  const firstError=errorKeys[0];
  const firstErrorStep=groups.findIndex(group=>group.includes(firstError));
  const initialStep=firstErrorStep<0?1:firstErrorStep+1;
  errorKeys.forEach(key=>{
    const field=byName(key);
    const message=errorMessages[key]?.[0];
    if(field&&message){
      const wrapper=field.closest('.field');
      if(wrapper){
        let error=wrapper.querySelector('.field-error');
        if(!error){error=document.createElement('span');error.className='field-error';error.id=field.id+'-error';wrapper.append(error);}
        error.textContent=message;
        field.setAttribute('aria-invalid','true');
        field.setAttribute('aria-describedby',error.id);
      }
    }
  });
  showStep(initialStep,false);
})();
</script>
@endsection
