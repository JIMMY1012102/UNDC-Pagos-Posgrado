import { chromium } from 'playwright';
import assert from 'node:assert/strict';

// Browser smoke test of the current three-page frontend.
// Backend + MariaDB journey is independently tested in PHP CI.
const origin = process.env.TEST_BASE_URL || 'http://127.0.0.1:8000';
const browser = await chromium.launch({ headless: true });
let failures = 0;

async function scenario(viewport, name) {
  const page = await browser.newPage({ viewport, reducedMotion: 'reduce' });
  const exceptions = [];
  page.on('pageerror', (error) => exceptions.push(error.message));

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
    const catalog = await page.goto(origin + '/tienda', { waitUntil: 'domcontentloaded' });
    assert.equal(catalog?.status(), 200, 'El catálogo debe responder HTTP 200');
    await page.locator('#catalogResultsTitle').waitFor();
    assert.equal(await page.locator('#tusneTitle').count(), 0, 'El título Nuestros trámites se eliminó del catálogo');
    assert.equal(await page.locator('#tusneCatalog').getAttribute('aria-labelledby'), 'catalogResultsTitle');
    assert.equal(await page.locator('link[href*="css/undc-tienda.css"]').count(), 1);
    assert.equal(await page.locator('script[src*="js/undc-tienda.js"]').count(), 1);

    const hero = page.locator('[data-market-carousel]');
    await hero.locator('[data-hero-next]').waitFor();
    assert.equal(await hero.locator('[data-hero-pause]').getAttribute('aria-pressed'), 'true', 'Respetar movimiento reducido');
    await hero.locator('[data-hero-next]').click();
    assert.equal(await hero.locator('[data-hero-count]').innerText(), '02 / 03');
    assert.equal(await hero.locator('[data-hero-slide]:not([hidden])').count(), 1);
    await hero.locator('[data-hero-prev]').click();
    assert.equal(await hero.locator('[data-hero-count]').innerText(), '01 / 03');
    const photo = hero.locator('.market-hero-photo');
    await photo.evaluate(img => img.decode());
    const photoInfo = await photo.evaluate(img => ({ width: img.clientWidth, height: img.clientHeight, ratio: img.naturalWidth / img.naturalHeight }));
    if (viewport.width <= 900) assert.ok(Math.abs(photoInfo.width / photoInfo.height - photoInfo.ratio) < .02, 'Foto móvil completa sin deformación');

    assert.deepEqual(await page.locator('.posgrado-links a').allTextContents(), [
      'Para ti', 'Admisión', 'Matrícula y pensiones', 'Trámites académicos',
      'Certificados y constancias', 'Tesis y grados', 'Servicios administrativos',
    ]);
    await page.waitForFunction(() => document.querySelector('#tusneResults').textContent.includes('Examen de prueba'));
    await page.locator('.posgrado-links [data-category-nav="servicios_administrativos"]').click();
    assert.match(await page.locator('#tusneResults').innerText(), /Biblioteca de prueba/);
    await page.waitForFunction(() => {
      const header = document.querySelector('.site-header').getBoundingClientRect();
      const results = document.querySelector('.catalog-results-heading').getBoundingClientRect();
      return Math.abs(results.top - header.bottom - 12) < 3;
    });

    assert.doesNotMatch(await page.locator('#tusneResults').innerText(), /Examen de prueba/);
    await page.locator('.posgrado-links [data-category-nav="tesis_grados"]').click();
    assert.equal(await page.locator('#tusneEmpty').isVisible(), true);
    await page.locator('.posgrado-links [data-category-nav="all"]').click();
    assert.match(await page.locator('#tusneResults').innerText(), /Examen de prueba/);
    assert.equal(await page.locator('#catalogFilters, [data-category-filter]').count(), 0, 'Solo debe existir el menú superior de categorías');
    const product = page.locator('.catalog-product').first();
    assert.equal(await product.locator('img').count(), 1);
    assert.equal(await product.locator('.catalog-product-name').innerText(), 'Examen de prueba');
    assert.equal(await product.locator('.catalog-product-price').innerText(), 'S/ 300.00');
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
    if (viewport.width >= 1200) {
      const menu = await page.locator('.posgrado-links').evaluate(el => ({
        font: parseFloat(getComputedStyle(el.querySelector('a')).fontSize),
        fits: el.scrollWidth <= el.clientWidth + 2,
        tops: [...el.querySelectorAll('a')].map(link => link.getBoundingClientRect().top),
      }));
      assert.ok(menu.font >= 15, 'Las categorías deben tener letra legible de al menos 15px');
      assert.equal(menu.fits, true, 'Las siete opciones deben caber al 100% sin desplazamiento');
      assert.ok(menu.tops.every(top => Math.abs(top - menu.tops[0]) < 2), 'Las siete opciones deben compartir fila');
    }
    // Enlarged accessibility text must not move the magnifier below the categories.
    await page.evaluate(() => {
      document.documentElement.setAttribute('data-accessibility-text', '');
      document.documentElement.style.setProperty('--a11y-text-scale', '1.2');
    });
    const linksBox = await page.locator('.posgrado-links').boundingBox();
    const searchBox = await page.locator('#catalogSearchToggle').boundingBox();
    assert.ok(searchBox.x >= linksBox.x + linksBox.width - 2, 'La lupa debe quedar a la derecha');
    assert.ok(Math.abs((searchBox.y + searchBox.height / 2) - (linksBox.y + linksBox.height / 2)) < 3,
      'La lupa y las categorías deben compartir la fila con texto ampliado');
    assert.equal(await page.evaluate(() => document.documentElement.scrollWidth > innerWidth + 2), false);
    await page.locator('.posgrado-links [data-category-nav="admision"]').click();
    await page.waitForFunction(() => Math.abs(
      document.querySelector('.catalog-results-heading').getBoundingClientRect().top -
      document.querySelector('.site-header').getBoundingClientRect().bottom - 12
    ) < 3);

    await page.evaluate(() => {
      document.documentElement.removeAttribute('data-accessibility-text');
      document.documentElement.style.removeProperty('--a11y-text-scale');
    });
    assert.equal(await page.locator('#search').isVisible(), false);
    await page.locator('#catalogSearchToggle').click();
    assert.equal(await page.locator('#search').isVisible(), true);
    assert.equal(await page.locator('#search').evaluate(el => document.activeElement === el), true);
    await page.locator('#search').fill('Biblioteca');
    assert.match(await page.locator('#tusneResults').innerText(), /Biblioteca de prueba/);
    assert.doesNotMatch(await page.locator('#tusneResults').innerText(), /Examen de prueba/);
    await page.locator('#search').press('Escape');
    assert.equal(await page.locator('#search').isVisible(), false);
    assert.equal(await page.locator('#catalogSearchToggle').getAttribute('aria-expanded'), 'false');
    assert.equal(await page.locator('#catalogSearchToggle').evaluate(el => document.activeElement === el), true);
    assert.equal(await page.evaluate(() => document.documentElement.scrollWidth > innerWidth + 2), false,
      'El catálogo y sus siete enlaces no deben desbordar la pantalla');

    const cart = await page.goto(origin + '/tienda/carrito', { waitUntil: 'domcontentloaded' });
    assert.equal(cart?.status(), 200, 'El carrito debe responder HTTP 200');
    await page.locator('#cartContinue').waitFor({ state: 'attached' });
    assert.match(await page.locator('.cart-review-heading h2').innerText(), /Revisa tu carrito/);
    assert.equal(await page.locator('#cartContinue').isDisabled(), true,
      'No puede continuarse con un carrito vacío');

    const login = await page.goto(origin + '/tienda/login', { waitUntil: 'domcontentloaded' });
    assert.equal(login?.status(), 200, 'El login debe responder HTTP 200');
    await page.locator('#marketLoginTitle').waitFor();
    assert.match(await page.locator('#marketLoginTitle').innerText(), /INICIA SESIÓN/);
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

await scenario({ width: 1440, height: 900 }, 'desktop');
await scenario({ width: 1366, height: 768 }, 'laptop');
await scenario({ width: 390, height: 844 }, 'mobile');
await browser.close();
if (failures > 0) process.exit(1);
