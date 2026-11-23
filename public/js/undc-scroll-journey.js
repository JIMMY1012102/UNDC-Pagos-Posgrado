(()=>{'use strict';
const root=document.querySelector('.undc-scroll-design');if(!root)return;
const header=document.querySelector('.site-header');
const setHeaderHeight=()=>{if(header)document.documentElement.style.setProperty('--market-header-height',header.getBoundingClientRect().height+'px');};
let raf=0,tilted=null;
const untilt=el=>{el.style.setProperty('--tx','0');el.style.setProperty('--ty','0');el.style.setProperty('--mx','.5');el.style.setProperty('--my','.5');};
const sync=()=>{if(raf)return;raf=requestAnimationFrame(()=>{raf=0;setHeaderHeight();const vh=window.innerHeight||800;
root.querySelectorAll('[data-rv]').forEach(el=>{const b=el.getBoundingClientRect(),d=Number(el.getAttribute('data-d'))||0;const v=(vh*.94-b.top-d*70)/(vh*.3);el.style.setProperty('--r',Math.min(1,Math.max(0,v)).toFixed(3));const c=(b.top+b.height/2-vh/2)/vh;el.style.setProperty('--c',Math.max(-1.5,Math.min(1.5,c)).toFixed(3));});
root.querySelectorAll('[data-jp]').forEach(el=>{const b=el.getBoundingClientRect(),v=(vh*.55-b.top-70)/Math.max(1,b.height-160);el.style.setProperty('--jp',Math.min(1,Math.max(0,v)).toFixed(4));});
const j=root.querySelector('#como-funciona');if(j){let act=0,best=1e9;j.querySelectorAll('.step').forEach((s,i)=>{const b=s.getBoundingClientRect(),d=Math.abs(b.top+b.height/2-vh*.5);if(d<best){best=d;act=i+1;}});j.setAttribute('data-act',j.getBoundingClientRect().top<vh*.45&&j.getBoundingClientRect().bottom>vh*.3?String(act):'0');}
});};
const pointer=e=>{const el=e.target?.closest?.('.undc-scroll-design [data-tilt]');if(tilted&&tilted!==el)untilt(tilted);tilted=el;if(!el)return;const b=el.getBoundingClientRect(),x=(e.clientX-b.left)/Math.max(1,b.width),y=(e.clientY-b.top)/Math.max(1,b.height);el.style.setProperty('--mx',x.toFixed(3));el.style.setProperty('--my',y.toFixed(3));el.style.setProperty('--tx',((x-.5)*2).toFixed(3));el.style.setProperty('--ty',((y-.5)*2).toFixed(3));};
const pointerOut=e=>{if(!e.relatedTarget&&tilted){untilt(tilted);tilted=null;}};
setHeaderHeight();if(header&&'ResizeObserver'in window)new ResizeObserver(()=>{setHeaderHeight();sync();}).observe(header);
window.addEventListener('scroll',sync,{passive:true,capture:true});window.addEventListener('resize',sync,{passive:true});document.addEventListener('pointermove',pointer,{passive:true});document.addEventListener('pointerout',pointerOut);sync();
})();