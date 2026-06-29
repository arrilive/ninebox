# ARCHITECTURE.md — NineBox Performance Platform

> **Este archivo es el contexto maestro del proyecto.**
> Léelo completo antes de tocar cualquier archivo. Todos los sprints de implementación parten de aquí.
> Si algo en el código contradice este documento, el documento manda.

---

## 1. Qué es este sistema

Plataforma web de evaluación de talento basada en la **Matriz Nine-Box** (McKinsey, 1970s). Un evaluador responde 10 preguntas sobre un colaborador — 5 miden desempeño actual, 5 miden potencial futuro — y el sistema calcula automáticamente en qué cuadrante del grid 3×3 cae esa persona.

**Propósito de negocio:** Dar a RH y líderes un lenguaje común y visual para decisiones de sucesión, desarrollo y promoción.

**Stack:** Laravel 12 · MySQL · Tailwind CSS · Alpine.js · Vite · Axios

---

## 2. Roles y scopes de acceso

| Rol | Quién es | Qué puede hacer |
|-----|----------|-----------------|
| `Superadmin` | Administrador del sistema | Crear/editar empresas, departamentos, usuarios. Ver todo. |
| `Dueño` | Propietario de una empresa | Ver el grid Nine-Box de **sus jefes**. Evaluar a sus jefes. |
| `Jefe` | Líder de un departamento | Ver el grid Nine-Box de **sus empleados**. Evaluar a sus empleados. |
| `Empleado` | Colaborador evaluado | **Sin acceso al sistema.** Solo es sujeto de evaluación. |

**Regla de oro:** Un jefe solo puede evaluar a los empleados de **su propio departamento**. Un dueño solo puede evaluar a los jefes de **su propia empresa**. Un empleado no tiene credenciales.

---

## 3. Flujo de negocio completo

```
[Superadmin crea empresa]
    → define nombre, logo
    → crea departamentos (N por empresa)
    → asigna Dueño a la empresa
    → asigna un Jefe a cada departamento
    → asigna Empleados a cada departamento

[Jefe evalúa a un Empleado]
    → abre lista de sus empleados filtrada por periodo (mes/año)
    → selecciona un empleado
    → responde 10 preguntas (5 desempeño + 5 potencial, escala 1–5)
    → puede guardar borrador y continuar después
    → al completar las 10 preguntas: envía
    → sistema calcula totales, resuelve cuadrante, registra resultado

[Dueño evalúa a un Jefe]
    → mismo flujo, pero sobre los jefes de su empresa

[Dashboard]
    → Jefe ve su grid Nine-Box con sus empleados posicionados
    → Dueño ve su grid Nine-Box con sus jefes posicionados
    → Superadmin puede filtrar por empresa, departamento, periodo
```

---

## 4. Esquema de base de datos — estado objetivo

> Las migraciones deben llegar a este estado. Si existe deuda migratoria previa, se resuelve con migraciones nuevas, no editando las existentes.

