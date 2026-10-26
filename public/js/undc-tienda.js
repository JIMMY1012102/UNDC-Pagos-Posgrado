(()=>{'use strict';
const $=id=>document.getElementById(id), csrf=document.querySelector('meta[name="csrf-token"]').content;
const state={token:sessionStorage.getItem('undc_cart_token'),cart:null,owned:false,profile:null,office:sessionStorage.getItem('undc_office_id')||'',catalog:[],screen:'catalog',authenticated:false};
const toast=(message,error=false)=>{const node=$('toast');node.textContent=message;node.className='toast show'+(error?' error':'');clearTimeout(toast.timer);toast.timer=setTimeout(()=>node.classList.remove('show'),4700)};
const api=async(path,method='GET',body=null,auth=false)=>{
const headers={Accept:'application/json','X-CSRF-TOKEN':csrf,...(body?{'Content-Type':'application/json'}:{}),...(auth&&state.token?{Authorization:'Bearer '+state.token}:{})};
const res=await fetch(path,{method,credentials:'same-origin',headers,...(body?{body:JSON.stringify(body)}:{})});
const json=await res.json().catch(()=>({}));
if(!res.ok){const error=new Error(json.message||'No se pudo completar la operación.');error.status=res.status;error.errors=json.errors;throw error}return json.data;
};
const esc=s=>String(s??'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
function screen(name){state.screen=name;document.querySelectorAll('.screen').forEach(x=>x.classList.toggle('visible',x.dataset.screen===name));document.querySelectorAll('.step').forEach((step,i)=>{const order=['catalog','cart','identity','review'];step.classList.toggle('active',order[i]===name);step.classList.toggle('done',order.indexOf(name)>i)});window.scrollTo({top:0,behavior:'smooth'});}
function badge(){const n=state.cart?.items_count||0;$('cartBadge').textContent=n;$('emptyBadge').hidden=n>0;}
function renderCart(){
const items=state.cart?.items||[];badge();
const list=$('cartRows');list.replaceChildren();
if(!items.length){const empty=document.createElement('div');empty.className='empty';empty.textContent='🛒 Tu carrito está vacío. Selecciona un concepto para empezar.';list.append(empty);}
for(const line of items){const row=document.createElement('div');row.className='cart-row';
const info=document.createElement('div');const label=document.createElement('b');label.textContent=line.slug||'Concepto '+line.catalog_item_id;const q=document.createElement('small');q.textContent='Cantidad: '+line.quantity;info.append(label,q);row.append(info);
if(!state.owned){const remove=document.createElement('button');remove.type='button';remove.className='mini';remove.textContent='Quitar';remove.setAttribute('aria-label','Quitar '+label.textContent);remove.onclick=()=>removeItem(line.id);row.append(remove)}list.append(row);}
$('cartTotalCount').textContent=(state.cart?.items_count||0)+' conceptos';
$('cartContinue').disabled=!items.length;$('sideContinue').disabled=!items.length;
}
function renderCatalog(items){
state.catalog=items;const query=$('search').value.toLocaleLowerCase('es');const target=$('catalogCards');target.replaceChildren();
const filtered=items.filter(item=>(item.name+' '+item.code+' '+(item.description||'')).toLocaleLowerCase('es').includes(query));
if(!filtered.length){const empty=document.createElement('div');empty.className='empty';empty.textContent='No encontramos conceptos publicados para esta búsqueda.';target.append(empty);return;}
for(const item of filtered){const card=document.createElement('article');card.className='product';
const icon=document.createElement('div');icon.className='product-icon';icon.textContent='📄';
const h=document.createElement('h3');h.textContent=item.name;
const sub=document.createElement('small');sub.textContent='Código '+item.code;
const des=document.createElement('p');des.textContent=item.description||'Concepto institucional publicado.';
const add=document.createElement('button');add.type='button';add.className='btn secondary';add.textContent='＋ Agregar al carrito';add.onclick=()=>addItem(item.id);
card.append(icon,h,sub,des,add);target.append(card);}
}
async function loadCatalog(){const id=$('office').value.trim();if(!/^[1-9]\d*$/.test(id)){toast('Indica un ID de oficina habilitado por Tesorería.',true);return}
try{const items=await api('/api/v1/tienda/catalogo?office_id='+encodeURIComponent(id));state.office=id;sessionStorage.setItem('undc_office_id',id);renderCatalog(items);$('catalogNote').textContent=items.length+' conceptos publicados';if(!items.length)toast('Todavía no hay conceptos publicados para esa oficina.');}catch(e){toast(e.message,true)}}
async function ensureCart(){if(state.token){try{state.cart=await api('/api/v1/tienda/carritos/actual','GET',null,true);renderCart();return}catch(e){if(e.status!==404)throw e;sessionStorage.removeItem('undc_cart_token');state.token=null}}
const result=await api('/api/v1/tienda/carritos','POST');state.token=result.token;sessionStorage.setItem('undc_cart_token',state.token);state.cart=result.cart;state.owned=false;renderCart()}
async function addItem(id){try{if(state.owned){toast('Tu carrito anterior ya fue vinculado. Para agregar conceptos, inicia una nueva selección.',true);return}await ensureCart();state.cart=await api('/api/v1/tienda/carritos/actual/items','POST',{catalog_item_id:id,quantity:1},true);renderCart();toast('¡Concepto agregado al carrito!')}catch(e){toast(e.message,true)}}
async function removeItem(id){try{state.cart=await api('/api/v1/tienda/carritos/actual/items/'+id,'DELETE',null,true);renderCart();toast('Concepto retirado.')}catch(e){toast(e.message,true)}}
async function authState(){const res=await fetch('/api/v1/acceso/estado',{headers:{Accept:'application/json'},credentials:'same-origin'});if(res.status===401){state.authenticated=false;return false}if(!res.ok)throw new Error('No se pudo verificar la sesión.');state.authenticated=true;return true}
async function purchase(){if(!(state.cart?.items_count>0)){toast('Agrega al menos un concepto para continuar.',true);return}screen('cart');await continuePurchase()}
async function continuePurchase(){
try{if(!(await authState())){screen('identity');$('loginBox').hidden=false;$('profileBox').hidden=true;return}
if(state.token&&state.cart?.items_count>0&&!state.owned){await api('/api/v1/tienda/precompra/vincular-carrito','POST',{cart_token:state.token});sessionStorage.removeItem('undc_cart_token');state.token=null;state.owned=true}
await refreshReview()}catch(e){toast(e.message,true)}}
async function refreshReview(){
const data=await api('/api/v1/tienda/precompra/resumen');state.owned=!!data.cart;state.cart=data.cart;state.profile=data.profile;renderCart();
if(!data.profile_complete){screen('identity');$('loginBox').hidden=true;$('profileBox').hidden=false;
if(data.profile)for(const [key,value] of Object.entries(data.profile)){const el=$('profileForm').elements.namedItem(key);if(el&&typeof value==='string')el.value=value}return;}
if(data.next_step!=='review'){screen('catalog');toast('No hay conceptos pendientes en tu carrito.');return}
screen('review');$('buyerName').textContent=[data.profile.first_names,data.profile.last_names].filter(Boolean).join(' ');
$('buyerDocument').textContent=(data.profile.document_type||'')+' '+(data.profile.document_number||'');
$('buyerContact').textContent=data.profile.contact_email||data.profile.phone||'No registrado';
const box=$('reviewList');box.replaceChildren();for(const item of data.cart?.items||[]){const row=document.createElement('div');row.className='cart-row';const strong=document.createElement('b');strong.textContent=item.slug;const amount=document.createElement('small');amount.textContent='Cantidad: '+item.quantity;row.append(strong,amount);box.append(row);}
$('reviewCount').textContent=data.cart.items_count+' conceptos';}
async function restore(){
try{if(await authState()){const owned=await api('/api/v1/tienda/precompra/resumen');if(owned.cart?.items_count>0){state.owned=true;state.cart=owned.cart;renderCart()}}
if(!state.owned)await ensureCart();
if(sessionStorage.getItem('undc_resume_purchase')==='1'){sessionStorage.removeItem('undc_resume_purchase');await continuePurchase();}
}catch(e){toast(e.message,true)}}
$('office').value=state.office;$('load').addEventListener('click',loadCatalog);$('search').addEventListener('input',()=>renderCatalog(state.catalog));
$('cartButton').addEventListener('click',()=>screen('cart'));$('sideContinue').addEventListener('click',purchase);$('cartContinue').addEventListener('click',continuePurchase);
document.querySelectorAll('[data-go]').forEach(el=>el.addEventListener('click',()=>screen(el.dataset.go)));
$('googleButton').addEventListener('click',()=>{sessionStorage.setItem('undc_resume_purchase','1');window.location.assign('/auth/google/redirect')});
$('profileForm').addEventListener('submit',async event=>{event.preventDefault();const form=new FormData(event.currentTarget);
try{await api('/api/v1/acceso/perfil','PUT',Object.fromEntries(form.entries()));await refreshReview();toast('Tus datos quedaron guardados para próximas compras.')}catch(e){let msg=e.message;if(e.errors){msg=Object.values(e.errors).flat().join(' ')}toast(msg,true)}});
$('reviewBack').addEventListener('click',()=>screen('cart'));
restore();if(state.office)loadCatalog();
})();