<?php
/**
 * Tablero de Laboratorio - Vista Kanban
 * MMQRO - Sistema de Gestión de Metrología
 */

$pagina = 'laboratorio';
include '../includes/navbar.php';

// Usar timezone de México
date_default_timezone_set('America/Mexico_City');

// Conexión a BD
$con = mysqli_connect('db', 'root', 'root', 'mmqro');
$solicitudes = [];
$pendientes = [];
$proceso = [];
$listos = [];

if ($con) {
    // Mostrar todas las solicitudes recientes (últimos 30 días), incluyendo las entregadas
    $sql = "SELECT s.*, p.nombre as nombre_solicitante, c.nombre as nombre_commodity FROM solicitudes s LEFT JOIN personal p ON s.numero_e = p.numero_e LEFT JOIN commodity c ON s.commodity = c.nombre WHERE s.fecha_creacion >= DATE_SUB(NOW(), INTERVAL 30 DAY) ORDER BY fecha_creacion DESC";
    $result = mysqli_query($con, $sql);
    while ($row = mysqli_fetch_assoc($result)) {
        if ($row['estatus'] === 'pendiente') $pendientes[] = $row;
        elseif ($row['estatus'] === 'proceso') $proceso[] = $row;
        elseif ($row['estatus'] === 'listo') $listos[] = $row;
        else $listos[] = $row; // entregado también va a listos
    }
    mysqli_close($con);
}

function tiempo_transcurrido($fecha) {
    // Usar timezone de México para el cálculo
    $now = new DateTime('now', new DateTimeZone('America/Mexico_City'));
    $fechaObj = new DateTime($fecha, new DateTimeZone('America/Mexico_City'));
    $diff = $now->getTimestamp() - $fechaObj->getTimestamp();
    $minutos = floor($diff / 60);
    $horas = floor($minutos / 60);
    if ($minutos < 1) return 'Ahora';
    if ($minutos < 60) return $minutos . ' min';
    if ($horas < 24) return $horas . ' hr';
    return floor($horas / 24) . ' días';
}
?>

<h3><i class="bi bi-kanban"></i> Tablero de Laboratorio</h3>

<!-- Stats -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="stat-card text-center">
            <div class="stat-number text-warning"><?php echo count($pendientes); ?></div>
            <div class="stat-label">Pendientes</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card text-center">
            <div class="stat-number text-info"><?php echo count($proceso); ?></div>
            <div class="stat-label">En Proceso</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card text-center">
            <div class="stat-number text-success"><?php echo count($listos); ?></div>
            <div class="stat-label">Listos</div>
        </div>
    </div>
</div>

