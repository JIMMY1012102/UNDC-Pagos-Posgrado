(() => {
'use strict';
const $=id=>document.getElementById(id);
const money=value=>'S/ '+Number(value).toLocaleString('es-PE',{minimumFractionDigits:2,maximumFractionDigits:2});
const date=value=>value?new Date(String(value).replace(' ','T')+(/Z$|[+-]\d\d:\d\d$/.test(value)?'':'Z')).toLocaleString('es-PE'):'—';
const labels={pending:'Pendiente de pago',expired:'Vencido',paid:'Pagado',cancelled:'Anulado',confirmed:'Confirmado'};
const node=(tag,text,cls)=>{const el=document.createElement(tag);if(text!==undefined)el.textContent=text;if(cls)el.className=cls;return el;};
async function api(path,method='GET',body){
 const res=await fetch('/api/v1/tienda/'+path,{method,credentials:'same-origin',headers:{Accept:'application/json','Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content},...(body?{body:JSON.stringify(body)}:{})});
 const json=await res.json();
 if(!res.ok)throw new Error(Object.values(json.errors||{}).flat().join(' ')||json.message||'No se pudo consultar el pedido.');
 return json.data;
}
let cartId=null,quoteGeneration=0,creating=false,page=1;
document.addEventListener('undc:payment-review',async event=>{
 cartId=event.detail.cartId;const generation=++quoteGeneration,button=$('generateOrder');if(!button)return;
 button.disabled=true;$('orderQuoteMessage').classList.remove('is-approved');$('orderQuoteMessage').textContent='Verificando las tarifas vigentes…';
 try{const quote=await api('pedidos/cotizar','POST',{cart_id:cartId});if(generation!==quoteGeneration)return;
 $('paymentPreviewItems').replaceChildren(...quote.items.map(item=>{const row=node('div',undefined,'payment-preview-line');const name=node('strong',item.name);name.append(node('small','Posgrado · Cantidad '+item.quantity));row.append(name,node('span',money(item.total_amount)));return row;}));
 $('paymentPreviewTotal').textContent=money(quote.total_amount);if($('paymentTotalLabel'))$('paymentTotalLabel').textContent='Total del pedido';$('orderQuoteMessage').classList.add('is-approved');$('orderQuoteMessage').textContent='Importes oficiales verificados. Puedes generar tu pedido.';button.disabled=false;
 }catch(error){if(generation===quoteGeneration){const message=$('orderQuoteMessage');message.textContent=error.message;message.title=error.message;}}
});
$('generateOrder')?.addEventListener('click',async()=>{
 if(creating||!cartId)return;creating=true;$('generateOrder').disabled=true;
 try{const order=await api('pedidos','POST',{cart_id:cartId});window.location.assign('/tienda/mis-pedidos?pedido='+encodeURIComponent(order.code));}
 catch(error){$('orderQuoteMessage').textContent=error.message;$('generateOrder').disabled=false;creating=false;}
});
async function detail(code){
 const order=await api('pedidos/'+encodeURIComponent(code)),box=$('orderDetail');box.replaceChildren();
 const grid=node('div',undefined,'payment-preview-grid order-detail-grid');
 const selection=node('section',undefined,'payment-selection-card order-selection');
 const buyer=node('div',undefined,'payment-buyer');
 const avatar=node('span',String(order.buyer_name||'').trim().split(/\s+/).slice(0,2).map(word=>Array.from(word)[0]||'').join('').toUpperCase(),'payment-buyer-avatar');
 const buyerCopy=node('div',undefined,'payment-buyer-copy');buyerCopy.append(node('h2',order.buyer_name),node('p',order.buyer_email));buyer.append(avatar,buyerCopy);selection.append(buyer);
 const heading=node('div',undefined,'payment-selection-heading');heading.append(node('h2','Conceptos del pedido'),node('span',order.items.length+' conceptos'));selection.append(heading);
 const concepts=node('div',undefined,'order-concepts');
 for(const item of order.items){const row=node('div',undefined,'payment-preview-line'),name=node('strong',item.concept_name);name.append(node('small','Posgrado · Cantidad '+item.quantity));row.append(name,node('span',money(item.total_amount)));concepts.append(row);}selection.append(concepts);
 const dates=node('div',undefined,'order-dates');dates.append(node('p','Creado: '+date(order.created_at)),node('p','Vence: '+date(order.expires_at)));selection.append(dates);
 const tracking=node('details',undefined,'order-tracking');tracking.append(node('summary','Seguimiento del pedido'));const history=node('ol',undefined,'order-timeline');for(const item of order.history)history.append(node('li',date(item.occurred_at)+' · '+(labels[item.to_status]||item.to_status)+' — '+(item.reason||'')));tracking.append(history);selection.append(tracking);
 const column=node('div',undefined,'payment-summary-column'),summary=node('aside',undefined,'payment-preview-summary');
 summary.append(node('h2','Resumen del pedido'),node('p',order.code,'order-code'),node('span',labels[order.status]||order.status,'order-status'));
 const total=node('div',undefined,'payment-total-block');total.append(node('p','Total del pedido'),node('strong',money(order.total_amount),'payment-preview-total'),node('p','Importes oficiales guardados en tu pedido.','payment-total-note'));summary.append(total);
 if(order.status==='pending'){
  const methods=node('section',undefined,'order-payment-options');methods.setAttribute('aria-label','Opciones de pago');methods.append(node('h3','Opciones de pago'));
  const methodGrid=node('div',undefined,'order-payment-grid');
  for(const [provider,description,kind,shape] of [
   ['Interbank','Pago bancario','bank','<path d="M3 10h18L12 3 3 10Zm2 10h14M7 10v7m5-7v7m5-7v7M3 21h18"/>'],
   ['Pay-Me','Yape y Visa','gateway','<rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 10h18m-5 5h2"/>']
  ]){
   const method=node('div',undefined,'order-payment-method'),icon=node('span',undefined,'order-payment-icon order-payment-icon--'+kind),copy=node('div');
   icon.setAttribute('aria-hidden','true');icon.innerHTML='<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">'+shape+'</svg>';
   copy.append(node('strong',provider),node('span',description));method.append(icon,copy);methodGrid.append(method);
  }
  methods.append(methodGrid);summary.append(methods);
  const pay=node('button','Pago aún no disponible','btn block order-pay-button');pay.type='button';pay.disabled=true;summary.append(pay);
 }else summary.append(node('p',order.status==='expired'?'Este pedido venció. Puedes volver al catálogo para realizar una nueva selección.':'Consulta el seguimiento para conocer el estado de tu pedido.','payment-quote-message'));
 const catalog=node('a','Seguir explorando','payment-text-button payment-modify');catalog.href='/tienda';summary.append(catalog);column.append(summary,node('p','El código identifica tu pedido; no acredita un pago.','payment-gateway-note'));grid.append(selection,column);box.append(grid);
 $('ordersListPanel').hidden=true;$('orderDetailPanel').hidden=false;$('orderDetailMessage').textContent='';
 $('orderDialogTitle').textContent=order.status==='pending'?'Tu pedido está listo.':'Detalle de tu pedido.';
 $('orderDialogTitle').focus({preventScroll:true});window.scrollTo?.({top:0,behavior:'instant'});
}
async function list(append=false){
 const message=$('ordersMessage');if(!message)return;
 message.textContent='Consultando pedidos…';$('refreshOrders').disabled=true;
 try{const data=await api('pedidos?page='+page);if(!append)$('ordersList').replaceChildren();
 for(const order of data.items){const row=node('article',undefined,'order-row'),info=node('div');info.append(node('strong',order.code),node('p',date(order.created_at)),node('p',money(order.total_amount)),node('span',labels[order.status]||order.status,'order-status'));const button=node('button','Ver detalle →','payment-text-button');button.type='button';button.addEventListener('click',()=>detail(order.code).catch(error=>message.textContent=error.message));row.append(info,button);$('ordersList').append(row);}
 $('moreOrders').hidden=!data.has_more;message.textContent=$('ordersList').children.length?'Estados consultados.':'Todavía no tienes pedidos. Genera uno desde tu carrito.';
 }catch(error){message.textContent=error.message;}finally{$('refreshOrders').disabled=false;}
}
$('refreshOrders')?.addEventListener('click',()=>{page=1;list();});
$('moreOrders')?.addEventListener('click',async()=>{$('moreOrders').disabled=true;page++;await list(true);$('moreOrders').disabled=false;});
$('closeOrderDialog')?.addEventListener('click',()=>{$('orderDetailPanel').hidden=true;$('ordersListPanel').hidden=false;$('ordersTitle').focus({preventScroll:true});window.history?.replaceState(null,'','/tienda/mis-pedidos');});
if($('ordersList')){list().then(()=>{const code=new URLSearchParams(window.location.search).get('pedido');if(code)detail(code).catch(error=>$('ordersMessage').textContent=error.message);});}
})();

