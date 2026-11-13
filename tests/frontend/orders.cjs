const {test}=require('node:test'),assert=require('node:assert/strict'),vm=require('node:vm'),fs=require('node:fs'),path=require('node:path');
function boot({fail=false,order=null,modifyFail=false}={}){
 const callbacks={},calls=[],redirects=[],els=new Map();
 class El{constructor(){this.classList={add(){},remove(){}};this.disabled=false;this.children=[];this.textContent='';this.listeners={};}addEventListener(k,f){this.listeners[k]=f;}append(...e){this.children.push(...e);}replaceChildren(...e){this.children=e;}setAttribute(){}focus(){}}
 for(const id of ['generateOrder','orderQuoteMessage','paymentPreviewItems','paymentPreviewTotal',...(order?['orderDetail','ordersListPanel','orderDetailPanel','orderDetailMessage','orderDialogTitle']:[])])els.set(id,new El());
 const document={getElementById:id=>els.get(id)||null,querySelector:()=>({content:'csrf'}),createElement:()=>new El(),addEventListener:(k,f)=>callbacks[k]=f};
 let release;const response=new Promise(r=>release=r);
 const fetch=async(url,options)=>{calls.push({url,options});if(order&&options.method==='GET')return {ok:true,json:async()=>({data:order})};if(url.endsWith('/modificar')&&modifyFail)return {ok:false,json:async()=>({message:'Pago en curso'})};if(url.endsWith('/cotizar'))return {ok:!fail,json:async()=>fail?{errors:{tariff:['Tarifa pendiente']}}:{data:{items:[{name:'<script>texto</script>',quantity:2,total_amount:'50.20'}],total_amount:'50.20'}}};await response;return {ok:true,json:async()=>({data:{code:'UNDC-TEST'}})};};
 const source=fs.readFileSync(path.join(__dirname,'../../public/js/undc-orders.js'),'utf8').replace(/\}\)\(\);\s*$/, 'globalThis.orderTest={detail};})();');const context={};
 vm.runInNewContext(source,Object.assign(context,{document,fetch,window:{location:{assign:p=>redirects.push(p)},scrollTo(){}},sessionStorage:{getItem(){return null},removeItem(){}},URLSearchParams,Intl,Date}));
 return {callbacks,calls,redirects,els,release,context};
}
test('approved quote renders server amount as text and double click creates once',async()=>{
 const b=boot();await b.callbacks['undc:payment-review']({detail:{cartId:7}});assert.equal(b.els.get('generateOrder').disabled,false);assert.match(b.els.get('paymentPreviewTotal').textContent,/50[,.]20/);
 assert.equal(b.els.get('paymentPreviewItems').children[0].children[0].textContent,'<script>texto</script>');
 const first=b.els.get('generateOrder').listeners.click();await b.els.get('generateOrder').listeners.click();assert.equal(b.calls.filter(c=>c.url.endsWith('/pedidos')).length,1);
 const body=JSON.parse(b.calls.at(-1).options.body);assert.equal(body.cart_id,7);assert.equal(body.total_amount,undefined);
 b.release();await first;assert.equal(b.redirects[0],'/tienda/mis-pedidos?pedido=UNDC-TEST');
});
test('unapproved quote keeps order button disabled and shows server reason',async()=>{
 const b=boot({fail:true});b.els.get('paymentPreviewTotal').textContent='S/ 260.45';const row={textContent:'Referencia del carrito'};b.els.get('paymentPreviewItems').append(row);await b.callbacks['undc:payment-review']({detail:{cartId:7}});assert.equal(b.els.get('generateOrder').disabled,true);assert.equal(b.els.get('orderQuoteMessage').textContent,'Tarifa pendiente');assert.equal(b.els.get('paymentPreviewTotal').textContent,'S/ 260.45');assert.equal(b.els.get('paymentPreviewItems').children[0],row);
});

const exampleOrder={code:'UNDC-TEST',status:'pending',can_modify:true,buyer_name:'Persona Demo',buyer_email:'demo@example.test',items:[{concept_name:'Concepto',quantity:1,total_amount:'25.10'}],total_amount:'25.10',history:[]};
const descendants=el=>[el,...el.children.flatMap(descendants)];
test('modify order submits once and returns to cart after success',async()=>{
 const b=boot({order:exampleOrder});await b.context.orderTest.detail(exampleOrder.code);
 const edit=descendants(b.els.get('orderDetail')).find(el=>el.textContent==='Modificar pedido');assert.ok(edit);
 const first=edit.listeners.click();await edit.listeners.click();assert.equal(b.calls.filter(c=>c.url.endsWith('/modificar')).length,1);
 assert.equal(b.calls.at(-1).options.method,'POST');assert.equal(b.calls.at(-1).options.headers['X-CSRF-TOKEN'],'csrf');
 b.release();await first;assert.equal(b.redirects.at(-1),'/tienda/carrito');
});
test('modification failure remains on order and allows retry',async()=>{
 const b=boot({order:exampleOrder,modifyFail:true});await b.context.orderTest.detail(exampleOrder.code);
 const edit=descendants(b.els.get('orderDetail')).find(el=>el.textContent==='Modificar pedido');await edit.listeners.click();
 assert.equal(edit.disabled,false);assert.equal(b.els.get('orderDetailMessage').textContent,'Pago en curso');assert.equal(b.redirects.length,0);
});
test('orders blocked by server do not offer modification',async()=>{
 const b=boot({order:{...exampleOrder,status:'paid',can_modify:false}});await b.context.orderTest.detail(exampleOrder.code);
 assert.equal(descendants(b.els.get('orderDetail')).some(el=>el.textContent==='Modificar pedido'),false);
});