<!-- Kanban -->
<div class="row">
    <!-- Pendientes -->
    <div class="col-md-4 mb-3">
        <div class="card kanban-column">
            <div class="kanban-header border-bottom">
                <i class="bi bi-hourglass-split text-warning"></i> Pendientes <span class="badge bg-secondary"><?php echo count($pendientes); ?></span>
            </div>
            <div class="card-body" style="max-height: 70vh; overflow-y: auto;">
                <?php if (empty($pendientes)): ?>
                    <p class="text-muted text-center">No hay solicitudes</p>
                <?php else: ?>
                    <?php foreach ($pendientes as $s): ?>
                        <?php $nombre = $s['nombre_solicitante'] ?: $s['numero_e']; ?>
                        <?php $esEspecial = !empty($s['especial']); ?>
                        <?php $esPrioridad = !empty($s['maquina_detenida']); ?>
                        <div class="kanban-card mb-2 <?php echo $esPrioridad ? 'border-start-warning bg-warning-subtle' : ($esEspecial ? 'border-start-danger bg-danger-subtle' : 'border-start-warning'); ?>" data-bs-toggle="modal" data-bs-target="#modalOrden" data-id="<?php echo $s['id']; ?>" data-estatus="pendiente" data-folio="<?php echo $s['folio']; ?>" data-tipo="<?php echo $s['tipo_solicitud']; ?>" data-solicitante="<?php echo $nombre; ?>" data-commodity="<?php echo $s['commodity']; ?>" data-cantidad="<?php echo $s['cantidad']; ?>" data-descripcion="<?php echo $s['descripcion'] ?? ''; ?>" data-fecha="<?php echo $s['fecha_creacion']; ?>" data-numero_parte="<?php echo $s['numero_parte'] ?? ''; ?>" style="cursor:pointer;">
                            <div class="card-body p-2">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <strong><?php echo $s['folio']; ?></strong>
                                        <?php if ($esPrioridad): ?>
                                        <span class="badge bg-warning text-dark ms-1"><i class="bi bi-exclamation-triangle"></i> MAQUINA DETENIDA</span>
                                        <?php endif; ?>
                                        <?php if ($esEspecial): ?>
                                        <span class="badge bg-danger ms-1"><i class="bi bi-star-fill"></i> ESPECIAL</span>
                                        <?php endif; ?>
                                        <?php if (!empty($s['numero_parte'])): ?>
                                        <span class="badge bg-secondary ms-1"><?php echo $s['numero_parte']; ?></span>
                                        <?php endif; ?>
                                        <br><small class="text-muted"><?php echo $s['descripcion'] ?: $s['tipo_solicitud']; ?></small>
                                    </div>
                                    <small class="text-muted"><?php echo tiempo_transcurrido($s['fecha_creacion']); ?></small>
                                </div>
                                <small class="text-muted d-block"><?php echo $nombre; ?> | <?php echo $s['commodity']; ?> (<?php echo $s['cantidad']; ?>)</small>
                                <div class="mt-1">
                                    <button class="btn btn-sm btn-info py-0 px-1" onclick="pasarEstatusRapido(<?php echo $s['id']; ?>, 'proceso')" title="Pasar a Proceso"><i class="bi bi-arrow-right"></i></button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Proceso -->
    <div class="col-md-4 mb-3">
        <div class="card kanban-column">
            <div class="kanban-header border-bottom">
                <i class="bi bi-arrow-repeat text-info"></i> En Proceso <span class="badge bg-secondary"><?php echo count($proceso); ?></span>
            </div>
            <div class="card-body" style="max-height: 70vh; overflow-y: auto;">
                <?php if (empty($proceso)): ?>
                    <p class="text-muted text-center">No hay solicitudes</p>
                <?php else: ?>
                    <?php foreach ($proceso as $s): ?>
                        <?php $nombre = $s['nombre_solicitante'] ?: $s['numero_e']; ?>
                        <?php $esEspecial = !empty($s['especial']); ?>
                        <?php $esPrioridad = !empty($s['maquina_detenida']); ?>
                        <div class="kanban-card mb-2 <?php echo $esPrioridad ? 'border-start-warning bg-warning-subtle' : ($esEspecial ? 'border-start-danger bg-danger-subtle' : 'border-start-info'); ?>" data-bs-toggle="modal" data-bs-target="#modalOrden" data-id="<?php echo $s['id']; ?>" data-estatus="proceso" data-folio="<?php echo $s['folio']; ?>" data-tipo="<?php echo $s['tipo_solicitud']; ?>" data-solicitante="<?php echo $nombre; ?>" data-commodity="<?php echo $s['commodity']; ?>" data-cantidad="<?php echo $s['cantidad']; ?>" data-descripcion="<?php echo $s['descripcion'] ?? ''; ?>" data-fecha="<?php echo $s['fecha_creacion']; ?>" data-numero_parte="<?php echo $s['numero_parte'] ?? ''; ?>" style="cursor:pointer;">
                            <div class="card-body p-2">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <strong><?php echo $s['folio']; ?></strong>
                                        <?php if ($esPrioridad): ?>
                                        <span class="badge bg-warning text-dark ms-1"><i class="bi bi-exclamation-triangle"></i> MAQUINA DETENIDA</span>
                                        <?php endif; ?>
                                        <?php if ($esEspecial): ?>
                                        <span class="badge bg-danger ms-1"><i class="bi bi-star-fill"></i> ESPECIAL</span>
                                        <?php endif; ?>
                                        <?php if (!empty($s['numero_parte'])): ?>
                                        <span class="badge bg-secondary ms-1"><?php echo $s['numero_parte']; ?></span>
                                        <?php endif; ?>
                                        <br><small class="text-muted"><?php echo $s['descripcion'] ?: $s['tipo_solicitud']; ?></small>
                                    </div>
                                    <small class="text-muted"><?php echo tiempo_transcurrido($s['fecha_creacion']); ?></small>
                                </div>
                                <small class="text-muted d-block"><?php echo $nombre; ?> | <?php echo $s['commodity']; ?> (<?php echo $s['cantidad']; ?>)</small>
                                <div class="mt-1">
                                    <button class="btn btn-sm btn-warning py-0 px-1 me-1" onclick="solicitarMotivoRapido(<?php echo $s['id']; ?>, 'pendiente')" title="A Pendiente"><i class="bi bi-arrow-left"></i></button>
                                    <button class="btn btn-sm btn-success py-0 px-1" onclick="pasarEstatusRapido(<?php echo $s['id']; ?>, 'listo')" title="Pasar a Listo"><i class="bi bi-check"></i></button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Listos -->
    <div class="col-md-4 mb-3">
        <div class="card kanban-column">
            <div class="kanban-header border-bottom">
                <i class="bi bi-check-circle text-success"></i> Listos <span class="badge bg-secondary"><?php echo count($listos); ?></span>
            </div>
            <div class="card-body" style="max-height: 70vh; overflow-y: auto;">
                <?php if (empty($listos)): ?>
                    <p class="text-muted text-center">No hay solicitudes</p>
                <?php else: ?>
                    <?php foreach ($listos as $s): ?>
                        <?php $nombre = $s['nombre_solicitante'] ?: $s['numero_e']; ?>
                        <?php $esEspecial = !empty($s['especial']); ?>
                        <?php $esPrioridad = !empty($s['maquina_detenida']); ?>
                        <div class="kanban-card mb-2 <?php echo $esPrioridad ? 'border-start-warning bg-warning-subtle' : ($esEspecial ? 'border-start-danger bg-danger-subtle' : 'border-start-success'); ?>" data-bs-toggle="modal" data-bs-target="#modalOrden" data-id="<?php echo $s['id']; ?>" data-estatus="listo" data-folio="<?php echo $s['folio']; ?>" data-tipo="<?php echo $s['tipo_solicitud']; ?>" data-solicitante="<?php echo $nombre; ?>" data-commodity="<?php echo $s['commodity']; ?>" data-cantidad="<?php echo $s['cantidad']; ?>" data-descripcion="<?php echo $s['descripcion'] ?? ''; ?>" data-fecha="<?php echo $s['fecha_completado'] ?? $s['fecha_creacion']; ?>" data-numero_parte="<?php echo $s['numero_parte'] ?? ''; ?>" style="cursor:pointer;">
                            <div class="card-body p-2">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <strong><?php echo $s['folio']; ?></strong>
                                        <?php if ($esPrioridad): ?>
                                        <span class="badge bg-warning text-dark ms-1"><i class="bi bi-exclamation-triangle"></i> MAQUINA DETENIDA</span>
                                        <?php endif; ?>
                                        <?php if ($esEspecial): ?>
                                        <span class="badge bg-danger ms-1"><i class="bi bi-star-fill"></i> ESPECIAL</span>
                                        <?php endif; ?>
                                        <?php if (!empty($s['numero_parte'])): ?>
                                        <span class="badge bg-secondary ms-1"><?php echo $s['numero_parte']; ?></span>
                                        <?php endif; ?>
                                        <br><small class="text-muted"><?php echo $s['descripcion'] ?: $s['tipo_solicitud']; ?></small>
                                    </div>
                                    <small class="text-muted"><?php echo tiempo_transcurrido($s['fecha_completado'] ?? $s['fecha_creacion']); ?></small>
                                </div>
                                <small class="text-muted d-block"><?php echo $nombre; ?> | <?php echo $s['commodity']; ?> (<?php echo $s['cantidad']; ?>)</small>
                                <div class="mt-1">
                                    <button class="btn btn-sm btn-info py-0 px-1" onclick="pasarEstatusRapido(<?php echo $s['id']; ?>, 'proceso')" title="A Proceso"><i class="bi bi-arrow-left"></i></button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal bootstrap -->
