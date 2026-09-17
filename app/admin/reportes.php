<?php
$pagina = 'reportes';
include '../includes/navbar.php';
?>

<script src="../vendor/js/chart.umd.min.js"></script>

<div class="container-fluid py-3">
    <h4><i class="bi bi-graph-up"></i> Reportes</h4>
    
    <!-- Panel de Exportar y Filtrar -->
    <div class="card mb-3 p-2">
        <div class="row g-2 align-items-center">
            <div class="col-auto">
                <label class="small"><strong>Filtrar por:</strong></label>
            </div>
            <div class="col-auto">
                <input type="date" id="fechaInicio" class="form-control form-control-sm" placeholder="Desde">
            </div>
            <div class="col-auto">
                <input type="date" id="fechaFin" class="form-control form-control-sm" placeholder="Hasta">
            </div>
            <div class="col-auto">
                <select id="tipoFiltro" class="form-select form-select-sm">
                    <option value="">Todos los tipos</option>
                    <option value="Producción">Producción</option>
                    <option value="PPAP">PPAP</option>
                    <option value="Pruebas Ingeniería">Pruebas Ingeniería</option>
                    <option value="Incoming">Incoming</option>
                </select>
            </div>
            <div class="col-auto">
                <select id="estatusFiltro" class="form-select form-select-sm">
                    <option value="">Todos los estados</option>
                    <option value="pendiente">Pendiente</option>
                    <option value="proceso">En Proceso</option>
                    <option value="listo">Listo</option>
                </select>
            </div>
            <div class="col-auto">
                <button class="btn btn-sm btn-outline-primary" onclick="aplicarFiltro()">
                    <i class="bi bi-funnel"></i> Aplicar
                </button>
            </div>
            <div class="col-auto ms-auto">
                <button class="btn btn-sm btn-success" onclick="aplicarFiltro(); exportarExcel()">
                    <i class="bi bi-file-earmark-excel"></i> Excel
                </button>
            </div>
            <div class="col-auto">
                <button class="btn btn-sm btn-danger" onclick="aplicarFiltro(); exportarPDF()">
                    <i class="bi bi-file-earmark-pdf"></i> PDF
                </button>
            </div>
        </div>
    </div>
    
    <!-- Crear Reporte Personalizado -->
    <div class="card mb-3 p-2">
        <div class="d-flex justify-content-between align-items-center">
            <h6 class="mb-0"><i class="bi bi-plus-circle"></i> Reportes Guardados</h6>
            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalReporte">
                <i class="bi bi-plus-lg"></i> Nuevo
            </button>
        </div>
        <div class="table-responsive mt-2">
            <table class="table table-sm table-bordered mb-0" id="tablaReportes">
                <thead class="table-light">
                    <tr>
                        <th>Nombre</th>
                        <th>Fecha Creación</th>
                        <th>Registros</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td colspan="4" class="text-center text-muted">No hay reportes guardados</td></tr>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Stats -->
    <div class="row mb-4 g-3">
        <div class="col-6 col-md-3">
            <div class="stat-card text-center">
                <div class="stat-number text-primary" id="statTotal">0</div>
                <div class="stat-label">Total</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card text-center">
                <div class="stat-number text-warning" id="statPendientes">0</div>
                <div class="stat-label">Pendientes</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card text-center">
                <div class="stat-number text-success" id="statListos">0</div>
                <div class="stat-label">Completados</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card text-center">
                <div class="stat-number" id="statPiezas">0</div>
                <div class="stat-label">Piezas</div>
            </div>
        </div>
    </div>
    
    <!-- Gráficas -->
    <div class="row g-2 mb-3">
        <div class="col-md-6">
            <div class="card p-2">
                <h6 class="mb-2">Por Tipo</h6>
                <div style="height: 180px; position: relative;"><canvas id="chartTipo"></canvas></div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card p-2">
                <h6 class="mb-2">Por Commodity</h6>
                <div style="height: 180px; position: relative;"><canvas id="chartCommodity"></canvas></div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card p-2">
                <h6 class="mb-2">Estado</h6>
                <div style="height: 180px; position: relative;"><canvas id="chartEstado"></canvas></div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card p-2">
                <h6 class="mb-2">Últimos 7 días</h6>
                <div style="height: 180px; position: relative;"><canvas id="chartDias"></canvas></div>
            </div>
        </div>
    </div>
    
    <!-- Tabla -->
    <div class="card p-2">
        <h6 class="mb-2">Solicitudes Recientes</h6>
        <div class="table-responsive">
            <table class="table table-striped table-sm mb-0">
                <thead>
                    <tr>
                        <th>Folio</th>
                        <th>Nombre</th>
                        <th>Tipo</th>
                        <th>Commodity</th>
                        <th>Cantidad</th>
                        <th>Fecha</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody id="tablaSolicitudes"></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Detalle Gráfica -->
