<section class="screen payment-preview" data-screen="payment" aria-labelledby="paymentPreviewTitle">
<div class="panel">
<button type="button" class="back" data-go="cart">← Volver al carrito</button>
<div class="payment-preview-heading"><span class="payment-preview-icon" aria-hidden="true">▤</span><div><h1 id="paymentPreviewTitle" tabindex="-1">Revisa tu pago</h1><p class="subtle">Comprueba tus datos y los conceptos seleccionados.</p></div><span class="chip">Vista previa</span></div>
<ol class="payment-progress" aria-label="Etapas de la compra"><li>✓ Carrito</li><li>✓ Identificación</li><li aria-current="step">3. Revisión del pago</li></ol>
<div class="payment-preview-grid">
<div><section class="summary-block"><h2>Datos del comprador</h2><p id="paymentBuyerName"></p><p id="paymentBuyerDocument"></p><p id="paymentBuyerEmail"></p></section>
<section class="summary-block"><h2>Conceptos seleccionados</h2><div id="paymentPreviewItems"></div></section>
<section class="summary-block"><h2>Medio de pago</h2><p class="subtle">Los medios de pago se mostrarán cuando se habilite la pasarela.</p><div class="payment-method-placeholder" aria-hidden="true">▱ &nbsp; Pago en línea</div></section></div>
<aside class="summary-block payment-preview-summary"><h2>Resumen de tu pago</h2><p><span>Conceptos</span><strong id="paymentPreviewCount"></strong></p><hr><p>Total referencial</p><strong class="payment-preview-total" id="paymentPreviewTotal"></strong><p class="smallprint">Importe sujeto a validación institucional.</p><div class="info" role="note">Esta es una vista previa. Los conceptos referenciales todavía no están habilitados para pago y no se realizará ningún cobro.</div><button class="btn block" type="button" disabled>Pago aún no disponible</button><button class="back" type="button" data-go="cart">Modificar mi selección</button></aside>
</div></div></section>
<style>
.payment-preview .panel{padding:clamp(18px,3vw,36px);border-radius:24px}
.payment-preview-heading{display:flex;align-items:center;gap:16px;margin:24px 0}
.payment-preview-heading h1{margin:0;font-size:calc(clamp(26px,3vw,38px)*var(--a11y-text-scale,1))}
.payment-preview-heading p{margin:6px 0 0}.payment-preview-heading>.chip{margin-left:auto}
.payment-preview-icon{display:grid;place-items:center;width:56px;height:56px;border-radius:16px;background:var(--undc-pill);color:#168aff;font-size:32px;flex-shrink:0}
.payment-progress{display:flex;flex-wrap:wrap;gap:12px 24px;list-style:none;padding:16px;margin:0 0 24px;border-radius:14px;background:var(--undc-pill);font-size:calc(13px*var(--a11y-text-scale,1))}
.payment-progress [aria-current]{color:#168aff;font-weight:750}
.payment-preview-grid{display:grid;grid-template-columns:minmax(0,1.5fr) minmax(270px,1fr);gap:24px}
.payment-preview h2{margin:0 0 18px;font-size:calc(19px*var(--a11y-text-scale,1))}
.payment-preview .summary-block{padding:22px;border-radius:18px;margin:0 0 18px;min-width:0}
.payment-preview .summary-block p{overflow-wrap:anywhere}
.payment-preview-summary{align-self:start}.payment-preview-summary>p:first-of-type{display:flex;justify-content:space-between;gap:12px}
.payment-preview-summary hr{border:0;border-top:1px solid var(--undc-card-border)}
.payment-preview-total{display:block;color:#168aff;font-size:calc(clamp(28px,3vw,40px)*var(--a11y-text-scale,1));overflow-wrap:anywhere}
.payment-preview-summary .btn{margin:20px 0 16px}.payment-preview-summary .back{display:block;margin:auto}
.payment-preview-line{display:flex;justify-content:space-between;gap:16px;padding:16px 0;border-bottom:1px solid var(--undc-card-border)}
.payment-preview-line:last-child{border:0}.payment-preview-line strong{overflow-wrap:anywhere}.payment-preview-line span{flex-shrink:0;color:#168aff;font-weight:700}
.payment-method-placeholder{padding:18px;border:1px dashed var(--undc-card-border);border-radius:12px;color:var(--undc-muted)}
@media(max-width:760px){.payment-preview-grid{grid-template-columns:minmax(0,1fr)}.payment-preview-heading{flex-wrap:wrap}.payment-preview-heading>.chip{margin-left:0}.payment-preview-line{flex-wrap:wrap}}
</style>