<div class="modal fade" id="modalOrden" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-clipboard-data"></i> <span id="modalFolio"></span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-6">
                        <label class="text-muted small">No. Parte</label>
                        <div class="fw-bold" id="modalNumeroParte"></div>
                    </div>
                    <div class="col-6">
                        <label class="text-muted small">Solicitante</label>
                        <div class="fw-bold" id="modalSolicitante"></div>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-6">
                        <label class="text-muted small">Commodity</label>
                        <div class="fw-bold" id="modalCommodity"></div>
                    </div>
                    <div class="col-6">
                        <label class="text-muted small">Cantidad</label>
                        <div class="fw-bold" id="modalCantidad"></div>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="text-muted small">Descripción</label>
                    <div id="modalDescripcion"></div>
                </div>
                <div class="mb-3">
                    <label class="text-muted small">Fecha</label>
                    <div id="modalFecha"></div>
                </div>
            </div>
            <div class="modal-footer" id="footerLab">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para motivo -->
<div class="modal fade" id="modalMotivo" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title"><i class="bi bi-exclamation-triangle"></i> Motivo requerido</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>¿Por qué deseas regresar esta solicitud a pendiente?</p>
                <textarea id="textoMotivo" class="form-control" rows="3" placeholder="Describe el motivo..."></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-warning" onclick="confirmarConMotivo()">Confirmar</button>
            </div>
        </div>
    </div>
</div>

