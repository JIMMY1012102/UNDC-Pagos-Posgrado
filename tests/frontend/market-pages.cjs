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
 addEventListener(k,f){this.listeners[k]=f} setAttribute(k,v){this.attrs[k]=v} removeAttribute(k){delete this.attrs[k]} toggleAttribute(){} append(...els){this.children.push(...els)} replaceChildren(){this.children=[]} focus(){} contains(){return false}
}
async function boot(page,{authenticated=false,complete=true,resume=false,local=new Map()}={}){
 const file={catalog:'tienda',cart:'modules/tienda/cart',login:'modules/acceso/login'}[page];
 const html=expand(read('resources/views/'+file+'.blade.php'))+expand(read('resources/views/layouts/market.blade.php'));
 const ids=new Map([...html.matchAll(/\bid="([^"]+)"/g)].map(m=>[m[1],new Element()]));
 const body=new Element();body.dataset.marketPage=page;const assigned=[];
 const storage=new Map(resume?[['undc_resume_purchase','1']]:[]);
 const store=m=>({getItem:k=>m.get(k)||null,setItem:(k,v)=>m.set(k,v),removeItem:k=>m.delete(k)});
 const document={body,documentElement:new Element(),getElementById:id=>ids.get(id)||null,querySelector:s=>s.startsWith('meta')?new Element():s==='.page-grid'||s==='.cart-review-panel'?new Element():null,querySelectorAll:()=>[],addEventListener(){},createElement:()=>new Element()};
 const context=vm.createContext({document,window:{addEventListener(){},location:{search:'',assign:u=>assigned.push(u)},scrollTo(){},getSelection(){return null}},localStorage:store(local),sessionStorage:store(storage),URLSearchParams,MutationObserver:class{observe(){}},setTimeout:()=>0,clearTimeout(){},fetch:async url=>({status:url.endsWith('/estado')&&!authenticated?401:200,ok:!url.endsWith('/estado')||authenticated,json:async()=>({data:url.endsWith('/estado')?{profile_complete:complete}:url.endsWith('/resumen')?{cart:null}:{token:'test-token',cart:{items:[],items_count:0}}})})});
 const source=read('public/js/undc-tienda.js').replace(/\}\)\(\);\s*$/, 'globalThis.marketTest={screen,renderCart,renderTusneCatalog,toggleReferenceCart,tusneConcepts};})();');
 vm.runInContext(source,context);await new Promise(resolve=>setImmediate(resolve));
 return {ids,assigned,storage,context,html};
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
