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
 addEventListener(k,f){this.listeners[k]=f} setAttribute(k,v){this.attrs[k]=v} removeAttribute(k){delete this.attrs[k]} showModal(){this.open=true} close(){this.open=false} toggleAttribute(){} append(...els){this.children.push(...els)} replaceChildren(){this.children=[]} scrollIntoView(){} focus(){} contains(){return false}
}
const fixture=[
 {id:12,code:'6.15',name:'Prospecto de admisión',category:'admision',office:{id:7,name:'Oficina de prueba'},description:'Concepto de prueba'},
 {id:13,code:'MAT',name:'Matrícula de Maestría',category:'matricula_pensiones',office:{id:7,name:'Oficina de prueba'},description:'Concepto de prueba'}
];
async function boot(page,{authenticated=false,complete=true,resume=false,local=new Map(),catalog=fixture,failCatalog=false,officeList=[{id:7,name:'Oficina de prueba'}],cart={items:[],items_count:0},search='',profile={first_names:'Ana',last_names:'Torres',document_type:'DNI',document_number:'12345678',contact_email:'ana@example.test'}}={}){
 const file={catalog:'tienda',cart:'modules/tienda/cart',login:'modules/acceso/login'}[page];
 const html=expand(read('resources/views/'+file+'.blade.php'))+expand(read('resources/views/layouts/market.blade.php'));
 const ids=new Map([...html.matchAll(/\bid="([^"]+)"/g)].map(m=>[m[1],new Element()]));
 const body=new Element();body.dataset.marketPage=page;const assigned=[];
 const storage=new Map(resume?[['undc_resume_purchase','1']]:[]);
 const store=m=>({getItem:k=>m.get(k)||null,setItem:(k,v)=>m.set(k,v),removeItem:k=>m.delete(k)});
 const document={body,documentElement:new Element(),dispatchEvent(){},getElementById:id=>ids.get(id)||null,querySelector:s=>s.startsWith('meta')?new Element():s==='.page-grid'||s==='.cart-review-panel'?new Element():null,querySelectorAll:()=>[],addEventListener(){},createElement:()=>new Element()};
 const calls=[];
 const fetch=async(url,options={})=>{
  calls.push({url,...options});
  if(url.endsWith('/estado')&&!authenticated)return {status:401,ok:false,json:async()=>({})};
  if(url.includes('/catalogo?')&&failCatalog)return {status:500,ok:false,json:async()=>({message:'Error de catálogo'})};
  let data;
  if(url.endsWith('/catalogo/oficinas'))data=officeList;
  else if(url.includes('/catalogo?'))data=catalog;
  else if(url.endsWith('/estado'))data={profile_complete:complete};
  else if(url.endsWith('/resumen'))data={cart:null};
  else if(url.endsWith('/perfil'))data=profile;
  else if(url.endsWith('/carritos'))data={token:'a'.repeat(64),cart};
  else if(url.endsWith('/items')&&options.method==='POST'){
   const body=JSON.parse(options.body);const item=catalog.find(item=>item.id===body.catalog_item_id);
   cart.items=[{id:99,catalog_item_id:body.catalog_item_id,quantity:body.quantity,slug:item?.name||'concepto'}];cart.items_count=body.quantity;data=cart;
  }else if(options.method==='DELETE'){cart.items=[];cart.items_count=0;data=cart}
  else data=cart;
  return {status:200,ok:true,json:async()=>({success:true,data})};
 };
 const context=vm.createContext({document,window:{addEventListener(){},location:{search,assign:u=>assigned.push(u)},scrollTo(){},getSelection(){return null}},localStorage:store(local),sessionStorage:store(storage),CustomEvent:class{constructor(type,options){this.type=type;this.detail=options.detail}},URLSearchParams,Intl,MutationObserver:class{observe(){}},setTimeout:()=>0,clearTimeout(){},fetch});
 const source=read('public/js/undc-tienda.js').replace(/\}\)\(\);\s*$/, 'globalThis.marketTest={openConceptDetail,selectCatalogCategory,screen,renderCart,renderTusneCatalog,addItem,removeItem,loadCatalog,loadOffices,showPaymentPreview,get tusneConcepts(){return tusneConcepts},catalogSuggestions,chooseSearchSuggestion};})();');
 vm.runInContext(source,context);await new Promise(resolve=>setImmediate(resolve));
 return {ids,assigned,storage,context,html,calls};
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

