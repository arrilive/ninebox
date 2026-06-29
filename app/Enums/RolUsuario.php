<?php
namespace App\Enums;
enum RolUsuario: string {
    case Superadmin = 'Superadmin';
    case Dueno      = 'Dueño';
    case Jefe       = 'Jefe';
    case Empleado   = 'Empleado';
}