```sql
-- Empresas (creadas por Superadmin desde UI, NO hardcodeadas en migraciones)
empresas
  id              PK
  nombre          varchar(150)
  slug            varchar(100) UNIQUE
  logo_path       varchar(255) NULL
  activa          boolean DEFAULT true
  created_at, updated_at

-- Tipos de usuario (seed fijo, no editable desde UI)
tipos_usuarios
  id              PK
  nombre          varchar(50)  -- 'Superadmin' | 'Dueño' | 'Jefe' | 'Empleado'
  created_at, updated_at

-- Departamentos (pertenecen a una empresa)
departamentos
  id              PK
  empresa_id      FK → empresas
  nombre          varchar(120)
  created_at, updated_at

-- Usuarios
usuarios
  id              PK
  empresa_id      FK → empresas NULL   -- NULL solo para Superadmin
  departamento_id FK → departamentos NULL
  tipo_usuario_id FK → tipos_usuarios
  nombre          varchar(100)
  apellido_paterno varchar(100) NULL
  apellido_materno varchar(100) NULL
  correo          varchar(150) NULL UNIQUE
  user_name       varchar(60) NULL UNIQUE
  password        varchar(255) NULL    -- NULL = sin acceso (Empleados)
  telefono        varchar(20) NULL
  created_at, updated_at

-- Cuadrantes Nine-Box (seed fijo, 9 registros)
nine_box
  id              PK
  nombre          varchar(80)   -- ej: 'Estrella', 'Diamante en bruto'
  posicion        tinyint UNIQUE -- 1-9, define posición en el grid
  descripcion     text NULL
  color_hex       varchar(7) NULL  -- color del cuadrante en el grid
  created_at, updated_at

-- Preguntas de la encuesta (seed fijo, 10 registros)
preguntas
  id              PK
  texto           text
  categoria       ENUM('desempeno', 'potencial')
  orden           tinyint          -- controla orden de display
  created_at, updated_at

-- Reglas de clasificación Nine-Box (seed fijo, 9 registros)
reglas_ninebox
  id              PK
  min_desempeno   tinyint
  max_desempeno   tinyint
  min_potencial   tinyint
  max_potencial   tinyint
  ninebox_id      FK → nine_box
  etiqueta        varchar(120) NULL
  activo          boolean DEFAULT true
  created_at, updated_at

-- Encuestas (una por evaluado por periodo; borrador hasta que se cierra)
encuestas
  id              PK
  evaluador_id    FK → usuarios    -- quien evalúa
  evaluado_id     FK → usuarios    -- quien es evaluado
  empresa_id      FK → empresas
  anio            smallint
  mes             tinyint
  total_desempeno tinyint NULL
  total_potencial tinyint NULL
  ninebox_id      FK → nine_box NULL
  notas_privadas  text NULL
  cerrada_en      timestamp NULL   -- NULL = borrador; NOT NULL = cerrada
  created_at, updated_at
  UNIQUE (evaluado_id, anio, mes)  -- un resultado por colaborador por periodo

-- Respuestas individuales de cada encuesta
evaluaciones
  id              PK AUTOINCREMENT
  encuesta_id     FK → encuestas
  pregunta_id     FK → preguntas
  puntaje         tinyint          -- 1-5
  comentario      text NULL
  created_at, updated_at
  UNIQUE (encuesta_id, pregunta_id)

-- Historial de resultados (se crea al cerrar una encuesta)
rendimientos
  id              PK AUTOINCREMENT  -- NO más PK compuesta frágil
  usuario_id      FK → usuarios
  ninebox_id      FK → nine_box
  encuesta_id     FK → encuestas    -- trazabilidad completa
  anio            smallint
  mes             tinyint
  created_at, updated_at
  UNIQUE (usuario_id, anio, mes)
```

**Tablas eliminadas:** `sucursales` — no está en el flujo de negocio definido. Si se necesita en el futuro, se agrega como feature, no como deuda prematura.

---

## 5. Lógica de clasificación Nine-Box — cómo funciona

### Escala de la encuesta
- 10 preguntas totales: 5 de desempeño + 5 de potencial
- Escala por pregunta: **1** (nunca / muy por debajo) → **5** (siempre / excelente)
- Rango por eje: mínimo **5** (todas en 1), máximo **25** (todas en 5)

### Semántica de los puntajes
| Puntaje por pregunta | Significado |
|---|---|
| 1 | Nunca / muy por debajo de lo esperado |
| 2 | Raramente / por debajo de lo esperado |
| 3 | A veces / cumple parcialmente |
| 4 | Frecuentemente / cumple expectativas |
| 5 | Siempre / supera consistentemente |

### Umbrales de clasificación por eje (cortes semánticos, no divisiones iguales)

Los cortes están diseñados para que reflejen la realidad de cómo los evaluadores usan escalas Likert. La mayoría de evaluaciones caen entre 3 y 4 por pregunta (15–20 puntos por eje), por lo que los umbrales priorizan discriminar bien el rango central.

