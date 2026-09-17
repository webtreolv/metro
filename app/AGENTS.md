# metrologia (MMQRO) - Contexto y Guía Detallada para Agentes AI

Este archivo es la guía de referencia absoluta para cualquier agente AI o desarrollador que trabaje en el proyecto **MMQRO (Sistema de Gestión de Solicitudes de Metrología)**. Define cómo funciona el sistema, la lógica de negocio subyacente, y la arquitectura técnica.

*(Nota: Para reglas globales de entorno Docker y directrices generales, consulta siempre el archivo maestro `C:\Proyectos\AGENTS.md`).*

---

## ⚙️ 1. Objetivo y Funcionalidad del Proyecto
**MMQRO** es un sistema interno de Metrología cuyo objetivo principal es gestionar, rastrear y organizar el ciclo de vida de las solicitudes de medición, calibración y revisión de piezas o equipos. El sistema conecta a los usuarios solicitantes (operadores/ingenieros) con el personal del laboratorio de metrología.

---

## 🏗️ 2. Arquitectura y Stack Tecnológico
* **Lenguaje:** PHP 8.2 (Patrón tradicional / estructural, con scripts separados para la API).
* **Base de Datos:** MariaDB 10.4.32 (Conexión vía host `db` y usuario `root` usando `mysqli`).
* **Frontend:** HTML5, Bootstrap 5, Bootstrap Icons, y JavaScript asíncrono (Fetch API) para interactuar con el backend.
* **Entorno de Ejecución:** Contenedor Docker compartido global (`C:\Docker\php82-mariadb104`). **NUNCA** modificar el entorno base sin autorización.

### Estructura de Directorios Principal
* `/api/`: Contiene los endpoints PHP que procesan las peticiones AJAX (ej. `solicitudes.php`, `proceso.php`, `completar.php`, `entregar.php`, etc.).
* `/config/`: Archivos críticos de configuración. Destaca `database.php` que maneja la conexión, la seguridad de la sesión (`iniciarSesionSegura()`), y el control de roles.
* `/sql/`: Contiene el esquema completo de la base de datos (`mmqro.sql`).
* `/admin/` y `/laboratorio/`: Módulos de la interfaz de usuario dependiendo del rol (Dashboard de administración y Tablero de trabajo del laboratorio).
* `index.html`: Punto de entrada principal para la creación de solicitudes (Dashboard del usuario general).

---

## 🔄 3. Lógica de Negocio y Ciclo de Vida de una Solicitud
El núcleo del sistema es la gestión de "Solicitudes". Cada solicitud recibe un **Folio** automático (formato `Mym-001`, ej. `M2608-001`).

### Flujo de Estados (Estatus):
1. **Pendiente (`pendiente`)**: El usuario crea la solicitud a través del portal inicial (`index.html`). Selecciona su Número E (empleado), tipo de solicitud, commodity, y máquina.
2. **En Proceso (`proceso`)**: El laboratorio recibe la pieza y un metrólogo inicia el trabajo de medición. (`api/proceso.php`).
3. **Listo (`listo`)**: El trabajo ha finalizado. La pieza está lista para ser recogida, pero físicamente sigue en el laboratorio. Se registra fecha y hora (`api/completar.php`).
4. **Entregado (`entregado`)**: El solicitante (o el responsable) recoge la pieza. El ciclo se cierra oficialmente (`api/entregar.php`).

### Roles y Autorización (`config/database.php`):
* **`usuario` (default):** Solo puede crear solicitudes y ver su estatus.
* **`laboratorio`:** Personal técnico que opera el tablero (`/laboratorio/tablero.php`), cambia los estados (Proceso, Listo, Entregado) y gestiona el flujo de trabajo.
* **`admin`:** Administradores globales, acceden a reportes, catálogos e importaciones de personal.

---

## 🗄️ 4. Esquema de Base de Datos (`mmqro`)
La base de datos relacional se estructura en las siguientes tablas principales:

* **`solicitudes`**: Tabla central del sistema. Registra `folio`, `numero_e` (solicitante), detalles de la pieza (`numero_parte`, `commodity`, `maquina`), y rastrea tiempos (`fecha_creacion`, `fecha_completado`, `fecha_entrega`) y el `estatus`.
* **`usuarios` / `personal`**: Almacena a los empleados y usuarios del sistema, identificados por su **Número E**. Incluye control de accesos y roles.
* **Catálogos Desplegables**:
  * `commodity`: Familias o categorías de productos.
  * `maquinas`: Equipos o líneas asociadas a las solicitudes.
  * `tipo_solicitud`: Clasificación del servicio (ej. Calibración, Medición 3D, etc.).
* **`motivos_cambio`**: Tabla de auditoría para rastrear justificaciones cuando se modifica el flujo o se cancela una solicitud.
* **`reportes`**: Tabla de agregación y guardado de resultados o métricas generadas por el sistema.

---

## 🚀 5. Roadmap e Ideas Pendientes (Mejoras Futuras)
*(Registro de funcionalidades a implementar según autorizaciones futuras)*
* Implementar generación y envío de certificados de medición/calibración en formato PDF.
* Crear un sistema de alertas automatizadas (cronjobs/emails) para notificar a los responsables sobre equipos próximos a vencer en su vigencia de calibración.
* Refinamiento en la seguridad de subida de archivos (si se anexan planos o documentos a las solicitudes).
