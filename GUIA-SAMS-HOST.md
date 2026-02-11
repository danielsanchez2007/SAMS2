## Configurar SAMS para abrir por nombre (no 127.0.0.1)

Esta guia deja el proyecto para abrirlo como:

- `http://sams:8000`

### 1) Copiar variables de entorno

Si no tienes archivo `.env`, crealo desde `.env.example`:

```bash
cp .env.example .env
```

Verifica que estas variables existan en `.env`:

```env
APP_URL=http://sams:8000
APP_HOST=0.0.0.0
APP_PORT=8000
VITE_HOST=sams
VITE_PORT=5173
VITE_BIND_HOST=0.0.0.0
```

### 2) Registrar el nombre `sams` en tu sistema

Agrega esta linea al archivo `hosts` del sistema:

```text
127.0.0.1    sams
```

Rutas comunes:

- Windows: `C:\Windows\System32\drivers\etc\hosts`
- Linux/Mac: `/etc/hosts`

### 3) Limpiar cache de configuracion de Laravel

```bash
php artisan config:clear
php artisan cache:clear
```

### 4) Iniciar el proyecto

```bash
composer dev
```

### 5) Abrir en navegador

Abre:

- `http://sams:8000`

Si usas Vite en modo desarrollo, el HMR quedara en `sams:5173`.
