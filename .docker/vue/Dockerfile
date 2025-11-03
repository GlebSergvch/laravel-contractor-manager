FROM node:18.17.0-alpine

# set working directory
# Если установить /app - будут необъяснимые ошибки: [vite-node] [ERR_LOAD_URL] ..vue
WORKDIR /var/www/frontend

# install and cache app dependencies
COPY ./package*.json ./

COPY . .

# add `/application/node_modules/.bin` to $PATH
ENV PATH /var/www/frontend/node_modules/.bin:$PATH

# start app
#CMD ["npm", "run", "dev"]

