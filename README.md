# Proyecto Liam – Docker Setup

## Levantar el entorno

1. **Clonar el repositorio y posicionarse en la raíz.**

2. **Crear un archivo `.env` con las siguientes variables:**

```env
MYSQL_DATABASE=
MYSQL_USER=
MYSQL_PASSWORD=
MYSQL_ROOT_PASSWORD=
```

3. **Lanzar la app**

- Si es la primera vez

```sh
docker compose up --build -d
```

- Si ya se ha lanzado antes

```sh
docker compose up -d
```

## URLs accesibles

| **Servicio**    | **URL**                                        | **Descripción**                    |
| --------------- | ---------------------------------------------- | ---------------------------------- |
| Laravel Backend | [http://localhost:8000](http://localhost:8000) | API y aplicación backend (Laravel) |
| Vue Frontend    | [http://localhost:5173](http://localhost:5173) | Interfaz frontend (Vite + Vue)     |
| phpMyAdmin      | [http://localhost:8080](http://localhost:8080) | Admin web para MySQL               |

## Health checks

> Nota: Solo funcionan cuando el entorno esta configurado en modo desarrollo
> APP_ENV=local

- [/api/check/health](http://localhost:8000/api/check/health) -> Devuelve si la API esta levantada
- [/api/check/db](http://localhost:8000/api/check/db) -> Devuelve si la base de datos esta conectada correctamente

## Comandos útiles

- Lanzar docker => <code>docker compose up -d</code>
- Ver los contenedores de docker => <code>docker ps</code>
- Detener docker => <code>docker compose stop</code>
- Abrir editor de codigo => <code>code .</code>
