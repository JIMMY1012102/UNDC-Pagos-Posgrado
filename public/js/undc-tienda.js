(()=>{'use strict';
const $=id=>document.getElementById(id), csrf=document.querySelector('meta[name="csrf-token"]').content;
const state={token:sessionStorage.getItem('undc_cart_token'),cart:null,owned:false,profile:null,office:sessionStorage.getItem('undc_office_id')||'',catalog:[],screen:'catalog',authenticated:false};
let currentLanguage=localStorage.getItem('undc_language')==='en'?'en':'es';
const languagePairs={
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
{id:'admission',name:'Admisión',en:'Admission',icon:'🎓',color:'#28b8a4',description:'Ingreso y postulación a programas de Posgrado.',descriptionEn:'Entry and application to Graduate School programs.',count:3},
{id:'tuition',name:'Matrícula y pensiones',en:'Enrollment and tuition',icon:'📚',color:'#2697f3',description:'Matrícula académica, pensiones mensuales y recargos.',descriptionEn:'Academic enrollment, monthly tuition and late fees.',count:6},
{id:'certificates',name:'Certificados y constancias',en:'Certificates and records',icon:'📜',color:'#a879ed',description:'Documentos, certificados y constancias académicas.',descriptionEn:'Academic documents, certificates and records.',count:9},
{id:'transfers',name:'Traslados y convalidaciones',en:'Transfers and credit recognition',icon:'⇄',color:'#e6a93d',description:'Traslados internos, externos y convalidación de asignaturas.',descriptionEn:'Internal and external transfers and course recognition.',count:3},
{id:'degrees',name:'Tesis y grados académicos',en:'Thesis and academic degrees',icon:'🏅',color:'#c95a68',description:'Tesis, jurados, sustentaciones y grados académicos.',descriptionEn:'Thesis, committees, defenses and academic degrees.',count:8}
];
const tusneConcepts=[
{id:'6.15',category:'admission',name:'Prospecto de admisión de Posgrado',en:'Graduate admission prospectus',price:'S/ 50.00'},
{id:'6.10',category:'admission',name:'Derecho de inscripción al examen de admisión de Posgrado',en:'Graduate admission exam registration fee',price:'S/ 300.00'},
{id:'6.7',category:'admission',name:'Constancia de ingreso de Posgrado',en:'Graduate admission record',price:'S/ 50.00'},
{id:'6.12',category:'tuition',name:'Derecho de matrícula de Maestría',en:"Master's enrollment fee",price:'S/ 300.00'},
{id:'6.11',category:'tuition',name:'Derecho de matrícula de Doctorado',en:'Doctoral enrollment fee',price:'S/ 500.00'},
{id:'6.21',category:'tuition',name:'Pensión de enseñanza mensual de Maestría',en:"Monthly Master's tuition",price:'S/ 400.00',note:'El período correspondiente deberá identificarse antes de habilitar el pago.',noteEn:'The applicable period must be identified before payment is enabled.'},
{id:'6.20',category:'tuition',name:'Pensión de enseñanza mensual de Doctorado',en:'Monthly Doctoral tuition',price:'S/ 600.00',note:'El período correspondiente deberá identificarse antes de habilitar el pago.',noteEn:'The applicable period must be identified before payment is enabled.'},
{id:'6.17',category:'tuition',name:'Recargo por matrícula extemporánea de Maestría',en:"Late Master's enrollment surcharge",price:'S/ 50.00',note:'No disponible como concepto de libre compra hasta definir sus condiciones.',noteEn:'Not available for open purchase until its conditions are defined.'},
{id:'6.16',category:'tuition',name:'Recargo por matrícula extemporánea de Doctorado',en:'Late Doctoral enrollment surcharge',price:'S/ 60.00',note:'No disponible como concepto de libre compra hasta definir sus condiciones.',noteEn:'Not available for open purchase until its conditions are defined.'},
{id:'6.1',category:'certificates',name:'Derecho de trámite administrativo',en:'Administrative processing fee',price:'S/ 5.00'},
{id:'6.2',category:'certificates',name:'Carné de biblioteca de Posgrado',en:'Graduate School library card',price:'S/ 10.00'},
{id:'6.3',category:'certificates',name:'Certificado de estudios de Doctorado',en:'Doctoral transcript certificate',price:'S/ 91.00'},
{id:'6.4',category:'certificates',name:'Certificado de estudios de Maestría',en:"Master's transcript certificate",price:'S/ 72.00'},
{id:'6.5',category:'certificates',name:'Constancia de egresado de Posgrado',en:'Graduate completion record',price:'S/ 40.00'},
{id:'6.6',category:'certificates',name:'Constancia de estudios de Posgrado',en:'Graduate enrollment record',price:'S/ 30.00'},
{id:'6.8',category:'certificates',name:'Constancia de no adeudo de la Escuela de Posgrado',en:'Graduate School no-debt record',price:'S/ 50.00'},
{id:'6.22',category:'certificates',name:'Constancia de similitud de Posgrado (Turnitin)',en:'Graduate similarity report (Turnitin)',price:'S/ 40.00'},
{id:'6.27',category:'certificates',name:'Otras constancias',en:'Other records',price:'S/ 50.00',note:'El derecho de trámite administrativo de S/ 5.00 no se suma automáticamente. Su aplicación conjunta requiere confirmación de Posgrado y Tesorería.',noteEn:'The S/ 5.00 administrative processing fee is not added automatically. Its joint application requires confirmation from the Graduate School and Treasury.'},
{id:'6.9',category:'transfers',name:'Convalidación de asignatura de Posgrado',en:'Graduate course recognition',price:'S/ 100.00',unit:'por asignatura',unitEn:'per course',note:'La cantidad de asignaturas requiere validación académica.',noteEn:'The number of courses requires academic validation.'},
{id:'6.18',category:'transfers',name:'Traslado externo de Posgrado',en:'External Graduate School transfer',price:'S/ 300.00',note:'Sujeto a requisitos y evaluación de la Escuela de Posgrado.',noteEn:'Subject to requirements and evaluation by the Graduate School.'},
{id:'6.19',category:'transfers',name:'Traslado interno de Posgrado',en:'Internal Graduate School transfer',price:'S/ 200.00',note:'Sujeto a requisitos y evaluación de la Escuela de Posgrado.',noteEn:'Subject to requirements and evaluation by the Graduate School.'},
{id:'6.23-m',tusne:'6.23',category:'degrees',name:'Asesoramiento de tesis para Maestría',en:"Master's thesis advising",price:'S/ 600.00'},
{id:'6.23-d',tusne:'6.23',category:'degrees',name:'Asesoramiento de tesis para Doctorado',en:'Doctoral thesis advising',price:'S/ 800.00'},
{id:'6.24',category:'degrees',name:'Inscripción de proyecto de tesis para Maestría o Doctorado',en:"Master's or Doctoral thesis project registration",price:'S/ 200.00'},
{id:'6.25',category:'degrees',name:'Nombramiento de jurado para Maestría o Doctorado',en:"Master's or Doctoral committee appointment",price:'S/ 150.00'},
{id:'6.26-m',tusne:'6.26',category:'degrees',name:'Sustentación de tesis para Maestría',en:"Master's thesis defense",price:'S/ 700.00'},
{id:'6.26-d',tusne:'6.26',category:'degrees',name:'Sustentación de tesis para Doctorado',en:'Doctoral thesis defense',price:'S/ 900.00'},
{id:'6.14',category:'degrees',name:'Otorgamiento de grado académico de Maestro',en:"Awarding of Master's academic degree",price:'S/ 800.00'},
{id:'6.13',category:'degrees',name:'Otorgamiento de grado académico de Doctor',en:'Awarding of Doctoral academic degree',price:'S/ 1,000.00'}
];
const referenceCartKey='undc_tusne_reference_cart';
let referenceCart=[];
try{const saved=JSON.parse(localStorage.getItem(referenceCartKey)||'[]');if(Array.isArray(saved))referenceCart=saved.filter(id=>tusneConcepts.some(item=>item.id===id))}catch{}
let tusneCategory=tusneCategories[0].id;
const localized=(es,en)=>currentLanguage==='en'?en:es;
const categoryById=id=>tusneCategories.find(category=>category.id===id);
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
const imageFile=item.category==='admission'?'tarjet.png':visuals[id].file;
return {id,...visuals[id],src:'/assets/catalogo/ilustraciones/'+imageFile};
}
function conceptDescription(item,category=categoryById(item.category)){const description=currentLanguage==='en'?(item.descriptionEn||item.description):(item.description||item.descriptionEn);if(description)return description;if(category)return localized(category.description,category.descriptionEn);return localized('Concepto publicado en el catálogo institucional.','Service published in the institutional catalog.')}
function catalogIllustration(visual){return '<div class="concept-illustration" style="--illustration-wash:'+visual.wash+'" aria-hidden="true"><img src="'+visual.src+'" alt="" loading="lazy" decoding="async"></div>'}
function cartGlyph(){return '<svg aria-hidden="true" viewBox="0 0 24 24"><circle cx="9" cy="20" r="1.5"/><circle cx="19" cy="20" r="1.5"/><path d="M3 4h2l2.5 11h11l2-8H6"/></svg>'}
function renderConceptCard(item,category){const name=localized(item.name,item.en),unit=item.unit?localized(item.unit,item.unitEn):'',added=referenceCart.includes(item.id),visual=getCatalogVisual({...item,category:category.id});return '<article class="tusne-concept" style="--category-color:'+category.color+'"><div class="concept-card-main"><div class="concept-copy"><div class="concept-card-head"><span class="concept-icon" aria-hidden="true">'+visual.icon+'</span><div class="concept-heading"><span class="category-tag">'+esc(localized(category.name,category.en))+'</span><h4>'+esc(name)+'</h4></div></div><p class="concept-description">'+esc(conceptDescription(item,category))+'</p></div>'+catalogIllustration(visual)+'</div>'+(unit?'<div class="concept-facts"><span><b>'+esc(localized('Unidad','Unit'))+'</b> '+esc(unit)+'</span></div>':'')+'<div class="concept-meta"><span class="concept-price"><small>'+esc(localized('Precio referencial','Reference price'))+'</small><strong>'+esc(item.price)+'</strong></span></div><div class="concept-actions"><button class="detail-button" type="button" data-concept-detail="'+esc(item.id)+'">'+esc(localized('Ver detalles','View details'))+'</button><button class="reference-add-button'+(added?' is-added':'')+'" type="button" data-reference-cart="'+esc(item.id)+'" aria-pressed="'+String(added)+'">'+cartGlyph()+'<span>'+esc(localized(added?'Agregado al carrito':'Agregar al carrito',added?'Added to cart':'Add to cart'))+'</span></button></div></article>'}
function renderTusneCatalog(){
const query=$('search')?.value.trim().toLocaleLowerCase(currentLanguage==='en'?'en':'es')||'';
$('catalogFilters').innerHTML=tusneCategories.map(category=>'<button class="catalog-filter'+(tusneCategory===category.id?' active':'')+'" type="button" data-category-filter="'+category.id+'" aria-pressed="'+String(tusneCategory===category.id)+'">'+esc(localized(category.name,category.en))+'</button>').join('');
const visible=tusneConcepts.filter(item=>{const category=categoryById(item.category);const inCategory=tusneCategory==='all'||item.category===tusneCategory;const searchable=[item.name,item.en,item.tusne||item.id,category.name,category.en].join(' ').toLocaleLowerCase(currentLanguage==='en'?'en':'es');return inCategory&&searchable.includes(query)});
const groups=(tusneCategory==='all'?tusneCategories:[categoryById(tusneCategory)]).map(category=>({category,items:visible.filter(item=>item.category===category.id)})).filter(group=>group.items.length);
$('tusneResults').innerHTML=groups.map(({category,items})=>'<section class="category-result-group" style="--category-color:'+category.color+'"><h3 class="category-result-title">'+category.icon+' '+esc(localized(category.name,category.en))+'</h3><div class="concept-grid">'+items.map(item=>renderConceptCard(item,category)).join('')+'</div></section>').join('');
$('tusneResults').hidden=!visible.length;$('tusneEmpty').hidden=!!visible.length;
$('visibleConceptCount').textContent=visible.length+' '+localized(visible.length===1?'opción visible':'opciones visibles',visible.length===1?'visible option':'visible options');
const selected=tusneCategory==='all'?null:categoryById(tusneCategory);$('catalogResultsTitle').textContent=selected?localized(selected.name,selected.en):localized('Todos los conceptos','All services');
}
function openConceptDetail(id){const item=tusneConcepts.find(concept=>concept.id===id);if(!item)return;const category=categoryById(item.category),name=localized(item.name,item.en),unit=item.unit?localized(item.unit,item.unitEn):'',note=item.note?localized(item.note,item.noteEn):localized('Esta tarifa referencial no constituye aprobación académica ni está habilitada para pago.','This reference fee does not constitute academic approval and is not enabled for payment.');$('conceptDialogContent').innerHTML='<span class="dialog-category">'+esc(localized(category.name,category.en))+'</span><h2 id="conceptDialogTitle">'+esc(name)+'</h2><p class="dialog-description">'+esc(conceptDescription(item))+'</p><div class="dialog-facts"><div class="dialog-fact"><small>'+esc(localized('Referencia documental','Document reference'))+'</small><strong>TUSNE '+esc(item.tusne||item.id)+'</strong></div><div class="dialog-fact"><small>'+esc(localized('Precio referencial','Reference price'))+'</small><strong>'+esc(item.price+(unit?' '+unit:''))+'</strong></div><div class="dialog-fact"><small>'+esc(localized('Oficina responsable','Responsible office'))+'</small><strong>'+esc(localized('Escuela de Posgrado','Graduate School'))+'</strong></div><div class="dialog-fact"><small>'+esc(localized('Disponibilidad','Availability'))+'</small><strong>'+esc(localized('No habilitado para pago','Not enabled for payment'))+'</strong></div></div><p class="dialog-note">'+esc(note)+'</p>';const dialog=$('conceptDialog');dialog.style.setProperty('--dialog-color',category.color);dialog.showModal()}
function screen(name){state.screen=name;document.body.classList.toggle('commerce-view',name!=='catalog');document.querySelectorAll('.screen').forEach(x=>x.classList.toggle('visible',x.dataset.screen===name));document.querySelector('.page-grid')?.classList.toggle('cart-view',name==='cart');document.querySelectorAll('.step').forEach((step,i)=>{const order=['catalog','cart','identity','review'];step.classList.toggle('active',order[i]===name);step.classList.toggle('done',order.indexOf(name)>i)});window.scrollTo({top:0,behavior:'smooth'});}
function setMainView(name){document.querySelectorAll('[data-main-view]').forEach(view=>{view.hidden=view.dataset.mainView!==name});document.querySelectorAll('[data-main-view-link]').forEach(link=>{const active=link.dataset.mainViewLink===name;link.classList.toggle('active',active);if(active)link.setAttribute('aria-current','page');else link.removeAttribute('aria-current')})}
function badge(){const n=(state.cart?.items_count||0)+referenceCart.length;$('cartBadge').textContent=n;const mobileBadge=$('mobileCartBadge');if(mobileBadge)mobileBadge.textContent=n;$('emptyBadge').textContent=n?localized(n===1?'1 concepto seleccionado':'{n} conceptos seleccionados',n===1?'1 service selected':'{n} services selected').replace('{n}',n):localized('Aún no has agregado conceptos','You have not added any services yet');}
function renderCart(){
const items=state.cart?.items||[],referenceItems=referenceCart.map(id=>tusneConcepts.find(item=>item.id===id)).filter(Boolean);badge();
const sideItems=$('sideCartItems');sideItems.replaceChildren();for(const line of items){const entry=document.createElement('p');entry.className='side-cart-item';entry.textContent=line.slug||'Concepto '+line.catalog_item_id;sideItems.append(entry)}for(const item of referenceItems){const entry=document.createElement('p');entry.className='side-cart-item';entry.textContent=localized(item.name,item.en)+' · '+localized('Referencial','Reference');sideItems.append(entry)}$('sideCartSummary').textContent=items.length||referenceItems.length?localized('Tus conceptos seleccionados:','Your selected services:'):localized('Organiza tus conceptos antes de continuar.','Organize your services before continuing.');
const list=$('cartRows');list.replaceChildren();
if(!items.length&&!referenceItems.length){const empty=document.createElement('div');empty.className='empty';empty.textContent=localized('🛒 Tu carrito está vacío. Selecciona un concepto para empezar.','🛒 Your cart is empty. Select a service to get started.');list.append(empty);}
for(const line of items){const row=document.createElement('div');row.className='cart-row';
const info=document.createElement('div');const label=document.createElement('b');label.textContent=line.slug||'Concepto '+line.catalog_item_id;const q=document.createElement('small');q.textContent='Cantidad: '+line.quantity;info.append(label,q);row.append(info);
if(!state.owned){const remove=document.createElement('button');remove.type='button';remove.className='mini';remove.textContent='Quitar';remove.setAttribute('aria-label','Quitar '+label.textContent);remove.onclick=()=>removeItem(line.id);row.append(remove)}list.append(row);}
for(const item of referenceItems){const category=categoryById(item.category),row=document.createElement('div');row.className='cart-row reference-cart-row';const info=document.createElement('div'),label=document.createElement('b'),detail=document.createElement('small'),price=document.createElement('small');label.textContent=localized(item.name,item.en);detail.textContent='TUSNE '+(item.tusne||item.id)+' · '+localized(category.name,category.en)+' · '+localized('Referencial · no pagable','Reference · not payable');price.textContent=item.price;info.append(label,detail,price);row.append(info);const remove=document.createElement('button');remove.type='button';remove.className='mini';remove.textContent=localized('Quitar','Remove');remove.setAttribute('aria-label',localized('Quitar ','Remove ')+label.textContent);remove.onclick=()=>{referenceCart=referenceCart.filter(id=>id!==item.id);localStorage.setItem(referenceCartKey,JSON.stringify(referenceCart));renderTusneCatalog();renderCart()};row.append(remove);list.append(row)}
$('cartTotalCount').textContent=((state.cart?.items_count||0)+referenceItems.length)+' '+localized('conceptos','services');
$('cartContinue').disabled=!items.length;$('sideContinue').disabled=!items.length;
}
function toggleReferenceCart(id){if(referenceCart.includes(id))referenceCart=referenceCart.filter(itemId=>itemId!==id);else referenceCart.push(id);localStorage.setItem(referenceCartKey,JSON.stringify(referenceCart));renderTusneCatalog();renderCart();const added=referenceCart.includes(id);toast(added?localized('Concepto agregado al carrito como selección referencial.','Service added as a reference selection.'):localized('Concepto retirado del carrito.','Service removed from cart.'));}
function renderCatalog(items){
state.catalog=items;const query=$('search').value.toLocaleLowerCase('es');const target=$('catalogCards');target.replaceChildren();
const filtered=items.filter(item=>(item.name+' '+item.code+' '+(item.description||'')).toLocaleLowerCase('es').includes(query));
if(!filtered.length){const empty=document.createElement('div');empty.className='empty';empty.textContent='No encontramos conceptos publicados para esta búsqueda.';target.append(empty);return;}
for(const item of filtered){const visual=getCatalogVisual(item),office=item.office?.name,requirements=item.requirements;const card=document.createElement('article');card.className='product catalog-card';
card.innerHTML='<div class="concept-card-main"><div class="concept-copy"><div class="concept-card-head"><span class="concept-icon" aria-hidden="true">'+visual.icon+'</span><div class="concept-heading"><span class="category-tag">'+esc(localized('Catálogo institucional','Institutional catalog'))+'</span><h3>'+esc(item.name)+'</h3></div></div><p class="concept-description">'+esc(item.description||localized('Concepto publicado en el catálogo institucional.','Service published in the institutional catalog.'))+'</p></div>'+catalogIllustration(visual)+'</div>'+(office?'<div class="concept-facts"><span><b>'+esc(localized('Oficina','Office'))+'</b> '+esc(office)+'</span></div>':'')+'<div class="concept-meta"><small><b>'+esc(localized('Código','Code'))+'</b><span>'+esc(item.code)+'</span></small><span class="concept-price"><small>'+esc(localized('Precio referencial','Reference price'))+'</small><strong>'+esc(localized('Pendiente','Pending'))+'</strong></span></div><div class="concept-actions"><details class="official-details"><summary>'+esc(localized('Ver detalles','View details'))+'</summary><div class="official-details-body">'+(requirements?'<b>'+esc(localized('Requisitos','Requirements'))+'</b><p>'+esc(requirements)+'</p>':'<p>'+esc(item.description||localized('Concepto publicado en el catálogo institucional.','Service published in the institutional catalog.'))+'</p>')+'</div></details><button type="button" class="reference-add-button official-add-button">'+cartGlyph()+'<span>'+esc(localized('Agregar al carrito','Add to cart'))+'</span></button></div><span class="unavailable-label">'+esc(localized('Selección referencial · No habilitada para pago','Reference selection · Payment not enabled'))+'</span>';
const add=card.querySelector('.official-add-button');add.addEventListener('click',()=>addItem(item.id));target.append(card);}
}
async function loadCatalog(){const id=$('office').value.trim();if(!/^[1-9]\d*$/.test(id)){toast('Indica un ID de oficina habilitado por Tesorería.',true);return}
try{const items=await api('/api/v1/tienda/catalogo?office_id='+encodeURIComponent(id));state.office=id;sessionStorage.setItem('undc_office_id',id);renderCatalog(items);$('catalogNote').textContent=items.length+' conceptos publicados';if(!items.length)toast('Todavía no hay conceptos publicados para esa oficina.');}catch(e){toast(e.message,true)}}
async function ensureCart(){if(state.token){try{state.cart=await api('/api/v1/tienda/carritos/actual','GET',null,true);renderCart();return}catch(e){if(e.status!==404)throw e;sessionStorage.removeItem('undc_cart_token');state.token=null}}
const result=await api('/api/v1/tienda/carritos','POST');state.token=result.token;sessionStorage.setItem('undc_cart_token',state.token);state.cart=result.cart;state.owned=false;renderCart()}
async function addItem(id){try{if(state.owned){toast('Tu precompra anterior ya está vinculada. La edición de carritos vinculados se habilitará en una versión posterior.',true);return}await ensureCart();state.cart=await api('/api/v1/tienda/carritos/actual/items','POST',{catalog_item_id:id,quantity:1},true);renderCart();toast('¡Concepto agregado al carrito!')}catch(e){toast(e.message,true)}}
async function removeItem(id){try{state.cart=await api('/api/v1/tienda/carritos/actual/items/'+id,'DELETE',null,true);renderCart();toast('Concepto retirado.')}catch(e){toast(e.message,true)}}
async function authState(){const res=await fetch('/api/v1/acceso/estado',{headers:{Accept:'application/json'},credentials:'same-origin'});if(res.status===401){state.authenticated=false;return false}if(!res.ok)throw new Error('No se pudo verificar la sesión.');state.authenticated=true;return true}
async function purchase(){if(!(state.cart?.items_count>0)){toast('Agrega al menos un concepto para continuar.',true);return}screen('cart');await continuePurchase()}
async function continuePurchase(){
try{if(!(await authState())){screen('identity');$('loginBox').hidden=false;$('profileBox').hidden=true;return}
if(state.token&&state.cart?.items_count>0&&!state.owned){await api('/api/v1/tienda/precompra/vincular-carrito','POST',{cart_token:state.token});sessionStorage.removeItem('undc_cart_token');state.token=null;state.owned=true}
await refreshReview()}catch(e){toast(e.message,true)}}
async function refreshReview(){
const data=await api('/api/v1/tienda/precompra/resumen');state.owned=!!data.cart;state.cart=data.cart;state.profile=data.profile;renderCart();
if(!data.profile_complete){screen('identity');$('loginBox').hidden=true;$('profileBox').hidden=false;
if(data.profile)for(const [key,value] of Object.entries(data.profile)){const el=$('profileForm').elements.namedItem(key);if(el&&typeof value==='string')el.value=value}return;}
if(data.next_step!=='review'){screen('catalog');toast('No hay conceptos pendientes en tu carrito.');return}
screen('review');$('buyerName').textContent=[data.profile.first_names,data.profile.last_names].filter(Boolean).join(' ');
$('buyerDocument').textContent=(data.profile.document_type||'')+' '+(data.profile.document_number||'');
$('buyerContact').textContent=data.profile.contact_email||data.profile.phone||'No registrado';
const box=$('reviewList');box.replaceChildren();for(const item of data.cart?.items||[]){const row=document.createElement('div');row.className='cart-row';const strong=document.createElement('b');strong.textContent=item.slug;const amount=document.createElement('small');amount.textContent='Cantidad: '+item.quantity;row.append(strong,amount);box.append(row);}
$('reviewCount').textContent=data.cart.items_count+' conceptos';}
async function restore(){
try{if(await authState()){const owned=await api('/api/v1/tienda/precompra/resumen');if(owned.cart?.items_count>0){state.owned=true;state.cart=owned.cart;renderCart()}}
if(!state.owned)await ensureCart();
if(sessionStorage.getItem('undc_resume_purchase')==='1'){sessionStorage.removeItem('undc_resume_purchase');await continuePurchase();}
}catch(e){toast(e.message,true)}}
$('office').value=state.office;$('load').addEventListener('click',loadCatalog);$('search').addEventListener('input',()=>{renderTusneCatalog();renderCatalog(state.catalog)});
$('catalogFilters').addEventListener('click',event=>{const button=event.target.closest('[data-category-filter]');if(!button)return;tusneCategory=button.dataset.categoryFilter;renderTusneCatalog()});
$('tusneResults').addEventListener('click',event=>{const addButton=event.target.closest('[data-reference-cart]');if(addButton){toggleReferenceCart(addButton.dataset.referenceCart);return}const button=event.target.closest('[data-concept-detail]');if(button)openConceptDetail(button.dataset.conceptDetail)});
$('clearCatalogFilters').addEventListener('click',()=>{tusneCategory='all';$('search').value='';renderTusneCatalog();renderCatalog(state.catalog)});
$('conceptDialogClose').addEventListener('click',()=>$('conceptDialog').close());$('conceptDialog').addEventListener('click',event=>{if(event.target===$('conceptDialog'))$('conceptDialog').close()});
const headerNav=$('headerNavPanel'),menuButton=$('headerMenuButton'),closeHeaderMenu=()=>{headerNav?.classList.remove('is-open');menuButton?.setAttribute('aria-expanded','false')};
menuButton?.addEventListener('click',()=>{const open=headerNav.classList.toggle('is-open');menuButton.setAttribute('aria-expanded',String(open))});
document.querySelector('.header-search')?.addEventListener('submit',event=>{event.preventDefault();document.getElementById('catalogo')?.scrollIntoView({behavior:'smooth'});closeHeaderMenu()});
headerNav?.querySelectorAll('.nav-links a').forEach(link=>link.addEventListener('click',event=>{const view=link.dataset.mainViewLink;if(view){event.preventDefault();if(state.screen!=='catalog')screen('catalog');if(view==='procedures'){tusneCategory=tusneCategories[0].id;renderTusneCatalog()}setMainView(view)}closeHeaderMenu()}));
$('cartButton').addEventListener('click',()=>screen('cart'));$('mobileCartButton')?.addEventListener('click',()=>{screen('cart');closeHeaderMenu()});$('sideContinue').addEventListener('click',purchase);$('cartContinue').addEventListener('click',continuePurchase);
document.querySelectorAll('[data-go]').forEach(el=>el.addEventListener('click',()=>screen(el.dataset.go)));
$('googleButton').addEventListener('click',()=>{sessionStorage.setItem('undc_resume_purchase','1');window.location.assign('/auth/google/redirect')});
$('profileForm').addEventListener('submit',async event=>{event.preventDefault();const form=new FormData(event.currentTarget);
try{await api('/api/v1/acceso/perfil','PUT',Object.fromEntries(form.entries()));await refreshReview();toast('Tus datos quedaron guardados para próximas compras.')}catch(e){let msg=e.message;if(e.errors){msg=Object.values(e.errors).flat().join(' ')}toast(msg,true)}});
$('reviewBack').addEventListener('click',()=>screen('cart'));
renderTusneCatalog();restore();if(state.office)loadCatalog();
})();
