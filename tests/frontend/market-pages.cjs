// Run with: node --test tests/frontend/market-pages.cjs
const {test}=require('node:test');
const assert=require('node:assert/strict');
const fs=require('node:fs'),path=require('node:path'),vm=require('node:vm');
const root=path.resolve(__dirname,'../..');
const read=p=>fs.readFileSync(path.join(root,p),'utf8');
function expand(text){return text.replace(/@include\('([^']+)'\)/g,(_,name)=>expand(read('resources/views/'+name.replaceAll('.','/')+'.blade.php')))}
class Element {
 constructor(){this.dataset={};this.listeners={};this.style={setProperty(){}};this.classList={toggle(){},remove(){},contains(){return false}};this.content='csrf';this.value='';this.hidden=true;this.children=[];this.attrs={};}
 querySelectorAll(){return []} querySelector(){return null}
 addEventListener(k,f){this.listeners[k]=f} setAttribute(k,v){this.attrs[k]=v} removeAttribute(k){delete this.attrs[k]} toggleAttribute(){} append(...els){this.children.push(...els)} replaceChildren(){this.children=[]} scrollIntoView(){} focus(){} contains(){return false}
}
async function boot(page,{authenticated=false,complete=true,resume=false,local=new Map(),cart=null}={}){
 const file={catalog:'tienda',cart:'modules/tienda/cart',login:'modules/acceso/login'}[page];
 const html=expand(read('resources/views/'+file+'.blade.php'))+expand(read('resources/views/layouts/market.blade.php'));
 const ids=new Map([...html.matchAll(/\bid="([^"]+)"/g)].map(m=>[m[1],new Element()]));
 const body=new Element();body.dataset.marketPage=page;const assigned=[],requests=[];
 const storage=new Map(resume?[['undc_resume_purchase','1']]:[]);
 const store=m=>({getItem:k=>m.get(k)||null,setItem:(k,v)=>m.set(k,v),removeItem:k=>m.delete(k)});
 const document={body,documentElement:new Element(),getElementById:id=>ids.get(id)||null,querySelector:s=>s.startsWith('meta')?new Element():s==='.page-grid'||s==='.cart-review-panel'?new Element():null,querySelectorAll:()=>[],addEventListener(){},createElement:()=>new Element()};
 const context=vm.createContext({document,window:{addEventListener(){},location:{search:'',assign:u=>assigned.push(u)},scrollTo(){},getSelection(){return null}},localStorage:store(local),sessionStorage:store(storage),URLSearchParams,MutationObserver:class{observe(){}},setTimeout:()=>0,clearTimeout(){},fetch:async (url,options={})=>{requests.push({url,method:options.method||'GET'});return ({status:url.endsWith('/estado')&&!authenticated?401:200,ok:!url.endsWith('/estado')||authenticated,json:async()=>({data:url.endsWith('/estado')?{profile_complete:complete}:url.endsWith('/resumen')?{cart}:url.endsWith('/perfil')?{first_names:'Estudiante',last_names:'Demo',contact_email:'demo@example.test'}:{token:'test-token',cart:{items:[],items_count:0}}})})}});
 const source=read('public/js/undc-tienda.js').replace(/\}\)\(\);\s*$/, 'globalThis.marketTest={screen,renderCart,renderTusneCatalog,toggleReferenceCart,tusneConcepts,catalogSuggestions,chooseSearchSuggestion,showPaymentPreview,selectPaymentMethod,showPaymentInstructions,paymentPreviewSelection};})();');
 vm.runInContext(source,context);await new Promise(resolve=>setImmediate(resolve));
 return {ids,assigned,storage,context,html,requests};
}
for(const page of ['catalog','cart','login'])test(page+' initializes with only its own page controls',async()=>{
 const app=await boot(page);assert.equal(app.ids.has('tusneCatalog'),page==='catalog');assert.equal(app.ids.has('cartRows'),page==='cart');assert.equal(app.ids.has('marketLoginEmail'),page==='login');
 assert.equal(app.ids.get('toast').textContent,undefined,'startup should not report an exception');
 assert.deepEqual(app.assigned,[]);
 app.context.marketTest.screen(page==='cart'?'login':'cart');assert.equal(app.assigned[0],page==='cart'?'/tienda/login':'/tienda/carrito');
 assert.ok(app.ids.has('accessibilityPanel'));
});
test('anonymous login retains pending purchase instead of consuming or redirecting it',async()=>{
 const app=await boot('login',{resume:true});assert.equal(app.storage.get('undc_resume_purchase'),'1');assert.deepEqual(app.assigned,[]);
});
test('authenticated OAuth return takes pending purchase to cart',async()=>{
 const app=await boot('catalog',{authenticated:true,resume:true});assert.deepEqual(app.assigned,['/tienda/carrito']);assert.equal(app.storage.get('undc_resume_purchase'),'1');
});

