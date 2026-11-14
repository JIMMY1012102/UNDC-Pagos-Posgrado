@extends('layouts.market')
@section('title', 'Inicia sesión · UNDC MARKET')
@section('marketPage', 'login')
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
.btn:focus-visible,.field:focus-within,.eye:focus-visible,.lnk:focus-visible,.sw:focus-visible{outline:3px solid #b6f36b;outline-offset:3px}
.field{display:flex;align-items:center;gap:12px;height:62px;padding:0 16px;border-radius:18px;background:rgba(255,255,255,.06);border:1.5px solid rgba(255,255,255,.16);transition:border-color .25s,background .25s,box-shadow .25s}
.field:hover{border-color:rgba(255,255,255,.32)}
.field:focus-within{border-color:#b6f36b;background:rgba(182,243,107,.08);box-shadow:0 0 0 5px rgba(182,243,107,.14),0 12px 30px -12px rgba(182,243,107,.5)}
.inp{flex:1;min-width:0;height:100%;border:0;background:transparent;color:#fff;font-size:16px;outline:0}
.inp::placeholder{color:#85bda3}
.eye{border:0;background:transparent;color:#cfeee0;width:40px;height:40px;border-radius:12px;display:flex;align-items:center;justify-content:center;cursor:pointer;transition:background .2s}
.eye:hover{background:rgba(255,255,255,.12)}
.lnk{color:#b6f36b;font-weight:600;transition:color .2s}
.lnk:hover{color:#e6ffb3}
.sw{width:46px;height:26px;border-radius:13px;border:0;padding:3px;cursor:pointer;transition:background .25s;display:flex;align-items:center}
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
.emerald-layout{position:relative;max-width:1360px;margin:auto;padding:28px;display:flex;align-items:center;gap:56px;min-height:100svh}
.emerald-intro{flex:1 1 540px!important}.emerald-panel{flex:1 1 440px;min-width:0;max-width:520px;width:100%;margin:auto}
.emerald-toolbar{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:20px}
.emerald-toolbar a,.emerald-toolbar button{padding:10px 16px;border:1px solid #639c7e;border-radius:14px;background:transparent;color:#d7ff9a;font-size:14px;cursor:pointer}
.emerald-login .market-login-card{position:relative;top:auto;max-width:none;width:100%;padding:40px 38px;margin:0;border:0;border-radius:36px;background:linear-gradient(160deg,#0a3a28,#052a1c);box-shadow:none;color:#fff}
.emerald-login .market-login-heading{display:flex;flex-direction:column;gap:0;padding:0;margin:0 0 30px;text-align:center}
.emerald-login .market-login-symbol{width:68px;height:68px;border-radius:22px;background:linear-gradient(135deg,#b6f36b,#34d399);color:#04281c;display:flex;align-items:center;justify-content:center;transform:rotate(-6deg)}
.emerald-login .market-login-symbol svg{width:32px;height:32px}
.emerald-login h1{font:800 34px Sora,Inter,sans-serif;color:#fff;margin:18px 0 8px;letter-spacing:-.5px}
.emerald-login .market-login-heading p{font-size:15px;color:#cfeee0;margin:0}
.emerald-login .market-login-password{display:block;margin:0}
.emerald-login .market-login-password label{display:block;color:#d3f3e4;font-size:14px;font-weight:600;margin:16px 0 8px}
.emerald-login .market-login-input{display:flex;height:62px;gap:12px;padding:0 16px;border-radius:18px;background:rgb(255 255 255 / 6%);border:1.5px solid rgb(255 255 255 / 16%)}
.emerald-login .market-login-input:focus-within{border-color:#b6f36b;outline:3px solid #b6f36b;outline-offset:3px}
.emerald-login .market-login-input svg{width:22px;height:22px;color:#9be8c4;flex-shrink:0}
.emerald-login .market-login-input input{width:100%;min-width:0;border:0;background:transparent;color:#fff;outline:0;font:16px Inter,system-ui;height:100%;padding:0}
.emerald-login input::placeholder{color:#85bda3}.emerald-login input:autofill{box-shadow:0 0 0 1000px #103f2e inset;-webkit-text-fill-color:#fff}
.emerald-login .market-login-forgot{display:block;margin:12px 0 18px auto;color:#b6f36b;background:none;border:0;opacity:.65;font-size:14px}
.emerald-login .market-login-submit{width:100%;height:62px;border-radius:18px;border:0;background:linear-gradient(90deg,#b6f36b,#6ee7b7);color:#04281c;font-size:18px;font-weight:700;box-shadow:0 20px 44px -10px rgb(182 243 107 / 35%)}
.emerald-login .market-login-divider{margin:24px 0;color:#9be8c4}.emerald-login .market-login-divider span{background:#073020;color:#9be8c4;padding:0 14px}
.emerald-login .market-login-google{height:58px;background:rgb(255 255 255 / 7%);border:1.5px solid rgb(255 255 255 / 28%);border-radius:18px;color:#fff;font-size:16px}
.emerald-login .market-login-register{margin:24px 0 0;color:#cfeee0;font-size:15px;display:flex;flex-wrap:wrap;gap:6px;justify-content:center}.emerald-login .market-login-register a{color:#b6f36b;font-weight:700}
.emerald-login .eye{flex-shrink:0}.emerald-login a:focus-visible,.emerald-login button:focus-visible{outline:3px solid #b6f36b;outline-offset:4px}
@media(max-width:1000px){.emerald-layout{gap:28px}.emerald-intro .stage{transform:scale(.8);margin:-40px -50px}.emerald-intro h1{font-size:40px!important}.emerald-intro .float{padding:10px}.emerald-intro .sora{font-size:24px!important}}
@media(max-width:760px){.emerald-layout{flex-direction:column;padding:24px 16px;gap:28px}.emerald-intro{flex:auto!important;width:100%;gap:20px!important}.emerald-intro .stage{display:none}.emerald-intro h1{font-size:30px!important}.emerald-intro h1 br{display:none}.emerald-panel{flex:auto;max-width:520px}.emerald-login .market-login-card{padding:28px 22px}.emerald-intro .sora{font-size:24px!important}.gb{border-radius:30px}}


/* Compact viewport layout: shrink the content instead of clipping the form. */
@media(min-width:761px){
.emerald-layout{max-width:1200px;min-height:100svh;padding:16px 28px;gap:40px}
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
.emerald-login .market-login-password label{margin:12px 0 6px}
.emerald-login .market-login-input{height:48px;border-radius:14px}
.emerald-login .market-login-forgot{margin:8px 0 12px;min-height:24px}
.emerald-login .market-login-submit{height:48px;border-radius:14px;font-size:16px}
.emerald-login .market-login-divider{margin:16px 0}
.emerald-login .market-login-google{height:46px;border-radius:14px;font-size:15px}
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
.emerald-login .market-login-divider{margin:12px 0}
.emerald-login .market-login-register{margin-top:12px}
}


/* Keep autofill and text consistent with the active login palette. */
.emerald-login .market-login-input input{flex:1;width:0;-webkit-text-fill-color:currentColor}
.emerald-login .market-login-input input:is(:autofill,:-webkit-autofill){box-shadow:0 0 0 1000px #103f2e inset;-webkit-text-fill-color:#fff;border-radius:8px}
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
body.light-mode .emerald-login .market-login-input{background:#f3faf6;border-color:#a7d1ba;box-shadow:none}
body.light-mode .emerald-login .market-login-input input{background:transparent;color:#163e2e;-webkit-text-fill-color:#163e2e}
body.light-mode .emerald-login .market-login-input input:is(:autofill,:-webkit-autofill){box-shadow:0 0 0 1000px #f3faf6 inset;-webkit-text-fill-color:#163e2e}
body.light-mode .emerald-login .market-login-input input::placeholder{color:#547562;-webkit-text-fill-color:#547562}
body.light-mode .emerald-login .market-login-input svg,body.light-mode .emerald-login .eye{color:#197950}
body.light-mode .emerald-login .market-login-forgot{color:#466c58;opacity:1}
body.light-mode .emerald-login .market-login-submit{background:#087c53;color:#fff;box-shadow:0 8px 20px rgb(8 124 83 / 18%)}
body.light-mode .emerald-login .market-login-divider,body.light-mode .emerald-login .market-login-divider span{background:transparent;color:#52705f}
body.light-mode .emerald-login .market-login-divider::before,body.light-mode .emerald-login .market-login-divider::after{background:#d1e5d9}
body.light-mode .emerald-login .market-login-google{background:#fff;color:#214e38;border-color:#a7d1ba;box-shadow:none}
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
body[data-accessibility-color] .emerald-login .float svg,
body[data-accessibility-color] .emerald-login .market-login-input svg,
body[data-accessibility-color] .emerald-login .market-login-symbol svg{color:var(--a11y-ink)!important}
body[data-accessibility-color] .emerald-login .market-login-input input,
body[data-accessibility-color] .emerald-login .market-login-input input:is(:autofill,:-webkit-autofill){
color:var(--a11y-ink)!important;-webkit-text-fill-color:var(--a11y-ink)!important;
box-shadow:0 0 0 1000px var(--a11y-surface) inset!important;caret-color:var(--a11y-ink);
}
body[data-accessibility-color] .emerald-login .market-login-input input::placeholder{color:var(--a11y-ink)!important;-webkit-text-fill-color:var(--a11y-ink)!important;opacity:.75}
body[data-accessibility-color] .emerald-login .market-login-heading h1,
body[data-accessibility-color] .emerald-login .emerald-intro h1,
body[data-accessibility-color] .emerald-login .emerald-intro h1 span{
color:var(--a11y-ink)!important;-webkit-text-fill-color:var(--a11y-ink)!important;background:none!important;
}
body[data-accessibility-color] .emerald-login .market-login-divider::before,
body[data-accessibility-color] .emerald-login .market-login-divider::after{background:var(--a11y-border)!important}

</style>
<section class="screen visible emerald-login" data-screen="login" aria-labelledby="marketLoginTitle">
<div aria-hidden="true"><div class="aur a1"></div><div class="aur a2"></div><div class="aur a3"></div><div class="gridbg"></div></div>
<div class="emerald-layout">

<section class="emerald-intro" style="flex:1 1 540px;min-width:0;display:flex;flex-direction:column;gap:8px;align-self:stretch;justify-content:space-between">
<div class="in1" style="display:flex;align-items:center;gap:16px;padding-top:8px">
<img class="emerald-university-emblem" src="{{ asset('Logo_UNDC_OF.png') }}" alt="Emblema de la Universidad Nacional de Cañete" width="64" height="76">
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

<section class="emerald-panel">
<div class="emerald-toolbar"><a href="/tienda">← Regresar</a></div>
<div class="gb in3"><div class="market-login-card">
<div class="market-login-heading"><span class="market-login-symbol"><svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg></span><div><h1 id="marketLoginTitle" tabindex="-1">Bienvenido</h1><p>Inicia sesión para continuar con tus pagos.</p></div></div>
@include('modules.acceso.partials.errors')
<form class="market-login-password" method="post" action="{{ route('tienda.login.store') }}">
@csrf<label for="marketLoginEmail">Correo electrónico</label><div class="market-login-input"><svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 6 9 7 9-7"/></svg><input id="marketLoginEmail" type="email" name="email" value="{{ old('email') }}" autocomplete="username" maxlength="254" required placeholder="Correo electrónico"></div>
<label for="marketLoginPassword">Contraseña</label><div class="market-login-input"><svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></svg><input id="marketLoginPassword" type="password" name="password" autocomplete="current-password" maxlength="128" required placeholder="Tu contraseña"><button class="eye" type="button" id="toggleLoginPassword" aria-controls="marketLoginPassword" aria-label="Mostrar contraseña" aria-pressed="false">◉</button></div>
<button type="button" class="market-login-forgot" disabled>¿Olvidaste tu contraseña?</button>
<button type="submit" class="btn market-login-submit">Iniciar sesión</button>
</form>
<div class="market-login-divider"><span>o continúa con</span></div>
@php($marketGoogleReady = config('services.google.enabled') === true && filled(config('services.google.client_id')) && filled(config('services.google.client_secret')) && filled(config('services.google.redirect')) && request()->isSecure() && str_starts_with((string) config('services.google.redirect'), 'https://'))
@if($marketGoogleReady)
<a id="marketLoginGoogle" class="market-login-google" href="/auth/google/redirect"><svg class="market-google-icon" aria-hidden="true" viewBox="0 0 48 48"><path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5Z"/><path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65Z"/><path fill="#FBBC05" d="M10.53 28.59A14.4 14.4 0 0 1 9.75 24c0-1.59.27-3.13.76-4.59l-7.98-6.19A23.9 23.9 0 0 0 0 24c0 3.87.92 7.52 2.56 10.78l7.97-6.19Z"/><path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.91-5.8l-7.73-6c-2.15 1.45-4.92 2.3-8.18 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48Z"/></svg>Continuar con Google</a>
@else
<button id="marketLoginGoogle" class="market-login-google" type="button" disabled><svg class="market-google-icon" aria-hidden="true" viewBox="0 0 48 48"><path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5Z"/><path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65Z"/><path fill="#FBBC05" d="M10.53 28.59A14.4 14.4 0 0 1 9.75 24c0-1.59.27-3.13.76-4.59l-7.98-6.19A23.9 23.9 0 0 0 0 24c0 3.87.92 7.52 2.56 10.78l7.97-6.19Z"/><path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.91-5.8l-7.73-6c-2.15 1.45-4.92 2.3-8.18 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48Z"/></svg>Continuar con Google</button>

@endif
<div class="market-login-register"><span>¿Aún no tienes una cuenta?</span><a href="{{ route('tienda.registro') }}" >Regístrate</a></div>

</div></div></section></div></section>
<script>
document.getElementById('toggleLoginPassword').addEventListener('click', function () {
    const input = document.getElementById('marketLoginPassword');
    const show = input.type === 'password';
    input.type = show ? 'text' : 'password';
    this.setAttribute('aria-pressed', String(show));
    this.setAttribute('aria-label', show ? 'Ocultar contraseña' : 'Mostrar contraseña');
});
</script>
@endsection