| Nivel | Rango (suma de 5 preguntas) | Promedio por pregunta | Interpretación |
|---|---|---|---|
| **Bajo** | 5 – 12 | < 2.5 | Por debajo de lo aceptable. Requiere intervención. |
| **Medio** | 13 – 19 | 2.6 – 3.8 | Cumple expectativas con variación. Personal sólido. |
| **Alto** | 20 – 25 | ≥ 4.0 | Supera expectativas consistentemente. |

### Grid Nine-Box y nombres de cuadrantes

El eje X es **Desempeño** (horizontal), el eje Y es **Potencial** (vertical). La posición en el grid se define así:

```
             BAJO           MEDIO           ALTO
           desempeño      desempeño      desempeño
            (5-12)         (13-19)        (20-25)

ALTO     [pos 6]         [pos 8]         [pos 9]
potencial Diamante        Estrella en     Estrella
(20-25)   en bruto        desarrollo

MEDIO    [pos 2]         [pos 5]         [pos 7]
potencial Mal empleado    Personal        Elemento
(13-19)                   sólido          importante

BAJO     [pos 1]         [pos 3]         [pos 4]
potencial Inaceptable     Aceptable       Personal
(5-12)                                    clave
```

### Colores de cuadrantes (colorimetría estándar HR)
Los colores siguen el estándar de la industria para Nine-Box: verde para alto rendimiento/potencial, amarillo para medio, rojo/naranja para bajo.

| Cuadrante | Nombre | Color |
|---|---|---|
| 9 — Alto/Alto | Estrella | `#16a34a` (verde) |
| 8 — Medio/Alto | Estrella en desarrollo | `#22c55e` (verde claro) |
| 7 — Alto/Medio | Elemento importante | `#65a30d` (lima) |
| 6 — Bajo/Alto | Diamante en bruto | `#eab308` (amarillo) |
| 5 — Medio/Medio | Personal sólido | `#ca8a04` (ámbar) |
| 4 — Alto/Bajo | Personal clave | `#d97706` (naranja) |
| 3 — Medio/Bajo | Aceptable | `#f97316` (naranja rojizo) |
| 2 — Bajo/Medio | Mal empleado | `#dc2626` (rojo) |
| 1 — Bajo/Bajo | Inaceptable | `#991b1b` (rojo oscuro) |

---

## 6. Arquitectura de código

```
app/
├── Enums/
│   ├── RolUsuario.php          -- 'Superadmin' | 'Dueño' | 'Jefe' | 'Empleado'
│   └── CategoriaPregunta.php   -- 'desempeno' | 'potencial'
│
├── Models/
│   ├── User.php                -- helpers: esSuperadmin(), esDueno(), esJefe(), esEmpleado()
│   ├── Empresa.php
│   ├── Departamento.php
│   ├── Encuesta.php            -- scope: borrador(), cerrada()
│   ├── Evaluacion.php
│   ├── NineBox.php
│   ├── Pregunta.php
│   ├── ReglaNinebox.php        -- método estático: resolver(int $d, int $p): self
│   ├── Rendimiento.php
│   └── TipoUsuario.php
│
├── Services/
│   └── EvaluacionService.php
│       ├── guardarRespuestas(Encuesta, array): void
│       ├── calcularTotales(Encuesta): array ['desempeno' => int, 'potencial' => int]
│       ├── resolverCuadrante(int, int): ReglaNinebox
│       ├── cerrarEncuesta(Encuesta, int $anio, int $mes, User $evaluador): Encuesta
│       └── registrarRendimiento(Encuesta): Rendimiento
│
├── Policies/
│   ├── EncuestaPolicy.php      -- view, create, update, close
│   └── EmpresaPolicy.php       -- solo Superadmin
│
├── Http/
│   ├── Controllers/
│   │   ├── DashboardController.php         -- delgado: solo coordina y pasa datos a vista
│   │   ├── EncuestaController.php          -- delgado: valida → service → redirect
│   │   └── Admin/
│   │       ├── EmpresaController.php
│   │       ├── DepartamentoController.php
│   │       └── UsuarioController.php
│   │
│   ├── Middleware/
│   │   └── RequiereAcceso.php             -- reemplaza PuedeEvaluar; usa RolUsuario enum
│   │
│   └── Requests/
│       ├── StoreEvaluacionRequest.php
│       ├── StoreEmpresaRequest.php
│       ├── StoreDepartamentoRequest.php
│       └── StoreUsuarioRequest.php
```

