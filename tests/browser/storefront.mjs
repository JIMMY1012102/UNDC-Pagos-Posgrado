import { chromium, firefox } from 'playwright';
import assert from 'node:assert/strict';
import {readFileSync, mkdirSync} from 'node:fs';

// Browser smoke test of the current three-page frontend.
// Backend + MariaDB journey is independently tested in PHP CI.
const origin = process.env.TEST_BASE_URL || 'http://127.0.0.1:8000';
const firefoxRegisterOnly = process.env.REGISTER_FIREFOX_ONLY === '1';
const browserEngine = firefoxRegisterOnly ? firefox : chromium;
const browser = await browserEngine.launch({ headless: true, executablePath: firefoxRegisterOnly ? undefined : (process.env.PLAYWRIGHT_CHROMIUM_EXECUTABLE_PATH || undefined) });
let failures = 0;

async function scenario(viewport, name) {
  const page = await browser.newPage({ viewport, reducedMotion: 'reduce' });
  const exceptions = [];
  page.on('pageerror', (error) => exceptions.push(error.message));

  const mobile = viewport.width <= 940;
  async function selectCategory(category) {
    const menu=page.locator('.store-posgrado-menu');
    if(await menu.getAttribute('open') === null)await menu.locator('summary').click();
    await menu.locator(`[data-category-nav="${category}"]`).click();

  }

  try {
    // Deterministic publications keep navigation checks independent of DB setup.
    await page.route('**/api/v1/tienda/catalogo/oficinas', route => route.fulfill({ json: {
      success: true, data: [{ id: 7, name: 'Posgrado de prueba' }],
    } }));
    await page.route('**/api/v1/tienda/catalogo?*', route => route.fulfill({ json: {
      success: true, data: [
        { id: 12, name: 'Examen de prueba', category: 'admision', reference_amount: '300.00' },
        { id: 13, name: 'Biblioteca de prueba', category: 'servicios_administrativos', reference_amount: '10.00' },
      ],
    } }));
    const catalog = await page.goto(origin + '/tienda/catalogo', { waitUntil: 'domcontentloaded' });
    assert.equal(catalog?.status(), 200, 'El catálogo debe responder HTTP 200');
    await page.locator('#catalogResultsTitle').waitFor();
    assert.equal(await page.locator('#tusneTitle').count(), 0, 'El título Nuestros trámites se eliminó del catálogo');
    assert.equal(await page.locator('#tusneCatalog').getAttribute('aria-labelledby'), 'catalogResultsTitle');
    assert.equal(await page.locator('link[href*="css/undc-tienda.css"]').count(), 1);
    assert.equal(await page.locator('script[src*="js/undc-tienda.js"]').count(), 1);

    const floatingA11y = page.locator('#accessibilityFloatingButton');
    await floatingA11y.waitFor();
    const a11yBox = await floatingA11y.boundingBox();
    assert.ok(a11yBox.x > viewport.width - 90 && a11yBox.y > viewport.height - 90, 'Accesibilidad debe estar fija abajo a la derecha');
    assert.equal(await page.locator('.utility-controls [data-accessibility-toggle]').count(), 0);
    await floatingA11y.click();
    assert.equal(await page.locator('#accessibilityPanel').isVisible(), true);
    await page.keyboard.press('Escape');
    assert.equal(await page.locator('#accessibilityPanel').isVisible(), false);
    assert.equal(await floatingA11y.evaluate(el => el === document.activeElement), true);

    assert.equal(await page.locator('#marketHome, #hero').count(), 0, 'Inicio y catálogo son pantallas separadas');
    await page.waitForFunction(() => document.querySelector('#tusneResults').textContent.includes('Examen de prueba'));
    await selectCategory('servicios_administrativos');
    assert.match(await page.locator('#tusneResults').innerText(), /Biblioteca de prueba/);
    await page.waitForFunction(() => {
      const header = document.querySelector('.site-header').getBoundingClientRect();
      const results = document.querySelector('.catalog-results-heading').getBoundingClientRect();
      return Math.abs(results.top - header.bottom - 12) < 3;
    });

    assert.doesNotMatch(await page.locator('#tusneResults').innerText(), /Examen de prueba/);
    await selectCategory('tesis_grados');
    assert.equal(await page.locator('#tusneEmpty').isVisible(), true);
    await selectCategory('all');
    assert.match(await page.locator('#tusneResults').innerText(), /Examen de prueba/);
    assert.equal(await page.locator('#catalogFilters, [data-category-filter]').count(), 0, 'Solo debe existir el menú superior de categorías');
    const product = page.locator('.catalog-product').first();
    assert.equal(await product.locator('img').count(), 1);
    assert.equal(await product.locator('.catalog-product-name').innerText(), 'Examen de prueba');
    assert.equal((await product.locator('.catalog-product-price').innerText()).replace(/\s+/g,' '), 'S/ 300.00');
    assert.equal(await product.locator('.category-tag, .concept-description, .concept-actions, [data-catalog-cart]').count(), 0);
    const imageBox = await product.locator('.catalog-product-image').boundingBox();
    const titleBox = await product.locator('.catalog-product-name').boundingBox();
    const priceBox = await product.locator('.catalog-product-price').boundingBox();
    assert.ok(imageBox.y + imageBox.height <= titleBox.y, 'La imagen debe ir encima del nombre');
    assert.ok(titleBox.y + titleBox.height <= priceBox.y, 'El precio debe ir debajo del nombre');
    await product.locator('.catalog-product-link').focus();
    await page.keyboard.press('Enter');
    assert.equal(await page.locator('#conceptDialog').isVisible(), true);
    assert.equal(await page.locator('#conceptDialogContent [data-catalog-cart]').count(), 1);
    await page.keyboard.press('Escape');
    assert.equal(await page.locator('#conceptDialog').isVisible(), false);
    await page.locator('#search').fill('Biblioteca');
    assert.match(await page.locator('#tusneResults').innerText(), /Biblioteca de prueba/);
    assert.doesNotMatch(await page.locator('#tusneResults').innerText(), /Examen de prueba/);
    await page.locator('#search').press('Escape');
    assert.equal(await page.evaluate(() => document.documentElement.scrollWidth > innerWidth + 2), false,
      'El catálogo no debe desbordarse horizontalmente');

    const cart = await page.goto(origin + '/tienda/carrito', { waitUntil: 'domcontentloaded' });
    assert.equal(cart?.status(), 200, 'El carrito debe responder HTTP 200');
    await page.locator('#sideContinue').waitFor({ state: 'attached' });
    assert.match(await page.locator('.cart-modern-heading h1').innerText(), /Revisa tu carrito/);
    assert.equal(await page.locator('#sideContinue').isDisabled(), true,
      'No puede continuarse con un carrito vacío');

    // Review uses intercepted API data; no institutional data or real order is created.
    const profile={first_names:'Ana',last_names:'Torres',document_type:'DNI',document_number:'12345678',contact_email:'ana@example.test'};
    const reviewCart={id:7,items_count:3,items:[
      {id:1,catalog_item_id:12,name:'DERECHO DE MATRICULA MAESTRIA',quantity:1,reference_amount:'300.00'},
      {id:2,catalog_item_id:13,name:'RECARGO POR MATRICULA EXTEMPORANEA DOCTORADO',quantity:1,reference_amount:'60.00'},
      {id:3,catalog_item_id:14,name:'RECARGO POR MATRICULA EXTEMPORANEA MAESTRIA',quantity:1,reference_amount:'60.00'},
    ]};
    await page.route('**/api/v1/acceso/estado',route=>route.fulfill({json:{data:{profile_complete:true}}}));
    await page.route('**/api/v1/acceso/perfil',route=>route.fulfill({json:{data:profile}}));
    await page.route('**/api/v1/tienda/precompra/resumen',route=>route.fulfill({json:{data:{cart:reviewCart,profile,profile_complete:true,next_step:'review'}}}));
    await page.route('**/api/v1/tienda/pedidos/cotizar',route=>route.fulfill({status:422,json:{message:'Uno o más conceptos requieren una tarifa vigente aprobada o la tarifa encontrada no es válida. No se generó el pedido.'}}));
    await page.goto(origin+'/tienda/carrito',{waitUntil:'domcontentloaded'});
    await page.waitForFunction(()=>document.getElementById('cartSummaryCount').textContent==='3');
    assert.match(await page.locator('#cartSummaryEstimate').innerText(),/420[,.]00/);
    assert.equal(await page.locator('#cartRows .cart-service-row').count(),3);
    assert.equal(await page.locator('#cartRows .cart-service-icon').first().innerText(),'01');
    assert.equal(await page.evaluate(()=>document.documentElement.scrollWidth>innerWidth+2),false,'El carrito no debe desbordarse horizontalmente');
    await page.locator('.cart-modern-footer').scrollIntoViewIfNeeded();
    assert.equal(await page.locator('.cart-modern-footer').isVisible(),true,'Las acciones del carrito deben ser accesibles con scroll');
    await page.locator('#sideContinue').click();
    await page.locator('#paymentPreviewTitle').waitFor({state:'visible'});
    assert.equal(await page.locator('[data-screen="review"]').count(),0,'No debe existir una pantalla antigua intermedia de revisión');
    await page.waitForFunction(()=>document.getElementById('orderQuoteMessage').textContent.includes('No se generó'));
    assert.equal(await page.locator('#paymentBuyerName').innerText(),'Ana Torres');
    assert.equal(await page.locator('#paymentBuyerDocument').innerText(),'DNI •••• 5678');
    assert.match(await page.locator('#paymentPreviewTotal').innerText(),/420[,.]00/);
    assert.equal(await page.locator('#paymentPreviewItems .payment-preview-line').count(),3);
    assert.equal(await page.locator('#generateOrder').isDisabled(),true);
    assert.equal(await page.locator('.market-social-rail').count(),0);
    assert.equal(await page.evaluate(()=>document.documentElement.scrollWidth>innerWidth+2),false,'La revisión no debe desbordarse horizontalmente');
    await page.locator('[data-screen="payment"] .payment-review-footer').scrollIntoViewIfNeeded();
    assert.equal(await page.locator('[data-screen="payment"] .payment-review-footer').isVisible(),true);
    await page.locator('#editPaymentBuyer').click();
    assert.equal(await page.locator('#firstNames').inputValue(),'Ana');
    assert.equal(await page.locator('#docNumber').inputValue(),'12345678');
    // Authenticated page shell and API responses are intercepted for browser-only QA.
    await page.locator('[data-screen="identity"] [data-go="cart"]').click();
    await page.unroute('**/api/v1/tienda/pedidos/cotizar');
    const order={code:'UNDC-01ABCDEFGHIJKLMNOPQRSTUVWX',status:'pending',currency:'PEN',total_amount:'420.00',created_at:'2026-11-13 12:00:00',expires_at:'2026-11-14 12:00:00',paid_at:null,can_modify:true,buyer_name:'Ana Torres',buyer_email:'ana@example.test',payment_enabled:false,items:reviewCart.items.map(item=>({concept_name:item.name,concept_code:'TEST',quantity:item.quantity,unit_amount:item.reference_amount,total_amount:item.reference_amount})),history:[{to_status:'pending',reason:'Pedido generado con importes oficiales verificados.',occurred_at:'2026-11-13 12:00:00'}]};
    await page.route('**/api/v1/tienda/pedidos/cotizar',route=>route.fulfill({json:{data:{items:reviewCart.items.map(item=>({name:item.name,quantity:item.quantity,total_amount:item.reference_amount})),total_amount:'420.00'}}}));
    await page.route('**/api/v1/tienda/pedidos',route=>route.fulfill({json:{data:route.request().method()==='POST'?order:{items:[order],page:1,has_more:false}}}));
    await page.route('**/api/v1/tienda/pedidos?*',route=>route.fulfill({json:{data:{items:[order],page:1,has_more:false}}}));
    await page.route('**/api/v1/tienda/pedidos/UNDC-*',route=>route.fulfill({json:{data:order}}));
    const template=readFileSync(new URL('../../resources/views/modules/tienda/orders.blade.php',import.meta.url),'utf8');
    const content=template.split("@section('content')")[1].split('@endsection')[0].replace(/\{\{ asset\('([^']+)'\) \}\}/g,(_,file)=>'/'+file).replace(/\?v=\{\{[^}]+\}\}/g,'');
    const shell=(await page.content()).replace(/<main\b[^>]*>[\s\S]*?<\/main>/,'<main class="wrap" id="catalogo">'+content+'</main>').replace('data-market-page="cart"','data-market-page="orders"').replace(/<body class="[^"]*"/,'<body class="commerce-view market-cart-view market-orders-view light-mode"');
    await page.route('**/tienda/mis-pedidos*',route=>route.fulfill({contentType:'text/html',body:shell}));
    await page.locator('#sideContinue').click();
    await page.waitForFunction(()=>!document.getElementById('generateOrder').disabled);
    assert.match(await page.locator('#orderQuoteMessage').innerText(),/oficiales verificados/);
    await page.locator('#generateOrder').click();
    await page.locator('#orderDetailPanel').waitFor({state:'visible'});
    assert.match(await page.locator('#orderDetail').innerText(),/420[,.]00/);
    assert.equal(await page.locator('.order-concepts .payment-preview-line').count(),3);
    assert.equal(await page.locator('.order-pay-button').isDisabled(),true);
    assert.equal(await page.getByRole('button',{name:'Modificar pedido',exact:true}).isVisible(),true);
    assert.equal(await page.evaluate(()=>document.documentElement.scrollWidth>innerWidth+2),false,'El detalle del pedido no debe desbordarse horizontalmente');
    await page.goto(origin+'/tienda/mis-pedidos',{waitUntil:'domcontentloaded'});
    await page.locator('#ordersList .order-row').waitFor();
    assert.equal(await page.locator('#ordersListPanel').isVisible(),true);
    assert.equal(await page.locator('#ordersList .order-row').count(),1);
    await page.getByRole('button',{name:'Ver detalle →',exact:true}).click();
    await page.route('**/api/v1/tienda/pedidos/UNDC-*/modificar',route=>route.fulfill({json:{data:{cart_id:77}}}));
    await page.getByRole('button',{name:'Modificar pedido',exact:true}).click();
    await page.waitForURL('**/tienda/carrito');
    await page.unroute('**/api/v1/tienda/pedidos/UNDC-*/modificar');
    await page.unroute('**/tienda/mis-pedidos*');
    await page.unroute('**/api/v1/tienda/pedidos');
    await page.unroute('**/api/v1/tienda/pedidos/UNDC-*');
    await page.unroute('**/api/v1/tienda/pedidos?*');
    await page.unroute('**/api/v1/acceso/estado');
    await page.unroute('**/api/v1/acceso/perfil');
    await page.unroute('**/api/v1/tienda/precompra/resumen');
    await page.unroute('**/api/v1/tienda/pedidos/cotizar');

    const login = await page.goto(origin + '/tienda/login', { waitUntil: 'domcontentloaded' });
    assert.equal(login?.status(), 200, 'El login debe responder HTTP 200');
    await page.locator('#marketLoginTitle').waitFor();
    assert.match(await page.locator('#marketLoginTitle').innerText(), /Bienvenido/);
    assert.equal(await page.locator('input[name="email"]').count(), 1);
    assert.equal(await page.locator('input[name="password"]').count(), 1);
    assert.equal(await page.locator('#marketLoginGoogle').isDisabled(), true,
      'Google OAuth debe permanecer deshabilitado sin credenciales OTI');

    assert.deepEqual(exceptions, [], 'La interfaz no debe lanzar errores JavaScript');
    const overflow = await page.evaluate(
      () => document.documentElement.scrollWidth > window.innerWidth + 2
    );
    assert.equal(overflow, false, 'No debe existir desbordamiento horizontal');

    console.log('PASS browser ' + name + ': catálogo, carrito sin cobro, login y responsive');
  } catch (error) {
    failures++;
    console.error('FAIL browser ' + name + ': ' + error.stack);
    await page.screenshot({ path: '/tmp/undc-browser-' + name + '.png', fullPage: true }).catch(() => {});
  } finally {
    await page.close();
  }
}

// Responsive checks follow the current split home/catalogue experience.
// The journey home intentionally has no legacy accessibility floating panel.
async function responsiveHome(viewport) {
  const page = await browser.newPage({ viewport, reducedMotion: 'reduce' });
  const exceptions = [];
  page.on('pageerror', error => exceptions.push(error.message));
  try {
    await page.goto(origin + '/tienda', { waitUntil: 'networkidle' });
    await page.locator('#undc-track').waitFor();
    await page.locator('.stage').waitFor();
    await page.locator('.h1').waitFor();
    await page.locator('#q').waitFor();
    await page.locator('.bg img').first().evaluate(img => img.decode());
    const bounds = await page.evaluate(() => {
      const selectors = ['#undc-track', '.stage', '.hdr', '.h1', '.search'];
      const measured = Object.fromEntries(selectors.map(selector => {
        const element = document.querySelector(selector);
        const rect = element.getBoundingClientRect();
        return [selector, { x: rect.x, right: rect.right, y: rect.y, bottom: rect.bottom, width: rect.width, height: rect.height }];
      }));
      const title = document.querySelector('.s1 .h1').getBoundingClientRect();
      const search = document.querySelector('.s1 .search').getBoundingClientRect();
      const stage = document.querySelector('.stage').getBoundingClientRect();
      measured.copySearch = {
        titleBottom: title.bottom, searchTop: search.top,
        stageTop: stage.top, stageBottom: stage.bottom,
        separated: title.bottom <= search.top,
        titleInside: title.top >= stage.top && title.bottom <= stage.bottom,
        searchInside: search.top >= stage.top && search.bottom <= stage.bottom,
      };
      return measured;
    });
    for (const [selector, rect] of Object.entries(bounds)) {
      if (selector === 'copySearch') continue;
      assert.ok(rect.width > 0 && rect.height > 0, selector + ' debe tener dimensiones visibles');
      assert.ok(rect.x >= -1 && rect.right <= viewport.width + 1,
        selector + ' debe permanecer dentro del viewport: ' + JSON.stringify(rect));
    }
    assert.equal(await page.locator('a[href="/tienda/catalogo"]').count() > 0, true,
      'La portada debe ofrecer acceso al catálogo independiente');
    assert.equal(bounds.copySearch.separated, true, 'El título de portada no debe chocar con el buscador');
    assert.equal(bounds.copySearch.titleInside, true, 'El título debe permanecer dentro del escenario');
    assert.equal(bounds.copySearch.searchInside, true, 'El buscador debe permanecer dentro del escenario');
    assert.equal(await page.evaluate(() => document.documentElement.scrollWidth > innerWidth + 2), false,
      'La portada no debe desbordarse horizontalmente');
    assert.deepEqual(exceptions, [], 'La portada no debe lanzar errores JavaScript');
    console.log('PASS home responsive ' + viewport.width + 'px: hero, búsqueda, navegación y sin overflow');
  } catch (error) {
    failures++;
    console.error('FAIL home responsive ' + viewport.width + 'px: ' + error.stack);
  } finally {
    await page.close();
  }
}

// Theme switches are available on the catalogue shell, not on the immersive home.
async function responsiveThemeCatalog(viewport) {
  const page = await browser.newPage({ viewport, reducedMotion: 'reduce' });
  const exceptions = [];
  page.on('pageerror', error => exceptions.push(error.message));
  try {
    await page.route('**/api/v1/tienda/catalogo/oficinas', route => route.fulfill({ json: {
      success: true, data: [{ id: 7, name: 'Posgrado de prueba' }],
    } }));
    await page.route('**/api/v1/tienda/catalogo?*', route => route.fulfill({ json: {
      success: true, data: [
        { id: 12, name: 'Examen de prueba', category: 'admision', reference_amount: '300.00' },
        { id: 13, name: 'Biblioteca de prueba', category: 'servicios_administrativos', reference_amount: '10.00' },
      ],
    } }));
    await page.goto(origin + '/tienda/catalogo', { waitUntil: 'networkidle' });
    await page.locator('.catalog-product').first().waitFor();
    await page.evaluate(() => document.fonts.ready);
    await page.locator('#accessibilityFloatingButton').waitFor();

    async function theme(value) {
      await page.locator('#accessibilityFloatingButton').click();
      await page.locator('[data-accessibility-theme="' + value + '"]').click();
      await page.keyboard.press('Escape');
      await page.evaluate(() => scrollTo(0, 0));
      await page.waitForFunction(value =>
        document.body.classList.contains('light-mode') === (value === 'light'), value);
    }
    async function geometry() {
      return page.evaluate(() => {
        const selectors = ['.site-header', '#tusneCatalog',
          '#catalogResultsTitle', '.catalog-product'];
        return Object.fromEntries(selectors.map(selector => {
          const element = document.querySelector(selector);
          const rect = element.getBoundingClientRect();
          const style = getComputedStyle(element);
          return [selector, {
            x: rect.x, y: rect.y + scrollY, width: rect.width, height: rect.height,
            font: style.font, lineHeight: style.lineHeight, position: style.position,
            display: style.display,
          }];
        }));
      });
    }
    await theme('dark');
    const dark = await geometry();
    await theme('light');
    const light = await geometry();
    for (const selector of Object.keys(dark)) {
      for (const key of (['#tusneCatalog', '.catalog-product'].includes(selector) ? ['x', 'y', 'width'] : ['x', 'y', 'width', 'height'])) {
        assert.ok(Math.abs(dark[selector][key] - light[selector][key]) < 1,
          selector + ' ' + key + ' debe conservarse al cambiar de tema');
      }
      for (const key of ['font', 'lineHeight', 'position', 'display']) {
        assert.equal(light[selector][key], dark[selector][key], selector + ' ' + key);
      }
    }
    assert.deepEqual(exceptions, [], 'El catálogo no debe lanzar errores JavaScript');
    console.log('PASS catalogue theme parity ' + viewport.width + 'px: tema sin alterar geometría');
  } catch (error) {
    failures++;
    console.error('FAIL catalogue theme parity ' + viewport.width + 'px: ' + error.stack);
  } finally {
    await page.close();
  }
}
async function responsiveWelcome(viewport) {
  const page = await browser.newPage({ viewport, reducedMotion: 'reduce' });
  try {
    const response = await page.goto(origin + '/', { waitUntil: 'domcontentloaded' });
    assert.equal(response?.status(), 200, 'La página raíz debe responder HTTP 200');
    const geometry = await page.evaluate(() => ({
      max: getComputedStyle(document.documentElement).getPropertyValue('--container-max').trim(),
      header: document.querySelector('header.welcome-container').getBoundingClientRect().width,
      content: document.querySelector('div.welcome-container').getBoundingClientRect().width,
      scrollWidth: document.documentElement.scrollWidth,
    }));
    assert.equal(geometry.max, viewport.width >= 1920 ? '1680px' : '1440px',
      'welcome.blade.php debe declarar el mismo --container-max que el layout Market');
    assert.ok(geometry.header <= viewport.width && geometry.content <= viewport.width,
      'El contenedor de bienvenida debe caber en el viewport');
    assert.ok(geometry.scrollWidth <= viewport.width + 2, 'La bienvenida no debe desbordarse horizontalmente');
    console.log('PASS welcome container ' + viewport.width + 'px: ' + geometry.max);
  } finally {
    await page.close();
  }
}

async function responsiveAccountPage(viewport, kind) {
  const page = await browser.newPage({ viewport, reducedMotion: 'reduce' });
  const exceptions = [];
  page.on('pageerror', error => exceptions.push(error.message));
  try {
    const response = await page.goto(origin + '/tienda/registro', { waitUntil: 'domcontentloaded' });
    assert.equal(response?.status(), 200, 'El registro debe responder HTTP 200');
    await page.locator('.market-login-layout').waitFor();
    await page.locator('.account-data-card').waitFor();

    if (kind === 'register') {
      const cardHeights = [];
      const desktop = viewport.width >= 1024;
      const noVerticalScroll = async step => {
        if (!desktop) return;
        const metrics = await page.evaluate(() => ({
          viewport: innerHeight, rootClient: document.documentElement.clientHeight,
          rootScroll: document.documentElement.scrollHeight, bodyScroll: document.body.scrollHeight,
        }));
        assert.ok(metrics.rootScroll <= metrics.rootClient + 1 && metrics.bodyScroll <= metrics.viewport + 1,
          'El documento no debe tener scroll vertical en el paso ' + step + ': ' + JSON.stringify(metrics));
        const g = await page.evaluate(() => {
          const rect = el => {const r=el.getBoundingClientRect();return {top:r.top,bottom:r.bottom,left:r.left,right:r.right,height:r.height,width:r.width};};
          const body=document.querySelector('[data-register-step]:not([hidden]) .form-grid');
          const footer=document.querySelector('[data-register-step]:not([hidden]) .register-step-actions');
          const heading=rect(document.querySelector('.market-login-heading'));
          const progress=rect(document.querySelector('.register-progress'));
          const first=rect(document.querySelector('[data-register-step]:not([hidden]) .form-grid input:not([type=hidden]),[data-register-step]:not([hidden]) .form-grid select'));
          const bodyRect=rect(body), footerRect=rect(footer);
          const back=document.querySelector('[data-register-step]:not([hidden]) [data-register-back]');
          const next=document.querySelector('[data-register-step]:not([hidden]) [data-register-next],[data-register-step]:not([hidden]) button[type=submit]');
          return {body:bodyRect,footer:footerRect,heading,progress,first,scrollHeight:body.scrollHeight,clientHeight:body.clientHeight,back:back?{...rect(back),whiteSpace:getComputedStyle(back).whiteSpace}:null,next:next?rect(next):null};
        });
        assert.ok(g.progress.top>=g.heading.bottom-1,'Stepper intersecta encabezado paso '+step+': '+JSON.stringify(g));
        assert.ok(g.first.top>=g.progress.bottom-1&&g.first.top-g.progress.bottom<=32,'El primer campo debe quedar debajo del stepper, sin solaparse y a <=32px en paso '+step+': '+JSON.stringify(g));
        assert.ok(g.footer.top>=g.body.bottom-1,'El pie debe quedar después del cuerpo desplazable: '+JSON.stringify(g));
        if(g.back){assert.equal(g.back.whiteSpace,'nowrap','Atrás debe estar en una sola línea');assert.ok(Math.abs(g.back.height-g.next.height)<=3,'Atrás y Continuar deben tener altura similar');}
        if(step===2){
          const reachable=await page.locator('#account_country').evaluate(el=>{
            el.scrollIntoView({block:'end',inline:'nearest'});
            const r=el.getBoundingClientRect(),b=document.querySelector('[data-register-step="2"] .form-grid').getBoundingClientRect();
            const f=document.querySelector('[data-register-step="2"] .register-step-actions').getBoundingClientRect();
            return {field:{top:r.top,bottom:r.bottom,left:r.left,right:r.right},body:{top:b.top,bottom:b.bottom,left:b.left,right:b.right},footer:{top:f.top,bottom:f.bottom},scrollHeight:document.querySelector('[data-register-step="2"] .form-grid').scrollHeight,clientHeight:document.querySelector('[data-register-step="2"] .form-grid').clientHeight};
          });
          assert.ok(reachable.field.top>=reachable.body.top-1&&reachable.field.bottom<=reachable.body.bottom+1,
            'País debe ser alcanzable dentro del cuerpo tras scrollIntoView: '+JSON.stringify(reachable));
          assert.ok(reachable.field.bottom<=reachable.footer.top+1,
            'País no debe intersectar el pie de botones: '+JSON.stringify(reachable));
          assert.ok(reachable.scrollHeight>=reachable.clientHeight,
            'El cuerpo debe exponer dimensiones de scroll válidas: '+JSON.stringify(reachable));
        }
        const card=await page.locator('.account-data-card').boundingBox();cardHeights.push(card.height);
      };
      const captureStep = async step => {
        if (viewport.width === 1366 && [590, 768].includes(viewport.height)) {
          mkdirSync('artifacts/registration-steps', { recursive: true });
          await page.screenshot({
            path: 'artifacts/registration-steps/step-' + step + '-' + viewport.height + 'h.png',
            fullPage: false,
          });
        }
      };

      await page.locator('.market-login-heading h1').waitFor({ state: 'visible' });
      await page.locator('[data-register-step="1"]').waitFor({ state: 'visible' });
      assert.equal(await page.locator('[data-register-subtitle]').count(), 0,
        'El encabezado no debe repetir el nombre del paso');
      assert.equal(await page.locator('.register-progress-segments li').count(), 3,
        'El progreso debe mostrar tres segmentos etiquetados');
      assert.deepEqual((await page.locator('.register-progress-segments li').allTextContents()).map(text => text.trim()),
        ['Identidad', 'Contacto', 'Acceso']);
      assert.equal(await page.locator('.register-progress-segments li[aria-current="step"]').getAttribute('data-state'), 'current');
      if (desktop) {
        const chips = await page.locator('.emerald-intro .float').evaluateAll(items => items.map(item => {
          const r = item.getBoundingClientRect();
          return { left:r.left, right:r.right, top:r.top, bottom:r.bottom };
        }));
        for (let i=0;i<chips.length;i++) for (let j=i+1;j<chips.length;j++) {
          assert.ok(chips[i].right <= chips[j].left || chips[j].right <= chips[i].left ||
            chips[i].bottom <= chips[j].top || chips[j].bottom <= chips[i].top,
            'Los chips del panel izquierdo no deben superponerse: '+JSON.stringify({i,j,chips}));
        }
      }
      assert.equal(await page.locator('[data-register-progress-label]').innerText(), 'Paso 1 de 3');
      for (const selector of ['#account_first_names', '#account_last_names', '#account_document_type', '#account_document_number']) {
        assert.equal(await page.locator(selector).isVisible(), true, selector + ' debe estar en Identidad');
      }
      assert.equal(await page.locator('#account_email').isVisible(), false, 'El correo pertenece al paso Contacto');
      assert.equal(await page.locator('#account_password').isVisible(), false, 'La contraseña pertenece al paso Acceso');
      assert.equal(await page.locator('.field-error:visible').count(),0,'No mostrar errores al cargar Identidad');
      await noVerticalScroll(1);
      await captureStep(1);

      await page.locator('#account_first_names').fill('Usuario QA');
      await page.locator('#account_last_names').fill('Prueba');
      await page.locator('#account_document_number').fill('12345678');
      await page.locator('[data-register-step="1"] [data-register-next]').click();
      assert.equal(await page.locator('.field-error').count(), 0, 'Identidad válida debe avanzar sin errores');
      assert.equal(await page.locator('[data-register-progress-label]').innerText(), 'Paso 2 de 3');
      for (const selector of ['#account_phone_country_code', '#account_phone', '#account_email', '#account_line_1', '#account_line_2', '#account_city', '#account_state', '#account_country']) {
        assert.equal(await page.locator(selector).isVisible(), true, selector + ' debe estar en Contacto y ubicación');
      }
      assert.equal(await page.locator('#account_password').isVisible(), false);
      if (viewport.width === 375) {
        const mobileGridLocator = page.locator('[data-register-step="2"] .form-grid');
        const mobileGrid = await mobileGridLocator.evaluate(el => ({
          overflowY:getComputedStyle(el).overflowY,
          scrollHeight:el.scrollHeight, clientHeight:el.clientHeight,
          gradientOpacity:getComputedStyle(el,'::after').opacity,
          gradientPointerEvents:getComputedStyle(el,'::after').pointerEvents,
          bounds:(()=>{const r=el.getBoundingClientRect();return {top:r.top,bottom:r.bottom,height:r.height};})()
        }));
        assert.equal(mobileGrid.overflowY, 'auto',
          '[data-register-step="2"] .form-grid overflowY calculado=' + mobileGrid.overflowY + '; esperado=auto; geometría=' + JSON.stringify(mobileGrid));
        assert.equal(mobileGrid.gradientPointerEvents, 'none',
          '[data-register-step="2"] .form-grid::after pointer-events calculado=' + mobileGrid.gradientPointerEvents + '; esperado=none; geometría=' + JSON.stringify(mobileGrid));
        if (mobileGrid.scrollHeight > mobileGrid.clientHeight + 1) {
          assert.equal(mobileGrid.gradientOpacity, '1',
            '[data-register-step="2"] .form-grid::after opacity calculada=' + mobileGrid.gradientOpacity + '; esperado=1 con scroll pendiente; geometría=' + JSON.stringify(mobileGrid));
          await mobileGridLocator.evaluate(el => { el.scrollTop = el.scrollHeight; });
          await page.waitForFunction(() => getComputedStyle(document.querySelector('[data-register-step="2"] .form-grid'),'::after').opacity === '0');
        }
      }
      assert.equal(await page.locator('#account_phone_country_code').inputValue(),'+51','El código de país debe iniciar en +51');
      assert.equal(await page.locator('.field-error:visible').count(),0,'Entrar a Contacto no debe mostrar errores sin interacción');
      await noVerticalScroll(2);
      if (viewport.width === 375) {
        const mobileGrid = await page.locator('[data-register-step="2"] .form-grid').evaluate(el => ({
          overflowY:getComputedStyle(el).overflowY,
          scrollHeight:el.scrollHeight, clientHeight:el.clientHeight
        }));
        assert.equal(mobileGrid.overflowY, 'auto',
          '[data-register-step="2"] .form-grid overflowY calculado=' + mobileGrid.overflowY + '; esperado=auto; geometría=' + JSON.stringify(mobileGrid));
      }
      await page.locator('#account_email').focus();
      await page.locator('#account_line_1').focus();
      assert.match(await page.locator('#account_email').locator('xpath=ancestor::div[contains(@class,"field")]').locator('.field-error').innerText(), /obligatorio/i,
        'El correo puede mostrar error al salir después de tocarlo');
      await page.locator('[data-register-step="2"] [data-register-next]').click();
      assert.ok(await page.locator('#account_email').locator('xpath=ancestor::div[contains(@class,"field")]').locator('.field-error').isVisible(),
        'Continuar debe mantener la validación en línea');
      assert.equal(await page.locator('[data-register-progress-label]').innerText(), 'Paso 2 de 3');
      await page.locator('#account_email').fill('qa@example.test');
      await noVerticalScroll(2);
      await captureStep(2);

      const cardHeight = await page.locator('.account-data-card').evaluate(el => el.getBoundingClientRect().height);
      await page.locator('[data-register-step="2"] [data-register-next]').click();
      assert.equal(await page.locator('[data-register-progress-label]').innerText(), 'Paso 3 de 3');
      assert.equal(await page.locator('#account_password').isVisible(), true);
      assert.equal(await page.locator('#account_password_confirmation').isVisible(), true);
      assert.equal(await page.locator('#account_email').isVisible(), false);
      assert.equal(await page.locator('.field-error:visible').count(), 0,
        'No mostrar errores al entrar al paso Acceso sin interacción ni respuesta de servidor');
      const passwordGroups = await page.locator('[data-register-step="3"] .form-grid > .field').evaluateAll(groups => groups.map(group => {
        const label = group.querySelector(':scope > label');
        const input = group.querySelector('.register-password-control input');
        const wrapper = group.querySelector('.register-password-control');
        const labelStyle = getComputedStyle(label);
        const inputStyle = getComputedStyle(input);
        const wrapperStyle = getComputedStyle(wrapper);
        const labelRect = label.getBoundingClientRect();
        const bordered = [wrapperStyle, inputStyle].filter(style => ['Top','Right','Bottom','Left'].some(side => parseFloat(style['border'+side+'Width']) > 0)).length;
        return { labelText:label.textContent.trim(), labelDisplay:labelStyle.display, labelVisibility:labelStyle.visibility,
          labelOpacity:labelStyle.opacity, labelHeight:labelRect.height, wrapperBorder:wrapperStyle.borderTopWidth,
          inputBorders:[inputStyle.borderTopWidth,inputStyle.borderRightWidth,inputStyle.borderBottomWidth,inputStyle.borderLeftWidth],
          inputBackground:inputStyle.backgroundColor, borderedElements:bordered };
      }));
      assert.equal(passwordGroups.length, 2, 'Deben existir dos grupos de contraseña');
      for (const group of passwordGroups) {
        assert.notEqual(group.labelDisplay, 'none', group.labelText + ' debe estar visible');
        assert.equal(group.labelVisibility, 'visible', group.labelText + ' debe ser visible');
        assert.ok(Number(group.labelOpacity) > 0 && group.labelHeight > 0, group.labelText + ' debe tener geometría visible: ' + JSON.stringify(group));
        assert.equal(group.borderedElements, 1, group.labelText + ' debe tener un solo elemento con borde: ' + JSON.stringify(group));
        assert.ok(group.inputBorders.every(width => parseFloat(width) === 0), group.labelText + ' input debe ser transparente y sin borde: ' + JSON.stringify(group));
        assert.equal(group.inputBackground, 'rgba(0, 0, 0, 0)', group.labelText + ' input debe tener fondo transparente');
      }
      const eyeBox = await page.locator('[data-toggle-password="account_password"]').boundingBox();
      const eyePadding = await page.locator('#account_password').evaluate(el => ({
        paddingRight:getComputedStyle(el).paddingRight,
        numericPaddingRight:parseFloat(getComputedStyle(el).paddingRight)
      }));
      assert.ok(eyePadding.numericPaddingRight >= 44,
        '#account_password padding-right calculado=' + eyePadding.paddingRight + '; esperado=44px; selector=.register-password-control input');
      const passwordBox = await page.locator('#account_password').locator('xpath=ancestor::div[contains(@class,"register-password-control")]').boundingBox();
      assert.ok(eyeBox.x >= passwordBox.x && eyeBox.x + eyeBox.width <= passwordBox.x + passwordBox.width &&
        eyeBox.y >= passwordBox.y && eyeBox.y + eyeBox.height <= passwordBox.y + passwordBox.height,
        'El botón ojo debe quedar dentro del contenedor del input');
      assert.equal(await page.locator('[data-password-strength]').isVisible(), true,
        'El indicador de fortaleza debe ser visible en Acceso');
      const strengthGeometry = await page.locator('[data-password-strength]').evaluate(el => {
        const r = el.getBoundingClientRect();
        return { height:r.height, top:r.top, bottom:r.bottom,
          minHeight:getComputedStyle(el).minHeight };
      });
      assert.ok(strengthGeometry.height >= 18,
        '[data-password-strength] height calculado=' + strengthGeometry.height + 'px; esperado >=18px; geometría=' + JSON.stringify(strengthGeometry));
      if (desktop) {
        const stepThreeScroll = await page.locator('[data-register-step="3"] .form-grid').evaluate(el => ({
          overflowY:getComputedStyle(el).overflowY,
          scrollHeight:el.scrollHeight, clientHeight:el.clientHeight,
          gradientOpacity:getComputedStyle(el,'::after').opacity,
          bounds:(()=>{const r=el.getBoundingClientRect();return {top:r.top,bottom:r.bottom,height:r.height};})()
        }));
        assert.equal(stepThreeScroll.overflowY, 'visible',
          '[data-register-step="3"] .form-grid overflowY calculado=' + stepThreeScroll.overflowY + '; esperado=visible; geometría=' + JSON.stringify(stepThreeScroll));
        assert.equal(stepThreeScroll.gradientOpacity, '0',
          '[data-register-step="3"] .form-grid::after opacity calculada=' + stepThreeScroll.gradientOpacity + '; esperado=0; geometría=' + JSON.stringify(stepThreeScroll));
      }
      await noVerticalScroll(3);
      await captureStep(3);
      if (firefoxRegisterOnly) {
        mkdirSync('artifacts/registration-firefox', { recursive: true });
        await page.screenshot({ path:'artifacts/registration-firefox/step-3-1366x' + viewport.height + '.png', fullPage:false });
      }
      const nextCardHeight = await page.locator('.account-data-card').evaluate(el => el.getBoundingClientRect().height);
      assert.ok(cardHeight > 0 && nextCardHeight > 0,
        'La tarjeta debe conservar dimensiones válidas en cada paso');

      const passwordGroup = page.locator('#account_password').locator('xpath=ancestor::div[contains(@class,"field")]');
      const confirmationGroup = page.locator('#account_password_confirmation').locator('xpath=ancestor::div[contains(@class,"field")]');
      await page.locator('#account_password').focus();
      await page.locator('#account_password_confirmation').focus();
      assert.equal(await passwordGroup.locator('.field-error:visible').count(), 1,
        'El blur de la contraseña tocada debe mostrar un único error en su grupo');
      assert.equal(await passwordGroup.locator('.field-error').innerText(), 'Este campo es obligatorio.');
      const passwordErrorOrder = await passwordGroup.evaluate(group => {
        const error=group.querySelector('.field-error'), help=group.querySelector('.password-help'), strength=group.querySelector('[data-password-strength]');
        return {errorInside:group.contains(error), beforeHelp:!!(error.compareDocumentPosition(help)&Node.DOCUMENT_POSITION_FOLLOWING), beforeStrength:!!(error.compareDocumentPosition(strength)&Node.DOCUMENT_POSITION_FOLLOWING)};
      });
      assert.deepEqual(passwordErrorOrder,{errorInside:true,beforeHelp:true,beforeStrength:true},'El error de contraseña debe ir tras el control y antes de ayuda/fortaleza');
      await page.locator('#account_password').fill('Demo-password-2026');
      await page.locator('#account_password_confirmation').fill('');
      await page.locator('[data-register-step="3"] button[type="submit"]').click();
      assert.equal(await confirmationGroup.locator('.field-error:visible').count(), 1,
        'El submit debe mostrar un único error de confirmación dentro de su grupo');
      assert.equal(await confirmationGroup.locator('.field-error').innerText(), 'Este campo es obligatorio.');
      await page.locator('#account_password_confirmation').fill('Different-password-2026');
      await page.locator('[data-register-step="3"] button[type="submit"]').click();
      assert.equal(await confirmationGroup.locator('.field-error:visible').count(), 1);
      assert.match(await confirmationGroup.locator('.field-error').innerText(), /no coinciden/i,
        'La confirmación debe validar coincidencia en línea');
      const eye = page.locator('[data-toggle-password="account_password"]');
      await eye.click();
      assert.equal(await eye.getAttribute('aria-pressed'), 'true');
      assert.equal(await page.locator('#account_password').getAttribute('type'), 'text',
        'El botón ojo debe mostrar la contraseña');
      await eye.click();
      assert.equal(await eye.getAttribute('aria-pressed'), 'false');
      assert.equal(await page.locator('#account_password').getAttribute('type'), 'password',
        'El botón ojo debe ocultar la contraseña');

      await page.locator('[data-register-step="3"] [data-register-back]').click();
      assert.equal(await page.locator('[data-register-progress-label]').innerText(), 'Paso 2 de 3');
      assert.equal(await page.locator('#account_email').inputValue(), 'qa@example.test');
      await page.locator('[data-register-step="2"] [data-register-back]').click();
      assert.equal(await page.locator('[data-register-progress-label]').innerText(), 'Paso 1 de 3');
      assert.equal(await page.locator('#account_first_names').inputValue(), 'Usuario QA',
        'Atrás debe conservar los datos de Identidad');
    }

    if (kind === 'profile') {
      // La ruta real de perfil exige una sesión autenticada. Se reutiliza el
      // mismo layout y los campos compartidos del formulario real de registro
      // para comprobar el shell responsive sin crear usuarios ni escribir datos.
      await page.evaluate(() => {
        const card = document.querySelector('.account-data-card');
        const heading = card.querySelector('.market-login-heading h1');
        heading.textContent = 'PERFIL DEL COMPRADOR';
        const form = card.querySelector('form');
        form.querySelectorAll('input[type="password"]').forEach(input => input.closest('.field')?.remove());
        const email = form.querySelector('#account_email');
        email.readOnly = true;
        email.value = 'qa@example.test';
        const submit = form.querySelector('button[type="submit"]');
        submit.textContent = 'Guardar perfil →';
        const logout = document.createElement('form');
        logout.innerHTML = '<button class="back" type="button">← Regresar</button>';
        card.insertBefore(logout, heading.closest('.market-login-heading'));
      });
    }

    for (const theme of ['dark', 'light']) {
      await page.evaluate(isLight => document.body.classList.toggle('light-mode', isLight), theme === 'light');
      const geometry = await page.evaluate(() => {
        const layout = document.querySelector('.market-login-layout').getBoundingClientRect();
        const card = document.querySelector('.market-login-card').getBoundingClientRect();
        return {
          layout: { x: layout.x, right: layout.right, width: layout.width },
          card: { x: card.x, right: card.right, width: card.width, height: card.height },
          viewport: innerWidth,
          scrollWidth: document.documentElement.scrollWidth,
        };
      });
      assert.ok(geometry.layout.width > 0 && geometry.card.width > 0,
        'El formulario debe tener dimensiones visibles (' + theme + ')');
      assert.ok(geometry.card.x >= -1 && geometry.card.right <= viewport.width + 1,
        'La tarjeta debe permanecer dentro del viewport (' + theme + '): ' + JSON.stringify(geometry));
      assert.ok(geometry.scrollWidth <= viewport.width + 2,
        'El formulario no debe provocar desbordamiento horizontal (' + theme + '): ' + JSON.stringify(geometry));
    }
    assert.deepEqual(exceptions, [], 'La vista de cuenta no debe lanzar errores JavaScript');
    console.log('PASS ' + kind + ' responsive ' + viewport.width + 'px');
  } catch (error) {
    failures++;
    console.error('FAIL ' + kind + ' responsive ' + viewport.width + 'px: ' + error.stack);
    await page.screenshot({ path: '/tmp/undc-account-' + kind + '-' + viewport.width + '.png', fullPage: true }).catch(() => {});
  } finally {
    await page.close();
  }
}

for (const width of [320, 375, 768, 1024, 1366, 1920]) {
  const viewport = { width, height: width <= 768 ? 844 : 768 };
  await responsiveHome(viewport);
  await responsiveThemeCatalog(viewport);
}

if (firefoxRegisterOnly) {
  for (const viewport of [{ width:1366, height:590 }, { width:1366, height:768 }]) {
    await responsiveAccountPage(viewport, 'register');
  }
} else {
// Validación solicitada de la portada en sus alturas de escritorio reales.
await responsiveHome({ width: 1600, height: 900 });
await responsiveHome({ width: 1920, height: 1080 });
await responsiveWelcome({ width: 1440, height: 900 });
await responsiveWelcome({ width: 1920, height: 1080 });

// Registro real y shell de perfil con los mismos campos compartidos.
for (const viewport of [
  { width: 1366, height: 590 },
  { width: 1366, height: 768 },
  { width: 1440, height: 900 },
  { width: 1920, height: 1080 },
  { width: 1024, height: 768 },
  { width: 768, height: 844 },
  { width: 375, height: 812 },
]) {
  await responsiveAccountPage(viewport, 'register');
  await responsiveAccountPage(viewport, 'profile');
}


await scenario({ width: 1440, height: 900 }, 'desktop');
await scenario({ width: 1366, height: 768 }, 'laptop');
await scenario({ width: 390, height: 844 }, 'mobile');
}
await browser.close();
if (failures > 0) process.exit(1);


