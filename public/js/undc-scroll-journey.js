(()=>{'use strict';
const track=document.getElementById('undc-track');
if(!track)return;
let raf=0,tilted=null;
const untilt=el=>{el.style.setProperty('--tx','0');el.style.setProperty('--ty','0');el.style.setProperty('--mx','.5');el.style.setProperty('--my','.5');};
const sync=()=>{
 if(raf)return;
 raf=requestAnimationFrame(()=>{
  raf=0;
  const stage=track.firstElementChild;
  const distance=Math.max(1,track.offsetHeight-(stage?stage.offsetHeight:0));
  const progress=Math.min(1,Math.max(0,-track.getBoundingClientRect().top/distance));
  track.style.setProperty('--p',progress.toFixed(4));
  track.setAttribute('data-scene',progress<.26?'1':progress<.51?'2':progress<.76?'3':'4');
  const header=document.getElementById('undc-hdr');
  if(header){
   const bounds=track.getBoundingClientRect();
   header.setAttribute('data-solid',stage&&bounds.bottom<=stage.offsetHeight+4?'1':'0');
  }
  const vh=window.innerHeight||800;
  document.querySelectorAll('[data-rv]').forEach(el=>{
   const b=el.getBoundingClientRect(),d=Number(el.getAttribute('data-d'))||0;
   const v=(vh*.94-b.top-d*70)/(vh*.3);
   el.style.setProperty('--r',Math.min(1,Math.max(0,v)).toFixed(3));
   const c=(b.top+b.height/2-vh/2)/vh;
   el.style.setProperty('--c',Math.max(-1.5,Math.min(1.5,c)).toFixed(3));
  });
  document.querySelectorAll('[data-jp]').forEach(el=>{
   const b=el.getBoundingClientRect();
   const v=(vh*.55-b.top-70)/Math.max(1,b.height-160);
   el.style.setProperty('--jp',Math.min(1,Math.max(0,v)).toFixed(4));
  });
  const journey=document.getElementById('como-funciona');
  if(journey){
   const bounds=journey.getBoundingClientRect();
   let active=0,best=1e9;
   journey.querySelectorAll('.step').forEach((step,i)=>{
    const b=step.getBoundingClientRect(),d=Math.abs(b.top+b.height/2-vh*.5);
    if(d<best){best=d;active=i+1;}
   });
   journey.setAttribute('data-act',bounds.top<vh*.45&&bounds.bottom>vh*.3?String(active):'0');
  }
 });
};
const onPointer=e=>{
 const el=e.target&&e.target.closest?e.target.closest('[data-tilt]'):null;
 if(tilted&&tilted!==el)untilt(tilted);
 tilted=el;
 if(!el)return;
 const r=el.getBoundingClientRect(),x=(e.clientX-r.left)/Math.max(1,r.width),y=(e.clientY-r.top)/Math.max(1,r.height);
 el.style.setProperty('--mx',x.toFixed(3));el.style.setProperty('--my',y.toFixed(3));
 el.style.setProperty('--tx',((x-.5)*2).toFixed(3));el.style.setProperty('--ty',((y-.5)*2).toFixed(3));
};
const onPointerOut=e=>{if(!e.relatedTarget&&tilted){untilt(tilted);tilted=null;}};
window.addEventListener('scroll',sync,{passive:true,capture:true});
window.addEventListener('resize',sync);
document.addEventListener('pointermove',onPointer,{passive:true});
document.addEventListener('pointerout',onPointerOut);
sync();
})();