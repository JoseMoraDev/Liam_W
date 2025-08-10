# Live Ambience Weather - LIAM

## History

### August
#### semana 32
##### dom 10
  - Endpoints TrafficFlow y TrafficFlow funcionando
  - 
##### sab 09
  - Creación de la base de datos mediante migración Laravel. 
  - Crear seeders para todas las tablas. 
  - Revisar y cambiar datos fake a datos reales en los seeders.
  - Aplicar los seeders.
##### vie 08 
  - Volver a levantar el entorno de desarrollo en un nuevo repo, todo listo para desarrollar. 
  - Api & Database connection works.




### próximos objetivos
    - hacer api aqicn
    - ip/geo loc, implementar en aemet y aqicn
    - hacer aemet con autogeo

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