test('selection survives catalog to login to cart through shared browser storage',async()=>{
 const local=new Map();const catalog=await boot('catalog',{local});
 const id=catalog.context.marketTest.tusneConcepts[0].id;
 catalog.context.marketTest.toggleReferenceCart(id);
 await boot('login',{local});
 const cart=await boot('cart',{local});
 assert.equal(cart.ids.get('cartSummaryCount').textContent,'1');
 assert.equal(cart.ids.get('cartContinue').disabled,false);
 assert.match(cart.ids.get('cartContinue').textContent,/Iniciar sesión/);
 assert.deepEqual(JSON.parse(local.get('undc_tusne_reference_cart')),[id]);
});

test('authenticated incomplete profile goes to personal data before resuming cart',async()=>{
 const app=await boot('cart',{authenticated:true,complete:false,resume:true});
 assert.deepEqual(app.assigned,['/tienda/perfil']);
 assert.equal(app.storage.get('undc_resume_purchase'),'1');
});

test('header search finds services across categories without accents or word-order restrictions',async()=>{
 const app=await boot('catalog');const input=app.ids.get('search');
 input.value='  MAESTRIA matricula  ';input.listeners.input();
 assert.match(app.ids.get('tusneResults').innerHTML,/Matrícula/);
 assert.doesNotMatch(app.ids.get('tusneResults').innerHTML,/Prospecto/);
 input.value='6.15';input.listeners.input();
 assert.match(app.ids.get('tusneResults').innerHTML,/Prospecto/);
 assert.match(app.ids.get('visibleConceptCount').textContent,/^1 /);
 input.value='xyz-inexistente';input.listeners.input();
 assert.equal(app.ids.get('tusneEmpty').hidden,false);
 input.value='';input.listeners.input();
 assert.equal(app.ids.get('tusneEmpty').hidden,true);
 assert.equal(app.ids.get('visibleConceptCount').textContent.split(' ')[0],String(app.context.marketTest.tusneConcepts.length));
});

test('search suggestions rank exact names first, limit results and select a product',async()=>{
 const app=await boot('catalog');const api=app.context.marketTest;
 const suggestions=api.catalogSuggestions('matricula');assert.ok(suggestions.length>0);assert.ok(suggestions.length<=6);
 const product=suggestions[0];
 assert.equal(api.catalogSuggestions(product.name)[0].id,product.id);
 assert.equal(api.catalogSuggestions('xyz-inexistente').length,0);
 assert.equal(api.catalogSuggestions('   ').length,0);
 const input=app.ids.get('search'),list=app.ids.get('searchSuggestions');
 input.value='matricula';input.listeners.input();
 assert.equal(list.hidden,false);assert.equal(input.attrs['aria-expanded'],'true');
 api.chooseSearchSuggestion(0);
 assert.equal(input.value,product.name);assert.equal(list.hidden,true);
 assert.match(app.ids.get('tusneResults').innerHTML,/Matrícula/);
});



