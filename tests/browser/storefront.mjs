import { chromium } from 'playwright';
import assert from 'node:assert/strict';
import {readFileSync} from 'node:fs';

// Browser smoke test of the current three-page frontend.
// Backend + MariaDB journey is independently tested in PHP CI.
const origin = process.env.TEST_BASE_URL || 'http://127.0.0.1:8000';
const browser = await chromium.launch({ headless: true, executablePath: process.env.PLAYWRIGHT_CHROMIUM_EXECUTABLE_PATH || undefined });
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
      return Object.fromEntries(selectors.map(selector => {
        const element = document.querySelector(selector);
        const rect = element.getBoundingClientRect();
        return [selector, { x: rect.x, right: rect.right, width: rect.width, height: rect.height }];
      }));
    });
    for (const [selector, rect] of Object.entries(bounds)) {
      assert.ok(rect.width > 0 && rect.height > 0, selector + ' debe tener dimensiones visibles');
      assert.ok(rect.x >= -1 && rect.right <= viewport.width + 1,
        selector + ' debe permanecer dentro del viewport: ' + JSON.stringify(rect));
    }
    assert.equal(await page.locator('a[href="/tienda/catalogo"]').count() > 0, true,
      'La portada debe ofrecer acceso al catálogo independiente');
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
      for (const key of ['x', 'y', 'width', 'height']) {
        assert.ok(Math.abs(dark[selector][key] - light[selector][key]) < 1,
          selector + ' ' + key + ' debe conservarse al cambiar de tema');
      }
      for (const key of ['font', 'lineHeight', 'position', 'display']) {
        assert.equal(light[selector][key], dark[selector][key], selector + ' ' + key);
      }
    }
    assert.equal(await page.evaluate(() => document.documentElement.scrollWidth > innerWidth + 2),
      false, 'El catálogo no debe desbordarse horizontalmente');
    assert.deepEqual(exceptions, [], 'El catálogo no debe lanzar errores JavaScript');
    console.log('PASS catalogue theme parity ' + viewport.width + 'px: tema sin alterar geometría');
  } catch (error) {
    failures++;
    console.error('FAIL catalogue theme parity ' + viewport.width + 'px: ' + error.stack);
  } finally {
    await page.close();
  }
}
for (const width of [320, 375, 768, 1024, 1366, 1920]) {
  const viewport = { width, height: width <= 768 ? 844 : 768 };
  await responsiveHome(viewport);
  await responsiveThemeCatalog(viewport);
}


await scenario({ width: 1440, height: 900 }, 'desktop');
await scenario({ width: 1366, height: 768 }, 'laptop');
await scenario({ width: 390, height: 844 }, 'mobile');
await browser.close();
if (failures > 0) process.exit(1);


