# Changelog

All notable changes to this project will be documented in this file.

## [Unreleased]
- **Diseño**: Se unificó el encabezado principal de todo el sistema (pantalla de inicio, admin, reportes y dashboard) para usar un rojo vivo (`#ff0000`) y texto en contraste blanco. Se implementó rompedor de caché CSS.
- **Gráficos**: Se corrigió un error visual que ocultaba las gráficas (falta de `position: relative`) en `reportes.php` y `pantalla_fuera_index.html`.
- **Interacción**: Se agregó la funcionalidad para que al hacer clic en cualquier gráfica interactiva, se despliegue un modal mostrando el detalle de esos datos en formato de tabla.
- **Corrección (Solicitudes)**: En `admin/solicitudes.php`, los selectores de "Commodity" y "Tipo de Solicitud" ahora se cargan dinámicamente desde la BD para evitar pérdida de datos al editar solicitudes.
- **Corrección (Reportes)**: La exportación a PDF/Excel de los "Reportes Guardados" ahora respeta correctamente los filtros almacenados en lugar de exportar toda la base de datos.
- **Mejora (Reportes)**: La eliminación de reportes guardados actualiza la tabla correctamente por AJAX incluso cuando se elimina el último registro existente.
