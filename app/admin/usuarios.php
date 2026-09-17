<?php
$pagina = 'usuarios';
include '../includes/navbar.php';
?>

<h3><i class="bi bi-people"></i> Usuarios</h3>
<span class="text-muted small">Click en Editar para modificar o eliminar</span>

<div class="card p-3">
    <button class="btn btn-primary btn-sm mb-2" onclick="nuevoUsuario()">
        <i class="bi bi-plus-circle"></i> Nuevo
    </button>
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Número E</th>
                    <th>Nombre</th>
                    <th>Rol</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="tablaUsuarios">
                <tr><td colspan="6" class="text-center">Cargando...</td></tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="modalUsuario" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitulo">Nuevo Usuario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formUsuario">
                    <input type="hidden" id="usuarioId">
                    <div class="mb-3">
                        <label class="form-label">Número E</label>
                        <input type="text" id="numeroE" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nombre</label>
                        <input type="text" id="nombre" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" id="password" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Rol</label>
                        <select id="rol" class="form-select">
                            <option value="usuario">Usuario</option>
                            <option value="laboratorio">Laboratorio</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Estado</label>
                        <select id="estatus" class="form-select">
                            <option value="activo">Activo</option>
                            <option value="inactivo">Inactivo</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="btnEliminar" style="display:none;" onclick="eliminarUsuario()">Eliminar</button>
                <button type="button" class="btn btn-primary" onclick="guardarUsuario()">Guardar</button>
            </div>
        </div>
    </div>
</div>

<script src="../vendor/js/bootstrap.bundle.min.js"></script>
<script>
let modal;

document.addEventListener('DOMContentLoaded', function() {
    modal = new bootstrap.Modal(document.getElementById('modalUsuario'));
    cargarUsuarios();
});

async function cargarUsuarios() {
    try {
        const res = await fetch('/metrologia/api/usuarios.php');
        const data = await res.json();
        if (data.success) {
            document.getElementById('tablaUsuarios').innerHTML = data.usuarios.map(u => 
                '<tr><td>'+u.id+'</td><td>'+u.numero_e+'</td><td>'+u.nombre+'</td><td>'+u.rol+'</td><td>'+u.estatus+'</td><td><button class="btn btn-sm btn-primary" onclick="editarUsuario('+u.id+')">Editar</button></td></tr>'
            ).join('');
        }
    } catch(e) { console.log(e); }
}

function nuevoUsuario() {
    document.getElementById('modalTitulo').textContent = 'Nuevo Usuario';
    document.getElementById('usuarioId').value = '';
    document.getElementById('numeroE').value = '';
    document.getElementById('nombre').value = '';
    document.getElementById('password').value = '';
    document.getElementById('password').required = true;
    document.getElementById('rol').value = 'usuario';
    document.getElementById('estatus').value = 'activo';
    document.getElementById('btnEliminar').style.display = 'none';
    document.getElementById('password').parentElement.querySelector('label').textContent = 'Password *';
    modal.show();
}

async function editarUsuario(id) {
    const res = await fetch('/metrologia/api/usuarios.php?id=' + id);
    const data = await res.json();
    if (data.success && data.usuarios.length > 0) {
        const u = data.usuarios[0];
        document.getElementById('modalTitulo').textContent = 'Editar Usuario';
        document.getElementById('usuarioId').value = u.id;
        document.getElementById('numeroE').value = u.numero_e;
        document.getElementById('nombre').value = u.nombre;
        document.getElementById('password').value = '';
        document.getElementById('password').required = false;
        document.getElementById('rol').value = u.rol;
        document.getElementById('estatus').value = u.estatus;
        document.getElementById('btnEliminar').style.display = 'inline-block';
        document.getElementById('password').parentElement.querySelector('label').textContent = 'Password (dejar en blanco para mantener)';
        modal.show();
    }
}

async function guardarUsuario() {
    const id = document.getElementById('usuarioId').value;
    const numero_e = document.getElementById('numeroE').value;
    const nombre = document.getElementById('nombre').value;
    const password = document.getElementById('password').value;
    const rol = document.getElementById('rol').value;
    const estatus = document.getElementById('estatus').value;
    
    if (!numero_e || !nombre) {
        alert('Completa los campos requeridos');
        return;
    }
    
    const formData = new FormData();
    if (id) formData.append('id', id);
    formData.append('numero_e', numero_e);
    formData.append('nombre', nombre);
    formData.append('rol', rol);
    formData.append('estatus', estatus);
    if (password) formData.append('password', password);
    
    try {
        const res = await fetch('/metrologia/api/usuarios.php', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();
        if (data.success) {
            modal.hide();
            cargarUsuarios();
        } else {
            alert('Error: ' + data.error);
        }
    } catch(e) { alert('Error: ' + e); }
}

async function eliminarUsuario() {
    const id = document.getElementById('usuarioId').value;
    if (!id || !confirm('¿Eliminar usuario?')) return;
    
    try {
        const res = await fetch('/metrologia/api/usuarios.php?id=' + id, { method: 'DELETE' });
        const data = await res.json();
        if (data.success) {
            modal.hide();
            cargarUsuarios();
        } else {
            alert('Error: ' + data.error);
        }
    } catch(e) { alert('Error: ' + e); }
}
</script>
</body>
</html>
