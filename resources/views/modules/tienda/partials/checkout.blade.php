<section class="screen" data-screen="identity">
<div class="panel"><button class="back" type="button" data-go="cart">← Volver al carrito</button><h2>¡Ya casi terminamos!</h2><p class="subtle">Identifícate de forma segura para continuar con tus conceptos seleccionados.</p>
<div id="loginBox"><div class="login-note"><span class="google">G</span><div><b>Ingresar con tu cuenta Google</b><p class="smallprint" style="margin:4px 0">Puedes utilizar una cuenta Google personal o institucional. No necesitas registrarte antes de elegir conceptos.</p></div></div>
<button id="googleButton" class="btn block" type="button">Continuar con Google →</button><p class="smallprint">La autenticación real estará disponible cuando OTI configure las credenciales y HTTPS.</p></div>
<div id="profileBox" hidden><p class="info">Solo te solicitamos estos datos en tu primer ingreso. Se conservarán en tu perfil para próximos trámites.</p>
<form id="profileForm"><div class="form-grid">
<div class="field"><label for="firstNames">Nombres *</label><input id="firstNames" name="first_names" required maxlength="160" autocomplete="given-name"></div>
<div class="field"><label for="lastNames">Apellidos *</label><input id="lastNames" name="last_names" required maxlength="160" autocomplete="family-name"></div>
<div class="field"><label for="docType">Tipo de documento *</label><select id="docType" name="document_type" required><option value="DNI">DNI</option><option value="CE">Carné de extranjería</option><option value="PASAPORTE">Pasaporte</option></select></div>
<div class="field"><label for="docNumber">Número de documento *</label><input id="docNumber" name="document_number" required maxlength="32"></div>
<div class="field"><label for="phoneCountryCode">Código de país</label><input id="phoneCountryCode" name="phone_country_code" value="+51" maxlength="5" autocomplete="tel-country-code"></div>
<div class="field"><label for="phone">Teléfono</label><input id="phone" name="phone" autocomplete="tel-national" inputmode="numeric" maxlength="15"></div>
<div class="field"><label for="line1">Dirección</label><input id="line1" name="line_1" maxlength="255" autocomplete="address-line1"></div>
<div class="field"><label for="line2">Referencia (opcional)</label><input id="line2" name="line_2" maxlength="255" autocomplete="address-line2"></div>
<div class="field"><label for="city">Ciudad</label><input id="city" name="city" maxlength="120" autocomplete="address-level2"></div>
<div class="field"><label for="state">Departamento / región</label><input id="state" name="state" maxlength="120" autocomplete="address-level1"></div>
<div class="field"><label for="country">País</label><input id="country" name="country" value="PE" maxlength="2" autocomplete="country" autocapitalize="characters"></div>
<div class="field"><label for="email">Correo de contacto</label><input id="email" name="contact_email" type="email" autocomplete="email" maxlength="254"></div>
</div><button type="submit" class="btn block" style="margin-top:24px">Guardar y continuar →</button></form></div></div>
</section>
