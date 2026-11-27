<aside class="undc-assistant" data-undc-assistant>
    <button class="undc-assistant-launcher" type="button" aria-expanded="false" aria-controls="undcAssistantWindow">
        <span class="undc-assistant-launcher-art" aria-hidden="true">
            <img src="{{ asset('assets/asistente/mascota-undc.webp') }}" alt="" loading="lazy">
        </span>
        <span class="undc-assistant-launcher-copy">
            <strong>Asistente <b>UNDC</b></strong>
            <small>¿En qué puedo ayudarte?</small>
        </span>
        <span class="undc-assistant-launcher-arrow" aria-hidden="true">›</span>
    </button>

    <section class="undc-assistant-window" id="undcAssistantWindow" aria-label="Chat con el asistente UNDC" hidden>
        <header class="undc-assistant-header">
            <img src="{{ asset('assets/asistente/mascota-undc.webp') }}" alt="" aria-hidden="true">
            <span><strong>Asistente UNDC</strong><small><i></i> En línea</small></span>
            <button type="button" class="undc-assistant-close" data-assistant-close aria-label="Cerrar asistente">×</button>
        </header>
        <div class="undc-assistant-messages" data-assistant-messages role="log" aria-live="polite" aria-relevant="additions">
            <div class="undc-assistant-message is-bot">
                <p>¡Hola! Soy el asistente de UNDC MARKET. Puedo ayudarte a consultar oficinas, conceptos, códigos e importes del catálogo.</p>
            </div>
        </div>
        <form class="undc-assistant-form" data-assistant-form>
            <label class="sr-only" for="undcAssistantInput">Escribe tu consulta</label>
            <textarea id="undcAssistantInput" name="message" rows="1" maxlength="4000" placeholder="Escribe tu consulta..." required></textarea>
            <button type="submit" aria-label="Enviar mensaje" data-assistant-send>
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 12 20 4l-5 16-3-6-8-2Zm8 2 8-10"/></svg>
            </button>
        </form>
        <p class="undc-assistant-disclaimer">Información de orientación basada en el catálogo UNDC.</p>
    </section>
</aside>
