import { chromium } from 'playwright';
import assert from 'node:assert/strict';

const origin = process.env.TEST_BASE_URL || 'http://127.0.0.1:8000';
const browser = await chromium.launch({headless:true});
let failures=0;

async function scenario(viewport, name) {
 const page = await browser.newPage({viewport});
 const errors=[];
 page.on('pageerror', e=>errors.push(e.message));
 let authenticated=false, profileComplete=false, claimed=false, token='a'.repeat(64), cart={items:[],items_count:0}, profile=null;
 const json=(data,status=200)=>({status,contentType:'application/json',body:JSON.stringify(data)});
 await page.route('**/api/v1/**',async route=>{
  const req=route.request(),url=new URL(req.url()),path=url.pathname,method=req.method();
  let data=null,status=200;
  if(path==='/api/v1/acceso/estado'){if(!authenticated){status=401;data={message:'No autenticado'};}else data={authenticated:true,profile_complete:profileComplete};}
  else if(path==='/api/v1/tienda/catalogo'){data=[{id:101,name:'Constancia de estudios',code:'POS-001',description:'Trámite académico de Posgrado'}];}
  else if(path==='/api/v1/tienda/carritos'&&method==='POST'){data={token,cart};status=201;}
  else if(path==='/api/v1/tienda/carritos/actual'&&method==='GET'){if(claimed){status=404;data={message:'Carrito no disponible'}}else data=cart;}
  else if(path==='/api/v1/tienda/carritos/actual/items'&&method==='POST'){const payload=req.postDataJSON();cart={items:[{id:11,slug:'constancia-estudios',catalog_item_id:payload.catalog_item_id,quantity:payload.quantity}],items_count:payload.quantity};data=cart;}
  else if(path.startsWith('/api/v1/tienda/carritos/actual/items/')&&method==='DELETE'){cart={items:[],items_count:0};data=cart;}
  else if(path==='/api/v1/tienda/precompra/vincular-carrito'&&method==='POST'){assert.equal(req.postDataJSON().cart_token,token);claimed=true;data=cart;}
  else if(path==='/api/v1/tienda/precompra/resumen'){data={profile_complete:profileComplete,profile,cart:claimed?cart:null,next_step:profileComplete?'review':'complete_profile',checkout_enabled:false,total_amount:null};}
  else if(path==='/api/v1/acceso/perfil'&&method==='PUT'){profile=req.postDataJSON();profileComplete=true;data=profile;}
  else {status=404;data={message:'Ruta no simulada: '+path};}
  await route.fulfill(json({success:status<400,data,message:status>=400?data.message:'OK'},status));
 });
 await page.route('**/auth/google/redirect',async route=>{authenticated=true;await route.fulfill({status:302,headers:{location:'/tienda'},body:''})});
 try{
  await page.goto(origin+'/tienda',{waitUntil:'domcontentloaded'});
  await page.locator('#office').fill('7');await page.locator('#load').click();
  await page.getByRole('heading',{name:'Constancia de estudios'}).waitFor();
  await page.locator('#search').fill('inexistente');await assert.equal(await page.locator('.product').count(),0);
  await page.locator('#search').fill('constancia');
  await page.locator('.product button').click();
  await page.locator('#cartBadge').getByText('1').waitFor();
  await page.locator('#cartButton').click();
  await page.locator('[data-screen="cart"].visible').waitFor();
  await page.locator('#cartContinue').click();
  await page.locator('#loginBox:visible').waitFor();
  await page.locator('#googleButton').click();
  await page.locator('#profileBox:visible').waitFor({timeout:10000});
  await page.locator('[name="first_names"]').fill('Persona');
  await page.locator('[name="last_names"]').fill('Prueba');
  await page.locator('[name="document_number"]').fill('00000000');
  await page.locator('#profileForm button[type="submit"]').click();
  await page.locator('[data-screen="review"].visible').waitFor();
  assert.equal(await page.locator('#buyerName').textContent(),'Persona Prueba');
  assert.equal(await page.locator('[data-screen="review"] button:disabled').count(),1);
  await page.reload({waitUntil:'domcontentloaded'});
  await page.locator('#cartButton').click();
  await page.locator('#cartTotalCount').getByText('1 conceptos').waitFor();
  assert.deepEqual(errors,[]);
  const overflow=await page.evaluate(()=>document.documentElement.scrollWidth>innerWidth+2);
  assert.equal(overflow,false,'No debe existir desbordamiento horizontal');
  console.log('PASS browser '+name+': catálogo, búsqueda, carrito, login simulado, registro, revisión, regreso y responsive');
 }catch(e){failures++;console.error('FAIL browser '+name+': '+e.stack);await page.screenshot({path:'/tmp/undc-browser-'+name+'.png',fullPage:true}).catch(()=>{});}
 finally{await page.close();}
}
await scenario({width:1440,height:900},'desktop');
await scenario({width:390,height:844},'mobile');
await browser.close();
if(failures)process.exit(1);
