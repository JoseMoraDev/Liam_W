# Etapa de desarrollo
FROM node:20-bullseye

# Variables para facilitar customización
ENV NUXT_HOST=0.0.0.0 \
    NUXT_PORT=3000 \
    NODE_ENV=development

WORKDIR /app

# Copia solo package.json y lock para instalar primero (mejor caché)
COPY package*.json ./

# Instala dependencias
RUN npm install

# Copia el resto del código
COPY . .

# Expone el puerto de Nuxt
EXPOSE 3000

# Comando por defecto (modo desarrollo)
CMD ["npm", "run", "dev"]