<div class="modal fade" id="modalDetalleGrafica" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalDetalleTitle">Detalle</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-striped table-sm">
                        <thead>
                            <tr>
                                <th>Folio</th>
                                <th>Solicitante</th>
                                <th>Tipo</th>
                                <th>Commodity</th>
                                <th>Cantidad</th>
                                <th>Fecha</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody id="tablaDetalleGrafica"></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script src="../vendor/js/bootstrap.bundle.min.js"></script>
<script>
    const colors = ['#C0392B', '#E74C3C', '#922B21', '#641E16', '#F1948A', '#E6B0AA'];
    let charts = {};
    
    async function cargarDatos() {
        try {
            const res = await fetch('/metrologia/api/estadisticas.php');
            const data = await res.json();
            
            if (data.success) {
                document.getElementById('statTotal').innerText = data.stats.total;
                document.getElementById('statPendientes').innerText = data.stats.pendientes;
                document.getElementById('statListos').innerText = data.stats.listos;
                document.getElementById('statPiezas').innerText = data.stats.piezas;
                
                charts.chartTipo = new Chart(document.getElementById('chartTipo'), {
                    type: 'doughnut',
                    data: {
                        labels: data.charts.tipo.map(x => x.tipo),
                        datasets: [{ data: data.charts.tipo.map(x => x.cantidad), backgroundColor: colors }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: true, position: 'bottom', font: { size: 10 } } }, onClick: (e, els, ch) => onChartClick(e, els, ch, 'tipo') }
                });
                
                charts.chartCommodity = new Chart(document.getElementById('chartCommodity'), {
                    type: 'bar',
                    data: {
                        labels: data.charts.commodity.slice(0,5).map(x => x.commodity.substring(0,10)),
                        datasets: [{ data: data.charts.commodity.slice(0,5).map(x => x.cantidad), backgroundColor: '#C0392B' }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, ticks: { font: { size: 10 } } }, x: { ticks: { font: { size: 10 } } } }, onClick: (e, els, ch) => onChartClick(e, els, ch, 'commodity') }
                });
                
                charts.chartEstado = new Chart(document.getElementById('chartEstado'), {
                    type: 'doughnut',
                    data: {
                        labels: ['Pendientes', 'Proceso', 'Listos'],
                        datasets: [{ data: [data.stats.pendientes, data.stats.proceso || 0, data.stats.listos], backgroundColor: ['#f39c12', '#3498db', '#27ae60'] }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: true, position: 'bottom', font: { size: 10 } } }, onClick: (e, els, ch) => onChartClick(e, els, ch, 'estado') }
                });
                
                charts.chartDias = new Chart(document.getElementById('chartDias'), {
                    type: 'line',
                    data: {
                        labels: data.charts.dias.map(x => x.dia),
                        datasets: [{ label: 'Solicitudes', data: data.charts.dias.map(x => x.cantidad), borderColor: '#C0392B', backgroundColor: 'rgba(192,57,43,0.1)', fill: true, tension: 0.4 }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, ticks: { font: { size: 10 } } }, x: { ticks: { font: { size: 10 } } } }, onClick: (e, els, ch) => onChartClick(e, els, ch, 'dias') }
                });
            }
        } catch(e) { console.log(e); }
    }
    
    async function cargarTabla() {
        try {
            const res = await fetch('/metrologia/api/todas.php');
            const data = await res.json();
            if (data.success) {
                document.getElementById('tablaSolicitudes').innerHTML = data.solicitudes.map(s => 
                    `<tr>
                        <td><strong>${s.folio}</strong></td>
                        <td>${s.numero_e}</td>
                        <td>${s.tipo_solicitud}</td>
                        <td>${s.commodity}</td>
                        <td>${s.cantidad}</td>
                        <td><small>${s.fecha}</small></td>
                        <td><span class="badge ${s.estatus==='listo'?'bg-success':'bg-warning'}">${s.estatus}</span></td>
                    </tr>`
                ).join('');
            }
        } catch(e) { console.log(e); }
    }
    
    cargarDatos();
    cargarTabla();
    cargarReportes();
    
    function cargarReportes() {
        fetch('/metrologia/api/exportar.php?accion=listar')
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                const tbody = document.getElementById('tablaReportes').querySelector('tbody');
                if (data.reportes.length > 0) {
                    tbody.innerHTML = data.reportes.map(r => 
                        `<tr>
                            <td>${r.nombre}</td>
                            <td><small>${r.fecha_creacion}</small></td>
                            <td><span class="badge bg-primary">${r.registros}</span></td>
                            <td>
                                <button class="btn btn-sm btn-success" onclick="descargarReporte(${r.id}, 'excel')" title="Excel"><i class="bi bi-file-earmark-excel"></i></button>
                                <button class="btn btn-sm btn-danger" onclick="descargarReporte(${r.id}, 'pdf')" title="PDF"><i class="bi bi-file-earmark-pdf"></i></button>
                                <button class="btn btn-sm btn-warning" onclick="eliminarReporte(${r.id})" title="Eliminar"><i class="bi bi-trash"></i></button>
                            </td>
                        </tr>`
                    ).join('');
                } else {
                    tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted">No hay reportes guardados</td></tr>';
                }
            }
        });
    }
    
    function guardarReporte() {
        const nombre = document.getElementById('nombreReporte').value || 'Reporte-' + new Date().toLocaleString();
        const filtros = {
            fecha_inicio: document.getElementById('repFechaInicio').value,
            fecha_fin: document.getElementById('repFechaFin').value,
            tipo: document.getElementById('repTipo').value,
            estatus: document.getElementById('repEstatus').value,
            commodity: document.getElementById('repCommodity').value,
            solicitante: document.getElementById('repSolicitante').value,
            area: document.getElementById('repArea').value,
            min_piezas: document.getElementById('repMinPiezas').value
        };
        const campos = getSelectedCampos().split(',');
        
        fetch('/metrologia/api/exportar.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({nombre, filtros, campos})
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('modalReporte')).hide();
                cargarReportes();
            }
        });
    }
    
    function descargarReporte(id, formato) {
        window.open('/metrologia/api/exportar.php?id=' + id + '&formato=' + formato, '_blank');
    }
    
    function eliminarReporte(id) {
        if (confirm('¿Eliminar este reporte?')) {
            fetch('/metrologia/api/exportar.php?accion=eliminar&id=' + id)
            .then(r => r.json())
            .then(data => {
                if (data.success) cargarReportes();
            });
        }
    }
    
    let datosFiltrados = [];
    
    function renderDetalle(solicitudes) {
        document.getElementById('tablaDetalleGrafica').innerHTML = solicitudes.map(s => 
            `<tr>
                <td><strong>${s.folio}</strong></td>
                <td>${s.numero_e || s.nombre_solicitante || s.solicitante || '-'}</td>
                <td>${s.tipo_solicitud || s.tipo || '-'}</td>
                <td>${s.commodity}</td>
                <td>${s.cantidad}</td>
                <td><small>${s.fecha || s.fecha_creacion || '-'}</small></td>
                <td><span class="badge ${s.estatus==='listo'?'bg-success':s.estatus==='proceso'?'bg-info':'bg-warning'}">${s.estatus}</span></td>
            </tr>`
        ).join('');
        new bootstrap.Modal(document.getElementById('modalDetalleGrafica')).show();
    }
    
    function onChartClick(event, elements, chart, tipoGrafica) {
        if (!elements.length) return;
        const index = elements[0].index;
        const label = chart.data.labels[index];
        document.getElementById('modalDetalleTitle').innerText = 'Detalle: ' + label;
        
        let filtrados = [];
        if (tipoGrafica === 'tipo') {
            filtrados = datosFiltrados.filter(s => s.tipo_solicitud === label || s.tipo === label);
        } else if (tipoGrafica === 'commodity') {
            filtrados = datosFiltrados.filter(s => s.commodity && s.commodity.substring(0,10) === label);
        } else if (tipoGrafica === 'estado') {
            const st = label.toLowerCase();
            filtrados = datosFiltrados.filter(s => s.estatus === st);
        } else if (tipoGrafica === 'dias') {
            filtrados = datosFiltrados.filter(s => {
                const fd = (s.fecha || s.fecha_creacion || '').substring(0,10);
                return fd === label;
            });
        }
        renderDetalle(filtrados);
    }
    
    async function cargarTabla() {
        try {
            const res = await fetch('/metrologia/api/todas.php');
            const data = await res.json();
            if (data.success) {
                datosFiltrados = data.solicitudes;
                document.getElementById('tablaSolicitudes').innerHTML = data.solicitudes.map(s => 
                    `<tr>
                        <td><strong>${s.folio}</strong></td>
                        <td>${s.numero_e || s.nombre_solicitante || '-'}</td>
                        <td>${s.tipo_solicitud || s.tipo || '-'}</td>
                        <td>${s.commodity}</td>
                        <td>${s.cantidad}</td>
                        <td><small>${s.fecha || s.fecha_creacion || '-'}</small></td>
                        <td><span class="badge ${s.estatus==='listo'?'bg-success':'bg-warning'}">${s.estatus}</span></td>
                    </tr>`
                ).join('');
            }
        } catch(e) { console.log(e); }
    }
    
    let filtrosActivos = {};
    
    function aplicarFiltro() {
        const fechaIni = document.getElementById('fechaInicio').value;
        const fechaFin = document.getElementById('fechaFin').value;
        const tipo = document.getElementById('tipoFiltro').value;
        const estatus = document.getElementById('estatusFiltro').value;
        
        filtrosActivos = {fecha_inicio: fechaIni, fecha_fin: fechaFin, tipo, estatus};
        
        const params = new URLSearchParams();
        if (fechaIni) params.append('fecha_inicio', fechaIni);
        if (fechaFin) params.append('fecha_fin', fechaFin);
        if (tipo) params.append('tipo', tipo);
        if (estatus) params.append('estatus', estatus);
        
        fetch('/metrologia/api/filtrar.php?' + params)
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                const solicitudes = data.solicitudes;
                datosFiltrados = solicitudes;
                
                // Actualizar stats
                document.getElementById('statTotal').innerText = solicitudes.length;
                document.getElementById('statPendientes').innerText = solicitudes.filter(s => s.estatus === 'pendiente').length;
                document.getElementById('statListos').innerText = solicitudes.filter(s => s.estatus === 'listo').length;
                document.getElementById('statPiezas').innerText = solicitudes.reduce((a, b) => a + (parseInt(b.cantidad) || 0), 0);
                
                // Actualizar tabla
                document.getElementById('tablaSolicitudes').innerHTML = solicitudes.map(s => 
                    `<tr>
                        <td><strong>${s.folio}</strong></td>
                        <td>${s.numero_e || s.nombre_solicitante || '-'}</td>
                        <td>${s.tipo_solicitud}</td>
                        <td>${s.commodity}</td>
                        <td>${s.cantidad}</td>
                        <td><small>${s.fecha}</small></td>
                        <td><span class="badge ${s.estatus==='listo'?'bg-success':s.estatus==='proceso'?'bg-info':'bg-warning'}">${s.estatus}</span></td>
                    </tr>`
                ).join('');
                
                // Actualizar gráficos
                actualizarGraficos(data);
            }
        });
    }
    
    function actualizarGraficos(data) {
        //Por Tipo
        const tipoCounts = {};
        data.solicitudes.forEach(s => {
            tipoCounts[s.tipo_solicitud] = (tipoCounts[s.tipo_solicitud] || 0) + 1;
        });
        
        if (charts.chartTipo) charts.chartTipo.destroy();
        charts.chartTipo = new Chart(document.getElementById('chartTipo'), {
            type: 'doughnut',
            data: {
                labels: Object.keys(tipoCounts),
                datasets: [{ data: Object.values(tipoCounts), backgroundColor: colors }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: true, position: 'bottom', font: { size: 10 } } }, onClick: (e, els, ch) => onChartClick(e, els, ch, 'tipo') }
        });
        
        //Por Commodity
        const commCounts = {};
        data.solicitudes.forEach(s => {
            commCounts[s.commodity] = (commCounts[s.commodity] || 0) + 1;
        });
        
        if (charts.chartCommodity) charts.chartCommodity.destroy();
        charts.chartCommodity = new Chart(document.getElementById('chartCommodity'), {
            type: 'bar',
            data: {
                labels: Object.keys(commCounts).slice(0,5),
                datasets: [{ data: Object.values(commCounts).slice(0,5), backgroundColor: '#C0392B' }]
            },
            options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true } }, onClick: (e, els, ch) => onChartClick(e, els, ch, 'commodity') }
        });
        
        //Estado
        const estCounts = {pendiente:0, proceso:0, listo:0};
        data.solicitudes.forEach(s => {
            if (estCounts[s.estatus] !== undefined) estCounts[s.estatus]++;
        });
        
        if (charts.chartEstado) charts.chartEstado.destroy();
        charts.chartEstado = new Chart(document.getElementById('chartEstado'), {
            type: 'doughnut',
            data: {
                labels: ['Pendiente', 'Proceso', 'Listo'],
                datasets: [{ data: [estCounts.pendiente, estCounts.proceso, estCounts.listo], backgroundColor: ['#f39c12', '#3498db', '#27ae60'] }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: true, position: 'bottom' } }, onClick: (e, els, ch) => onChartClick(e, els, ch, 'estado') }
        });
    }
    
    function exportarExcel() {
        const fechaIni = document.getElementById('fechaInicio').value;
        const fechaFin = document.getElementById('fechaFin').value;
        
        let url = '/metrologia/api/exportar.php?formato=excel';
        if (fechaIni) url += '&fecha_inicio=' + fechaIni;
        if (fechaFin) url += '&fecha_fin=' + fechaFin;
        
        window.open(url, '_blank');
    }
    
    function exportarPDF() {
        const fechaIni = document.getElementById('fechaInicio').value;
        const fechaFin = document.getElementById('fechaFin').value;
        
        let url = '/metrologia/api/exportar.php?formato=pdf';
        if (fechaIni) url += '&fecha_inicio=' + fechaIni;
        if (fechaFin) url += '&fecha_fin=' + fechaFin;
        
        window.open(url, '_blank');
    }
    
    function generarReporteExcel() {
        const params = new URLSearchParams({
            nombre: document.getElementById('nombreReporte').value || 'Reporte',
            fecha_inicio: document.getElementById('repFechaInicio').value,
            fecha_fin: document.getElementById('repFechaFin').value,
            tipo: document.getElementById('repTipo').value,
            estatus: document.getElementById('repEstatus').value,
            commodity: document.getElementById('repCommodity').value,
            solicitante: document.getElementById('repSolicitante').value,
            area: document.getElementById('repArea').value,
            min_piezas: document.getElementById('repMinPiezas').value,
            ordenar: document.getElementById('repOrdenar').value,
            campos: getSelectedCampos(),
            formato: 'excel'
        });
        
        window.open('/metrologia/api/exportar.php?' + params, '_blank');
    }
    
    function generarReportePDF() {
        const params = new URLSearchParams({
            nombre: document.getElementById('nombreReporte').value || 'Reporte',
            fecha_inicio: document.getElementById('repFechaInicio').value,
            fecha_fin: document.getElementById('repFechaFin').value,
            tipo: document.getElementById('repTipo').value,
            estatus: document.getElementById('repEstatus').value,
            commodity: document.getElementById('repCommodity').value,
            solicitante: document.getElementById('repSolicitante').value,
            area: document.getElementById('repArea').value,
            min_piezas: document.getElementById('repMinPiezas').value,
            ordenar: document.getElementById('repOrdenar').value,
            campos: getSelectedCampos(),
            formato: 'pdf'
        });
        
        window.open('/metrologia/api/exportar.php?' + params, '_blank');
    }
