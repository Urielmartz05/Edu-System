'use strict';
(() => {
    const page = document.body.dataset.page;
    const root = document.querySelector('#page-content');
    const dialog = document.querySelector('#editor');
    const escape = value => String(value ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    const initialUsers = [
        {id:'20260001', name:'Valentina Torres', email:'valentina.torres@institucion.edu', role:'Alumno', status:'Verificado'},
        {id:'20260002', name:'Diego Hernández', email:'diego.hernandez@institucion.edu', role:'Alumno', status:'Verificado'},
        {id:'DOC-001', name:'Laura Méndez', email:'laura.mendez@institucion.edu', role:'Ponente', status:'Verificado'},
        {id:'DOC-002', name:'Carlos Rivera', email:'carlos.rivera@institucion.edu', role:'Ponente', status:'Verificado'},
        {id:'20260003', name:'Sofía Ramírez', email:'sofia.ramirez@institucion.edu', role:'Alumno', status:'Pendiente'},
        {id:'DIR-001', name:'Andrea Castillo', email:'andrea.castillo@institucion.edu', role:'Director', status:'Verificado'},
        {id:'20260004', name:'Mateo López', email:'mateo.lopez@institucion.edu', role:'Alumno', status:'Verificado'},
        {id:'ADM-001', name:'Gabriel Ortiz', email:'gabriel.ortiz@institucion.edu', role:'Administrador', status:'Verificado'}
    ];
    const initialCourses = [
        {id:'MAT-101', title:'Matemáticas aplicadas', category:'Ciencias exactas', teacher:'Laura Méndez', students:32, capacity:40, progress:78, status:'Publicado', description:'Resolución de problemas y pensamiento matemático.'},
        {id:'PRG-201', title:'Fundamentos de programación', category:'Tecnología', teacher:'Carlos Rivera', students:28, capacity:35, progress:65, status:'Publicado', description:'Introducción a la lógica y al desarrollo de software.'},
        {id:'COM-101', title:'Comunicación académica', category:'Humanidades', teacher:'Laura Méndez', students:24, capacity:30, progress:84, status:'Publicado', description:'Lectura crítica y escritura de textos académicos.'},
        {id:'DAT-202', title:'Análisis de datos', category:'Tecnología', teacher:'Carlos Rivera', students:20, capacity:30, progress:52, status:'Publicado', description:'Herramientas para explorar e interpretar información.'},
        {id:'FIS-102', title:'Física experimental', category:'Ciencias exactas', teacher:'Laura Méndez', students:0, capacity:30, progress:0, status:'En revisión', description:'Aplicación del método científico en el laboratorio.'},
        {id:'HIS-101', title:'Historia contemporánea', category:'Humanidades', teacher:'Laura Méndez', students:0, capacity:30, progress:0, status:'Pausado', description:'Análisis de los procesos históricos contemporáneos.'}
    ];
    const storageKey = 'edu-admin-demo-v1';
    let data = {users:initialUsers, courses:initialCourses};
    try { const saved = JSON.parse(sessionStorage.getItem(storageKey)); if (saved && Array.isArray(saved.users) && Array.isArray(saved.courses)) data = saved; } catch (_) { /* Demo also works without browser storage. */ }
    let currentPage = 1;
    let period = 'actual';
    const pageSize = 5;
    const priorEnrolled = () => published().reduce((sum, c) => sum + Math.round(c.students * .83), 0);
    const totalEnrolled = () => data.courses.reduce((sum, c) => sum + Number(c.students), 0);
    const published = () => data.courses.filter(c => c.status === 'Publicado');
    const average = () => { const courses = published(); return courses.length ? Math.round(courses.reduce((sum,c) => sum + Number(c.progress),0) / courses.length) : 0; };
    const number = n => Number(n).toLocaleString('es-MX');
    const badge = (value, tone = '') => `<span class="pill ${tone || (['Publicado','Verificado'].includes(value) ? 'green' : ['En revisión','Pendiente'].includes(value) ? 'amber' : '')}">${escape(value)}</span>`;
    const kpis = (items, five = false) => `<section class="kpi-grid ${five ? 'five' : ''}" aria-label="Indicadores de demostración">${items.map(([label,value,note],i) => `<article class="kpi"><span class="kpi-index">${String(i+1).padStart(2,'0')}</span><h2 class="kpi-label">${label}</h2><strong class="kpi-value">${value}</strong><p class="kpi-note">${note}</p></article>`).join('')}</section>`;
    const periodSelect = () => `<label><span class="filter-label">Periodo del informe</span><select id="period"><option value="actual" ${period === 'actual' ? 'selected' : ''}>Periodo actual · Ago–Dic 2026</option><option value="anterior" ${period === 'anterior' ? 'selected' : ''}>Periodo anterior · Ene–Jun 2026</option></select></label>`;
    const heading = (title, subtitle, actions = '') => `<section class="page-heading"><div><p class="eyebrow">GESTIÓN ACADÉMICA</p><h1>${title}</h1><p class="subtitle">${subtitle}</p></div><div class="actions">${actions}</div></section>`;
    const panelHead = (title, description, action = '') => `<div class="panel-head"><div><h2>${title}</h2><p>${description}</p></div>${action}</div>`;
    function chart() {
        const previous = period === 'anterior';
        const labels = previous ? ['Ene','Feb','Mar','Abr','May','Jun'] : ['Jul','Ago','Sep','Oct','Nov','Dic'];
        const values = previous ? [18,28,34,31,40,45] : [22,37,48,57,64,71];
        const completed = previous ? [8,12,18,21,27,32] : [10,15,25,32,42,51];
        return `<div class="chart"><div class="chart-legend"><span><i class="legend-dot"></i>Inscripciones</span><span><i class="legend-dot light"></i>Finalizaciones</span></div><div class="bar-chart" role="img" aria-label="Datos ilustrativos: ${labels.map((m,i) => `${m}: ${values[i]} inscripciones y ${completed[i]} finalizaciones`).join('; ')}">${values.map((v,i) => `<div class="bar-group"><div class="bar" style="height:${v/80*100}%" title="${labels[i]}: ${v} inscripciones"></div><div class="bar light" style="height:${completed[i]/80*100}%" title="${completed[i]} finalizaciones"></div></div>`).join('')}</div><div class="chart-labels" aria-hidden="true">${labels.map(m => `<span>${m}</span>`).join('')}</div></div><p class="chart-foot">Número de registros por mes · Serie de ejemplo</p>`;
    }
    const progressRow = (label, value, note, tone = '') => `<div><div class="progress-title"><span>${escape(label)}</span><strong>${value}%</strong></div><div class="progress-track" role="progressbar" aria-label="${escape(label)}" aria-valuenow="${value}" aria-valuemin="0" aria-valuemax="100"><div class="progress-fill ${tone}" style="width:${value}%"></div></div>${note ? `<p class="list-note">${escape(note)}</p>` : ''}</div>`;
    function dashboard() {
        root.innerHTML = `<section class="welcome" aria-labelledby="welcome-title"><h1 id="welcome-title">Bienvenido a tu comunidad académica</h1><img class="welcome-image" src="/resources/images/academic-welcome.svg" alt="" width="500" height="300"></section>` +
        kpis([
            ['Alumnos registrados',data.users.filter(u => u.role === 'Alumno').length,'Comunidad estudiantil'],
            ['Cursos publicados',published().length,`De ${data.courses.length} cursos en catálogo`],
            ['Inscripciones',number(totalEnrolled()),'En los cursos del periodo'],
            ['Ponentes',data.users.filter(u => u.role === 'Ponente').length,'Equipo docente registrado'],
            ['Cuentas pendientes',data.users.filter(u => u.status === 'Pendiente').length,'Pendientes de verificación']
        ],true) +
        `<div class="quick-links"><a class="quick-link" href="adminUsers.php">Administrar comunidad <span aria-hidden="true">→</span></a><a class="quick-link" href="adminCourses.php">Revisar catálogo de cursos <span aria-hidden="true">→</span></a><a class="quick-link" href="adminStatistics.php">Consultar rendimiento <span aria-hidden="true">→</span></a></div>` +
        `<div class="grid"><section class="panel">${panelHead('Cursos con mayor participación','Distribución de alumnos en el periodo','<a class="btn text" href="adminCourses.php">Ver cursos →</a>')}<div class="table-wrap"><table><thead><tr><th scope="col">CURSO / PONENTE</th><th scope="col">INSCRITOS</th><th scope="col">ESTADO</th></tr></thead><tbody>${[...data.courses].sort((a,b) => b.students-a.students).slice(0,4).map(c => `<tr><td><span class="cell-title">${escape(c.title)}</span><span class="cell-sub">${escape(c.teacher)}</span></td><td>${c.students} <span class="cell-sub">de ${c.capacity} lugares</span></td><td>${badge(c.status)}</td></tr>`).join('')}</tbody></table></div></section><section class="panel">${panelHead('Atención y seguimiento','Pendientes de la comunidad escolar')}<div class="timeline"><div class="timeline-item"><span class="timeline-mark"></span><div><strong>Cuentas por verificar: ${data.users.filter(u => u.status === 'Pendiente').length}</strong><p>Revisa el estado de los nuevos usuarios.</p><a class="btn text" href="adminUsers.php?status=Pendiente">Revisar usuarios →</a></div></div><div class="timeline-item"><span class="timeline-mark"></span><div><strong>Cursos en revisión: ${data.courses.filter(c => c.status === 'En revisión').length}</strong><p>Consulta los cursos pendientes de publicación.</p><a class="btn text" href="adminCourses.php?status=En%20revisi%C3%B3n">Revisar cursos →</a></div></div><div class="timeline-item"><span class="timeline-mark"></span><div><strong>Revisa el avance académico</strong><p>Identifica los cursos que necesitan acompañamiento.</p><a class="btn text" href="adminStatistics.php">Consultar estadísticas →</a></div></div></div></section></div>`;
    }
    function directory() {
        const users = page === 'users';
        root.innerHTML = heading(users ? 'Comunidad escolar' : 'Catálogo de cursos',users ? 'Administra los perfiles, roles y accesos de tu institución.' : 'Organiza la oferta académica y da seguimiento a cada curso.',`<button class="btn" data-export>Exportar CSV</button><button class="btn primary" data-create>${users ? '+ Nuevo usuario' : '+ Nuevo curso'}</button>`) +
        (users ? kpis([['Usuarios registrados',data.users.length,'Todos los perfiles'],['Alumnos',data.users.filter(u => u.role === 'Alumno').length,'Comunidad estudiantil'],['Equipo académico',data.users.filter(u => ['Ponente','Director'].includes(u.role)).length,'Ponentes y directores'],['Por verificar',data.users.filter(u => u.status === 'Pendiente').length,'Cuentas pendientes']]) : kpis([['Cursos en catálogo',data.courses.length,'Oferta académica completa'],['Publicados',published().length,'Disponibles para alumnos'],['En revisión',data.courses.filter(c => c.status === 'En revisión').length,'Pendientes de publicación'],['Inscripciones',number(totalEnrolled()),'En todos los cursos']])) +
        `<section class="panel">${panelHead(users ? 'Directorio de usuarios' : 'Oferta académica',users ? 'Consulta y edita los perfiles de la comunidad.' : 'Consulta la capacidad, los responsables y el estado de publicación.',badge('Datos de muestra','wine'))}<div class="filters"><label class="search"><span class="filter-label">${users ? 'Buscar por nombre, correo o ID' : 'Buscar por curso, ponente o código'}</span><input class="field" id="search" type="search" placeholder="${users ? 'Buscar por nombre, correo o ID…' : 'Buscar por curso, ponente o código…'}"></label><label><span class="filter-label">${users ? 'Rol' : 'Área académica'}</span><select id="category"><option value="">${users ? 'Todos los roles' : 'Todas las áreas'}</option>${(users ? ['Alumno','Ponente','Director','Administrador'] : ['Ciencias exactas','Tecnología','Humanidades']).map(v => `<option>${v}</option>`).join('')}</select></label><label><span class="filter-label">Estado</span><select id="status"><option value="">Todos los estados</option>${(users ? ['Verificado','Pendiente'] : ['Publicado','En revisión','Pausado']).map(v => `<option>${v}</option>`).join('')}</select></label></div><div class="table-wrap"><table><thead><tr>${(users ? ['USUARIO','ID / CONTROL','ROL','ESTADO','ACCIONES'] : ['CURSO','PONENTE','INSCRITOS / CUPO','ESTADO','ACCIONES']).map(h => `<th scope="col">${h}</th>`).join('')}</tr></thead><tbody id="records"></tbody></table></div><div class="table-footer"><span id="record-count"></span><div class="pagination"><button class="btn" id="previous" aria-label="Página anterior">Anterior</button><span id="page-number"></span><button class="btn" id="next" aria-label="Página siguiente">Siguiente</button></div></div></section><p class="subtitle">${users ? 'Los roles organizan la participación de alumnos, ponentes, directores y administradores.' : 'Los cursos en revisión y pausados permanecen en el catálogo administrativo.'}</p>`;
        const requestedStatus = new URLSearchParams(location.search).get('status');
        if (requestedStatus) document.querySelector('#status').value = requestedStatus;
        ['search','category','status'].forEach(id => document.getElementById(id).addEventListener('input',() => {currentPage = 1; renderRows();}));
        document.querySelector('#previous').onclick = () => {currentPage--; renderRows();};
        document.querySelector('#next').onclick = () => {currentPage++; renderRows();};
        renderRows();
    }
    function filtered() {
        const normalize = v => String(v).normalize('NFD').replace(/[\u0300-\u036f]/g,'').toLowerCase();
        const query = normalize(document.querySelector('#search').value.trim());
        const category = document.querySelector('#category').value;
        const status = document.querySelector('#status').value;
        return data[page].filter(r => (!query || normalize(page === 'users' ? `${r.id} ${r.name} ${r.email}` : `${r.id} ${r.title} ${r.teacher}`).includes(query)) && (!category || (r.role || r.category) === category) && (!status || r.status === status));
    }
    function renderRows() {
        const records = filtered();
        const pages = Math.max(1,Math.ceil(records.length/pageSize));
        currentPage = Math.max(1,Math.min(currentPage,pages));
        document.querySelector('#records').innerHTML = records.slice((currentPage-1)*pageSize,currentPage*pageSize).map(r => page === 'users' ? `<tr><td><div class="person"><span class="avatar" aria-hidden="true">${escape(r.name.split(' ').map(n => n[0]).slice(0,2).join(''))}</span><span><span class="cell-title">${escape(r.name)}</span><span class="cell-sub">${escape(r.email)}</span></span></div></td><td>${escape(r.id)}</td><td>${badge(r.role,r.role === 'Administrador' ? 'wine' : '')}</td><td>${badge(r.status)}</td><td><button class="row-action" data-edit="${escape(r.id)}" aria-label="Editar a ${escape(r.name)}">Editar</button></td></tr>` : `<tr><td><span class="cell-title">${escape(r.title)}</span><span class="cell-sub">${escape(r.id)} · ${escape(r.category)}</span></td><td>${escape(r.teacher)}</td><td>${r.students} / ${r.capacity}<span class="cell-sub">${Math.round(r.students/r.capacity*100)}% de ocupación</span></td><td>${badge(r.status)}</td><td><button class="row-action" data-edit="${escape(r.id)}" aria-label="Editar ${escape(r.title)}">Editar</button></td></tr>`).join('') || '<tr><td colspan="5" class="empty"><strong>No se encontraron resultados</strong>Prueba con otro término o ajusta los filtros.</td></tr>';
        document.querySelector('#record-count').textContent = records.length ? `Mostrando ${(currentPage-1)*pageSize+1}–${Math.min(currentPage*pageSize,records.length)} de ${records.length} registros` : '0 registros';
        document.querySelector('#page-number').textContent = `${currentPage} / ${pages}`;
        document.querySelector('#previous').disabled = currentPage === 1;
        document.querySelector('#next').disabled = currentPage === pages;
    }
    function statistics() {
        const prior = period === 'anterior';
        root.innerHTML = heading('Estadísticas académicas','Información para acompañar el aprendizaje y tomar mejores decisiones.',`${periodSelect()}<button class="btn" data-export>Exportar informe</button>`) +
        kpis([['Inscripciones',prior ? number(priorEnrolled()) : number(totalEnrolled()),'Total en el periodo de muestra'],['Tasa de finalización',prior ? '68%' : '74%','Inscripciones con curso finalizado'],['Avance promedio',prior ? '63%' : `${average()}%`,'En los cursos publicados'],['Calificación promedio',prior ? '8.1 / 10' : '8.6 / 10','Promedio de evaluaciones de muestra']]) +
        `<div class="grid"><section class="panel">${panelHead('Evolución del aprendizaje','Inscripciones y finalizaciones por mes',badge(prior ? 'Ene–Jun 2026' : 'Ago–Dic 2026'))}${chart()}</section><section class="panel">${panelHead('Distribución de resultados','Alumnos por rango de calificación')}<div class="progress-list">${progressRow('Sobresaliente · 9 a 10',prior ? 28 : 38,'Porcentaje de alumnos evaluados','green')}${progressRow('Satisfactorio · 7 a 8.9',prior ? 48 : 46,'Porcentaje de alumnos evaluados')}${progressRow('Requiere apoyo · Menos de 7',prior ? 24 : 16,'Seguimiento académico recomendado','gold')}</div></section></div>` +
        `<aside class="insight"><strong>Acompañamiento académico</strong><p>${prior ? 'El 24%' : 'El 16%'} de los alumnos de este escenario requiere apoyo. Revisa el avance por curso para orientar las acciones de seguimiento.</p></aside>` +
        `<section class="panel">${panelHead('Rendimiento por curso',prior ? 'Resultados ilustrativos del periodo anterior' : 'Avance, participación y capacidad de los cursos publicados')}<div class="table-wrap"><table><thead><tr><th scope="col">CURSO</th><th scope="col">INSCRITOS</th><th scope="col">OCUPACIÓN</th><th scope="col">AVANCE PROMEDIO</th><th scope="col">SEGUIMIENTO</th></tr></thead><tbody>${published().map(c => { const progress = prior ? Math.max(0,c.progress-9) : c.progress; const students = prior ? Math.round(c.students*.83) : c.students; return `<tr><td><span class="cell-title">${escape(c.title)}</span><span class="cell-sub">${escape(c.teacher)}</span></td><td>${students}</td><td>${Math.round(students/c.capacity*100)}%</td><td>${progress}%</td><td>${badge(progress >= 70 ? 'En buen avance' : 'Requiere seguimiento',progress >= 70 ? 'green' : 'amber')}</td></tr>`; }).join('') || '<tr><td colspan="5" class="empty">No hay cursos publicados.</td></tr>'}</tbody></table></div></section><p class="subtitle">La finalización, las calificaciones y las series mensuales son escenarios ilustrativos; no representan información institucional real.</p>`;
        document.querySelector('#period').onchange = e => {period = e.target.value; statistics();};
    }
    const field = (label,name,value = '',type = 'text',full = false) => `<label class="form-field ${full ? 'full' : ''}">${label}<input class="field" name="${name}" value="${escape(value)}" type="${type}" required maxlength="${type === 'email' ? 180 : 120}"></label>`;
    const selectField = (label,name,values,value) => `<label class="form-field">${label}<select name="${name}" required>${values.map(v => `<option ${v === value ? 'selected' : ''}>${escape(v)}</option>`).join('')}</select></label>`;
    let returnFocus;
    function openEditor(id) {
        const users = page === 'users';
        const record = id ? data[page].find(r => r.id === id) : {};
        if (!record) return;
        returnFocus = document.activeElement;
        dialog.innerHTML = `<form id="edit-form"><div class="dialog-head"><h2 id="dialog-title">${id ? 'Editar' : 'Nuevo'} ${users ? 'usuario' : 'curso'}</h2><button class="close-dialog" type="button" aria-label="Cerrar formulario">×</button></div><p class="dialog-note">Demostración: los cambios se guardan únicamente en esta pestaña.</p><div class="form-grid">${users ? field('Número de control / ID','id',record.id) + selectField('Rol','role',['Alumno','Ponente','Director','Administrador'],record.role) + field('Nombre completo','name',record.name,'text',true) + field('Correo institucional','email',record.email,'email',true) + selectField('Estado de cuenta','status',['Pendiente','Verificado'],record.status) : field('Código del curso','id',record.id) + selectField('Área académica','category',['Ciencias exactas','Tecnología','Humanidades'],record.category) + field('Título del curso','title',record.title,'text',true) + selectField('Ponente asignado','teacher',[...new Set(data.users.filter(u => u.role === 'Ponente').map(u => u.name).concat(record.teacher ? [record.teacher] : []))],record.teacher) + selectField('Estado de publicación','status',['En revisión','Publicado','Pausado'],record.status) + `<label class="form-field">Cupo máximo<input class="field" type="number" name="capacity" min="${record.students || 1}" max="1000" value="${record.capacity || 30}" required></label><label class="form-field full">Descripción<textarea class="field" name="description" rows="3" maxlength="1000">${escape(record.description || '')}</textarea></label>`}</div><div class="form-actions"><button class="btn" type="button" data-cancel>Cancelar</button><button class="btn primary" type="submit">Guardar ${users ? 'usuario' : 'curso'}</button></div></form>`;
        const form = dialog.querySelector('form');
        const idInput = form.elements.namedItem('id');
        idInput.readOnly = Boolean(id);
        form.addEventListener('input',() => {idInput.setCustomValidity(''); if(users) form.elements.namedItem('email').setCustomValidity('');});
        form.onsubmit = event => {
            event.preventDefault();
            const values = Object.fromEntries(new FormData(form));
            Object.keys(values).forEach(key => values[key] = values[key].trim());
            for (const input of form.querySelectorAll('input[required]')) {
                if (!input.value.trim()) {input.setCustomValidity('Completa este campo sin usar solo espacios.');input.reportValidity();input.oninput = () => input.setCustomValidity('');return;}
            }
            if(data[page].some(r => r.id === values.id && r.id !== id)) {idInput.setCustomValidity('Este identificador ya está registrado.');idInput.reportValidity();return;}
            if(users && data.users.some(r => r.email.toLowerCase() === values.email.toLowerCase() && r.id !== id)) {const email = form.elements.namedItem('email');email.setCustomValidity('Este correo ya está registrado.');email.reportValidity();return;}
            const nextRecord = users ? values : {...values, capacity:Number(values.capacity), students:record.students || 0, progress:record.progress || 0};
            if(id) {data[page][data[page].findIndex(r => r.id === id)] = nextRecord;
                if(users && record.role === 'Ponente' && values.name !== record.name) data.courses.forEach(c => {if(c.teacher === record.name) c.teacher = values.name;});
            } else data[page].unshift(nextRecord);
            let persisted = true;
            try {sessionStorage.setItem(storageKey,JSON.stringify(data));} catch (_) {persisted = false;}
            dialog.close();directory();document.querySelector('[data-create]').focus();notify(persisted ? 'Cambios guardados en la demostración de esta pestaña.' : 'Cambios aplicados a esta vista. El almacenamiento del navegador no está disponible.');
        };
        dialog.showModal();document.body.classList.add('modal-open');
    }
    function notify(message) {
        const toast = document.querySelector('.toast');toast.textContent = message;toast.hidden = false;clearTimeout(notify.timer);notify.timer = setTimeout(() => toast.hidden = true,4500);
    }
    function exportCSV() {
        let rows;
        if(page === 'statistics') {
            const prior = period === 'anterior';
            rows = [['Informe de demostración',prior ? 'Ene–Jun 2026' : 'Ago–Dic 2026'],['Indicador','Valor'],['Inscripciones',prior ? priorEnrolled() : totalEnrolled()],['Tasa de finalización',prior ? '68%' : '74%'],['Avance promedio',prior ? '63%' : `${average()}%`],['Calificación promedio',prior ? '8.1' : '8.6'],[],['Curso','Inscritos','Cupo','Avance promedio'],...published().map(c => [c.title,prior ? Math.round(c.students*.83) : c.students,c.capacity,`${prior ? Math.max(0,c.progress-9) : c.progress}%`])];
        } else rows = page === 'users' ? [['ID','Nombre','Correo','Rol','Estado'],...filtered().map(r => [r.id,r.name,r.email,r.role,r.status])] : [['Código','Curso','Área','Ponente','Inscritos','Cupo','Estado'],...filtered().map(r => [r.id,r.title,r.category,r.teacher,r.students,r.capacity,r.status])];
        const csv = '\uFEFF' + rows.map(row => row.map(v => {let text = String(v);if(/^[=+@\-\t\r]/.test(text)) text = "'" + text;return '"' + text.replace(/"/g,'""') + '"';}).join(',')).join('\r\n');
        const url = URL.createObjectURL(new Blob([csv],{type:'text/csv;charset=utf-8;'}));
        const link = document.createElement('a');link.href = url;link.download = `edu-system-${page}-demostracion.csv`;link.click();setTimeout(() => URL.revokeObjectURL(url),1000);notify('Archivo de demostración exportado.');
    }
    document.addEventListener('click',e => {
        const button = e.target.closest('button');if(!button) return;
        if(button.hasAttribute('data-create')) openEditor();
        if(button.hasAttribute('data-edit')) openEditor(button.dataset.edit);
        if(button.hasAttribute('data-export')) exportCSV();
        if(button.matches('.close-dialog,[data-cancel]')) dialog.close();
    });
    dialog.addEventListener('close',() => {document.body.classList.remove('modal-open');if(returnFocus?.isConnected) returnFocus.focus();});
    dialog.addEventListener('click',e => {if(e.target === dialog) {const rect = dialog.getBoundingClientRect();if(e.clientX < rect.left || e.clientX > rect.right || e.clientY < rect.top || e.clientY > rect.bottom) dialog.close();}});
    if(page === 'dashboard') dashboard();else if(page === 'statistics') statistics();else directory();
})();
