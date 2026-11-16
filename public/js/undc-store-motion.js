(() => {
'use strict';
if(document.body.dataset.marketPage!=='catalog')return;
const hero=document.querySelector('.immersive-hero');
if(!hero||!window.IntersectionObserver||!window.ResizeObserver)return;
const reduced=window.matchMedia('(prefers-reduced-motion: reduce)');
const title=hero.querySelector('h1'),photo=hero.querySelector('.market-hero-photo');
let frame=0,entrancePlayed=false;
const animations=new Set(),pending=new Set(),revealed=new WeakSet();
function track(animation){animations.add(animation);animation.finished.catch(()=>{}).finally(()=>animations.delete(animation));}
if(title){
 title.setAttribute('aria-label',title.innerText.trim());
 let index=0;
 const textNodes=[...title.childNodes].filter(node=>node.nodeType===Node.TEXT_NODE);
 for(const textNode of textNodes){
  const fragment=document.createDocumentFragment();
  for(const part of textNode.textContent.split(/(\s+)/)){
   if(!part.trim()){fragment.append(document.createTextNode(part));continue;}
   const word=document.createElement('span');word.className='kinetic-word';word.textContent=part;
   word.setAttribute('aria-hidden','true');word.style.setProperty('--word-direction',index%2?-1:1);word.dataset.wordIndex=index++;fragment.append(word);
  }
  textNode.replaceWith(fragment);
 }
}
function resetMotion(){
 hero.style.setProperty('--hero-travel','0px');hero.style.setProperty('--title-travel','0px');
 hero.style.setProperty('--word-spread','0px');hero.style.setProperty('--title-scale','1');
}
function update(){
 frame=0;
 if(reduced.matches){resetMotion();return;}
 const bounds=hero.getBoundingClientRect();
 const progress=Math.max(0,Math.min(1,-bounds.top/Math.max(1,bounds.height)));
 hero.style.setProperty('--hero-travel',(progress*55).toFixed(2)+'px');
 hero.style.setProperty('--title-travel',(-progress*38).toFixed(2)+'px');
 hero.style.setProperty('--word-spread',(progress*22).toFixed(2)+'px');
 hero.style.setProperty('--title-scale',String(1-progress*.035));
}
function schedule(){if(!frame)frame=window.requestAnimationFrame(update);}
const observer=new IntersectionObserver(entries=>{
 for(const entry of entries){
  if(!entry.isIntersecting)continue;
  const element=entry.target;observer.unobserve(element);pending.delete(element);
  if(revealed.has(element))continue;revealed.add(element);
  if(reduced.matches)continue;
  const isCard=element.matches('.catalog-product'),delay=isCard?Number(element.dataset.revealIndex||0)%3*70:0;
  track(element.animate([{opacity:0,translate:'0 28px'},{opacity:1,translate:'0 0'}],{duration:650,delay,easing:'cubic-bezier(.2,.8,.2,1)'}));
 }
},{threshold:.08,rootMargin:'0px 0px -20px 0px'});
function watchSections(){
 document.querySelectorAll('.catalog-results-heading,.catalog-product,.store-show-all,.store-footer-inner').forEach((element,index)=>{
  if(revealed.has(element)||pending.has(element))return;
  element.dataset.revealIndex=index;pending.add(element);observer.observe(element);
 });
}
function enter(){
 if(entrancePlayed||reduced.matches)return;
 entrancePlayed=true;
 hero.querySelectorAll('.kinetic-word').forEach((word,index)=>track(word.animate([{opacity:0,translate:'0 36px',filter:'blur(5px)'},{opacity:1,translate:'0 0',filter:'blur(0)'}],{duration:850,delay:index*95,easing:'cubic-bezier(.2,.8,.2,1)'})));
}
reduced.addEventListener('change',()=>{
 if(reduced.matches){animations.forEach(animation=>animation.cancel());resetMotion();}
 else{enter();schedule();}
});
window.addEventListener('scroll',schedule,{passive:true});
window.addEventListener('resize',schedule,{passive:true});
const results=document.getElementById('tusneResults');
if(results)new MutationObserver(watchSections).observe(results,{childList:true,subtree:true});
new ResizeObserver(schedule).observe(hero);
enter();watchSections();schedule();
})();