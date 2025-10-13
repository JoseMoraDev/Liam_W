instalado leaflet y chartjs
--> ver dependencias del proyecto

# Live Ambience Weather - LIAM

# COMMANDS
  - GIT > remoto sobre local > git pull origin main
  - npm i @svg-maps/spain

## Comandos
Docker build
```sh
docker compose up --build -d
```

Levantar Contenedores
```sh
docker compose up -d
```

Detener Contenedores
```sh
docker compose down
```

Ver Contenedores
```sh
docker compose ps
```

# PROBLEM SOLVER
- si mysql no va: win + R services.msc buscar mysql80 e iniciar

## URLs accesibles

| **Servicio**    | **URL**                                        | **Descripción**                    |
| --------------- | ---------------------------------------------- | ---------------------------------- |
| Laravel Backend | [http://localhost:8000](http://localhost:8000) | API y aplicación backend (Laravel) |
| Vue Frontend    | [http://localhost:3000](http://localhost:3000) | Interfaz frontend (Vite + Vue)     |
| phpMyAdmin      | [http://localhost:8080](http://localhost:8080) | Admin web para MySQL               |

## Health checks -solo dev mode-
> APP_ENV=local
- [/api/check/health](http://localhost:8000/api/check/health) -> Devuelve si la API esta levantada
- [/api/check/db](http://localhost:8000/api/check/db) -> Devuelve si la base de datos esta conectada correctamente
