/**
 * JavaScript principal
 * MMQRO - Sistema de Gestión de Solicitudes de Metrología
 */

document.addEventListener('DOMContentLoaded', function() {
    // Inicializar componentes
    initReloj();
    initValidacionFormularios();
    initAutoRefresh();
});

/**
 * Reloj en tiempo real
 */
function initReloj() {
    const relojElement = document.getElementById('reloj');
    
    if (!relojElement) return;
    
    function actualizarReloj() {
        const ahora = new Date();
        const opciones = {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        };
        relojElement.textContent = ahora.toLocaleDateString('es-MX', opciones);
    }
    
    actualizarReloj();
    setInterval(actualizarReloj, 1000);
}

/**
 * Validación de formularios
 */
function initValidacionFormularios() {
    const forms = document.querySelectorAll('.needs-validation');
    
    forms.forEach(function(form) {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
}

/**
 * Auto refresh para pantallas públicas
 */
function initAutoRefresh() {
    const refreshElement = document.getElementById('auto-refresh');
    
    if (!refreshElement) return;
    
    const interval = parseInt(refreshElement.dataset.refresh || '30000');
    
    setInterval(function() {
        location.reload();
    }, interval);
}

/**
 * Calcular tiempo transcurrido
 * @param {string} fecha - Fecha en formato SQL
 * @returns {string}
 */
function TiempoTranscurrido(fecha) {
    const inicio = new Date(fecha);
    const ahora = new Date();
    const diffMs = ahora - inicio;
    
    const minutos = Math.floor(diffMs / 60000);
    const horas = Math.floor(minutos / 60);
    const dias = Math.floor(horas / 24);
    
    if (minutos < 1) return 'Ahora mismo';
    if (minutos < 60) return minutos + ' min';
    if (horas < 24) return horas + ' hr';
    return dias + ' día' + (dias > 1 ? 's' : '');
}

/**
 * Completar orden (laboratorio)
 * @param {number} id - ID de la solicitud
 */
function completarOrden(id) {
    mostrarModalConfirmacion(
        'Confirmar Completado',
        '¿Marcar esta orden como completada?',
        'success',
        function() {
            fetch('acciones.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=completar&id=' + id
            })
            .then(function(response) {
                return response.json();
            })
            .then(function(data) {
                if (data.success) {
                    mostrarModalAlerta('Éxito', 'Orden completada correctamente', 'success');
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    mostrarModalAlerta('Error', data.message || 'Error al completar orden', 'danger');
                }
            })
            .catch(function(error) {
                mostrarModalAlerta('Error', 'Error de conexión', 'danger');
            });
        }
    );
}

/**
 * Mostrar alerta temporal
 * @param {string} mensaje
 * @param {string} tipo
 */
function mostrarAlerta(mensaje, tipo) {
    const alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-' + tipo + ' alert-dismissible fade show';
    alertDiv.setAttribute('role', 'alert');
    alertDiv.innerHTML = mensaje + '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
    
    const container = document.querySelector('.container');
    if (container) {
        container.insertBefore(alertDiv, container.firstChild);
        
        setTimeout(function() {
            alertDiv.remove();
        }, 5000);
    }
}

/**
 * Confirmar eliminación con modal Bootstrap
 * @returns {boolean}
 */
function confirmarEliminar(callback) {
    mostrarModalConfirmacion(
        'Confirmar Eliminación',
        '¿Está seguro de que desea eliminar este registro? Esta acción no se puede deshacer.',
        'danger',
        callback
    );
}

/**
 * Mostrar modal de confirmación
 * @param {string} titulo
 * @param {string} mensaje
 * @param {string} tipo - 'primary', 'danger', 'warning', etc.
 * @param {function} callback - función a ejecutar si confirma
 */
function mostrarModalConfirmacion(titulo, mensaje, tipo = 'primary', callback = null) {
    // Crear modal si no existe
    let modal = document.getElementById('modalConfirmacion');
    if (!modal) {
        const modalHtml = `
        <div class="modal fade" id="modalConfirmacion" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-${tipo} text-white">
                        <h5 class="modal-title" id="modalConfirmTitulo"></h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p id="modalConfirmMensaje"></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-${tipo}" id="btnConfirmarModal">Confirmar</button>
                    </div>
                </div>
            </div>
        </div>
        `;
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        modal = document.getElementById('modalConfirmacion');
    }
    
    // Configurar contenido
    document.getElementById('modalConfirmTitulo').textContent = titulo;
    document.getElementById('modalConfirmMensaje').textContent = mensaje;
    
    const btnConfirmar = document.getElementById('btnConfirmarModal');
    btnConfirmar.className = `btn btn-${tipo}`;
    
    // Manejar confirmación
    const newModal = new bootstrap.Modal(modal);
    btnConfirmar.onclick = function() {
        newModal.hide();
        if (callback) callback();
    };
    
    newModal.show();
}

/**
 * Mostrar modal de alerta simple
 * @param {string} titulo
 * @param {string} mensaje
 * @param {string} tipo - 'success', 'danger', 'warning', 'info', 'primary'
 */
function mostrarModalAlerta(titulo, mensaje, tipo = 'info') {
    let modal = document.getElementById('modalAlerta');
    if (!modal) {
        const modalHtml = `
        <div class="modal fade" id="modalAlerta" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-${tipo} text-white">
                        <h5 class="modal-title" id="modalAlertaTitulo"></h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p id="modalAlertaMensaje" class="mb-0"></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-${tipo}" data-bs-dismiss="modal">Aceptar</button>
                    </div>
                </div>
            </div>
        </div>
        `;
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        modal = document.getElementById('modalAlerta');
    }
    
    document.getElementById('modalAlertaTitulo').textContent = titulo;
    document.getElementById('modalAlertaMensaje').textContent = mensaje;
    
    const modalHeader = modal.querySelector('.modal-header');
    modalHeader.className = `modal-header bg-${tipo} text-white`;
    
    new bootstrap.Modal(modal).show();
}

/**
 * Exportar a PDF
 */
function exportarPDF() {
   window.print();
}

/**
 * Validar número E
 * @param {string} value
 * @returns {boolean}
 */
function validarNumeroE(value) {
    return /^[A-Za-z0-9\-]+$/.test(value) && value.length <= 20;
}

/**
 * Validar código de tarjeta
 * @param {string} value
 * @returns {boolean}
 */
function validarCodigoTarjeta(value) {
    return value.length > 0 && value.length <= 50;
}

/**
 * Validar cantidad
 * @param {string} value
 * @returns {boolean}
 */
function validarCantidad(value) {
    const num = parseInt(value);
    return !isNaN(num) && num > 0 && num <= 10000;
}

/**
 * Inicializar gráfico
 * @param {string} canvasId
 * @param {object} config
 */
function initChart(canvasId, config) {
    const canvas = document.getElementById(canvasId);
    if (!canvas) return null;
    
    return new Chart(canvas, config);
}

/**
 * Formatear fecha
 * @param {string|Date} fecha
 * @returns {string}
 */
function formatearFecha(fecha) {
    const date = new Date(fecha);
    return date.toLocaleDateString('es-MX', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

/**
 * Formatear hora
 * @param {string|Date} fecha
 * @returns {string}
 */
function formatearHora(fecha) {
    const date = new Date(fecha);
    return date.toLocaleTimeString('es-MX', {
        hour: '2-digit',
        minute: '2-digit'
    });
}

/**
 * Detectar código QR/Barcode desde teclado
 */
function initQRScanner() {
    let inputBuffer = '';
    let lastKeyTime = 0;
    const inputField = document.getElementById('codigo_tarjeta');
    
    if (!inputField) return;
    
    inputField.addEventListener('keypress', function(e) {
        const currentTime = new Date().getTime();
        
        if (currentTime - lastKeyTime > 100) {
            inputBuffer = '';
        }
        
        inputBuffer += String.fromCharCode(e.which);
        lastKeyTime = currentTime;
        
        if (e.key === 'Enter') {
            if (inputBuffer.length > 5) {
                inputField.value = inputBuffer.trim();
                e.preventDefault();
            }
            inputBuffer = '';
        }
    });
}