**Regla:** Los controladores no contienen lógica de negocio. Cualquier cálculo, decisión, o query no-trivial va en un Service o en un scope del modelo.

---

## 7. Roles — implementación con Enum (PHP 8.1)

```php
// app/Enums/RolUsuario.php
enum RolUsuario: string {
    case Superadmin = 'Superadmin';
    case Dueno      = 'Dueño';
    case Jefe       = 'Jefe';
    case Empleado   = 'Empleado';
}
```

El modelo `User` expone:

```php
public function rol(): RolUsuario
{
    return RolUsuario::from($this->tipoUsuario->nombre);
}

public function esSuperadmin(): bool { return $this->rol() === RolUsuario::Superadmin; }
public function esDueno(): bool      { return $this->rol() === RolUsuario::Dueno; }
public function esJefe(): bool       { return $this->rol() === RolUsuario::Jefe; }
public function esEmpleado(): bool   { return $this->rol() === RolUsuario::Empleado; }
```

**Prohibido:** `method_exists()`, strings mágicos como `'Jefe'` sueltos en controladores, IDs hardcodeados como `[2, 4]` en middleware.

---

## 8. Sistema de diseño — Notion-inspired

### Filosofía
Warm minimalism. Superficies limpias, tipografía con autoridad, color funcional (no decorativo). El color solo aparece donde comunica algo: estados, cuadrantes, acciones. Todo lo demás es neutro.

### Paleta de colores

```js
// tailwind.config.js — tokens del sistema
colors: {
  // Superficies (inspirado en Notion)
  canvas:   '#ffffff',       // fondo principal
  surface:  '#f7f6f3',       // cards, sidebars
  border:   '#e9e8e4',       // separadores, inputs
  
  // Tipografía
  ink:      '#191918',       // texto principal (no negro puro)
  'ink-2':  '#6b6b6b',       // texto secundario
  'ink-3':  '#a3a3a3',       // placeholders, disabled
  
  // Acción principal
  primary:       '#2563eb',  // azul — botones, links, focus ring
  'primary-hover':'#1d4ed8',
  
  // Estados semánticos
  success:  '#16a34a',
  warning:  '#d97706',
  danger:   '#dc2626',
  
  // Nine-Box (ver sección 5 para asignación por cuadrante)
  'nb-high':    '#16a34a',
  'nb-mid-high':'#22c55e',
  'nb-mid':     '#ca8a04',
  'nb-low-mid': '#dc2626',
  'nb-low':     '#991b1b',
}
```

### Tipografía
- Familia: `Inter` (ya incluida con Tailwind)
- Pesos usados: 400 (body), 500 (labels, nav), 600 (subtítulos), 700 (títulos de sección)
- Tamaños: escala de Tailwind estándar (`text-sm`, `text-base`, `text-lg`, `text-xl`, `text-2xl`)
- No usar pesos 300 ni 800+

### Espaciado y forma
- Espaciado base: múltiplos de 4px (sistema Tailwind)
- Border radius: `rounded` (4px) para inputs y badges · `rounded-lg` (8px) para cards · `rounded-xl` (12px) para modales/panels
- Sombras: solo `shadow-sm` para cards en superficie blanca · sin sombras dramáticas
- Sin gradientes en la UI de la aplicación (solo permitidos en el header del login)

### Estados de encuesta — colores y microcopy

| Estado | Badge color | Texto en UI |
|---|---|---|
| No iniciada | `bg-gray-100 text-gray-600` | "Sin evaluar" |
| Borrador | `bg-amber-100 text-amber-700` | "En progreso (X/10)" |
| Cerrada/Enviada | `bg-green-100 text-green-700` | "Evaluada" |