test('published API concepts replace browser references and add uses numeric database ID',async()=>{
 const local=new Map([['undc_tusne_reference_cart','["6.15"]']]);
 const app=await boot('catalog',{local});
 assert.match(app.ids.get('tusneResults').innerHTML,/Prospecto de admisión/);
 assert.equal(app.ids.get('catalogOfficeControls').hidden,true);
 assert.equal(app.ids.get('catalogStatus').hidden,true);
 assert.equal(app.ids.get('catalogRetry').hidden,true);
 assert.match(app.ids.get('tusneResults').innerHTML,/data-concept-detail="12"/);
 assert.match(app.ids.get('tusneResults').innerHTML,/Por confirmar/);
 assert.ok(app.calls.some(call=>call.url==='/api/v1/tienda/catalogo?office_id=7'));
 await app.context.marketTest.addItem('12');
 const call=app.calls.find(call=>call.url.endsWith('/items')&&call.method==='POST');
 assert.deepEqual(JSON.parse(call.body),{catalog_item_id:12,quantity:1});
 assert.equal(call.headers.Authorization,'Bearer '+'a'.repeat(64));
 assert.equal(app.ids.get('cartBadge').textContent,1);
 app.context.marketTest.openConceptDetail('12');
 assert.match(app.ids.get('conceptDialogContent').innerHTML,/Agregado al carrito/);
 await app.context.marketTest.removeItem(99);
 assert.equal(app.ids.get('cartBadge').textContent,0);
 assert.equal(local.get('undc_tusne_reference_cart'),'["6.15"]','old references are ignored, never promoted to purchases');
});
test('empty or failed API never falls back to hardcoded products',async()=>{
 const empty=await boot('catalog',{catalog:[]});
 assert.equal(empty.ids.get('tusneResults').innerHTML,'');
 assert.equal(empty.ids.get('tusneEmpty').hidden,false);
 const failed=await boot('catalog',{failCatalog:true});
 assert.equal(failed.ids.get('tusneResults').innerHTML,'');
 assert.equal(failed.ids.get('catalogRetry').hidden,false);
 assert.match(failed.ids.get('catalogStatus').textContent,/No se pudo/);
 assert.equal(failed.ids.get('catalogStatus').hidden,false);
});
test('offices are explicit and an arbitrary office 1 is never assumed',async()=>{
 const app=await boot('catalog',{officeList:[{id:7,name:'Primera'},{id:9,name:'Segunda'}]});
 assert.ok(!app.calls.some(call=>call.url.includes('/catalogo?')));
 assert.equal(app.ids.get('catalogOfficeControls').hidden,false);
 app.ids.get('catalogOffice').value='9';
 await app.ids.get('catalogOffice').listeners.change();
 await new Promise(resolve=>setImmediate(resolve));
 assert.ok(app.calls.some(call=>call.url.endsWith('office_id=9')));
});
test('API text is escaped before rendering cards',async()=>{
 const app=await boot('catalog',{catalog:[{...fixture[0],name:'<img src=x onerror=alert(1)>',description:'<script>bad()</script>'}]});
 const html=app.ids.get('tusneResults').innerHTML;
 assert.ok(!html.includes('<img src=x'));
 assert.match(html,/&lt;img/);
 app.context.marketTest.openConceptDetail('12');
 assert.match(app.ids.get('conceptDialogContent').innerHTML,/&lt;script&gt;/);
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


test('six top categories filter API concepts, survive reload and reset with Para ti',async()=>{
 const app=await boot('catalog',{catalog:[...fixture,{...fixture[0],id:14,name:'Carné de biblioteca',category:'servicios_administrativos'}]});
 const api=app.context.marketTest;
 api.selectCatalogCategory('servicios_administrativos');
 assert.match(app.ids.get('tusneResults').innerHTML,/Carné de biblioteca/);
 assert.doesNotMatch(app.ids.get('tusneResults').innerHTML,/Prospecto de admisión/);
 assert.equal(app.ids.get('catalogResultsTitle').textContent,'Servicios administrativos');
 await api.loadCatalog();
 assert.equal(app.ids.get('catalogResultsTitle').textContent,'Servicios administrativos');
 api.selectCatalogCategory('tesis_grados');
 assert.equal(app.ids.get('tusneEmpty').hidden,false);
 api.selectCatalogCategory('all');
 assert.match(app.ids.get('tusneResults').innerHTML,/Prospecto de admisión/);
 const login=await boot('login');login.context.marketTest.selectCatalogCategory('tramites');
 assert.deepEqual(login.assigned,['/tienda?category=tramites']);
 for(const label of ['Para ti','Admisión','Matrícula y pensiones','Trámites académicos','Certificados y constancias','Tesis y grados','Servicios administrativos'])assert.ok(app.html.includes(label));
});

test('search stays collapsed until magnifier click and Escape closes it',async()=>{
 const app=await boot('catalog');const form=app.ids.get('headerSearch'),toggle=app.ids.get('catalogSearchToggle');
 assert.equal(form.hidden,true);
 toggle.listeners.click();
 assert.equal(form.hidden,false);assert.equal(toggle.attrs['aria-expanded'],'true');
 app.ids.get('search').value='matricula';app.ids.get('search').listeners.input();
 assert.match(app.ids.get('tusneResults').innerHTML,/Matrícula/);
 form.listeners.keydown({key:'Escape',preventDefault(){}});
 assert.equal(form.hidden,true);assert.equal(toggle.attrs['aria-expanded'],'false');
 assert.equal(app.ids.get('searchSuggestions').hidden,true);
 toggle.listeners.click();toggle.listeners.click();assert.equal(form.hidden,true);
});


const categoriasEspanol={admission:'admision',tuition:'matricula_pensiones',transfers:'tramites',certificates:'certificados_constancias',degrees:'tesis_grados',administrative:'servicios_administrativos'};
for(const [anterior,actual] of Object.entries(categoriasEspanol))test('categoría '+actual+' filtra y conserva enlaces anteriores',async()=>{
 const catalog=[{...fixture[0],category:actual},{...fixture[1],category:'categoria_personalizada'}];
 const app=await boot('catalog',{catalog,search:'?category='+actual});
 assert.equal(app.ids.get('visibleConceptCount').textContent,'1 opción visible');
 assert.match(app.ids.get('tusneResults').innerHTML,/Prospecto de admisión/);
 assert.doesNotMatch(app.ids.get('tusneResults').innerHTML,/Matrícula de Maestría/);
 const legacy=await boot('catalog',{catalog:[{...fixture[0],category:anterior}],search:'?category='+anterior});
 assert.equal(legacy.context.marketTest.tusneConcepts[0].category,actual);
 assert.equal(legacy.ids.get('visibleConceptCount').textContent,'1 opción visible');
 const login=await boot('login');login.context.marketTest.selectCatalogCategory(anterior);
 assert.deepEqual(login.assigned,['/tienda?category='+actual]);
 assert.ok(app.html.includes('data-category-nav="'+actual+'"'));
 assert.ok(!app.html.includes('category='+anterior+'#'));
});
test('categorías personalizadas y vacías permanecen visibles en Otros conceptos',async()=>{
 const app=await boot('catalog',{catalog:[{...fixture[0],category:'personalizada'},{...fixture[1],category:null}]});
 assert.ok(app.context.marketTest.tusneConcepts.every(item=>item.category==='general'));
 assert.equal(app.ids.get('visibleConceptCount').textContent,'2 opciones visibles');
});


test('product cards contain only image, name and formatted reference price',async()=>{
 const app=await boot('catalog',{catalog:[{...fixture[0],reference_amount:'300.00'}]});
 const html=app.ids.get('tusneResults').innerHTML;
 assert.match(html,/catalog-product-image/);
 assert.match(html,/catalog-product-name/);
 assert.match(html,/S\/ 300\.00/);
 for(const removed of ['category-tag','concept-description','concept-icon','concept-actions','data-catalog-cart','Ver detalles</','Importe</'])assert.ok(!html.includes(removed),removed);
 app.context.marketTest.openConceptDetail('12');
 assert.equal(app.ids.get('conceptDialog').open,true);
 const detail=app.ids.get('conceptDialogContent').innerHTML;
 assert.match(detail,/Concepto de prueba/);
 assert.match(detail,/Importe referencial/);
 assert.match(detail,/data-catalog-cart="12"/);
});
for(const amount of [null,undefined,'',-1,'-1.00','<script>','NaN',true])test('invalid reference amount stays pending: '+String(amount),async()=>{
 const app=await boot('catalog',{catalog:[{...fixture[0],reference_amount:amount}]});
 assert.match(app.ids.get('tusneResults').innerHTML,/Por confirmar/);
});
test('zero reference amount is displayed without enabling checkout',async()=>{
 const app=await boot('catalog',{catalog:[{...fixture[0],reference_amount:'0.00',checkout_enabled:false}]});
 assert.match(app.ids.get('tusneResults').innerHTML,/S\/ 0\.00/);
 assert.ok(!app.calls.some(call=>call.url.includes('/pagos')));
});


test('the top navigation is the only category menu and filtering still renders products',async()=>{
 const app=await boot('catalog');
 assert.equal(app.ids.has('catalogFilters'),false);
 assert.ok(!app.html.includes('data-category-filter='));
 assert.ok(app.html.includes('data-category-nav="matricula_pensiones"'));
 app.context.marketTest.selectCatalogCategory('matricula_pensiones');
 assert.match(app.ids.get('tusneResults').innerHTML,/Matrícula de Maestría/);
 assert.doesNotMatch(app.ids.get('tusneResults').innerHTML,/Prospecto de admisión/);
 assert.equal(app.ids.get('catalogResultsTitle').textContent,'Matrícula y pensiones');
 app.context.marketTest.selectCatalogCategory('all');
 assert.equal(app.ids.get('visibleConceptCount').textContent,'2 opciones visibles');
});


test('category title is positioned below the measured sticky header',async()=>{
 const app=await boot('catalog'),scrolled=[];
 app.context.window.innerHeight=768;
 app.context.window.scrollY=200;
 app.context.window.scrollTo=options=>scrolled.push(options);
 app.context.window.matchMedia=()=>({matches:true});
 const query=app.context.document.querySelector;
 app.context.document.querySelector=s=>s==='.catalog-results-heading'?{getBoundingClientRect:()=>({top:500})}:s==='.site-header'?{getBoundingClientRect:()=>({height:144})}:query(s);
 app.context.marketTest.selectCatalogCategory('matricula_pensiones');
 assert.equal(scrolled.at(-1).top,544);
 assert.equal(scrolled.at(-1).behavior,'instant');
 assert.equal(app.ids.get('tusneCatalog').style.minHeight,'612px');
 app.context.marketTest.selectCatalogCategory('all');
 assert.equal(scrolled.at(-1).top,0);
 assert.equal(app.ids.get('tusneCatalog').style.minHeight,'');
});


test('cart displays API names and reference totals without loading the catalog page',async()=>{
 const cart={items:[{id:1,catalog_item_id:101,slug:'posgrado-6-15',name:'Matrícula de Doctorado',reference_amount:'125.10',quantity:2},{id:2,catalog_item_id:102,name:'Prospecto',reference_amount:'10.25',quantity:1}],items_count:3,checkout_enabled:false,total_amount:null};
 const app=await boot('cart',{cart});
 const rows=app.ids.get('cartRows').children;
 assert.equal(rows[0].children[1].children[0].textContent,'Matrícula de Doctorado');
 assert.match(rows[0].children[2].textContent,/250[,.]20/);
 assert.match(app.ids.get('cartSummaryEstimate').textContent,/260[,.]45/);
 assert.ok(!app.calls.some(call=>call.url.includes('/catalogo?')));
 assert.equal(cart.total_amount,null);assert.equal(cart.checkout_enabled,false);
});
test('cart never invents a total when a reference amount is missing or invalid',async()=>{
 for(const amount of [null,undefined,'-1.00','<script>','',true]){
  const app=await boot('cart',{cart:{items:[{id:1,catalog_item_id:101,name:'Concepto',reference_amount:amount,quantity:1}],items_count:1}});
  assert.equal(app.ids.get('cartSummaryEstimate').textContent,'Por confirmar');
  assert.equal(app.ids.get('cartRows').children[0].children[2].textContent,'Por confirmar');
 }
});


test('payment review retains cart reference amounts and quantities before the approved quote',async()=>{
 const cart={id:7,items:[{id:1,catalog_item_id:101,name:'<script>Concepto</script>',reference_amount:'125.10',quantity:2},{id:2,catalog_item_id:102,name:'Prospecto',reference_amount:'10.25',quantity:1}],items_count:3,checkout_enabled:false,total_amount:null};
 const app=await boot('cart',{cart});
 await app.context.marketTest.showPaymentPreview();
 const rows=app.ids.get('paymentPreviewItems').children;
 assert.equal(rows[0].children[0].textContent,'<script>Concepto</script>');
 assert.equal(rows[0].children[0].children[0].textContent,'Posgrado · Cantidad 2');
 assert.equal(rows[0].children[1].textContent,app.ids.get('cartRows').children[0].children[2].textContent);
 assert.match(rows[0].children[1].textContent,/250[,.]20/);
 assert.equal(app.ids.get('paymentPreviewTotal').textContent,app.ids.get('cartSummaryEstimate').textContent);
 assert.match(app.ids.get('paymentPreviewTotal').textContent,/260[,.]45/);
 assert.equal(cart.total_amount,null);assert.equal(cart.checkout_enabled,false);
 assert.equal(app.ids.get('paymentBuyerInitials').textContent,'AT');
 assert.equal(app.ids.get('paymentBuyerDocument').textContent,'DNI •••• 5678');
 assert.equal(app.ids.get('paymentSelectionCount').textContent,'2 conceptos');
});
test('payment review keeps unknown totals pending instead of showing a partial sum',async()=>{
 for(const amount of [null,undefined,'-1.00','<script>','',true]){
  const app=await boot('cart',{cart:{items:[{id:1,catalog_item_id:101,name:'Disponible',reference_amount:'10.25',quantity:1},{id:2,catalog_item_id:102,name:'Pendiente',reference_amount:amount,quantity:1}],items_count:2}});
  await app.context.marketTest.showPaymentPreview();
  assert.match(app.ids.get('paymentPreviewItems').children[0].children[1].textContent,/10[,.]25/);
  assert.equal(app.ids.get('paymentPreviewItems').children[1].children[1].textContent,'Por confirmar');
  assert.equal(app.ids.get('paymentPreviewTotal').textContent,'Por confirmar');
 }
});
