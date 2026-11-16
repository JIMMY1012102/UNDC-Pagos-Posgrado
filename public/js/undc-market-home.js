/* No scroll hijacking: progressive motion with a readable static fallback. */
(()=>{'use strict';
const home=document.getElementById('marketHome');if(!home)return;
const story=document.getElementById('homeStory'),steps=[...story.querySelectorAll('ol>li')],counter=document.getElementById('homeStoryCounter'),link=document.getElementById('homeStoryLink'),bands=home.querySelector('.home-bands');
const reduce=window.matchMedia('(prefers-reduced-motion: reduce)'),wide=window.matchMedia('(min-width:901px)');
const destinations=[['/tienda/catalogo','Explorar trámites ↗'],['/tienda/carrito','Revisar mi carrito →'],['/tienda/mis-pedidos','Consultar mis pedidos ↗']];
let frame=0,active=-1;
function update(){frame=0;const animated=!reduce.matches&&wide.matches;story.classList.toggle('motion-ready',animated);const rect=story.getBoundingClientRect(),header=document.querySelector('.site-header')?.getBoundingClientRect().height||70,travel=Math.max(1,rect.height-(window.innerHeight-header));const progress=Math.min(1,Math.max(0,(header-rect.top)/travel));const index=animated?Math.min(2,Math.floor(progress*3)):0;
if(index!==active){active=index;steps.forEach((step,i)=>step.classList.toggle('is-active',i===index));counter.textContent='0'+(index+1)+' / 03';[link.href,link.textContent]=destinations[index];}
if(!reduce.matches){bands.style.setProperty('--band-shift',Math.round(-100-bands.getBoundingClientRect().top*.2)+'px');story.style.setProperty('--story-photo-shift',Math.round(progress*-24)+'px');}else{bands.style.removeProperty('--band-shift');story.style.removeProperty('--story-photo-shift');}}
function schedule(){if(!frame)frame=requestAnimationFrame(update);}
window.addEventListener('scroll',schedule,{passive:true});window.addEventListener('resize',schedule,{passive:true});reduce.addEventListener('change',schedule);wide.addEventListener('change',schedule);update();
})();
