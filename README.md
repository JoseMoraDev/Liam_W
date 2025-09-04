# Live Ambience Weather - LIAM

# COMMANDS
  - GIT > remoto sobre local > git pull origin main


# PROBLEM SOLVER
- si mysql no va: win + R services.msc buscar mysql80 e iniciar

## History


## ESTOY PERDIDO
  - en el gpt tengo una migración para solucionar lo de los enpoints metiendole un middleware
  - pero a la vez le he preguntado por como borrar restos de codigo que no me sirven para nada
    - en lugar de borrar alegremente, que verificar esos restos de codigo si sirven para algo o no

# LA API DE AEMET FALLA MAS QUE UNA ESCOPETA DE FERIA, HABRA QUE HACER RETRYS

### próximos objetivos
    - Enpoint últimas alertas - Por Comunidad Autónoma -
    - hacer autogeo: tener en cuenta, favorito, por defecto y otras ubicaciones


### LO QUE ESTOY HACIENDO

  - Autenticación de usuarios
    - API Tokens con Sanctum
    - Endpoints REST para gestionar el flujo

    - PROBLEMAS
      - font_id y color_id tienen que funcionar cuando se crea el usuario y valen NULL, o al crear el user establecerse como 1

  - Plan básico de Auth en tu API (Laravel 11 + Sanctum)

            Logout → POST /api/logout
            Requiere token válido.
            Borra el token actual.

            Perfil del usuario autenticado → GET /api/user
            Devuelve los datos del usuario según el token.

            Forgot password → POST /api/forgot-password
            Envía un mail con un link/token para resetear contraseña.

            Reset password → POST /api/reset-password
            Cambia la contraseña usando el token enviado al correo.






# LOG DIARIO DE TRABAJO

de login todo va menos el reset pass y user profile

### August
#### ===> semana 36
##### mar 02
  - Maquetar frontal
##### mar 02
  - Esqueleto del frontal
##### lun 01
  - Desdockerizar todo el proyecto
#### ===> semana 35
##### sab 30
  - Levantar toda la base de datos e insertar los seeders con un solo comando
  - Verificar que los seeders han insertado correctamente en las tablas
  - Verificar que todos los endpoints están funcionando
##### vie 29
  - Copia de seguridad del volumen mysql del proyecto
##### jue 28
  - Investigar por qué no puedo enviar correos desde gmail
  - Error en Docker, pierdo mis volúmenes, reinstalo

#### ===> semana 34
##### dom 24
  - instalar certificados CA
##### sab 23
  - /register works
  - /login works
  - /logout works
##### vie 22
  - registrar endpoints y coordenadas - fallido 
##### jue 21
  - registrar endpoints y coordenadas - fallido
##### lun 18
  -  Ubicarme donde lo dejé el sábado porque no apunté nada


#### ===> semana 33
  ##### jue 14
    - Listado de > 8.000 municipios en base de datos, esta vez con seeder
    - Listado autonomías y provincias en base de datos, esta vez con seeder
  ##### mie 13
    - Listado autonomías y provincias en base de datos, fix y funcionando todos los endpoints
  ##### mar 12
    - Endpoints predicción municipio diaria, y predicción municipio horaria de AEMET funcionando
    - Listado de > 8.000 municipios en base de datos
    - Listado autonomías y provincias en base de datos
  ##### lun 11
    - Endpoint últimas alertas de AEMET funcionando

#### ===> semana 32
  ##### dom 10
    - Endpoints TrafficFlow y TrafficFlow de TomTom funcionando
    - Enpoints AirQualityIP y AirQualityGeolocalization de AQICN funcionando
    - Enpoints PrevisiónNivológica, PrevisiónPlaya, ListadoPlayas, DetallePlaya, PrevisiónMontaña, y TemperaturaMar de AEMET funcionando
  ##### sab 09
    - Creación de la base de datos mediante migración Laravel. 
    - Crear seeders para todas las tablas. 
    - Revisar y cambiar datos fake a datos reales en los seeders.
    - Aplicar los seeders.
  ##### vie 08 
    - Volver a levantar el entorno de desarrollo en un nuevo repo, todo listo para desarrollar. 
    - Api & Database connection works.

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

## Endpoints
- 

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


## Estructura de la base de datos 

[Ver en db diagram.io](https://dbdiagram.io/d/liam_db-68821f64cca18e685c8ccbd5)

### Base de datos del sistema de usuarios, ubicaciones, APIs y datos meteorológicos

Table users {
  id integer [primary key]
  email varchar
  username varchar
  hashed_passwd varchar
  font_id integer [ref: > fonts.id]
  color_id integer [ref: > colors.id]
  last_location_latlon varchar
  last_location_city varchar
  last_location_updated_at timestamp
}


Table fonts {
  id integer [primary key]
  name varchar
  source varchar
}

Table locations {
  id integer [primary key]
  name varchar
  lat_lon varchar
  is_default boolean
  user_id integer [ref: > users.id]
}

Table tokens {
  id integer [primary key]
  api_desc text  
  token varchar
  placeholder_key varchar  
}

Table weather_data {
  id integer [primary key]
  location_id integer [ref: > locations.id]
  endpoint_id integer [ref: > api_calls_definitions.id]
  data json
  fetched_at timestamp
}

Table api_calls {
  id integer [primary key]
  description text
  params json 
  url text
  token_id integer [ref: > tokens.id]
  user_id integer [ref: > users.id]
  definition_id integer [ref: > api_calls_definitions.id]
}

Table api_calls_definitions {
  id integer [primary key]
  name varchar  
  description text  
  base_url varchar  
  path varchar  
  method varchar  
  path_placeholders json  
  query_placeholders json  
  auth_type varchar  
  default_format varchar  
  notes text  
}


Table colors {
  id integer [primary key]
  name varchar
  user_id integer [ref: > users.id, null]
  description text
  font_id integer [ref: > fonts.id]
  font_size varchar
  text_color varchar
  primary_color varchar
  secondary_color varchar
  background_color varchar
  border_color varchar
  shadow_color varchar
  hover_color varchar
  // if user_id is NULL then theme is public/global
}