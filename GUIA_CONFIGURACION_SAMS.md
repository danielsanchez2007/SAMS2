# Guia de configuracion local de SAMS (Windows + Laragon)

Esta guia deja el proyecto accesible por nombre de dominio local (`sams`) en lugar de `127.0.0.1`.

## Opcion A: usar `composer dev` (recomendado para desarrollo rapido)

Con esta opcion el backend corre en puerto `8000`, pero se accede por nombre:

- URL final: `http://sams:8000`

### Paso 1: agregar dominio local en `hosts`

Edita como administrador el archivo:

`C:\Windows\System32\drivers\etc\hosts`

y agrega:

```txt
127.0.0.1    sams
127.0.0.1    www.sams
```

### Paso 2: ajustar `.env`

En el archivo `.env` deja:

```env
APP_NAME=SAMS
APP_URL=http://sams:8000
```

### Paso 3: limpiar cache de config

```bash
php artisan optimize:clear
```

### Paso 4: iniciar entorno

```bash
composer dev
```

Abre en navegador:

`http://sams:8000`

---

## Opcion B: usar Laragon/Apache con VirtualHost (sin puerto)

Con esta opcion la URL final es:

- URL final: `http://sams`

### Paso 1: copiar `sams.conf`

Copia `sams.conf` a:

`C:\laragon\etc\apache2\sites-enabled\sams.conf`

### Paso 2: agregar hosts

En `C:\Windows\System32\drivers\etc\hosts` agrega:

```txt
127.0.0.1    sams
127.0.0.1    www.sams
127.0.0.1    sams.test
127.0.0.1    www.sams.test
```

### Paso 3: ajustar `.env`

Para esta opcion puedes usar:

```env
APP_URL=http://sams
```

### Paso 4: reiniciar Laragon

1. Stop All
2. Start All

Abre:

`http://sams`

---

## Error comun: `Failed to listen on 0.0.0.0:8000`

Si aparece:

`Intento de acceso a un socket no permitido por sus permisos de acceso`

la causa suele ser el bind a `0.0.0.0` en Windows.

En este repositorio ya quedo corregido para que `composer dev` use:

- `php artisan serve --host=127.0.0.1 --port=8000`
- `vite --host 127.0.0.1 --port 5173 --strictPort`

Si necesitas exponer por red local, usa:

```bash
composer dev:network
```
