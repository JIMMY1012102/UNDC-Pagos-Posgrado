const {test}=require('node:test'),assert=require('node:assert/strict'),vm=require('node:vm'),fs=require('node:fs'),path=require('node:path');
function boot({fail=false}={}){
 const callbacks={},calls=[],redirects=[],els=new Map();
 class El{constructor(){this.disabled=false;this.children=[];this.textContent='';this.listeners={};}addEventListener(k,f){this.listeners[k]=f;}append(...e){this.children.push(...e);}replaceChildren(...e){this.children=e;}}
 for(const id of ['generateOrder','orderQuoteMessage','paymentPreviewItems','paymentPreviewTotal'])els.set(id,new El());
 const document={getElementById:id=>els.get(id)||null,querySelector:()=>({content:'csrf'}),createElement:()=>new El(),addEventListener:(k,f)=>callbacks[k]=f};
 let release;const response=new Promise(r=>release=r);
 const fetch=async(url,options)=>{calls.push({url,options});if(url.endsWith('/cotizar'))return {ok:!fail,json:async()=>fail?{errors:{tariff:['Tarifa pendiente']}}:{data:{items:[{name:'<script>texto</script>',quantity:2,total_amount:'50.20'}],total_amount:'50.20'}}};await response;return {ok:true,json:async()=>({data:{code:'UNDC-TEST'}})};};
 vm.runInNewContext(fs.readFileSync(path.join(__dirname,'../../public/js/undc-orders.js'),'utf8'),{document,fetch,window:{location:{assign:p=>redirects.push(p)}},URLSearchParams,Intl,Date});
 return {callbacks,calls,redirects,els,release};
}
test('approved quote renders server amount as text and double click creates once',async()=>{
 const b=boot();await b.callbacks['undc:payment-review']({detail:{cartId:7}});assert.equal(b.els.get('generateOrder').disabled,false);assert.match(b.els.get('paymentPreviewTotal').textContent,/50[,.]20/);
 assert.equal(b.els.get('paymentPreviewItems').children[0].children[0].textContent,'<script>texto</script> × 2');
 const first=b.els.get('generateOrder').listeners.click();await b.els.get('generateOrder').listeners.click();assert.equal(b.calls.filter(c=>c.url.endsWith('/pedidos')).length,1);
 const body=JSON.parse(b.calls.at(-1).options.body);assert.equal(body.cart_id,7);assert.equal(body.total_amount,undefined);
 b.release();await first;assert.equal(b.redirects[0],'/tienda/mis-pedidos?pedido=UNDC-TEST');
});
test('unapproved quote keeps order button disabled and shows server reason',async()=>{
 const b=boot({fail:true});await b.callbacks['undc:payment-review']({detail:{cartId:7}});assert.equal(b.els.get('generateOrder').disabled,true);assert.equal(b.els.get('orderQuoteMessage').textContent,'Tarifa pendiente');
});
