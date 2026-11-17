/* No scroll hijacking: progressive motion with a readable static fallback. */
(()=>{'use strict';
const home=document.getElementById('marketHome');if(!home)return;
const story=document.getElementById('homeStory'),steps=[...story.querySelectorAll('ol>li')],counter=document.getElementById('homeStoryCounter'),link=document.getElementById('homeStoryLink'),bands=home.querySelector('.home-bands');
const reduce=window.matchMedia('(prefers-reduced-motion: reduce)'),wide=window.matchMedia('(min-width:901px)');
const destinations=[['/tienda/catalogo','Explorar trámites ↗'],['/tienda/carrito','Revisar mi carrito →'],['/tienda/mis-pedidos','Consultar mis pedidos ↗']];
const headerTop=document.querySelector('.top-inner');
if(headerTop&&!headerTop.querySelector('.home-help-link')){const help=document.createElement('a');help.className='home-help-link';help.href='#homeStory';help.textContent='Ayuda';headerTop.insertBefore(help,headerTop.querySelector('.utility-controls'));}
const hero=document.getElementById('hero'),heroBar=document.getElementById('homeHeroProgressBar'),heroValue=document.getElementById('homeHeroProgressValue');
// Size the official iframe to its container; no API keys or SDK are needed.
const feed=document.getElementById('homeFacebookFeed'),fbFrame=document.getElementById('homeFacebookFrame');
let facebookWidth=500,facebookTimer=0;
function resizeFacebook(){if(!feed||!fbFrame)return;const width=Math.max(180,Math.min(500,Math.floor(feed.getBoundingClientRect().width)));if(width===facebookWidth)return;facebookWidth=width;const url=new URL(fbFrame.src);url.searchParams.set('width',String(width));fbFrame.width=String(width);fbFrame.src=url.toString();}
if(feed&&fbFrame){resizeFacebook();if('ResizeObserver' in window)new ResizeObserver(()=>{clearTimeout(facebookTimer);facebookTimer=setTimeout(resizeFacebook,200);}).observe(feed);}
let frame=0,active=-1;
function update(){frame=0;
document.body.classList.toggle('home-past-hero',!!hero&&hero.getBoundingClientRect().bottom<=(document.querySelector('.site-header')?.getBoundingClientRect().height||70));

if(hero&&heroBar&&heroValue){const h=hero.getBoundingClientRect();const percent=Math.round(Math.min(1,Math.max(0,-h.top/Math.max(1,h.height)))*100);heroBar.style.setProperty('--hero-scroll-progress',percent+'%');heroValue.textContent=percent+'%';}
const lightHome=document.body.classList.contains('light-mode')&&!document.body.hasAttribute('data-accessibility-color');
const animated=!lightHome&&!reduce.matches&&wide.matches;story.classList.toggle('motion-ready',animated);const rect=story.getBoundingClientRect(),header=document.querySelector('.site-header')?.getBoundingClientRect().height||70,travel=Math.max(1,rect.height-(window.innerHeight-header));const progress=Math.min(1,Math.max(0,(header-rect.top)/travel));const index=animated?Math.min(2,Math.floor(progress*3)):0;
if(index!==active){active=index;steps.forEach((step,i)=>step.classList.toggle('is-active',i===index));counter.textContent='0'+(index+1)+' / 03';[link.href,link.textContent]=destinations[index];}
if(!reduce.matches){bands.style.setProperty('--band-shift',Math.round(-100-bands.getBoundingClientRect().top*.2)+'px');story.style.setProperty('--story-photo-shift',Math.round(progress*-24)+'px');}else{bands.style.removeProperty('--band-shift');story.style.removeProperty('--story-photo-shift');}}
function schedule(){if(!frame)frame=requestAnimationFrame(update);}
new MutationObserver(records=>{if(records.some(record=>record.oldValue!==document.body.getAttribute(record.attributeName)))schedule();}).observe(document.body,{attributes:true,attributeOldValue:true,attributeFilter:['class','data-accessibility-color']});
window.addEventListener('scroll',schedule,{passive:true});window.addEventListener('resize',schedule,{passive:true});reduce.addEventListener('change',schedule);wide.addEventListener('change',schedule);update();
})();

