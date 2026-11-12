(() => {
'use strict';
const $=id=>document.getElementById(id);
const money=value=>'S/ '+Number(value).toLocaleString('es-PE',{minimumFractionDigits:2,maximumFractionDigits:2});
const date=value=>value?new Date(String(value).replace(' ','T')+(/Z$|[+-]\d\d:\d\d$/.test(value)?'':'Z')).toLocaleString('es-PE'):'—';
const labels={pending:'Pendiente',expired:'Vencido',paid:'Pagado',cancelled:'Anulado',confirmed:'Confirmado'};
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
 button.disabled=true;$('orderQuoteMessage').textContent='Verificando las tarifas vigentes…';
 try{const quote=await api('pedidos/cotizar','POST',{cart_id:cartId});if(generation!==quoteGeneration)return;
 $('paymentPreviewItems').replaceChildren(...quote.items.map(item=>{const row=node('div',undefined,'payment-preview-line');row.append(node('strong',item.name+' × '+item.quantity),node('span',money(item.total_amount)));return row;}));
 $('paymentPreviewTotal').textContent=money(quote.total_amount);$('orderQuoteMessage').textContent='Tarifas aprobadas verificadas. Puedes generar tu pedido pendiente.';button.disabled=false;
 }catch(error){if(generation===quoteGeneration)$('orderQuoteMessage').textContent=error.message;}
});
$('generateOrder')?.addEventListener('click',async()=>{
 if(creating||!cartId)return;creating=true;$('generateOrder').disabled=true;
 try{const order=await api('pedidos','POST',{cart_id:cartId});window.location.assign('/tienda/mis-pedidos?pedido='+encodeURIComponent(order.code));}
 catch(error){$('orderQuoteMessage').textContent=error.message;$('generateOrder').disabled=false;creating=false;}
});
async function detail(code){
 const order=await api('pedidos/'+encodeURIComponent(code)),box=$('orderDetail');box.replaceChildren();
 box.append(node('p',order.code,'order-code'),node('span',labels[order.status]||order.status,'order-status'),node('p',order.buyer_name),node('p',order.buyer_email),node('p','Creado: '+date(order.created_at)),node('p','Vencimiento: '+date(order.expires_at)),node('h3','Conceptos del pedido'));
 for(const item of order.items){const line=node('div',undefined,'order-line');line.append(node('span',item.concept_name+' × '+item.quantity),node('strong',money(item.total_amount)));box.append(line);}
 box.append(node('h3','Total: '+money(order.total_amount)));
 if(order.status==='pending'){
  box.append(node('h3','Selecciona tu forma de pago'));
  const field=node('fieldset',undefined,'order-methods');field.append(node('legend','Medios de pago previstos'));
  for(const [value,label] of [['card','Tarjeta Visa / Mastercard'],['wallet','Billetera electrónica / Yape']]){const option=node('label'),input=document.createElement('input');input.type='radio';input.name='paymentMethod';input.value=value;option.append(input,node('span',label));field.append(option);}
  box.append(field,node('p','La pasarela aún no está habilitada. No se generarán QR ni se solicitarán datos de tarjetas hasta contar con la integración autorizada.','order-notice'));
  const pay=node('button','Pago aún no disponible','btn block');pay.type='button';pay.disabled=true;box.append(pay);
 }
 box.append(node('p','El código identifica el pedido; no es una autorización bancaria. Pagar el concepto no inicia automáticamente el trámite académico.','order-notice'),node('h3','Seguimiento'));
 const history=node('ol',undefined,'order-timeline');for(const item of order.history)history.append(node('li',date(item.occurred_at)+' · '+(labels[item.to_status]||item.to_status)+' — '+(item.reason||'')));box.append(history);
 if(!$('orderDialog').open)$('orderDialog').showModal();
}
async function list(append=false){
 const message=$('ordersMessage');if(!message)return;
 message.textContent='Consultando pedidos…';$('refreshOrders').disabled=true;
 try{const data=await api('pedidos?page='+page);if(!append)$('ordersList').replaceChildren();
 for(const order of data.items){const row=node('article',undefined,'order-row'),info=node('div');info.append(node('strong',order.code),node('p',date(order.created_at)),node('p',money(order.total_amount)),node('span',labels[order.status]||order.status,'order-status'));const button=node('button','Ver pedido y medios de pago','btn');button.type='button';button.addEventListener('click',()=>detail(order.code).catch(error=>message.textContent=error.message));row.append(info,button);$('ordersList').append(row);}
 $('moreOrders').hidden=!data.has_more;message.textContent=$('ordersList').children.length?'Estados consultados.':'Todavía no tienes pedidos. Genera uno desde tu carrito.';
 }catch(error){message.textContent=error.message;}finally{$('refreshOrders').disabled=false;}
}
$('refreshOrders')?.addEventListener('click',()=>{page=1;list();});
$('moreOrders')?.addEventListener('click',async()=>{$('moreOrders').disabled=true;page++;await list(true);$('moreOrders').disabled=false;});
$('closeOrderDialog')?.addEventListener('click',()=>$('orderDialog').close());
if($('ordersList')){list().then(()=>{const code=new URLSearchParams(window.location.search).get('pedido');if(code)detail(code).catch(error=>$('ordersMessage').textContent=error.message);});}
})();
