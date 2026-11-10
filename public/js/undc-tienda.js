(()=>{'use strict';
const $=id=>document.getElementById(id), csrf=document.querySelector('meta[name="csrf-token"]').content;
const marketPage=document.body.dataset.marketPage||'catalog';
const marketRoutes={catalog:'/tienda',cart:'/tienda/carrito',login:'/tienda/login'};
const state={token:sessionStorage.getItem('undc_cart_token'),cart:null,owned:false,profile:null,office:sessionStorage.getItem('undc_office_id')||'',catalog:[],screen:marketPage,authenticated:false};
let currentLanguage=localStorage.getItem('undc_language')==='en'?'en':'es';
const languagePairs={
"Estándar":"Standard","Cálido":"Warm","Azul oscuro":"Dark blue","Alto contraste":"High contrast","Invertido":"Inverted","Pausar":"Pause","Reanudar":"Resume","Restablecer ajustes":"Reset settings",

"Volver a la tienda":"Back to the store",
"Tienda virtual de pagos de la UNDC":"UNDC virtual payment store",
"Asistente universitario UNDC con birrete y computadora":"UNDC university assistant with graduation cap and laptop",
"Organiza tus trámites":"Organize your services",
"Acceso a tu perfil":"Access your profile",
"Consulta en línea":"Browse online",
"Inicia sesión en":"Sign in to",
"Accede a la tienda virtual de pagos de la UNDC.":"Access the UNDC virtual payment store.",
"Correo institucional o usuario":"Institutional email or username",
"Contraseña":"Password",
"¿Olvidaste tu contraseña?":"Forgot your password?",
"Iniciar sesión":"Sign in",
"El acceso con contraseña y su recuperación estarán disponibles próximamente.":"Password sign-in and recovery will be available soon.",
"El acceso con Google estará disponible cuando OTI complete su configuración.":"Google sign-in will be available once OTI completes its configuration.",
"¿Aún no tienes una cuenta?":"Don't have an account yet?",
"Regístrate · Próximamente":"Register · Coming soon",
"o":"or",

"Resumen de tu selección":"Selection summary",
"Conceptos seleccionados":"Selected services",
"Total referencial":"Reference total",
"Total referencial:":"Reference total:",

"Tu carrito está vacío":"Your cart is empty",
"Selecciona un concepto de nuestros catálogos o trámites para comenzar tu compra.":"Select a service from our catalogs to start your purchase.",
"Explorar conceptos y agregar al carrito":"Explore services and add to cart",
"Solo al continuar te pediremos iniciar sesión. No se realizará ningún cobro en esta etapa.":"We will only ask you to sign in when you continue. No charge will be made at this stage.",
"Ingresa y gestiona tus pagos en cualquier momento.":"Access and manage your payments anytime.",
"Agrega todos los trámites o servicios que necesites.":"Add all the procedures or services you need.",
"Accede de forma segura y reutiliza tus datos.":"Sign in securely and reuse your details.",

"Accesibilidad":"Accessibility",
"Cerrar accesibilidad":"Close accessibility",
"Tamaño del texto":"Text size",
"Disminuir tamaño del texto":"Decrease text size",
"Aumentar tamaño del texto":"Increase text size",
"Tema de color":"Colour theme",
"Tema claro":"Light theme",
"Tema sepia":"Sepia theme",
"Tema oscuro":"Dark theme",
"Alto contraste blanco":"White high contrast",
"Alto contraste amarillo":"Yellow high contrast",
"Leer en voz alta":"Read aloud",
"Selección":"Selection",
"Página":"Page",
"Detener":"Stop",
"Voz":"Voice",
"Velocidad de lectura":"Speech rate",
"Restablecer configuración":"Reset settings",

'Tienda virtual de pagos · UNDC':'Virtual payment store · UNDC',
'Tienda virtual de conceptos de Posgrado — Universidad Nacional de Cañete':'Virtual store for Graduate School services — National University of Cañete',
'Escudo de la Universidad Nacional de Cañete':'National University of Cañete crest',
'Servicios académicos y pagos de posgrado':'Graduate academic services and payments',
'Configuraciones':'Settings',
'Modo oscuro':'Dark mode',
'Modo claro':'Light mode',
'Abrir carrito':'Open cart',
'Inicia sesión':'Sign in',
'Abrir menú':'Open menu',
'Navegación principal':'Main navigation',
'Para ti':'For you',
'Mis trámites':'My procedures',
'Catalogos':'Catalogs',
'Categorías':'Categories',
'Buscar maestrías, trámites, servicios...':'Search graduate programs, procedures, services...',
'Buscar conceptos':'Search services',
'Buscar':'Search',
'Configuraciones secundarias':'Secondary settings',
'Tus trámites, mucho más cerca de ti.':'Your procedures, much closer to you.',
'Realiza tus pagos académicos de posgrado de manera rápida, sencilla y segura':'Make your graduate academic payments quickly, easily, and securely.',
'Nuestros trámites':'Our procedures',
'Versión de precompra · Sin pagos habilitados':'Pre-purchase version · Payments not enabled',
'Servicios digitales de Posgrado UNDC':'UNDC Graduate digital services',
'Explora nuestros conceptos':'Explore our services',
'Selecciona los servicios publicados para la oficina de Posgrado.':'Select the services published by the Graduate School.',
'Catálogo institucional':'Institutional catalog',
'Identificador de oficina autorizado':'Authorized office ID',
'ID definido por Tesorería':'ID defined by Treasury',
'Consultar':'Search',
'Introduce el identificador oficial de la oficina para consultar el catálogo.':'Enter the official office ID to view the catalog.',
'Aquí aparecerán tus conceptos disponibles.':'Your available services will appear here.',
'← Seguir explorando':'← Continue browsing',
'Revisa tu carrito':'Review your cart',
'Confirma los conceptos que seleccionaste antes de identificarte.':'Confirm the services you selected before signing in.',
'Continuar con mi compra →':'Continue with my purchase →',
'Solo al continuar te pediremos iniciar sesión. No se realizará ningún cobro.':'We will only ask you to sign in when you continue. No charge will be made.',
'← Volver al carrito':'← Back to cart',
'¡Ya casi terminamos!':'Almost done!',
'Identifícate de forma segura para continuar con tus conceptos seleccionados.':'Sign in securely to continue with your selected services.',
'Ingresar con tu cuenta Google':'Sign in with your Google account',
'Puedes utilizar una cuenta Google personal o institucional. No necesitas registrarte antes de elegir conceptos.':'You may use a personal or institutional Google account. You do not need to register before selecting services.',
'Continuar con Google →':'Continue with Google →',
'La autenticación real estará disponible cuando OTI configure las credenciales y HTTPS.':'Authentication will be available when OTI configures the credentials and HTTPS.',
'Solo te solicitamos estos datos en tu primer ingreso. Se conservarán en tu perfil para próximos trámites.':'We only request this information on your first sign-in. It will be saved in your profile for future procedures.',
'Nombres *':'First names *',
'Apellidos *':'Last names *',
'Tipo de documento *':'Document type *',
'Carné de extranjería':'Foreigner ID card',
'Pasaporte':'Passport',
'Número de documento *':'Document number *',
'Teléfono':'Phone',
'Correo de contacto':'Contact email',
'Guardar y continuar →':'Save and continue →',
'← Volver':'← Back',
'Último paso de precompra':'Final pre-purchase step',
'Revisa tu información':'Review your information',
'Estos datos y conceptos se usarían para el futuro pedido cuando se habilite el pago.':'This information and these services will be used for the future order when payments are enabled.',
'👤 Datos del comprador':'👤 Buyer information',
'🧾 Conceptos seleccionados':'🧾 Selected services',
'Los precios aún no están aprobados. Por seguridad, el sistema no genera órdenes ni procesa pagos.':'Prices have not yet been approved. For security, the system does not create orders or process payments.',
'Confirmar y pagar · Próximamente':'Confirm and pay · Coming soon',
'🛍️ Tu selección':'🛍️ Your selection',
'Precompra':'Pre-purchase',
'Organiza tus conceptos antes de continuar.':'Organize your services before continuing.',
'Aún no has agregado conceptos':'You have not added any services yet',
'Puedes consultar el detalle en tu carrito.':'You can review the details in your cart.',
'Ir a comprar →':'Start shopping →',
'¿Por qué usar la tienda?':'Why use the store?',
'✓ Consulta desde tu celular':'✓ Access it from your phone',
'✓ Selecciona varios conceptos':'✓ Select multiple services',
'✓ Conserva tu perfil para futuras visitas':'✓ Keep your profile for future visits',
'Universidad Nacional de Cañete · Oficina de Tecnologías de la Información · Versión de desarrollo de la tienda de Posgrado':'National University of Cañete · Information Technology Office · Graduate Store development version',
'🛒 Tu carrito está vacío. Selecciona un concepto para empezar.':'🛒 Your cart is empty. Select a service to get started.',
'Quitar':'Remove',
'No encontramos conceptos publicados para esta búsqueda.':'We could not find published services for this search.',
'Concepto institucional publicado.':'Published institutional service.',
'＋ Agregar al carrito':'＋ Add to cart',
'Indica un ID de oficina habilitado por Tesorería.':'Enter an office ID enabled by Treasury.',
'Todavía no hay conceptos publicados para esa oficina.':'There are no published services for that office yet.',
'Tu precompra anterior ya está vinculada. La edición de carritos vinculados se habilitará en una versión posterior.':'Your previous pre-purchase is already linked. Editing linked carts will be enabled in a future version.',
'¡Concepto agregado al carrito!':'Service added to cart!',
'Concepto retirado.':'Service removed.',
'No se pudo verificar la sesión.':'The session could not be verified.',
'Agrega al menos un concepto para continuar.':'Add at least one service to continue.',
'No hay conceptos pendientes en tu carrito.':'There are no pending services in your cart.',
'Tus datos quedaron guardados para próximas compras.':'Your information was saved for future purchases.',
'No se pudo completar la operación.':'The operation could not be completed.',
'No registrado':'Not provided',
'TUSNE UNDC · Escuela de Posgrado':'TUSNE UNDC · Graduate School',
'Tarifas referenciales del apartado 6, páginas 12–14, asociado con la Resolución de Comisión Organizadora N.º 083-2026-UNDC/CO. La numeración TUSNE es documental y no corresponde a códigos confirmados de SISPAGOS.':'Reference fees from section 6, pages 12–14, associated with Organizing Committee Resolution No. 083-2026-UNDC/CO. TUSNE numbering is documentary and does not correspond to confirmed SISPAGOS codes.',
'27 procedimientos TUSNE':'27 TUSNE procedures',
'29 opciones tarifarias':'29 pricing options',
'5 categorías':'5 categories',
'Información referencial · Pagos no habilitados':'Reference information · Payments not enabled',
'Filtrar trámites por categoría':'Filter procedures by category',
'Catálogo TUSNE referencial':'Reference TUSNE catalog',
'Todos los conceptos':'All services',
'29 opciones visibles':'29 visible options',
'No encontramos trámites':'No procedures found',
'Prueba otra búsqueda o vuelve a mostrar todas las categorías.':'Try another search or show all categories again.',
'Limpiar filtros':'Clear filters',
'Cerrar detalle':'Close details',
'Catálogo oficial publicado':'Published official catalog',
'Consulta únicamente conceptos vinculados y autorizados mediante los servicios institucionales existentes.':'View only linked and authorized services through the existing institutional services.',
'SISPAGOS · Catálogo institucional':'SISPAGOS · Institutional catalog'
};
const englishPairs=Object.fromEntries(Object.entries(languagePairs).map(([es,en])=>[en,es]));
function translatedValue(value,language=currentLanguage){
const match=String(value).match(/^(\s*)([\s\S]*?)(\s*)$/);if(!match)return value;
const [,before,core,after]=match,table=language==='en'?languagePairs:englishPairs;
let translated=table[core]||core;
if(translated===core&&language==='en'){
translated=core.replace(/^(\d+) conceptos publicados$/, '$1 published services').replace(/^(\d+) conceptos$/, '$1 services').replace(/^Código (.+)$/, 'Code $1').replace(/^Cantidad: (.+)$/, 'Quantity: $1').replace(/^Concepto (.+)$/, 'Service $1');
}else if(translated===core){
translated=core.replace(/^(\d+) published services$/, '$1 conceptos publicados').replace(/^(\d+) services$/, '$1 conceptos').replace(/^Code (.+)$/, 'Código $1').replace(/^Quantity: (.+)$/, 'Cantidad: $1').replace(/^Service (.+)$/, 'Concepto $1');
}
return before+translated+after;
}
function translateNode(root){
if(root.nodeType===Node.TEXT_NODE){if(!root.parentElement?.closest('[data-no-translate],script,style')){const next=translatedValue(root.nodeValue);if(next!==root.nodeValue)root.nodeValue=next}return}
if(root.nodeType!==Node.ELEMENT_NODE&&root.nodeType!==Node.DOCUMENT_NODE)return;
const walker=document.createTreeWalker(root,NodeFilter.SHOW_TEXT);let node;
while((node=walker.nextNode()))if(!node.parentElement?.closest('[data-no-translate],script,style')){const next=translatedValue(node.nodeValue);if(next!==node.nodeValue)node.nodeValue=next}
if(root.querySelectorAll)root.querySelectorAll('[placeholder],[aria-label],[title]').forEach(element=>['placeholder','aria-label','title'].forEach(attribute=>{if(element.hasAttribute(attribute)&&!element.closest('[data-no-translate]')){const current=element.getAttribute(attribute),next=translatedValue(current);if(next!==current)element.setAttribute(attribute,next)}}));
}
Object.assign(languagePairs,{'Admisión':'Admission','Matrícula y pensiones':'Enrollment and tuition','Trámites académicos':'Academic procedures','Certificados y constancias':'Certificates and records','Tesis y grados':'Thesis and degrees','Servicios administrativos':'Administrative services','Buscar conceptos...':'Search services...','Abrir buscador':'Open search','Cerrar buscador':'Close search'});
function updateLanguageControls(){
document.querySelectorAll('[data-language-picker]').forEach(picker=>{const flag=picker.querySelector('.language-flag'),label=picker.querySelector('.language-label');flag.classList.toggle('peru-flag',currentLanguage==='es');flag.classList.toggle('usa-flag',currentLanguage==='en');label.textContent=currentLanguage==='en'?'ENGLISH':'ESPAÑOL'});
}
const themeButtons=document.querySelectorAll('[data-theme-toggle]');
function applyTheme(theme){const light=theme==='light';document.body.classList.toggle('light-mode',light);document.querySelectorAll('[data-theme-light-src][data-theme-dark-src]').forEach(image=>{const lightSrc=image.dataset.themeLightSrc,darkSrc=image.dataset.themeDarkSrc;image.onerror=()=>{image.onerror=null;image.src=lightSrc};image.src=light?lightSrc:darkSrc});themeButtons.forEach(button=>{button.setAttribute('aria-pressed',String(!light));const label=button.querySelector('.theme-label');if(label)label.textContent=currentLanguage==='en'?(light?'Light mode':'Dark mode'):(light?'Modo claro':'Modo oscuro')});}
applyTheme(localStorage.getItem('undc_theme')==='light'?'light':'dark');
themeButtons.forEach(button=>button.addEventListener('click',()=>{const theme=document.body.classList.contains('light-mode')?'dark':'light';localStorage.setItem('undc_theme',theme);applyTheme(theme)}));
document.querySelectorAll('[data-language-picker]').forEach(picker=>{const button=picker.querySelector('.language-button'),menu=picker.querySelector('.language-menu');button.addEventListener('click',()=>{const opening=menu.hidden;document.querySelectorAll('.language-menu').forEach(item=>item.hidden=true);document.querySelectorAll('.language-button').forEach(item=>item.setAttribute('aria-expanded','false'));menu.hidden=!opening;button.setAttribute('aria-expanded',String(opening))});menu.querySelectorAll('[data-language]').forEach(option=>option.addEventListener('click',()=>{const next=option.dataset.language;if(next!==currentLanguage){currentLanguage=next;localStorage.setItem('undc_language',next);translateNode(document.body);document.title=translatedValue(document.title);const description=document.querySelector('meta[name="description"]');if(description)description.content=translatedValue(description.content);document.documentElement.lang=next;applyTheme(document.body.classList.contains('light-mode')?'light':'dark');renderTusneCatalog()}updateLanguageControls();menu.hidden=true;button.setAttribute('aria-expanded','false')}))});
document.addEventListener('click',event=>{if(!event.target.closest('[data-language-picker]'))document.querySelectorAll('[data-language-picker]').forEach(picker=>{picker.querySelector('.language-menu').hidden=true;picker.querySelector('.language-button').setAttribute('aria-expanded','false')})});
if(currentLanguage==='en'){translateNode(document.body);document.title=translatedValue(document.title);const description=document.querySelector('meta[name="description"]');if(description)description.content=translatedValue(description.content);document.documentElement.lang='en';applyTheme(document.body.classList.contains('light-mode')?'light':'dark')}
updateLanguageControls();
new MutationObserver(mutations=>mutations.forEach(mutation=>{if(mutation.type==='characterData')translateNode(mutation.target);else mutation.addedNodes.forEach(translateNode)})).observe(document.body,{childList:true,subtree:true,characterData:true});
const toast=(message,error=false)=>{const node=$('toast');node.textContent=message;node.className='toast show'+(error?' error':'');clearTimeout(toast.timer);toast.timer=setTimeout(()=>node.classList.remove('show'),4700)};
const api=async(path,method='GET',body=null,auth=false)=>{
const headers={Accept:'application/json','X-CSRF-TOKEN':csrf,...(body?{'Content-Type':'application/json'}:{}),...(auth&&state.token?{Authorization:'Bearer '+state.token}:{})};
const res=await fetch(path,{method,credentials:'same-origin',headers,...(body?{body:JSON.stringify(body)}:{})});
const json=await res.json().catch(()=>({}));
if(!res.ok){const error=new Error(json.message||'No se pudo completar la operación.');error.status=res.status;error.errors=json.errors;throw error}return json.data;
};
const esc=s=>String(s??'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
const tusneCategories=[
{id:'admision',name:'Admisión',en:'Admission',icon:'🎓',color:'#28b8a4',description:'Ingreso y postulación a programas de Posgrado.',descriptionEn:'Entry and application to Graduate School programs.',count:3},
{id:'matricula_pensiones',name:'Matrícula y pensiones',en:'Enrollment and tuition',icon:'📚',color:'#2697f3',description:'Matrícula académica, pensiones mensuales y recargos.',descriptionEn:'Academic enrollment, monthly tuition and late fees.',count:6},
{id:'servicios_administrativos',name:'Servicios administrativos',en:'Administrative services',icon:'▤',color:'#52878f',description:'Derecho de trámite administrativo y carné de biblioteca.',descriptionEn:'Administrative processing and library card.'},
{id:'certificados_constancias',name:'Certificados y constancias',en:'Certificates and records',icon:'📜',color:'#a879ed',description:'Documentos, certificados y constancias académicas.',descriptionEn:'Academic documents, certificates and records.',count:9},
{id:'tramites',name:'Trámites académicos',en:'Academic procedures',icon:'⇄',color:'#e6a93d',description:'Traslados internos, externos y convalidación de asignaturas.',descriptionEn:'Internal and external transfers and course recognition.',count:3},
{id:'tesis_grados',name:'Tesis y grados',en:'Thesis and degrees',icon:'🏅',color:'#c95a68',description:'Tesis, jurados, sustentaciones y grados académicos.',descriptionEn:'Thesis, committees, defenses and academic degrees.',count:8}
];
// Publications come exclusively from the API. Old reference selections are not purchases.
let tusneConcepts=[];
let tusneCategory='all';
let catalogRequest=0;
let cartRequest=null;
const pendingItems=new Set();
const localized=(es,en)=>currentLanguage==='en'?en:es;
const generalCategory={id:'general',name:'Otros conceptos',en:'Other services',icon:'▤',color:'#2697f3',description:'Concepto publicado en el catálogo institucional.',descriptionEn:'Service published in the institutional catalog.'};
tusneCategories.push(generalCategory);
// Compatibilidad con publicaciones y enlaces anteriores durante la actualización.
const categoriasAnteriores={admission:'admision',tuition:'matricula_pensiones',transfers:'tramites',certificates:'certificados_constancias',degrees:'tesis_grados',administrative:'servicios_administrativos'};
const normalizarCategoria=id=>Object.hasOwn(categoriasAnteriores,id)?categoriasAnteriores[id]:id;
const categoryById=id=>tusneCategories.find(category=>category.id===normalizarCategoria(id))||generalCategory;
function getCatalogVisual(item){
const source=[item.name,item.en,item.category,item.code].filter(Boolean).join(' ').normalize('NFD').replace(/[\u0300-\u036f]/g,'').toLocaleLowerCase();
const rules=[
{id:'prospecto',match:/prospect/},
{id:'examen-admision',match:/examen|admission exam/},
{id:'constancia-ingreso',match:/(constancia|record|certificate).*(ingreso|admission)|(ingreso|admission).*(constancia|record|certificate)/},
{id:'matricula-doctorado',match:/(matricula|enrollment).*(doctorado|doctoral)|(doctorado|doctoral).*(matricula|enrollment)/},
{id:'matricula-maestria',match:/(matricula|enrollment).*(maestria|master)|(maestria|master).*(matricula|enrollment)/},
{id:'pension-academica',match:/pension|tuition/},
{id:'documento-academico',match:/constancia|certificado|certificate|record|grado academico|academic degree/},
{id:'tramite-generico',match:/traslado|convalidacion|transfer|recognition|tesis|thesis|jurado|sustentacion/}
];
const id=rules.find(rule=>rule.match.test(source))?.id||'tramite-generico';
const visuals={
prospecto:{file:'prospecto.svg',wash:'#DFF2FF',icon:'📘'},
'examen-admision':{file:'examen-admision.svg',wash:'#EEEAFE',icon:'☑️'},
'constancia-ingreso':{file:'constancia-ingreso.svg',wash:'#F8F0D9',icon:'📜'},
'matricula-maestria':{file:'matricula-maestria.svg',wash:'#DFF2FF',icon:'📂'},
'matricula-doctorado':{file:'matricula-doctorado.svg',wash:'#EAE8FA',icon:'📖'},
'pension-academica':{file:'pension-academica.svg',wash:'#DDF4EE',icon:'🗓️'},
'documento-academico':{file:'documento-academico.svg',wash:'#E5F2FB',icon:'📄'},
'tramite-generico':{file:'tramite-generico.svg',wash:'#E8F3F8',icon:'🗂️'}
};
const imageFile=item.category==='admision'?'tarjet.png':visuals[id].file;
return {id,...visuals[id],src:'/assets/catalogo/ilustraciones/'+imageFile};
}
function conceptDescription(item,category=categoryById(item.category)){const description=currentLanguage==='en'?(item.descriptionEn||item.description):(item.description||item.descriptionEn);if(description)return description;if(category)return localized(category.description,category.descriptionEn);return localized('Concepto publicado en el catálogo institucional.','Service published in the institutional catalog.')}
function catalogIllustration(visual){return '<div class="concept-illustration" style="--illustration-wash:'+visual.wash+'" aria-hidden="true"><img src="'+visual.src+'" alt="" loading="lazy" decoding="async"></div>'}
function cartGlyph(){return '<svg aria-hidden="true" viewBox="0 0 24 24"><circle cx="9" cy="20" r="1.5"/><circle cx="19" cy="20" r="1.5"/><path d="M3 4h2l2.5 11h11l2-8H6"/></svg>'}
function catalogReferencePrice(item){
 const amount=item.reference_amount;
 if(typeof amount!=='string'||!/^\d+(?:\.\d{1,2})?$/.test(amount))return localized('Por confirmar','To be confirmed');
 const value=Number(amount);
 return Number.isFinite(value)?'S/ '+value.toLocaleString(currentLanguage==='en'?'en-US':'es-PE',{minimumFractionDigits:2,maximumFractionDigits:2}):localized('Por confirmar','To be confirmed');
}
function renderConceptCard(item,category){
 const name=localized(item.name,item.en),visual=getCatalogVisual({...item,category:category.id});
 return '<article class="tusne-concept catalog-product"><button class="catalog-product-link" type="button" data-concept-detail="'+esc(item.id)+'" aria-haspopup="dialog" aria-label="'+esc(localized('Ver detalles de ','View details for ')+name)+'"><span class="catalog-product-image"><img src="'+esc(visual.src)+'" alt="" loading="lazy" decoding="async"></span><span class="catalog-product-copy"><span class="catalog-product-name">'+esc(name)+'</span><span class="catalog-product-price">'+esc(item.price)+'</span></span></button></article>';
}
const normalizeCatalogSearch=value=>String(value||'').normalize('NFD').replace(/[\u0300-\u036f]/g,'').toLocaleLowerCase().trim();
function matchesCatalogSearch(item,query){const category=categoryById(item.category);const searchable=normalizeCatalogSearch([item.name,item.en,item.code||item.id,item.description,item.descriptionEn,category?.name,category?.en].join(' '));return normalizeCatalogSearch(query).split(/\s+/).filter(Boolean).every(word=>searchable.includes(word))}
function searchCatalog(reveal=false){
 if(marketPage!=='catalog'){window.location.assign('/tienda?q='+encodeURIComponent($('search')?.value.trim()||''));return}
 tusneCategory='all';renderTusneCatalog();
 if(reveal){setMainView('catalogs');const results=$('tusneCatalog');results?.scrollIntoView({behavior:'smooth',block:'start'});const title=$('catalogResultsTitle');title?.setAttribute('tabindex','-1');title?.focus({preventScroll:true})}
}
function renderTusneCatalog(){
updateCategoryNavigation();
if(!$('catalogFilters'))return;
const query=$('search')?.value.trim()||'';
$('catalogFilters').innerHTML=[{id:'all',name:'Todos',en:'All'},...tusneCategories.filter(category=>tusneConcepts.some(item=>item.category===category.id))].map(category=>'<button class="catalog-filter'+(tusneCategory===category.id?' active':'')+'" type="button" data-category-filter="'+category.id+'" aria-pressed="'+String(tusneCategory===category.id)+'">'+esc(localized(category.name,category.en))+'</button>').join('');
const visible=tusneConcepts.filter(item=>(tusneCategory==='all'||item.category===tusneCategory)&&matchesCatalogSearch(item,query));
const groups=(tusneCategory==='all'?tusneCategories:[categoryById(tusneCategory)]).map(category=>({category,items:visible.filter(item=>item.category===category.id)})).filter(group=>group.items.length);
$('tusneResults').innerHTML=groups.map(({category,items})=>'<section class="category-result-group" style="--category-color:'+category.color+'"><div class="concept-grid">'+items.map(item=>renderConceptCard(item,category)).join('')+'</div></section>').join('');
$('tusneResults').hidden=!visible.length;$('tusneEmpty').hidden=!!visible.length;
$('visibleConceptCount').textContent=visible.length+' '+localized(visible.length===1?'opción visible':'opciones visibles',visible.length===1?'visible option':'visible options');
const selected=tusneCategory==='all'?null:categoryById(tusneCategory);$('catalogResultsTitle').textContent=selected?localized(selected.name,selected.en):localized('Todos los conceptos','All services');
}
function openConceptDetail(id){const item=tusneConcepts.find(concept=>concept.id===id);if(!item)return;const category=categoryById(item.category),name=localized(item.name,item.en),unit=item.unit?localized(item.unit,item.unitEn):'',note=item.note?localized(item.note,item.noteEn):localized('Esta tarifa referencial no constituye aprobación académica ni está habilitada para pago.','This reference fee does not constitute academic approval and is not enabled for payment.');const added=(state.cart?.items||[]).some(line=>String(line.catalog_item_id)===String(item.id));$('conceptDialogContent').innerHTML='<span class="dialog-category">'+esc(localized(category.name,category.en))+'</span><h2 id="conceptDialogTitle">'+esc(name)+'</h2><p class="dialog-description">'+esc(conceptDescription(item))+'</p><div class="dialog-facts"><div class="dialog-fact"><small>'+esc(localized('Código del concepto','Service code'))+'</small><strong>'+esc(item.code||item.id)+'</strong></div><div class="dialog-fact"><small>'+esc(localized('Importe referencial','Reference amount'))+'</small><strong>'+esc(item.price+(unit?' '+unit:''))+'</strong></div><div class="dialog-fact"><small>'+esc(localized('Oficina responsable','Responsible office'))+'</small><strong>'+esc(item.office?.name||localized('Por confirmar','To be confirmed'))+'</strong></div><div class="dialog-fact"><small>'+esc(localized('Disponibilidad','Availability'))+'</small><strong>'+esc(localized('No habilitado para pago','Not enabled for payment'))+'</strong></div></div><p class="dialog-note">'+esc(note)+'</p><button class="reference-add-button dialog-add-button" type="button" data-catalog-cart="'+esc(item.id)+'" aria-pressed="'+String(added)+'">'+cartGlyph()+'<span>'+esc(localized(added?'Agregado al carrito':'Agregar al carrito',added?'Added to cart':'Add to cart'))+'</span></button>';const dialog=$('conceptDialog');dialog.style.setProperty('--dialog-color',category.color);dialog.showModal()}
function screen(name){const page=['identity','review','payment'].includes(name)?'cart':name;if(page!==marketPage){window.location.assign(marketRoutes[page]||marketRoutes.catalog);return}state.screen=name;document.body.classList.toggle('market-login-view',name==='login');document.body.classList.toggle('commerce-view',name!=='catalog');document.querySelectorAll('.screen').forEach(x=>x.classList.toggle('visible',x.dataset.screen===name));document.querySelector('.page-grid')?.classList.toggle('cart-view',name==='cart');document.querySelectorAll('.step').forEach((step,i)=>{const order=['catalog','cart','identity','review'];step.classList.toggle('active',order[i]===name);step.classList.toggle('done',order.indexOf(name)>i)});window.scrollTo({top:0,behavior:'smooth'});}
function updateCategoryNavigation(){
 document.querySelectorAll('[data-category-nav]').forEach(link=>{
  const active=link.dataset.categoryNav===tusneCategory;
  link.classList.toggle('active',active);
  if(active)link.setAttribute('aria-current','page');else link.removeAttribute('aria-current');
 });
}
function selectCatalogCategory(category,reveal=true){
 category=normalizarCategoria(category);
 if(category!=='all'&&!tusneCategories.some(item=>item.id===category))return;
 if(marketPage!=='catalog'){window.location.assign('/tienda?category='+encodeURIComponent(category));return}
 tusneCategory=category;if($('search'))$('search').value='';closeSearchSuggestions();
 setMainView(category==='all'?'procedures':'catalogs');renderTusneCatalog();
 if(reveal){if(category==='all')window.scrollTo({top:0,behavior:'smooth'});else $('tusneCatalog')?.scrollIntoView({behavior:'smooth',block:'start'})}
}
function setMainView(name){document.querySelectorAll('[data-main-view]').forEach(view=>{view.hidden=view.dataset.mainView!==name});document.querySelectorAll('[data-main-view-link]').forEach(link=>{const active=link.dataset.mainViewLink===name;link.classList.toggle('active',active);if(active)link.setAttribute('aria-current','page');else link.removeAttribute('aria-current')})}
function badge(){const n=(state.cart?.items_count||0);$('cartBadge').textContent=n;if($('drawerCartBadge'))$('drawerCartBadge').textContent=n;const mobileBadge=$('mobileCartBadge');if(mobileBadge)mobileBadge.textContent=n;if($('emptyBadge'))$('emptyBadge').textContent=n?localized(n===1?'1 concepto seleccionado':'{n} conceptos seleccionados',n===1?'1 service selected':'{n} services selected').replace('{n}',n):localized('Aún no has agregado conceptos','You have not added any services yet');}
function renderCart(){
 const items=state.cart?.items||[];badge();renderTusneCatalog();
 if(!$('cartRows')){updateCartAction();return}
 const count=items.length,cartIsEmpty=count===0;
 $('cartEmptyState').hidden=!cartIsEmpty;
 document.querySelector('.cart-review-panel').classList.toggle('is-empty',cartIsEmpty);
 document.querySelector('.page-grid').classList.toggle('cart-has-items',!cartIsEmpty);
 $('cartListHeading').hidden=cartIsEmpty;$('cartEstimateRow').hidden=cartIsEmpty;
 $('cartPurchaseSummary').hidden=cartIsEmpty;
 const estimate=localized('Por confirmar','To be confirmed');
 $('cartEstimate').textContent=estimate;$('cartSummaryEstimate').textContent=estimate;
 $('cartSummaryCount').textContent=String(count);
 $('cartTotalCount').textContent=localized('Tu selección','Your selection')+' ('+count+')';
 const sideItems=$('sideCartItems');sideItems.replaceChildren();
 $('sideCartSummary').textContent=cartIsEmpty?localized('Organiza tus conceptos antes de continuar.','Organize your services before continuing.'):localized('Importes pendientes de validación.','Reference amounts, subject to validation.');
 const list=$('cartRows');list.replaceChildren();
 const trash='<svg viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M9 6V3h6v3M5 6l1 15h12l1-15M10 10v7m4-7v7"/></svg>';
 function appendRow({name,detail,price,icon,remove}){
  const row=document.createElement('div');row.className='cart-row cart-service-row';
  const visual=document.createElement('span');visual.className='cart-service-icon';visual.setAttribute('aria-hidden','true');visual.textContent=icon;
  const info=document.createElement('div');info.className='cart-service-info';
  const title=document.createElement('b');title.textContent=name;
  const meta=document.createElement('small');meta.textContent=detail;info.append(title,meta);
  const amount=document.createElement('strong');amount.className='cart-service-price';amount.textContent=price;
  row.append(visual,info,amount);
  if(remove){const button=document.createElement('button');button.type='button';button.className='mini cart-remove';button.innerHTML=trash;button.setAttribute('aria-label',localized('Quitar ','Remove ')+name);button.title=localized('Quitar','Remove');button.onclick=remove;row.append(button)}
  list.append(row);
 }
 for(const line of items)appendRow({name:tusneConcepts.find(item=>item.id===String(line.catalog_item_id))?.name||line.slug||localized('Concepto ','Service ')+line.catalog_item_id,detail:localized('Cantidad: ','Quantity: ')+line.quantity,price:localized('Por confirmar','To be confirmed'),icon:'▤',remove:state.owned?null:()=>removeItem(line.id)});
 updateCartAction();
}
function catalogMessage(message,error=false){
 const status=$('catalogStatus');if(status){status.textContent=message;status.hidden=!message;status.setAttribute('role',error?'alert':'status')}
 if($('catalogRetry'))$('catalogRetry').hidden=!error;
}
async function loadCatalog(){
 const request=++catalogRequest;
 tusneConcepts=[];renderTusneCatalog();closeSearchSuggestions();
 const office=state.office;
 if(!office){catalogMessage(localized('Selecciona una oficina para ver sus conceptos.','Select an office to view its services.'));return}
 catalogMessage(localized('Cargando catálogo…','Loading catalog…'));
 try{
  const data=await api('/api/v1/tienda/catalogo?office_id='+encodeURIComponent(office));
  if(request!==catalogRequest)return;
  if(!Array.isArray(data))throw new Error('Respuesta de catálogo no válida.');
  tusneConcepts=data.filter(item=>Number.isSafeInteger(item.id)&&item.id>0).map(item=>({
   ...item,id:String(item.id),en:item.name,category:categoryById(item.category).id,
   descriptionEn:item.description,price:catalogReferencePrice(item),
   note:item.requirements||localized('Importe pendiente de autorización. Pago no disponible.','Amount pending approval. Payment unavailable.'),
   noteEn:item.requirements||'Amount pending approval. Payment unavailable.'
  }));
  renderTusneCatalog();renderCart();
  catalogMessage(tusneConcepts.length?'':localized('Esta oficina no tiene conceptos publicados disponibles.','This office has no available published services.'));
 }catch(e){if(request!==catalogRequest)return;catalogMessage(localized('No se pudo cargar el catálogo. Reintenta.','Could not load the catalog. Retry.'),true)}
}
async function loadOffices(){
 if(!$('catalogOffice'))return;
 catalogMessage(localized('Cargando oficinas…','Loading offices…'));
 try{
  const data=await api('/api/v1/tienda/catalogo/oficinas');
  if(!Array.isArray(data))throw new Error('Respuesta de oficinas no válida.');
  const select=$('catalogOffice');
  const offices=data.filter(item=>Number.isSafeInteger(item.id)&&item.id>0);
  if($('catalogOfficeControls'))$('catalogOfficeControls').hidden=offices.length<=1;
  select.innerHTML='<option value="">'+esc(localized('Selecciona una oficina','Select an office'))+'</option>'+offices.map(office=>'<option value="'+office.id+'">'+esc(office.name)+'</option>').join('');
  const requested=new URLSearchParams(window.location.search).get('office_id');
  const preferred=requested||state.office;
  state.office=offices.some(office=>String(office.id)===String(preferred))?String(preferred):(offices.length===1?String(offices[0].id):'');
  select.value=state.office;select.disabled=!offices.length;
  if(state.office)sessionStorage.setItem('undc_office_id',state.office);else sessionStorage.removeItem('undc_office_id');
  if(!offices.length){tusneConcepts=[];renderTusneCatalog();catalogMessage(localized('No hay oficinas con catálogo disponible.','No offices have an available catalog.'));return}
  await loadCatalog();
 }catch(e){catalogMessage(localized('No se pudieron cargar las oficinas. Reintenta.','Could not load offices. Retry.'),true)}
}
$('catalogOffice')?.addEventListener('change',()=>{state.office=$('catalogOffice').value;sessionStorage.setItem('undc_office_id',state.office);loadCatalog()});
$('catalogRetry')?.addEventListener('click',loadOffices);
function ensureCart(){
 if(!cartRequest)cartRequest=loadAnonymousCart().finally(()=>{cartRequest=null});
 return cartRequest;
}
async function loadAnonymousCart(){if(state.token){try{state.cart=await api('/api/v1/tienda/carritos/actual','GET',null,true);renderCart();return}catch(e){if(![401,404].includes(e.status))throw e;sessionStorage.removeItem('undc_cart_token');state.token=null}}
const result=await api('/api/v1/tienda/carritos','POST');state.token=result.token;sessionStorage.setItem('undc_cart_token',state.token);state.cart=result.cart;state.owned=false;renderCart()}
async function addItem(id){
 if(pendingItems.has(id))return;
 pendingItems.add(id);
 try{
  if(state.owned){toast('Tu precompra anterior ya está vinculada. La edición de carritos vinculados se habilitará en una versión posterior.',true);return}
  if(!tusneConcepts.some(item=>item.id===String(id)))return;
  await ensureCart();
  state.cart=await api('/api/v1/tienda/carritos/actual/items','POST',{catalog_item_id:Number(id),quantity:1},true);
  renderCart();renderTusneCatalog();toast('¡Concepto agregado al carrito!');
 }catch(e){toast(e.message,true)}finally{pendingItems.delete(id)}
}
async function removeItem(id){try{state.cart=await api('/api/v1/tienda/carritos/actual/items/'+id,'DELETE',null,true);renderCart();renderTusneCatalog();toast('Concepto retirado.')}catch(e){toast(e.message,true)}}
function cartHasConcepts(){return (state.cart?.items?.length||0)>0}
function updateCartAction(){
 const hasItems=cartHasConcepts();
 const label=state.authenticated?localized('Revisar mi precompra','Review my selection'):localized('Iniciar sesión para continuar','Sign in to continue');
 for(const id of ['cartContinue','sideContinue']){const button=$(id);if(!button)continue;button.disabled=!hasItems;button.textContent=label+' →'}
}
async function authState(){const res=await fetch('/api/v1/acceso/estado',{headers:{Accept:'application/json'},credentials:'same-origin'});if(res.status===401){state.authenticated=false;updateCartAction();return false}if(!res.ok)throw new Error('No se pudo verificar la sesión.');state.authenticated=true;const account=await res.json();state.profileComplete=account.data?.profile_complete!==false;if(!state.profileComplete&&!['profile','register'].includes(marketPage))window.location.assign('/tienda/perfil');updateCartAction();return true}
async function purchase(){if(!cartHasConcepts()){toast('Agrega al menos un concepto para continuar.',true);return}screen('cart');await continuePurchase()}
async function continuePurchase(){
try{
 if(!cartHasConcepts()){toast(localized('Agrega al menos un concepto para continuar.','Add at least one service to continue.'),true);return}
 if(!(await authState())){sessionStorage.setItem('undc_resume_purchase','1');screen('login');$('marketLoginTitle')?.focus({preventScroll:true});return}
 if(state.profileComplete===false)return;
 // Only the server cart can continue to pre-purchase review.

if(state.token&&state.cart?.items_count>0&&!state.owned){await api('/api/v1/tienda/precompra/vincular-carrito','POST',{cart_token:state.token});sessionStorage.removeItem('undc_cart_token');state.token=null;state.owned=true}
await refreshReview()}catch(e){toast(e.message,true)}}

async function showPaymentPreview(){
 const profile=await api('/api/v1/acceso/perfil');
 const institutional=state.cart?.items||[];
 $('paymentBuyerName').textContent=[profile?.first_names,profile?.last_names].filter(Boolean).join(' ');
 $('paymentBuyerDocument').textContent=[profile?.document_type,profile?.document_number].filter(Boolean).join(' ');
 $('paymentBuyerEmail').textContent=profile?.contact_email||'';
 const box=$('paymentPreviewItems');box.replaceChildren();
 for(const item of institutional){const row=document.createElement('div');row.className='payment-preview-line';const name=document.createElement('strong');name.textContent=(tusneConcepts.find(concept=>concept.id===String(item.catalog_item_id))?.name||item.slug||'Concepto')+' × '+item.quantity;const price=document.createElement('span');price.textContent=localized('Por confirmar','To be confirmed');row.append(name,price);box.append(row)}
 $('paymentPreviewCount').textContent=String(institutional.length);
 $('paymentPreviewTotal').textContent=localized('Por confirmar','To be confirmed');
 screen('payment');$('paymentPreviewTitle').focus({preventScroll:true});
}

async function refreshReview(){
const data=await api('/api/v1/tienda/precompra/resumen');state.owned=!!data.cart;state.cart=data.cart;state.profile=data.profile;renderCart();
if(!data.profile_complete){screen('identity');$('loginBox').hidden=true;$('profileBox').hidden=false;
if(data.profile)for(const [key,value] of Object.entries(data.profile)){const el=$('profileForm').elements.namedItem(key);if(el&&typeof value==='string')el.value=value}return;}
if(data.next_step!=='review'){screen('catalog');toast('No hay conceptos pendientes en tu carrito.');return}
screen('review');$('buyerName').textContent=[data.profile.first_names,data.profile.last_names].filter(Boolean).join(' ');
$('buyerDocument').textContent=(data.profile.document_type||'')+' '+(data.profile.document_number||'');
$('buyerContact').textContent=data.profile.contact_email||data.profile.phone||'No registrado';
const box=$('reviewList');box.replaceChildren();for(const item of data.cart?.items||[]){const row=document.createElement('div');row.className='cart-row';const strong=document.createElement('b');strong.textContent=item.slug;const amount=document.createElement('small');amount.textContent='Cantidad: '+item.quantity;row.append(strong,amount);box.append(row);}
$('reviewCount').textContent=data.cart.items_count+' conceptos';await showPaymentPreview();}
async function restore(){
if(['profile','register'].includes(marketPage))return;
try{if(await authState()){if(state.profileComplete===false)return;const owned=await api('/api/v1/tienda/precompra/resumen');if(owned.cart?.items_count>0){state.owned=true;state.cart=owned.cart;renderCart()}}
if(!state.owned)await ensureCart();
if(state.authenticated&&sessionStorage.getItem('undc_resume_purchase')==='1'){if(marketPage!=='cart'){screen('cart');return}sessionStorage.removeItem('undc_resume_purchase');await continuePurchase();}
}catch(e){toast(e.message,true)}}

const searchInput=$('search'),searchSuggestions=$('searchSuggestions');
let suggestedProducts=[],activeSuggestion=-1;
function closeSearchSuggestions(){if(searchSuggestions)searchSuggestions.hidden=true;searchInput?.setAttribute('aria-expanded','false');searchInput?.removeAttribute('aria-activedescendant');activeSuggestion=-1}
function setHeaderSearchOpen(open,focus=true){
 const form=$('headerSearch'),toggle=$('catalogSearchToggle');if(!form||!toggle)return;
 form.hidden=!open;toggle.setAttribute('aria-expanded',String(open));
 toggle.setAttribute('aria-label',localized(open?'Cerrar buscador':'Abrir buscador',open?'Close search':'Open search'));
 if(open){if(focus)searchInput?.focus()}else{closeSearchSuggestions();if(focus)toggle.focus()}
}
$('catalogSearchToggle')?.addEventListener('click',()=>setHeaderSearchOpen($('headerSearch').hidden));
$('headerSearch')?.addEventListener('keydown',event=>{if(event.key==='Escape'){event.preventDefault();setHeaderSearchOpen(false)}});
function catalogSuggestions(query){
 const normalized=normalizeCatalogSearch(query);if(!normalized)return [];
 return tusneConcepts.filter(item=>matchesCatalogSearch(item,query)).sort((a,b)=>{
  const rank=item=>{const name=normalizeCatalogSearch(localized(item.name,item.en));return name===normalized?0:name.startsWith(normalized)?1:2};
  return rank(a)-rank(b);
 }).slice(0,6);
}
function renderSearchSuggestions(){
 if(!searchInput||!searchSuggestions)return;
 suggestedProducts=catalogSuggestions(searchInput.value);activeSuggestion=-1;
 searchInput.removeAttribute('aria-activedescendant');
 if(!searchInput.value.trim()){closeSearchSuggestions();return}
 searchSuggestions.innerHTML=suggestedProducts.length?suggestedProducts.map((item,index)=>'<div class="search-suggestion" id="searchSuggestion'+index+'" role="option" aria-selected="false" data-search-suggestion="'+index+'"><strong>'+esc(localized(item.name,item.en))+'</strong><small>'+esc(localized(categoryById(item.category).name,categoryById(item.category).en))+' · '+esc(item.price)+'</small></div>').join(''):'<div class="search-no-results">'+esc(localized('No encontramos productos. Prueba otro nombre.','No products found. Try another name.'))+'</div>';
 searchSuggestions.hidden=false;searchInput.setAttribute('aria-expanded','true');
}
function chooseSearchSuggestion(index){
 const item=suggestedProducts[index];if(!item)return;
 searchInput.value=localized(item.name,item.en);closeSearchSuggestions();searchCatalog(true);
}
searchInput?.addEventListener('focus',renderSearchSuggestions);
searchInput?.addEventListener('keydown',event=>{
 if(event.key==='Escape'){closeSearchSuggestions();return}
 if(searchSuggestions?.hidden)return;
 if(['ArrowDown','ArrowUp'].includes(event.key)&&suggestedProducts.length){
  event.preventDefault();activeSuggestion=(activeSuggestion+(event.key==='ArrowDown'?1:-1)+suggestedProducts.length)%suggestedProducts.length;
  searchSuggestions.querySelectorAll('[role="option"]').forEach((option,index)=>option.setAttribute('aria-selected',String(index===activeSuggestion)));
  searchInput.setAttribute('aria-activedescendant','searchSuggestion'+activeSuggestion);
 }else if(event.key==='Enter'&&activeSuggestion>=0){event.preventDefault();chooseSearchSuggestion(activeSuggestion)}
});
searchSuggestions?.addEventListener('click',event=>{const option=event.target.closest('[data-search-suggestion]');if(option)chooseSearchSuggestion(Number(option.dataset.searchSuggestion))});
document.addEventListener('click',event=>{if(!event.target.closest('.header-search'))closeSearchSuggestions()});
document.addEventListener('focusin',event=>{if(!event.target.closest('.header-search'))closeSearchSuggestions()});

$('search')?.addEventListener('input',()=>{searchCatalog();renderSearchSuggestions();});
$('catalogFilters')?.addEventListener('click',event=>{const button=event.target.closest('[data-category-filter]');if(!button)return;tusneCategory=button.dataset.categoryFilter;renderTusneCatalog()});
$('tusneResults')?.addEventListener('click',event=>{const button=event.target.closest('[data-concept-detail]');if(button)openConceptDetail(button.dataset.conceptDetail)});
$('conceptDialogContent')?.addEventListener('click',async event=>{const button=event.target.closest('[data-catalog-cart]');if(!button)return;button.disabled=true;try{await addItem(button.dataset.catalogCart);if((state.cart?.items||[]).some(line=>String(line.catalog_item_id)===String(button.dataset.catalogCart)))$('conceptDialog')?.close()}finally{button.disabled=false}});
$('clearCatalogFilters')?.addEventListener('click',()=>{tusneCategory='all';$('search').value='';renderTusneCatalog();});
$('conceptDialogClose')?.addEventListener('click',()=>$('conceptDialog').close());$('conceptDialog')?.addEventListener('click',event=>{if(event.target===$('conceptDialog'))$('conceptDialog').close()});
const headerNav=$('headerNavPanel'),menuButton=$('headerMenuButton'),mobileDrawer=$('mobileDrawer');
const closeHeaderMenu=()=>{headerNav?.classList.remove('is-open');if(mobileDrawer?.open)mobileDrawer.close();menuButton?.setAttribute('aria-expanded','false');document.body.classList.remove('mobile-drawer-open')};
menuButton?.addEventListener('click',()=>{if(!mobileDrawer)return;if(mobileDrawer.open){closeHeaderMenu();return}mobileDrawer.showModal();document.body.classList.add('mobile-drawer-open');menuButton.setAttribute('aria-expanded','true');$('mobileDrawerClose').focus()});
$('mobileDrawerClose')?.addEventListener('click',closeHeaderMenu);
mobileDrawer?.addEventListener('close',()=>{menuButton?.setAttribute('aria-expanded','false');document.body.classList.remove('mobile-drawer-open')});
mobileDrawer?.addEventListener('click',event=>{if(event.target!==mobileDrawer)return;const box=mobileDrawer.getBoundingClientRect();if(event.clientX<box.left||event.clientX>box.right||event.clientY<box.top||event.clientY>box.bottom)closeHeaderMenu()});
mobileDrawer?.querySelectorAll('[data-accessibility-toggle]').forEach(button=>button.addEventListener('click',closeHeaderMenu));
window.addEventListener('resize',()=>{if(window.innerWidth>940)closeHeaderMenu()});
document.querySelector('.header-search')?.addEventListener('submit',event=>{event.preventDefault();closeSearchSuggestions();searchCatalog(true);closeHeaderMenu()});
document.querySelectorAll('.nav-links a, .drawer-links a').forEach(link=>link.addEventListener('click',event=>{const view=link.dataset.mainViewLink;if(view){event.preventDefault();if(marketPage!=='catalog'){window.location.assign('/tienda?view='+encodeURIComponent(view));return}if(view==='procedures'){tusneCategory='all';renderTusneCatalog()}setMainView(view)}closeHeaderMenu()}));
$('cartButton')?.addEventListener('click',()=>screen('cart'));$('mobileCartButton')?.addEventListener('click',()=>{screen('cart');closeHeaderMenu()});$('sideContinue')?.addEventListener('click',purchase);$('cartContinue')?.addEventListener('click',continuePurchase);
document.querySelectorAll('[data-go]').forEach(el=>el.addEventListener('click',()=>screen(el.dataset.go)));
$('googleButton')?.addEventListener('click',()=>{sessionStorage.setItem('undc_resume_purchase','1');window.location.assign('/auth/google/redirect')});
$('profileForm')?.addEventListener('submit',async event=>{event.preventDefault();const form=new FormData(event.currentTarget);
try{await api('/api/v1/acceso/perfil','PUT',Object.fromEntries(form.entries()));await refreshReview();toast('Tus datos quedaron guardados para próximas compras.')}catch(e){let msg=e.message;if(e.errors){msg=Object.values(e.errors).flat().join(' ')}toast(msg,true)}});
$('reviewBack')?.addEventListener('click',()=>screen('cart'));
renderTusneCatalog();loadOffices();restore();

/* Accessibility panel: browser-native speech, local preferences, keyboard access. */
const accessibilityPanel=$('accessibilityPanel');
const accessibilityTriggers=[...document.querySelectorAll('[data-accessibility-toggle]')];
let accessibilityOpener=null, accessibilitySelection='', accessibilityTheme='dark';
const preference=(key,fallback)=>{try{return localStorage.getItem(key)||fallback}catch{return fallback}};
const savePreference=(key,value)=>{try{localStorage.setItem(key,String(value))}catch{}};
let accessibilitySize=Math.max(1,Math.min(2,Number(preference('undc_accessibility_size','1'))||1));
function setAccessibilitySize(value){
 accessibilitySize=Math.round(Math.max(1,Math.min(2,value))*10)/10;
 document.documentElement.style.setProperty('--a11y-text-scale',String(accessibilitySize));
 document.documentElement.toggleAttribute('data-accessibility-text',accessibilitySize>1);
 $('accessibilityTextSize').textContent=Math.round(accessibilitySize*100)+' %';
 $('accessibilitySmaller').disabled=accessibilitySize<=1;
 $('accessibilityLarger').disabled=accessibilitySize>=2;
 savePreference('undc_accessibility_size',accessibilitySize);
}
function setAccessibilityTheme(value){
 accessibilityTheme=['light','sepia','dark','contrast','yellow'].includes(value)?value:'dark';
 delete document.body.dataset.accessibilityColor;
 const base=['light','sepia'].includes(accessibilityTheme)?'light':'dark';
 applyTheme(base);savePreference('undc_theme',base);
 if(!['light','dark'].includes(accessibilityTheme))document.body.dataset.accessibilityColor=accessibilityTheme;
 document.querySelectorAll('[data-accessibility-theme]').forEach(button=>button.setAttribute('aria-pressed',String(button.dataset.accessibilityTheme===accessibilityTheme)));
 savePreference('undc_accessibility_theme',accessibilityTheme);
}
function closeAccessibility(restoreFocus=false){
 accessibilityPanel.hidden=true;
 accessibilityTriggers.forEach(button=>button.setAttribute('aria-expanded','false'));
 if(restoreFocus&&accessibilityOpener?.isConnected)accessibilityOpener.focus();
}
accessibilityTriggers.forEach(button=>{
 button.addEventListener('pointerdown',()=>{const selected=window.getSelection()?.toString().trim();if(selected)accessibilitySelection=selected});
 button.addEventListener('click',()=>{
  if(!accessibilityPanel.hidden){closeAccessibility(true);return}
  const selected=window.getSelection()?.toString().trim();if(selected)accessibilitySelection=selected;
  accessibilityOpener=button;
  accessibilityPanel.hidden=false;
  accessibilityTriggers.forEach(item=>item.setAttribute('aria-expanded','true'));
  $('accessibilityClose').focus();
 });
});
$('accessibilityClose').addEventListener('click',()=>closeAccessibility(true));
document.addEventListener('keydown',event=>{if(event.key==='Escape'&&!accessibilityPanel.hidden){event.preventDefault();event.stopPropagation();closeAccessibility(true)}});
document.addEventListener('click',event=>{if(!accessibilityPanel.hidden&&!accessibilityPanel.contains(event.target)&&!accessibilityTriggers.some(button=>button.contains(event.target)))closeAccessibility()});
document.addEventListener('selectionchange',()=>{
 const selection=window.getSelection(),selected=selection?.toString().trim();
 if(selected&&!selection.anchorNode?.parentElement?.closest('#accessibilityPanel'))accessibilitySelection=selected;
});
$('accessibilitySmaller').addEventListener('click',()=>setAccessibilitySize(accessibilitySize-.1));
$('accessibilityLarger').addEventListener('click',()=>setAccessibilitySize(accessibilitySize+.1));
document.querySelectorAll('[data-accessibility-theme]').forEach(button=>button.addEventListener('click',()=>setAccessibilityTheme(button.dataset.accessibilityTheme)));
setAccessibilitySize(accessibilitySize);
setAccessibilityTheme(preference('undc_accessibility_theme',preference('undc_theme','dark')));

const speechAvailable='speechSynthesis' in window&&'SpeechSynthesisUtterance' in window;
const speechEngine=speechAvailable?window.speechSynthesis:null;
let speechVoices=[],speechGeneration=0,speechUtterance=null;
const voiceSelect=$('accessibilityVoice'),rateInput=$('accessibilityRate');
rateInput.value=String(Math.max(.5,Math.min(2,Number(preference('undc_accessibility_rate','1'))||1)));
function updateSpeechRate(){
 $('accessibilityRateValue').textContent=Number(rateInput.value).toFixed(1)+'×';
 rateInput.setAttribute('aria-valuetext',Number(rateInput.value).toFixed(1)+'×');
 savePreference('undc_accessibility_rate',rateInput.value);
}
function setReadingState(value){
 const labels={inactive:['Lectura: inactiva','Reading: inactive'],active:['Lectura: activa','Reading: active'],paused:['Lectura: pausada','Reading: paused']};
 const badge=$('accessibilityReadingState');badge.textContent=labels[value][currentLanguage==='en'?1:0];badge.dataset.reading=value;
 $('accessibilityPause').disabled=value==='inactive';
 $('accessibilityPause').setAttribute('aria-pressed',String(value==='paused'));
 $('accessibilityPauseLabel').textContent=localized(value==='paused'?'Reanudar':'Pausar',value==='paused'?'Resume':'Pause');
}
function speechStatus(es,en){$('accessibilityStatus').textContent=currentLanguage==='en'?en:es}
function stopReading(announce=true){speechGeneration++;speechEngine?.cancel();speechUtterance=null;setReadingState('inactive');if(announce)speechStatus('Lectura detenida.','Reading stopped.')}
function populateVoices(){
 const saved=voiceSelect.value||preference('undc_accessibility_voice','');
 speechVoices=speechEngine?.getVoices()||[];
 voiceSelect.replaceChildren();
 const automatic=document.createElement('option');automatic.value='';automatic.textContent=currentLanguage==='en'?'Automatic (page language)':'Automática (idioma de la página)';voiceSelect.append(automatic);
 speechVoices.forEach(voice=>{const option=document.createElement('option');option.value=voice.voiceURI;option.textContent=voice.name+' ('+voice.lang+')';voiceSelect.append(option)});
 voiceSelect.value=speechVoices.some(voice=>voice.voiceURI===saved)?saved:'';
}
function readText(text){
 if(!speechAvailable)return;
 stopReading(false);
 const clean=String(text||'').replace(/\s+/g,' ').trim();
 if(!clean){speechStatus('Selecciona primero un texto de la página.','Select some page text first.');return}
 // Queue short utterances to avoid long-text truncation in speech engines.
 const chunks=clean.match(/.{1,180}(?:\s|$)|.{1,180}/g)||[clean];
 const generation=speechGeneration,chosen=speechVoices.find(voice=>voice.voiceURI===voiceSelect.value);
 const language=currentLanguage==='en'?'en':'es';
 const voice=chosen||speechVoices.find(item=>item.lang.toLowerCase().startsWith(language));
 let next=0;
 const speakNext=()=>{
  if(generation!==speechGeneration)return;
  if(next>=chunks.length){speechUtterance=null;setReadingState('inactive');speechStatus('Lectura finalizada.','Reading finished.');return}
  const utterance=new window.SpeechSynthesisUtterance(chunks[next++]);
  speechUtterance=utterance;utterance.lang=voice?.lang||(language==='en'?'en-US':'es-PE');
  if(voice)utterance.voice=voice;
  utterance.rate=Number(rateInput.value);
  utterance.onend=speakNext;
  utterance.onerror=event=>{if(generation===speechGeneration&&event.error!=='canceled'&&event.error!=='interrupted'){setReadingState('inactive');speechStatus('No se pudo iniciar la voz. Prueba otra voz disponible.','Could not start speech. Try another available voice.')}};
  speechEngine.speak(utterance);
 };
 setReadingState('active');speechStatus('Leyendo…','Reading…');speakNext();
}
$('accessibilitySelection').addEventListener('click',()=>readText(window.getSelection()?.toString().trim()||accessibilitySelection));
$('accessibilityPage').addEventListener('click',()=>{
 const hero=[...document.querySelectorAll('.hero')].find(element=>!element.hidden);
 readText([hero?.innerText,document.querySelector('main')?.innerText].filter(Boolean).join('. '));
});
$('accessibilityPause').addEventListener('click',()=>{
 if(!speechAvailable||!speechUtterance)return;
 const paused=$('accessibilityReadingState').dataset.reading==='paused';
 if(paused){speechEngine.resume();setReadingState('active');speechStatus('Leyendo…','Reading…')}
 else{speechEngine.pause();setReadingState('paused');speechStatus('Lectura pausada.','Reading paused.')}
});
$('accessibilityStop').addEventListener('click',()=>stopReading());
voiceSelect.addEventListener('change',()=>{savePreference('undc_accessibility_voice',voiceSelect.value);stopReading(false)});
rateInput.addEventListener('input',()=>{updateSpeechRate();stopReading(false)});
$('accessibilityReset').addEventListener('click',()=>{
 stopReading(false);setAccessibilitySize(1);setAccessibilityTheme('light');
 voiceSelect.value='';savePreference('undc_accessibility_voice','');rateInput.value='1';updateSpeechRate();
 speechStatus('Configuración restablecida.','Settings reset.');
});
updateSpeechRate();populateVoices();setReadingState('inactive');
if(speechAvailable){
 speechEngine.addEventListener('voiceschanged',populateVoices);
 window.addEventListener('pagehide',()=>stopReading(false));
}else{
 ['accessibilitySelection','accessibilityPage','accessibilityPause','accessibilityStop','accessibilityVoice','accessibilityRate'].forEach(id=>$(id).disabled=true);
 speechStatus('La lectura en voz alta no está disponible en este navegador.','Read aloud is not available in this browser.');
}
document.querySelectorAll('[data-language]').forEach(button=>button.addEventListener('click',()=>{stopReading(false);populateVoices();if(speechAvailable)$('accessibilityStatus').textContent=''}));


document.querySelectorAll('[data-login-trigger]').forEach(link=>link.addEventListener('click',event=>{
 event.preventDefault();closeHeaderMenu();screen('login');$('marketLoginTitle')?.focus({preventScroll:true});
}));
$('marketLoginGoogle')?.addEventListener('click',()=>{if(cartHasConcepts())sessionStorage.setItem('undc_resume_purchase','1')});
document.querySelectorAll('[data-category-nav]').forEach(link=>link.addEventListener('click',event=>{event.preventDefault();selectCatalogCategory(link.dataset.categoryNav);closeHeaderMenu()}));
const requestedCategory=new URLSearchParams(window.location.search).get('category');
if(requestedCategory)selectCatalogCategory(requestedCategory,false);
const requestedView=new URLSearchParams(window.location.search).get('view');
if(marketPage==='catalog'&&['procedures','catalogs','categories'].includes(requestedView))setMainView(requestedView);

const requestedSearch=new URLSearchParams(window.location.search).get('q');
if(marketPage==='catalog'&&requestedSearch!==null&&$('search')){setHeaderSearchOpen(true,false);$('search').value=requestedSearch;searchCatalog(true)}

document.addEventListener('click',event=>{document.querySelectorAll('.account-menu[open]').forEach(menu=>{if(!menu.contains(event.target))menu.open=false})});
document.addEventListener('keydown',event=>{if(event.key==='Escape')document.querySelectorAll('.account-menu[open]').forEach(menu=>{menu.open=false;menu.querySelector('summary')?.focus()})});
document.querySelectorAll('[data-logout-form]').forEach(form=>form.addEventListener('submit',()=>{sessionStorage.removeItem('undc_resume_purchase');sessionStorage.removeItem('undc_cart_token')}));

})();

