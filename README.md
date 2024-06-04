-----------------------------------------------
|              Autoría                         |
-----------------------------------------------
| Nombre: Orlando Reina Ceron                  |
| Fecha: 31/05/2024                            |
| Descripción:                                 |
| Prueba de actualización de registro por      |
| cola de proceso usando Laravel 10.           |
-----------------------------------------------


# Crear Migraciones
php artisan make:migration create_security_types_table --create=security_types
php artisan make:migration create_securities_table --create=securities
php artisan make:migration create_security_prices_table --create=security_prices

# Crear Modelos
php artisan make:model SecurityType
php artisan make:model Security
php artisan make:model SecurityPrice

# Crear Controlador
php artisan make:controller HandleSyncSecurity

# Crear Job
php artisan make:job ProcessMicroserviceABC

# Configurar Colas
php artisan queue:table
php artisan migrate

# Crear Pruebas Unitarias
php artisan make:test HandleSyncSecurityTest --unit
php artisan make:test ProcessMicroserviceABCTest --unit

# Ejecutar Pruebas
php artisan test

# Ejecutar Servidor
php artisan serve