</script>

<!-- Modal Crear Reporte Personalizado -->
<div class="modal fade" id="modalReporte" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-plus-circle"></i> Nuevo Reporte Personalizado</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Nombre del Reporte</label>
                    <input type="text" id="nombreReporte" class="form-control" placeholder="Mi Reporte">
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label">Fecha Inicio</label>
                        <input type="date" id="repFechaInicio" class="form-control">
                    </div>
                    <div class="col-6">
                        <label class="form-label">Fecha Fin</label>
                        <input type="date" id="repFechaFin" class="form-control">
                    </div>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label">Tipo de Solicitud</label>
                        <select id="repTipo" class="form-select">
                            <option value="">Todos</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label">Estatus</label>
                        <select id="repEstatus" class="form-select">
                            <option value="">Todos</option>
                            <option value="pendiente">Pendiente</option>
                            <option value="proceso">En Proceso</option>
                            <option value="listo">Listo</option>
                        </select>
                    </div>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label">Commodity</label>
                        <select id="repCommodity" class="form-select">
                            <option value="">Todos</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label">Solicitante (Número E)</label>
                        <input type="text" id="repSolicitante" class="form-control" placeholder="E001">
                    </div>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label">Área</label>
                        <select id="repArea" class="form-select">
                            <option value="">Todas</option>
                            <option value="Producción">Producción</option>
                            <option value="Calidad">Calidad</option>
                            <option value="Incoming">Incoming</option>
                            <option value="Ingeniería">Ingeniería</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label">Mín. Piezas</label>
                        <input type="number" id="repMinPiezas" class="form-control" placeholder="0">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Campos a Mostrar</label>
                    <div class="row">
                        <div class="col-6">
                            <div class="form-check"><input class="form-check-input" type="checkbox" value="folio" id="chkFolio" checked><label class="form-check-label" for="chkFolio">Folio</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" value="fecha" id="chkFecha" checked><label class="form-check-label" for="chkFecha">Fecha</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" value="solicitante" id="chkSolicitante" checked><label class="form-check-label" for="chkSolicitante">Solicitante</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" value="tipo" id="chkTipo" checked><label class="form-check-label" for="chkTipo">Tipo</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" value="numero_parte" id="chkNumeroParte" checked><label class="form-check-label" for="chkNumeroParte">Número de Parte</label></div>
                        </div>
                        <div class="col-6">
                            <div class="form-check"><input class="form-check-input" type="checkbox" value="commodity" id="chkCommodity" checked><label class="form-check-label" for="chkCommodity">Commodity</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" value="cantidad" id="chkCantidad" checked><label class="form-check-label" for="chkCantidad">Cantidad</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" value="estatus" id="chkEstatus" checked><label class="form-check-label" for="chkEstatus">Estatus</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" value="descripcion" id="chkDescripcion"><label class="form-check-label" for="chkDescripcion">Descripción</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" value="maquina" id="chkMaquina" checked><label class="form-check-label" for="chkMaquina">Máquina</label></div>
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Ordenar por</label>
                    <select id="repOrdenar" class="form-select">
                        <option value="fecha_creacion DESC">Fecha (Recientes primero)</option>
                        <option value="fecha_creacion ASC">Fecha (Antiguos primero)</option>
                        <option value="cantidad DESC">Cantidad (Mayor primero)</option>
                        <option value="folio ASC">Folio (A-Z)</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="guardarReporte()">
                    <i class="bi bi-save"></i> Guardar
                </button>
                <button type="button" class="btn btn-success" onclick="generarReporteExcel()">
                    <i class="bi bi-file-earmark-excel"></i> Excel
                </button>
                <button type="button" class="btn btn-danger" onclick="generarReportePDF()">
                    <i class="bi bi-file-earmark-pdf"></i> PDF
                </button>
            </div>
        </div>
    </div>
