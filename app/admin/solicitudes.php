<?php
$pagina = 'solicitudes';
include '../includes/navbar.php';

// Conexión a BD
$con = mysqli_connect('db', 'root', 'root', 'mmqro');
$solicitudes = [];
$tipos = [];
$commodities = [];

$filtro = $_GET['filtro'] ?? '';
$busq = $_GET['busq'] ?? '';

if ($con) {
    $where = [];
    if ($filtro) $where[] = "s.estatus = '$filtro'";
    if ($busq) $where[] = "s.folio LIKE '%$busq%'";
    $sql = "SELECT s.*, p.nombre as nombre_empleado FROM solicitudes s LEFT JOIN personal p ON s.numero_e = p.numero_e";
    if ($where) $sql .= " WHERE " . implode(' AND ', $where);
    $sql .= " ORDER BY s.fecha_creacion DESC";
    $result = mysqli_query($con, $sql);
    while ($row = mysqli_fetch_assoc($result)) {
        $solicitudes[] = $row;
    }
    
    // Cargar catálogos
    $resT = mysqli_query($con, "SELECT nombre FROM tipo_solicitud ORDER BY nombre");
    if($resT) { while($row = mysqli_fetch_assoc($resT)) { $tipos[] = $row['nombre']; } }
    
    $resC = mysqli_query($con, "SELECT nombre FROM commodity ORDER BY nombre");
    if($resC) { while($row = mysqli_fetch_assoc($resC)) { $commodities[] = $row['nombre']; } }
    
    mysqli_close($con);
}
?>

<h3><i class="bi bi-file-earmark-text"></i> Solicitudes</h3>

<!-- Filtros -->
<div class="row mb-3">
    <div class="col-md-3">
        <select class="form-select" id="filtroEstatus" onchange="filtrar()">
            <option value="">Todos los estatus</option>
            <option value="pendiente">Pendiente</option>
            <option value="proceso">En Proceso</option>
            <option value="listo">Listo</option>
            <option value="entregado">Entregado</option>
        </select>
    </div>
    <div class="col-md-3">
        <input type="text" class="form-control" id="busqueda" placeholder="Buscar por folio..." onkeyup="filtrar()">
    </div>
    <div class="col-md-6 text-end">
        <button class="btn btn-primary" onclick="nuevaSolicitud()">
            <i class="bi bi-plus-circle"></i> Nueva Solicitud
        </button>
    </div>
</div>

<!-- Tabla -->
<div class="card">
    <div class="card-body">
        <table class="table table-striped table-hover" id="tablaSolicitudes">
            <thead>
                <tr>
                    <th>Folio</th>
                    <th>Fecha</th>
                    <th>Solicitante</th>
                    <th>Commodity</th>
                    <th>Tipo</th>
                    <th>Cantidad</th>
                    <th>Estatus</th>
                    <th></th>
                </tr>
            </thead>
            <tbody id="tbodySolicitudes">
                <?php foreach ($solicitudes as $s): ?>
                <tr>
                    <td><strong><?php echo $s['folio']; ?></strong></td>
                    <td><?php echo date('d/m/Y H:i', strtotime($s['fecha_creacion'])); ?></td>
                    <td><?php echo $s['nombre_empleado'] ?: $s['numero_e']; ?></td>
                    <td><?php echo $s['commodity']; ?></td>
                    <td><?php echo $s['tipo_solicitud']; ?></td>
                    <td><?php echo $s['cantidad']; ?></td>
                    <td>
                        <?php $badge = $s['estatus'] === 'listo' ? 'success' : ($s['estatus'] === 'proceso' ? 'warning' : 'secondary'); ?>
                        <span class="badge bg-<?php echo $badge; ?>"><?php echo $s['estatus']; ?></span>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-outline-info me-1" onclick="ver('<?php echo $s['folio']; ?>')" title="Ver"><i class="bi bi-eye"></i></button>
                        <button class="btn btn-sm btn-outline-primary me-1" onclick="editar('<?php echo $s['folio']; ?>')" title="Editar"><i class="bi bi-pencil"></i></button>
                        <button class="btn btn-sm btn-outline-danger" onclick="confirmarEliminar('<?php echo $s['folio']; ?>')" title="Eliminar"><i class="bi bi-trash"></i></button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Nueva/Editar Solicitud -->
<div class="modal fade" id="modalSolicitud" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="tituloModal"><i class="bi bi-file-earmark-plus"></i> Nueva Solicitud</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="inputId">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Número E</label>
                        <input type="text" class="form-control" id="inputNumeroE" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Tipo de Solicitud</label>
                        <select class="form-select" id="inputTipo">
                            <option value="">Seleccionar...</option>
                            <?php foreach($tipos as $t): ?>
                                <option value="<?php echo $t; ?>"><?php echo $t; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Commodity</label>
                        <select class="form-select" id="inputCommodity">
                            <option value="">Seleccionar...</option>
                            <?php foreach($commodities as $c): ?>
                                <option value="<?php echo $c; ?>"><?php echo $c; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Cantidad</label>
                        <input type="number" class="form-control" id="inputCantidad" value="1" min="1">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Estatus</label>
                    <select class="form-select" id="inputEstatus">
                        <option value="pendiente">Pendiente</option>
                        <option value="proceso">En Proceso</option>
                        <option value="listo">Listo</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer" id="footerEstatus">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" onclick="guardar()">Guardar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Ver -->
<div class="modal fade" id="modalVer" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="bi bi-file-earmark-text"></i> Detalles Solicitud</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detallesSolicitud"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Confirmar Eliminar -->
<div class="modal fade" id="modalEliminar" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bi bi-trash"></i> Confirmar Eliminación</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>¿Está seguro de eliminar la solicitud <strong id="eliminarFolio"></strong>?</p>
                <p class="text-danger">Esta acción no se puede deshacer.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" onclick="eliminar()">Eliminar</button>
            </div>
        </div>
    </div>
