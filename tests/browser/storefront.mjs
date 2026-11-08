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
    const catalog = await page.goto(origin + '/tienda', { waitUntil: 'domcontentloaded' });
    assert.equal(catalog?.status(), 200, 'El catálogo debe responder HTTP 200');
    await page.locator('#tusneTitle').waitFor();
    assert.match(await page.locator('#tusneTitle').innerText(), /Nuestros trámites/);
    assert.equal(await page.locator('link[href*="css/undc-tienda.css"]').count(), 1);
    assert.equal(await page.locator('script[src*="js/undc-tienda.js"]').count(), 1);

    const cart = await page.goto(origin + '/tienda/carrito', { waitUntil: 'domcontentloaded' });
    assert.equal(cart?.status(), 200, 'El carrito debe responder HTTP 200');
    await page.locator('#cartContinue').waitFor();
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
await scenario({ width: 390, height: 844 }, 'mobile');
await browser.close();
if (failures > 0) process.exit(1);
