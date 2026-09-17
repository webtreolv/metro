<?php
$pagina = 'catalogos';
include '../includes/navbar.php';
?>

<h3><i class="bi bi-gear"></i> Catalogos</h3>
                
                <!-- Tabs -->
                <ul class="nav nav-tabs mb-3" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#commodity">
                            Commodity
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#maquinas">
                            Maquinas
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#usuarios">
                            Usuarios
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tipoSolicitud">
                            Tipo Solicitud
                        </button>
                    </li>
                </ul>
                
                <!-- Contenido tabs -->
                <div class="tab-content">
                    <!-- Commodity -->
                    <div class="tab-pane fade show active" id="commodity">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <span>Commodity</span>
                                <button class="btn btn-sm btn-primary" onclick="nuevo('commodity')">
                                    <i class="bi bi-plus"></i> Nuevo
                                </button>
                            </div>
                            <div class="card-body">
                                <table class="table table-striped" id="tablaCommodity">
                                    <thead>
                                        <tr>
                                            <th>Nombre</th>
                                            <th>Descripcion</th>
                                            <th>Estado</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Maquinas -->
                    <div class="tab-pane fade" id="maquinas">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <span>Maquinas</span>
                                <button class="btn btn-sm btn-primary" onclick="nuevo('maquina')">
                                    <i class="bi bi-plus"></i> Nueva
                                </button>
                            </div>
                            <div class="card-body">
                                <table class="table table-striped" id="tablaMaquinas">
                                    <thead>
                                        <tr>
                                            <th>Nombre</th>
                                            <th>Ubicacion</th>
                                            <th>Estado</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Usuarios -->
                    <div class="tab-pane fade" id="usuarios">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <span>Usuarios</span>
                                <button class="btn btn-sm btn-primary" onclick="nuevo('personal')">
                                    <i class="bi bi-plus"></i> Nuevo
                                </button>
                            </div>
                            <div class="card-body">
                                <table class="table table-striped" id="tablaUsuarios">
                                    <thead>
                                        <tr>
                                            <th>Numero E</th>
                                            <th>Nombre</th>
                                            <th>Puesto</th>
                                            <th>Estado</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Tipo Solicitud -->
                    <div class="tab-pane fade" id="tipoSolicitud">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <span>Tipo Solicitud</span>
                                <button class="btn btn-sm btn-primary" onclick="nuevo('tipo_solicitud')">
                                    <i class="bi bi-plus"></i> Nuevo
                                </button>
                            </div>
                            <div class="card-body">
                                <table class="table table-striped" id="tablaTipoSolicitud">
                                    <thead>
                                        <tr>
                                            <th>Nombre</th>
                                            <th>Descripcion</th>
                                            <th>Estado</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="../vendor/js/bootstrap.bundle.min.js"></script>
    <script src="/js/main.js"></script>
    
    <script>
        // Iconos para acciones
        const ICON_NUEVO = '<i class="bi bi-plus-circle"></i>';
        const ICON_EDITAR = '<i class="bi bi-pencil"></i>';
        const ICON_ELIMINAR = '<i class="bi bi-trash"></i>';
        const ICON_GUARDAR = '<i class="bi bi-check2"></i>';
        
        function mostrarModalConfirmacion(titulo, mensaje, tipo, callback) {
            let modal = document.getElementById('modalConfirmacion');
            if (!modal) {
                const modalHtml = `
                <div class="modal fade" id="modalConfirmacion" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header bg-${tipo} text-white">
                                <h5 class="modal-title">${titulo}</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body"><p>${mensaje}</p></div>
                            <div class="modal-footer">
                                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                <button class="btn btn-${tipo}" id="btnConfirmarModal">Confirmar</button>
                            </div>
                        </div>
                    </div>
                </div>`;
                document.body.insertAdjacentHTML('beforeend', modalHtml);
            }
            modal = document.getElementById('modalConfirmacion');
            modal.querySelector('.modal-title').textContent = titulo;
            modal.querySelector('.modal-body p').textContent = mensaje;
            modal.querySelector('.modal-header').className = 'modal-header bg-' + tipo + ' text-white';
            const btn = modal.querySelector('#btnConfirmarModal');
            btn.className = 'btn btn-' + tipo;
            btn.onclick = function() {
                bootstrap.Modal.getInstance(modal)?.hide();
                if (callback) callback();
            };
            new bootstrap.Modal(modal).show();
        }
        
        // Funcion modal de alerta
        function mostrarModalAlerta(titulo, mensaje, tipo) {
            let modal = document.getElementById('modalAlerta');
            if (!modal) {
                const modalHtml = `
                <div class="modal fade" id="modalAlerta" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header bg-${tipo} text-white">
                                <h5 class="modal-title">${titulo}</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body"><p class="mb-0">${mensaje}</p></div>
                            <div class="modal-footer">
                                <button class="btn btn-${tipo}" data-bs-dismiss="modal">Aceptar</button>
                            </div>
                        </div>
                    </div>
                </div>`;
                document.body.insertAdjacentHTML('beforeend', modalHtml);
            }
            modal = document.getElementById('modalAlerta');
            modal.querySelector('.modal-title').textContent = titulo;
            modal.querySelector('.modal-body p').textContent = mensaje;
            modal.querySelector('.modal-header').className = 'modal-header bg-' + tipo + ' text-white';
            new bootstrap.Modal(modal).show();
        }
        
        // Funcion modal para nuevo registro
        function mostrarModalNuevo(tipo, callback) {
            mostrarModalFormulario(tipo, null, callback);
        }
        
        // Funcion modal para editar registro
        function mostrarModalEditar(tipo, id, datos, callback) {
            mostrarModalFormulario(tipo, { id, ...datos }, callback);
        }
        
        // Funcion modal unificada (nuevo y editar)
        function mostrarModalFormulario(tipo, datos, callback) {
            const esEdicion = !!datos;
            let titulo = esEdicion ? 'Editar ' + tipo.charAt(0).toUpperCase() + tipo.slice(1) : 'Nuevo ' + tipo;
            let btnTexto = esEdicion ? 'Actualizar' : 'Guardar';
            
            let campos = '';
            if (tipo === 'maquina') {
                campos = `
                    <div class="mb-3">
                        <label class="form-label">Nombre</label>
                        <input type="text" class="form-control" id="inputNombre" value="${datos?.nombre || ''}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ubicacion</label>
                        <input type="text" class="form-control" id="inputUbicacion" value="${datos?.ubicacion || ''}">
                    </div>`;
            } else if (tipo === 'personal') {
                campos = `
                    <div class="mb-3">
                        <label class="form-label">Numero E</label>
                        <input type="text" class="form-control" id="inputNumeroE" value="${datos?.numero_e || ''}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nombre</label>
                        <input type="text" class="form-control" id="inputNombre" value="${datos?.nombre || ''}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Puesto</label>
                        <input type="text" class="form-control" id="inputPuesto" value="${datos?.puesto || ''}">
                    </div>`;
            } else {
                campos = `
                    <div class="mb-3">
                        <label class="form-label">Nombre</label>
                        <input type="text" class="form-control" id="inputNombre" value="${datos?.nombre || ''}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descripcion</label>
                        <input type="text" class="form-control" id="inputDescripcion" value="${datos?.descripcion || ''}">
                    </div>`;
            }
            
            let modal = document.getElementById('modalNuevo');
            if (modal) modal.remove();
            
            const modalHtml = `
            <div class="modal fade" id="modalNuevo" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title">${titulo}</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">${campos}</div>
                        <div class="modal-footer">
                            <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button class="btn btn-primary" id="btnGuardarModal">${btnTexto}</button>
                        </div>
                    </div>
                </div>
            </div>`;
            document.body.insertAdjacentHTML('beforeend', modalHtml);
            modal = document.getElementById('modalNuevo');
            
            modal.querySelector('#btnGuardarModal').onclick = function() {
                const nombre = document.getElementById('inputNombre')?.value || '';
                const descripcion = document.getElementById('inputDescripcion')?.value || '';
                const ubicacion = document.getElementById('inputUbicacion')?.value || '';
                const numero_e = document.getElementById('inputNumeroE')?.value || '';
                const puesto = document.getElementById('inputPuesto')?.value || '';
                
                if (!nombre && tipo !== 'personal') {
                    mostrarModalAlerta('Error', 'El nombre es requerido', 'danger');
                    return;
                }
                if (tipo === 'personal' && !numero_e) {
                    mostrarModalAlerta('Error', 'El numero E es requerido', 'danger');
                    return;
                }
                
                bootstrap.Modal.getInstance(modal)?.hide();
                
                const payload = esEdicion 
                    ? { tipo, id: datos.id, nombre, descripcion, ubicacion, numero_e, puesto }
                    : { tipo, nombre, descripcion, ubicacion, numero_e, puesto };
                callback(payload, esEdicion);
            };
            new bootstrap.Modal(modal).show();
        }
        
        async function cargarCatalogos() {
            try {
                const res = await fetch('/api/catalogos.php');
                const data = await res.json();
                
                if (data.success) {
                    // Commodity
                    document.querySelector('#tablaCommodity tbody').innerHTML = data.commodity.map(c => 
                        `<tr>
                            <td>${c.nombre}</td>
                            <td>${c.descripcion || ''}</td>
                            <td><span class="badge ${c.activo==='1'?'bg-success':'bg-secondary'}">${c.activo==='1'?'Activo':'Inactivo'}</span></td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary me-1" onclick="editar('commodity',${c.id},'${c.nombre}','${c.descripcion||''}','${c.activo}')">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger" onclick="eliminar('commodity',${c.id})">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>`
                    ).join('') || '<tr><td colspan="4">Sin datos</td></tr>';
                    
                    // Maquinas
                    document.querySelector('#tablaMaquinas tbody').innerHTML = data.maquinas.map(m => 
                        `<tr>
                            <td>${m.nombre}</td>
                            <td>${m.ubicacion || ''}</td>
                            <td><span class="badge ${m.activo==='1'?'bg-success':'bg-secondary'}">${m.activo==='1'?'Activo':'Inactivo'}</span></td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary me-1" onclick="editar('maquina',${m.id},'${m.nombre}','${m.ubicacion||''}','${m.activo}')">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger" onclick="eliminar('maquina',${m.id})">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>`
                    ).join('') || '<tr><td colspan="4">Sin datos</td></tr>';
                    
                    // Personal (Usuarios)
                    document.querySelector('#tablaUsuarios tbody').innerHTML = data.personal.map(u => 
                        `<tr>
                            <td>${u.numero_e}</td>
                            <td>${u.nombre}</td>
                            <td>${u.puesto || ''}</td>
                            <td><span class="badge ${u.activo==='1'?'bg-success':'bg-secondary'}">${u.activo==='1'?'Activo':'Inactivo'}</span></td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary me-1" onclick="editar('personal',${u.id},'${u.nombre}','${u.puesto||''}','${u.numero_e}','${u.activo}')">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger" onclick="eliminar('personal',${u.id})">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>`
                    ).join('') || '<tr><td colspan="5">Sin datos</td></tr>';
                    
                    // Tipo Solicitud
                    document.querySelector('#tablaTipoSolicitud tbody').innerHTML = data.tipo_solicitud.map(t => 
                        `<tr>
                            <td>${t.nombre}</td>
                            <td>${t.descripcion || ''}</td>
                            <td><span class="badge ${t.activo==='1'?'bg-success':'bg-secondary'}">${t.activo==='1'?'Activo':'Inactivo'}</span></td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary me-1" onclick="editar('tipo_solicitud',${t.id},'${t.nombre}','${t.descripcion||''}','${t.activo}')">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger" onclick="eliminar('tipo_solicitud',${t.id})">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>`
                    ).join('') || '<tr><td colspan="4">Sin datos</td></tr>';
                }
            } catch(e) { console.log(e); }
        }
        
        async function nuevo(tipo) {
            mostrarModalNuevo(tipo, async function(datos) {
                try {
                    await fetch('/api/alta.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(datos)
                    });
                    cargarCatalogos();
                    mostrarModalAlerta('Exito', 'Registro guardado correctamente', 'success');
                } catch(e) { mostrarModalAlerta('Error', 'Error al guardar', 'danger'); }
            });
        }
        
        async function editar(tipo, id, nombre, extra1, extra2, extra3) {
            // Preparar datos dependiendo del tipo
            let datos = {};
            if (tipo === 'maquina') {
                datos = { nombre: nombre, ubicacion: extra1 };
            } else if (tipo === 'personal') {
                datos = { nombre: nombre, puesto: extra1, numero_e: extra2 };
            } else {
                datos = { nombre: nombre, descripcion: extra1 };
            }
            
            mostrarModalEditar(tipo, id, datos, async function(payload, esEdicion) {
                try {
                    await fetch('/api/cambio.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ tipo, id, ...payload })
                    });
                    cargarCatalogos();
                    mostrarModalAlerta('Exito', 'Registro actualizado correctamente', 'success');
                } catch(e) { mostrarModalAlerta('Error', 'Error al actualizar', 'danger'); }
            });
        }
        
        async function eliminar(tipo, id) {
            mostrarModalConfirmacion(
                'Confirmar Eliminacion',
                'Esta seguro de que desea eliminar este registro?',
                'danger',
                async function() {
                    try {
                        await fetch('/api/baja.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ tipo, id })
                        });
                        cargarCatalogos();
                        mostrarModalAlerta('Exito', 'Registro eliminado correctamente', 'success');
                    } catch(e) { mostrarModalAlerta('Error', 'Error al eliminar', 'danger'); }
                }
            );
        }
        
        function logout() {
            window.location.href = '../modulos/login/logout.php';
        }
        
        cargarCatalogos();
    </script>
    <script src="../vendor/js/bootstrap.bundle.min.js"></script>
</body>
</html>
</html>