</div>
<script>
    // Cargar catálogos dinámicamente
    fetch('/metrologia/api/catalogos.php')
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            const selTipo = document.getElementById('repTipo');
            data.tipo_solicitud.forEach(t => selTipo.add(new Option(t.nombre, t.nombre)));
            const selCommodity = document.getElementById('repCommodity');
            data.commodity.forEach(c => selCommodity.add(new Option(c.nombre, c.nombre)));
        }
    });

    function getSelectedCampos() {
        const campos = [];
        if (document.getElementById('chkFolio').checked) campos.push('folio');
        if (document.getElementById('chkFecha').checked) campos.push('fecha');
        if (document.getElementById('chkSolicitante').checked) campos.push('solicitante');
        if (document.getElementById('chkTipo').checked) campos.push('tipo');
        if (document.getElementById('chkCommodity').checked) campos.push('commodity');
        if (document.getElementById('chkCantidad').checked) campos.push('cantidad');
        if (document.getElementById('chkEstatus').checked) campos.push('estatus');
        if (document.getElementById('chkDescripcion').checked) campos.push('descripcion');
        if (document.getElementById('chkNumeroParte').checked) campos.push('numero_parte');
        if (document.getElementById('chkMaquina').checked) campos.push('maquina');
        return campos.join(',');
    }
</script>

</body></html>
