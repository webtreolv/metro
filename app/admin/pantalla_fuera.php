<?php
$pagina = 'pantalla_fuera';
include '../includes/navbar.php';
?>

<script src="../vendor/js/chart.umd.min.js"></script>

<div class="container-fluid py-3">
    <h4><i class="bi bi-display"></i> Pantalla Fuera</h4>
    
    <!-- Auto-refresh -->
    <meta http-equiv="refresh" content="30">
    
    <div class="row">
    <!-- Ordenes Pendientes de Producción -->
    <div class="col-md-6">
        <div class="card mb-3">
            <div class="card-header bg-warning text-dark">
                <i class="bi bi-clock"></i> Órdenes Pendientes de Producción
            </div>
            <div class="card-body" style="max-height: 250px; overflow-y: auto;">
                <table class="table table-sm table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Folio</th>
                            <th>Solicitante</th>
                            <th>Commodity</th>
                            <th>Hora</th>
                            <th>Tiempo</th>
                        </tr>
                    </thead>
                    <tbody id="tablaPendientes">
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Ordenes Listas para Recoger -->
    <div class="col-md-6">
        <div class="card mb-3">
            <div class="card-header bg-success text-white">
                <i class="bi bi-check-circle"></i> Órdenes Listas para Recoger
            </div>
            <div class="card-body" style="max-height: 250px; overflow-y: auto;">
                <table class="table table-sm table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Folio</th>
                            <th>Commodity</th>
                            <th>Solicitante</th>
                            <th>Lista Desde</th>
                        </tr>
                    </thead>
                    <tbody id="tablaListas">
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    </div>
    
    <!-- Gráfica de Concurrencia por Área -->
    <div class="card mb-3">
        <div class="card-header bg-primary text-white">
            <i class="bi bi-bar-chart"></i> Concurrencia por Área - Producción del Día
        </div>
        <div class="card-body">
            <div style="height: 250px;">
                <canvas id="chartConcurrencia"></canvas>
            </div>
        </div>
    </div>
</div>

<script src="../vendor/js/bootstrap.bundle.min.js"></script>
<script>
    function formatoTiempo(datetime) {
        if (!datetime) return '-';
        const fecha = new Date(datetime);
        const ahora = new Date();
        const diff = Math.floor((ahora - fecha) / 1000);
        
        if (diff < 60) return diff + ' seg';
        if (diff < 3600) return Math.floor(diff / 60) + ' min';
        if (diff < 86400) return Math.floor(diff / 3600) + ' hr';
        return Math.floor(diff / 86400) + ' días';
    }
    
    function cargarDatos() {
        fetch('../api/pantalla_fuera.php')
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                // Pendientes
                document.getElementById('tablaPendientes').innerHTML = data.pendientes.map(s => 
                    `<tr>
                        <td><strong>${s.folio}</strong></td>
                        <td>${s.solicitante}</td>
                        <td>${s.commodity}</td>
                        <td>${s.hora_pedido}</td>
                        <td><span class="badge bg-warning text-dark">${s.tiempo}</span></td>
                    </tr>`
                ).join('') || '<tr><td colspan="5" class="text-center">No hay órdenes pendientes</td></tr>';
                
                // Listas
                document.getElementById('tablaListas').innerHTML = data.listas.map(s => 
                    `<tr>
                        <td><strong>${s.folio}</strong></td>
                        <td>${s.commodity}</td>
                        <td>${s.solicitante}</td>
                        <td><span class="badge bg-success">${s.tiempo_lista}</span></td>
                    </tr>`
                ).join('') || '<tr><td colspan="4" class="text-center">No hay órdenes listas</td></tr>';
                
                // Gráfica
                if (data.concurrencia.labels.length > 0) {
                    new Chart(document.getElementById('chartConcurrencia'), {
                        type: 'bar',
                        data: {
                            labels: data.concurrencia.labels,
                            datasets: [{
                                label: 'Piezas',
                                data: data.concurrencia.data,
                                backgroundColor: data.concurrencia.colors
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: { y: { beginAtZero: true } },
                            plugins: { legend: { display: false } }
                        }
                    });
                }
            }
        });
    }
    
    cargarDatos();
    setInterval(cargarDatos, 30000);
</script>

</body></html>


