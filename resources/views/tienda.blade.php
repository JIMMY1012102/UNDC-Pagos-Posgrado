<!doctype html>
<html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Tienda de pagos UNDC — Precompra</title>
<style>
:root{font-family:system-ui,Arial,sans-serif;color:#183153;background:#f3f6fa}*{box-sizing:border-box}body{margin:0}header{background:#123a62;color:white;padding:22px 5%;display:flex;justify-content:space-between;align-items:center}header h1{font-size:1.35rem;margin:0}main{max-width:1150px;margin:30px auto;padding:0 20px}.layout{display:grid;grid-template-columns:2fr 1fr;gap:20px}.panel{background:white;border:1px solid #dce4ef;border-radius:14px;padding:22px;margin-bottom:20px;box-shadow:0 4px 18px #2038500a}.list{display:grid;gap:12px}.item{border:1px solid #dce4ef;border-radius:10px;padding:14px;display:flex;align-items:center;justify-content:space-between;gap:8px}button{background:#145da0;color:white;padding:11px 17px;border:0;border-radius:8px;cursor:pointer;font-size:1rem}button:disabled{background:#8998a8;cursor:not-allowed}button.secondary{background:#e7eef6;color:#173b63}input{padding:10px;border:1px solid #9bacbb;border-radius:7px;max-width:100%}label{display:block;margin:9px 0}.muted{color:#526879;font-size:.92rem}.error{color:#a52323} .notice{background:#fff4d8;border-radius:7px;padding:11px}.hidden{display:none}#status{min-height:25px}@media(max-width:780px){.layout{grid-template-columns:1fr}header{align-items:flex-start;gap:12px;flex-direction:column}}
</style></head><body>
<header><h1>Universidad Nacional de Cañete · Tienda de pagos</h1><span>Precompra de Posgrado · Sin cobros habilitados</span></header>
<main>
<p class="notice">Prototipo funcional. Los conceptos y tarifas deben ser aprobados por Tesorería. No se recibirán pagos en esta versión.</p>
<div class="layout"><section>
<div class="panel"><h2>Catálogo de conceptos</h2><p class="muted">Puedes explorar y agregar conceptos sin iniciar sesión.</p>
<label>Identificador de oficina autorizado <input id="office" type="number" min="1" placeholder="ID de oficina" aria-label="Identificador de oficina"></label>
<button id="load">Ver conceptos</button><div id="catalog" class="list" style="margin-top:14px"></div></div>
<div class="panel hidden" id="identity"><h2>Datos personales</h2><p>Se solicitan únicamente en el primer registro. Podrás editarlos posteriormente.</p>
<form id="profile">
<label>Nombres <input name="first_names" required maxlength="160"></label>
<label>Apellidos <input name="last_names" required maxlength="160"></label>
<label>Tipo de documento <input name="document_type" value="DNI" required maxlength="30"></label>
<label>Número de documento <input name="document_number" required maxlength="32"></label>
<label>Teléfono <input name="phone" maxlength="30"></label>
<label>Correo de contacto <input name="contact_email" type="email" maxlength="254"></label>
<button type="submit">Guardar datos y continuar</button></form></div>
<div class="panel hidden" id="review"><h2>Revisión de precompra</h2><p id="buyer"></p><div id="reviewLines"></div>
<p class="muted">Importe pendiente de validación institucional.</p><button disabled>Pago no disponible</button></div>
</section>
<aside><div class="panel"><h2>Tu carrito</h2><div id="cartItems" class="list"></div><p id="count">0 conceptos</p>
<button id="buy" disabled>Comprar</button><p class="muted">Al pulsar Comprar te pediremos iniciar sesión, no antes.</p></div></aside></div>
<p id="status" role="status" aria-live="polite"></p>
</main>
<script>
(() => {
 const $=id=>document.getElementById(id);
 const csrf=document.querySelector('meta[name="csrf-token"]').content;
 let token=sessionStorage.getItem('undc_cart_token');
 let lines=[], owned=false;
 const say=(message,error=false)=>{ $('status').textContent=message; $('status').className=error?'error':''; };
 const api=async(path,options={})=>{
  const h={'Accept':'application/json','X-CSRF-TOKEN':csrf,'Content-Type':'application/json',...(options.headers||{})};
  const response=await fetch(path,{credentials:'same-origin',...options,headers:h});
  const json=await response.json().catch(()=>({}));
  if(!response.ok){const e=new Error(json.message||'No se pudo completar la solicitud.');e.status=response.status;throw e;}
  return json.data;
 };
 const render=()=>{
  $('cartItems').replaceChildren();
  lines.forEach(line=>{
   const row=document.createElement('div');row.className='item';
   const label=document.createElement('span');label.textContent=(line.slug||('Concepto '+line.catalog_item_id))+' × '+line.quantity;
   row.append(label);
   if(!owned){const remove=document.createElement('button');remove.className='secondary';remove.textContent='Quitar';remove.onclick=()=>removeItem(line.id);row.append(remove);}
   $('cartItems').append(row);
  });
  $('count').textContent=lines.reduce((n,l)=>n+l.quantity,0)+' conceptos';
  $('buy').disabled=lines.length===0;
 };
 const showCart=cart=>{lines=cart?.items||[];render();};
 async function ensureCart(){
  if(token){try{showCart(await api('/api/v1/tienda/carritos/actual',{headers:{Authorization:'Bearer '+token}}));return;}catch(e){token=null;sessionStorage.removeItem('undc_cart_token');}}
  const result=await api('/api/v1/tienda/carritos',{method:'POST'});
  token=result.token;sessionStorage.setItem('undc_cart_token',token);showCart(result.cart);
 }
 async function removeItem(id){try{showCart(await api('/api/v1/tienda/carritos/actual/items/'+id,{method:'DELETE',headers:{Authorization:'Bearer '+token}}));}catch(e){say(e.message,true);}}
 $('load').onclick=async()=>{
  const id=$('office').value;
  if(!/^[1-9][0-9]*$/.test(id)){say('Indica la oficina habilitada por la UNDC.',true);return;}
  try{
   const items=await api('/api/v1/tienda/catalogo?office_id='+encodeURIComponent(id));
   $('catalog').replaceChildren();
   items.forEach(item=>{
    const row=document.createElement('div');row.className='item';
    const title=document.createElement('span');title.textContent=item.name+' ('+item.code+')';
    const add=document.createElement('button');add.textContent='Agregar';
    add.onclick=async()=>{try{await ensureCart();showCart(await api('/api/v1/tienda/carritos/actual/items',{method:'POST',headers:{Authorization:'Bearer '+token},body:JSON.stringify({catalog_item_id:item.id,quantity:1})}));say('Concepto agregado.');}catch(e){say(e.message,true);}};
    row.append(title,add);$('catalog').append(row);
   });
   if(!items.length)say('No hay conceptos publicados para esta oficina.');
  }catch(e){say(e.message,true);}
 };
 const review=async()=>{
  const data=await api('/api/v1/tienda/precompra/resumen');
  owned=true;showCart(data.cart);
  $('identity').classList.toggle('hidden',data.profile_complete);
  $('review').classList.toggle('hidden',!data.profile_complete || data.next_step!=='review');
  if(data.profile_complete && data.next_step==='review'){
   $('buyer').textContent='Comprador: '+data.profile.first_names+' '+data.profile.last_names+' · '+data.profile.document_type+' '+data.profile.document_number;
   $('reviewLines').textContent='Conceptos seleccionados: '+data.cart.items_count+'. El pago está deshabilitado.';
  }
  if(data.next_step==='cart')say('Tu carrito está vacío o venció. Selecciona conceptos nuevamente.');
 };
 const continuePurchase=async()=>{
  const state=await fetch('/api/v1/acceso/estado',{headers:{Accept:'application/json'},credentials:'same-origin'});
  if(state.status===401){sessionStorage.setItem('undc_resume_purchase','1');location.assign('/auth/google/redirect');return;}
  if(!state.ok)throw new Error('No se pudo comprobar tu sesión.');
  if(token){await api('/api/v1/tienda/precompra/vincular-carrito',{method:'POST',body:JSON.stringify({cart_token:token})});token=null;sessionStorage.removeItem('undc_cart_token');}
  await review();
 };
 $('buy').onclick=async()=>{try{await continuePurchase();}catch(e){say(e.message,true);}};
 $('profile').onsubmit=async e=>{
  e.preventDefault();const data=Object.fromEntries(new FormData(e.target).entries());
  try{await api('/api/v1/acceso/perfil',{method:'PUT',body:JSON.stringify(data)});await review();say('Tus datos han sido guardados y se reutilizarán en próximas visitas.');}catch(err){say(err.message,true);}
 };
 (async()=>{
  try{
   await ensureCart();
   if(sessionStorage.getItem('undc_resume_purchase')==='1'){
    sessionStorage.removeItem('undc_resume_purchase');await continuePurchase();
   }
  }catch(e){say(e.message,true);}
 })();
})();
</script>
</body></html>