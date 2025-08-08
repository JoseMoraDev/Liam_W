# Live Ambience Weather - LIAM

## History
#### August
08 Volver a levantar el entorno de desarrollo en un nuevo repo, todo listo para desarrollar. Api & Database connection works.


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

## Comandos útiles

- Lanzar docker => <code>docker compose up -d</code>
- Ver los contenedores de docker => <code>docker ps</code>
- Detener docker => <code>docker compose stop</code>
- Abrir editor de codigo => <code>code .</code>