</div>

<script src="../vendor/js/bootstrap.bundle.min.js"></script>
<script>
let folioEditar = '';
let folioEliminar = '';

function nuevaSolicitud() {
    folioEditar = '';
    document.getElementById('tituloModal').innerHTML = '<i class="bi bi-file-earmark-plus"></i> Nueva Solicitud';
    document.getElementById('inputId').value = '';
    document.getElementById('inputNumeroE').value = '';
    document.getElementById('inputTipo').value = 'calibración';
    document.getElementById('inputCantidad').value = '1';
    document.getElementById('inputEstatus').value = 'pendiente';
    new bootstrap.Modal(document.getElementById('modalSolicitud')).show();
}

let datosEditar = null;

function editar(folio) {
    folioEditar = folio;
    fetch('/metrologia/api/todas.php?folio=' + folio)
    .then(r => r.json())
    .then(d => {
        console.log('Editar datos:', d);
        if (d.success && d.solicitudes.length > 0) {
            datosEditar = d.solicitudes[0];
            document.getElementById('tituloModal').innerHTML = '<i class="bi bi-pencil"></i> Editar: ' + folio;
            document.getElementById('inputId').value = datosEditar.folio;
            document.getElementById('inputNumeroE').value = datosEditar.numero_e;
            
            // Set tipo de solicitud
            let selTipo = document.getElementById('inputTipo');
            selTipo.value = datosEditar.tipo_solicitud || datosEditar.tipo || '';
            
            // Set commodity
            let selComm = document.getElementById('inputCommodity');
            selComm.value = datosEditar.commodity || '';
            
            document.getElementById('inputCantidad').value = datosEditar.cantidad;
            document.getElementById('inputEstatus').value = datosEditar.estatus || 'pendiente';
            
            // Solo Cerrar y Guardar en modal editar
            let footer = '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>';
            footer += '<button type="button" class="btn btn-primary" onclick="guardar()">Guardar</button>';
            document.getElementById('footerEstatus').innerHTML = footer;
            
            new bootstrap.Modal(document.getElementById('modalSolicitud')).show();
        }
    });
}

function ver(folio) {
    fetch('/metrologia/api/todas.php?folio=' + folio)
    .then(r => r.json())
    .then(d => {
        if (d.success && d.solicitudes.length > 0) {
            const s = d.solicitudes[0];
            document.getElementById('detallesSolicitud').innerHTML = 
                '<table class="table table-borderless">' +
                '<tr><th>Folio:</th><td>'+s.folio+'</td></tr>' +
                '<tr><th>Fecha:</th><td>'+(s.fecha_creacion || '-')+'</td></tr>' +
                '<tr><th>Solicitante:</th><td>'+(s.nombre_solicitante || s.numero_e)+'</td></tr>' +
                '<tr><th>Commodity:</th><td>'+s.commodity+'</td></tr>' +
                '<tr><th>Tipo:</th><td>'+s.tipo_solicitud+'</td></tr>' +
                '<tr><th>Cantidad:</th><td>'+s.cantidad+'</td></tr>' +
                '<tr><th>Estatus:</th><td><span class="badge bg-'+(s.estatus==='listo'?'success':s.estatus==='proceso'?'warning':'secondary')+'">'+s.estatus+'</span></td></tr>' +
                '</table>';
            new bootstrap.Modal(document.getElementById('modalVer')).show();
        }
    });
}

function confirmarEliminar(folio) {
    folioEliminar = folio;
    document.getElementById('eliminarFolio').textContent = folio;
    new bootstrap.Modal(document.getElementById('modalEliminar')).show();
}

function eliminar() {
    if (!folioEliminar) return;
    fetch('/metrologia/api/solicitudes.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({action: 'eliminar', folio: folioEliminar})
    })
    .then(r => r.json())
    .then(d => {
        console.log('Eliminar:', d);
        if (d.success) location.reload();
        else alert('Error: ' + (d.error || 'Error al eliminar'));
    })
    .catch(e => {
        console.error(e);
        alert('Error de conexión');
    });
}

function cambiarEstatus(folio, estatus) {
    fetch('/metrologia/api/cambio.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({folio: folio, estatus: estatus})
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) location.reload();
    });
}

function guardar() {
    const datos = {
        folio: document.getElementById('inputId').value,
        numero_e: document.getElementById('inputNumeroE').value,
        tipo_solicitud: document.getElementById('inputTipo').value,
        commodity: document.getElementById('inputCommodity').value,
        cantidad: document.getElementById('inputCantidad').value,
        estatus: document.getElementById('inputEstatus').value
    };
    
    // Nueva solicitud = crear, Editar = action=editar
    const url = folioEditar 
        ? '/metrologia/api/solicitudes.php?action=editar' 
        : '/metrologia/api/alta.php';
    
    fetch(url, {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(datos)
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            bootstrap.Modal.getInstance(document.getElementById('modalSolicitud')).hide();
            location.reload();
        } else {
            alert('Error: ' + d.error);
        }
    });
}

function filtrar() {
    const filtro = document.getElementById('filtroEstatus').value;
    const busq = document.getElementById('busqueda').value;
    window.location.href = '?filtro=' + filtro + '&busq=' + busq;
}

window.onload = function() {
    const params = new URLSearchParams(window.location.search);
    document.getElementById('filtroEstatus').value = params.get('filtro') || '';
    document.getElementById('busqueda').value = params.get('busq') || '';
};
</script>
</body>
</html>
