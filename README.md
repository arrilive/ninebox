# NineBox – Sistema de Evaluación de Desempeño 9-Box

Aplicación web en Laravel para evaluar colaboradores usando la matriz 9-Box (desempeño y potencial).  
Permite que distintos roles (admin, dueños, jefes, empleados) capturen evaluaciones y/o visualicen los resultados en un dashboard tipo matriz 3×3.

---

# Objetivo del sistema

Centralizar el proceso de evaluación de talento de una empresa, permitiendo:

- Registrar colaboradores por departamento / sucursal.
- Evaluar desempeño y potencial mediante encuestas.
- Ubicar a cada colaborador en la matriz 9-Box.
- Apoyar la toma de decisiones (promociones, planes de desarrollo, etc.) con un dashboard visual.

---

# Funcionalidades principales

- **Gestión de usuarios y roles**
  - Superusuario
  - Dueños
  - Jefes
  - Empleados

- **Matriz 9-Box**
  - Cálculo de desempeño y potencial a partir de evaluaciones.
  - Asignación automática de cuadrante (1–9) según los resultados.
  - Visualización en un dashboard.

- **Encuestas y evaluaciones**
  - Preguntas configuradas mediante seeders.
  - Evaluaciones por jefe / dueño sobre colaboradores.
  - Registro de resultados en base de datos para análisis posterior.

- **Catálogos básicos**
  - Departamentos / sucursales.
  - Tipos de usuario / roles.
  - Reglas de mapeo para la matriz 9-Box.

> Proyecto orientado a práctica académica y portafolio de desarrollo, no listo para producción sin ajustes extra de seguridad y validación.

# Tecnologías

- **Backend:** Laravel (PHP)
- **Frontend:** Blade + Tailwind (compilado con Vite)
- **Base de datos:**  MariaDB / MySQL
- **Herramientas:** Composer, Node.js, npm

# Requisitos previos

Antes de correr el proyecto, asegúrate de tener instalado:

- PHP 8 en adelante
- Composer
- MySQL o MariaDB
- Node.js y npm
- Extensiones típicas de Laravel (mbstring, openssl, pdo, etc.)

---

# Instalación y ejecución en local

# 1. Clonar el repositorio:
git clone https://github.com/arrilive/ninebox.git
cd ninebox

# 2. Instalar dependencias de PHP
composer install

# 3. Configurar el archivo .env
APP_NAME="NineBox"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tu_database
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_password

Genera la clave de aplicación:
php artisan key:generate

# 4. Migraciones y seeders
php artisan migrate --seed

# 5. Instalar y compilar assets
npm install
npm run dev   # Para desarrollo
npm run build  # Para build de producción

# 6. Levantar el servidor de desarrollo
php artisan serve

---

# Usuario administrador de prueba

- Los seeders crean usuarios por defecto (superusuario / dueño / jefe / empleado).

- Ejemplo (ajusta a lo que realmente tengas en database/seeders):
  Correo: admin@example.com
  Contraseña: password