test('payment preview selects concepts, recalculates total and retains the cart',async()=>{
 const local=new Map();const catalog=await boot('catalog',{local});
 const products=catalog.context.marketTest.tusneConcepts.filter(item=>!item.unit).slice(0,2);
 for(const item of products)catalog.context.marketTest.toggleReferenceCart(item.id);
 const before=local.get('undc_tusne_reference_cart');
 const app=await boot('cart',{local,authenticated:true});
 await app.ids.get('cartContinue').listeners.click();
 assert.equal(app.context.marketTest.paymentPreviewSelection().count,2);
 const rows=app.ids.get('paymentPreviewItems').children;
 rows[0].children[0].checked=false;rows[0].children[0].listeners.change();
 assert.equal(app.ids.get('paymentPreviewCount').textContent,'1');
 assert.equal(app.ids.get('paymentPreviewTotal').textContent,products[1].price);
 rows[1].children[0].checked=false;rows[1].children[0].listeners.change();
 assert.equal(app.ids.get('paymentPreviewTotal').textContent,'S/ 0.00');
 assert.equal(app.ids.get('paymentContinue').disabled,true);
 assert.equal(local.get('undc_tusne_reference_cart'),before);
 app.context.marketTest.showPaymentInstructions();assert.equal(app.ids.get('paymentInstructions').hidden,true);
});

test('wallet instructions are a preview and switching methods clears previous instructions',async()=>{
 const local=new Map();const catalog=await boot('catalog',{local});
 catalog.context.marketTest.toggleReferenceCart(catalog.context.marketTest.tusneConcepts[0].id);
 const app=await boot('cart',{local,authenticated:true});
 await app.ids.get('cartContinue').listeners.click();
 const requestsBefore=app.requests.length;
 app.context.marketTest.selectPaymentMethod('wallet');
 app.ids.get('paymentContinue').listeners.click();
 assert.equal(app.ids.get('paymentInstructions').hidden,false);
 assert.equal(app.ids.get('paymentQrPlaceholder').hidden,false);
 assert.match(app.ids.get('paymentInstructionsText').textContent,/Cuando la pasarela esté habilitada/);
 app.context.marketTest.selectPaymentMethod('card');
 assert.equal(app.ids.get('paymentInstructions').hidden,true);
 app.ids.get('paymentContinue').listeners.click();
 assert.equal(app.ids.get('paymentQrPlaceholder').hidden,true);
 assert.match(app.ids.get('paymentInstructionsText').textContent,/no solicitamos ni almacenamos/);
 assert.equal(app.requests.length,requestsBefore,'instructions make no payment API calls');
});

test('per-unit prices never become a fixed payable total',async()=>{
 const local=new Map();const catalog=await boot('catalog',{local});
 const item=catalog.context.marketTest.tusneConcepts.find(item=>item.unit);
 assert.ok(item);catalog.context.marketTest.toggleReferenceCart(item.id);
 const app=await boot('cart',{local,authenticated:true});
 await app.ids.get('cartContinue').listeners.click();
 assert.equal(app.ids.get('paymentPreviewTotal').textContent,'Por confirmar');
 assert.equal(app.ids.get('paymentContinue').disabled,false,'instructions remain available without starting a payment');
});


test('institutional items without approved prices stay unpriced in mixed selection',async()=>{
 const local=new Map();const catalog=await boot('catalog',{local});
 catalog.context.marketTest.toggleReferenceCart(catalog.context.marketTest.tusneConcepts[0].id);
 const cart={items:[{id:1,slug:'concepto-demo',quantity:2}],items_count:1};
 const app=await boot('cart',{local,authenticated:true,cart});
 await app.ids.get('cartContinue').listeners.click();
 assert.equal(app.ids.get('paymentPreviewCount').textContent,'2');
 assert.equal(app.ids.get('paymentPreviewTotal').textContent,'Por confirmar');
 assert.equal(app.ids.get('paymentBuyerName').textContent,'Estudiante Demo');
});