<script src="../vendor/js/bootstrap.bundle.min.js"></script>
<script>
    let idActual = 0;
    let estatusActual = '';
    let estatusTarget = '';
    const modal = document.getElementById('modalOrden');
    const modalMotivo = new bootstrap.Modal(document.getElementById('modalMotivo'));

    modal.addEventListener('show.bs.modal', function(e) {
        const card = e.relatedTarget;
        idActual = card.dataset.id;
        estatusActual = card.dataset.estatus || 'pendiente';
        document.getElementById('modalFolio').textContent = card.dataset.folio;
        document.getElementById('modalNumeroParte').textContent = card.dataset.numero_parte || '-';
        document.getElementById('modalSolicitante').textContent = card.dataset.solicitante;
        document.getElementById('modalCommodity').textContent = card.dataset.commodity;
        document.getElementById('modalCantidad').textContent = card.dataset.cantidad + ' piezas';
        document.getElementById('modalDescripcion').textContent = card.dataset.descripcion || 'Sin descripción';
        document.getElementById('modalFecha').textContent = card.dataset.fecha;
        
        // Mostrar botones según estatus
        let footer = '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>';
        if (estatusActual === 'pendiente') {
            footer += '<button type="button" class="btn btn-info" onclick="pasarEstatus(&quot;proceso&quot;)"><i class="bi bi-arrow-right"></i> En Proceso</button>';
        } else if (estatusActual === 'proceso') {
            footer += '<button type="button" class="btn btn-warning" onclick="solicitarMotivo(&quot;pendiente&quot;)"><i class="bi bi-arrow-left"></i> Pendiente</button>';
            footer += '<button type="button" class="btn btn-success" onclick="pasarEstatus(&quot;listo&quot;)"><i class="bi bi-check"></i> Listo</button>';
        } else {
            footer += '<button type="button" class="btn btn-warning" onclick="solicitarMotivo(&quot;pendiente&quot;)"><i class="bi bi-arrow-left"></i> Pendiente</button>';
        }
        document.getElementById('footerLab').innerHTML = footer;
    });
    
    function solicitarMotivo(nuevoEstatus) {
        estatusTarget = nuevoEstatus;
        document.getElementById('textoMotivo').value = '';
        const modalOrden = bootstrap.Modal.getInstance(document.getElementById('modalOrden'));
        if (modalOrden) modalOrden.hide();
        setTimeout(() => modalMotivo.show(), 300);
    }
    
    function confirmarConMotivo() {
        const motivo = document.getElementById('textoMotivo').value.trim();
        if (!motivo) {
            alert('Por favor ingresa un motivo');
            return;
        }
        modalMotivo.hide();
        pasarEstatusConMotivo(estatusTarget, motivo);
    }
    
    function pasarEstatus(nuevoEstatus) {
        if (!idActual) return;
        fetch('../api/proceso.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'id=' + idActual + '&estatus=' + nuevoEstatus
        })
        .then(r => r.json())
        .then(d => {
            if (d.success) {
                location.reload();
            } else {
                alert('Error: ' + d.error);
            }
        })
        .catch(e => alert('Error: ' + e));
    }
    
    function pasarEstatusConMotivo(nuevoEstatus, motivo) {
        if (!idActual) return;
        fetch('../api/proceso.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'id=' + idActual + '&estatus=' + nuevoEstatus + '&motivo=' + encodeURIComponent(motivo)
        })
        .then(r => r.json())
        .then(d => {
            if (d.success) {
                location.reload();
            } else {
                alert('Error: ' + d.error);
            }
        })
        .catch(e => alert('Error: ' + e));
    }
    
    // Función rápida sin modal - también requiere motivo si va a pendiente
    function pasarEstatusRapido(id, nuevoEstatus) {
        fetch('../api/proceso.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'id=' + id + '&estatus=' + nuevoEstatus
        })
        .then(r => r.json())
        .then(d => {
            if (d.success) {
                location.reload();
            } else {
                alert('Error: ' + d.error);
            }
        })
        .catch(e => alert('Error: ' + e));
    }
    
    // Solicitar motivo de forma rápida
    function solicitarMotivoRapido(id, nuevoEstatus) {
        const motivo = prompt('¿Por qué regresas a pendiente?');
        if (!motivo || !motivo.trim()) {
            alert('Motivo requerido');
            return;
        }
        fetch('../api/proceso.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'id=' + id + '&estatus=' + nuevoEstatus + '&motivo=' + encodeURIComponent(motivo)
        })
        .then(r => r.json())
        .then(d => {
            if (d.success) {
                location.reload();
            } else {
                alert('Error: ' + d.error);
            }
        })
        .catch(e => alert('Error: ' + e));
    }
    
    // Auto-refresh cada 30 segundos
    setTimeout(() => location.reload(), 30000);
</script>

</div></body></html>