### Componentes compartidos obligatorios
- `<x-sidebar>` — único, parametrizable, no duplicar por vista
- `<x-stat-card>` — para KPIs del dashboard
- `<x-estado-badge status="...">` — badge de estado de encuesta
- `<x-empty-state>` — estado vacío con mensaje específico por contexto

### Microcopy — tono
Directo, sin tecnicismos, sin anglicismos innecesarios. Primera persona plural cuando el sistema habla al jefe ("Tu equipo", "Tus evaluaciones"). Errores específicos, nunca genéricos.

```
✓ "No has evaluado a nadie este mes. Empieza seleccionando un colaborador."
✗ "No hay datos disponibles."

✓ "No existe una regla Nine-Box para estos puntajes. Contacta al administrador."
✗ "Error al procesar la solicitud."
```

---

## 9. Convenciones de código

### Nombrado
- Modelos: singular PascalCase (`User`, `Encuesta`, `ReglaNinebox`)
- Tablas: plural snake_case (`usuarios`, `encuestas`, `reglas_ninebox`)
- Métodos en español (consistente con el dominio): `calcularTotales()`, `resolverCuadrante()`, `cerrarEncuesta()`
- Variables en español: `$evaluado`, `$encuesta`, `$totalDesempeno`
- Columnas de BD: snake_case en español: `total_desempeno`, `cerrada_en`, `evaluado_id`

### Scopes de modelo
Los scopes **filtran filas**, nunca retornan escalares ni hacen cálculos.

```php
// ✓ correcto
public function scopeBorrador($q) { return $q->whereNull('cerrada_en'); }
public function scopeCerrada($q)  { return $q->whereNotNull('cerrada_en'); }

// ✗ incorrecto — esto va en el Service
public function scopeConTotales($q) { return $q->selectRaw('SUM(...)'); }
```

### Evitar N+1
Para aggregations usar `GROUP BY` sobre eager loading cuando se necesite un conteo/suma por registro. Nunca hacer queries dentro de un `map()` o `foreach`.

### Tests
Arrange / Act / Assert. Un test = un comportamiento. Seed dentro del test, no en `setUp`. Nombres descriptivos en español: `'jefe_no_puede_evaluar_empleado_de_otro_departamento'`.

---

## 10. Lo que está prohibido en este proyecto

- `method_exists()` para verificar roles
- `Schema::hasColumn()` fuera de migraciones
- Lógica de negocio en controladores (más de una llamada al service está bien, lógica no)
- Arrays hardcodeados de datos de negocio en controladores (`$nombresCuadrantes = [...]`)
- Datos de negocio reales en migraciones (`DB::table('empresas')->insert(...)`)
- `DB::raw()` sin comentario explicando por qué no se puede hacer en Eloquent
- Métodos sin usar en controladores (si no tiene ruta, no existe)
- Commits directos a `main` o `dev`

---

## 11. Sprints de reestructuración — resumen

| Sprint | Nombre | Objetivo |
|---|---|---|
| 1 | Limpieza | Remover deuda técnica sin cambiar comportamiento |
| 2 | Roles y autorización | Enum + Policies; eliminar method_exists |
| 3 | Esquema limpio | Migraciones hacia el estado objetivo de sección 4 |
| 4 | EvaluacionService | Extraer lógica de negocio del controlador |
| 5 | Tests del flujo core | Cobertura de los 5 comportamientos críticos |
| 6 | Administración | Wizard de empresa, CRUD departamentos/usuarios |
| 7 | UX/UI pass | Design system, componentes compartidos, microcopy |

Los prompts de cada sprint están en `/docs/sprints/`.

---

## 12. Contexto del equipo

- **Metodología:** Scrum adaptado, sprints semanales
- **Flujo de ramas:** `feature/issue-XX-desc` y `fix/issue-XX-desc`, nunca directo a `main`/`dev`
- **Commits:** Conventional Commits (`feat:`, `fix:`, `refactor:`, `test:`, `chore:`)
- **Revisión:** Claude revisa el diff antes de cada commit. El IDE (Cursor/Antigravity) genera, Claude valida.
- **Deuda técnica:** Si se acepta, se documenta en el issue con el motivo. Si se ignora, no existe.
