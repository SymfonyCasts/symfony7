# Configuración de la base de datos y Docker

Muy bien, ¡ya tenemos Doctrine instalado! Pero ahora necesitamos, ya sabes, poner en marcha un servidor de base de datos.

## `DATABASE_URL` Variable de entorno

Echa un vistazo a nuestro archivo `.env`. Cuando instalamos Doctrine, la receta Flex añadió esta sección doctrine-bundle. La variable de entorno`DATABASE_URL` es donde le decimos a Doctrine cómo conectarse a nuestra base de datos. Es una cadena especial con aspecto de URL llamada DSN, si quieres un poco de terminología friki.

Contiene el tipo de base de datos a la que nos estamos conectando -`mysql`, `postgres`, `sqlite`, `borgsql`, etc, un nombre de usuario, contraseña, host, puerto y el nombre de la base de datos. Cualquier parámetro de consulta es una configuración extra.

Por defecto, `DATABASE_URL` está configurado para conectarse a una base de datos Postgres y eso es lo que utilizaremos. Lo pondremos en marcha muy fácilmente con Docker.

Si no quieres utilizar Docker, ¡no hay problema! Comenta esta línea y descomenta la de`sqlite`. SQLite no requiere un servidor: es sólo un archivo en tu sistema de archivos. Como Doctrine abstrae la capa de base de datos, en su mayor parte, el código que escribamos funcionará con cualquier tipo de base de datos. ¡Genial!

Recuerda, no guardes ninguna información sensible en este archivo: está comprometido en tu repositorio. Si tienes tu propio servidor de base de datos localmente, crea un archivo `.env.local`(git lo ignora), y establece allí tu propio `DATABASE_URL`.

## Iniciar un contenedor Postgres con Docker

Vale, ¿cómo podemos poner en marcha un servidor de base de datos Postgres?

Echa un vistazo a `compose.yaml`. Lo ha añadido una receta de Flex y contiene la configuración de Docker, incluido este servicio `database` para poner en marcha un contenedor Postgres ¡Fantástico! Puedes hacer lo que quieras, pero nosotros sólo vamos a utilizar Docker como una forma cómoda de ejecutar localmente un servidor de base de datos. El propio PHP está instalado normalmente en mi máquina.

Abre tu terminal y ejecuta:

```terminal
docker compose up -d
```

Esto inicia los contenedores Docker y `-d` le dice a Docker que lo haga todo en segundo plano.

Pero, ¿dónde se ejecuta el servidor de bases de datos? ¿En qué puerto? ¿No necesitamos saberlo para poder actualizar `DATABASE_URL` para que apunte a él?

## ¡La CLI de Symfony es genial!

No! El binario CLI de `symfony` que está ejecutando el servidor web tiene algo de magia Docker! Salta y actualiza la aplicación. Aquí abajo, pasa el ratón sobre "Servidor". Contiene detalles sobre el servidor Symfony CLI. Esta parte significa que ha detectado automáticamente nuestros contenedores Docker y ha configurado las variables de entorno por nosotros

Te lo mostraré. Ve a nuestro terminal y ejecuta:

```terminal
symfony var:export --multiline
```

Esto nos muestra algunas variables de entorno extra que el Symfony CLI está configurando por nosotros, además de las que están en `.env`. 

Desplázate un poco hacia arriba para ver.... ¡Aquí está! `DATABASE_URL` ¡! Esto anula la que está en `.env` y apunta a la base de datos Postgres que se ejecuta en Docker. Ese número de puerto cambiará aleatoriamente, pero la CLI de Symfony siempre utilizará el correcto.

## `symfony console` vs `bin/console`

Ahora, estamos acostumbrados a ejecutar los comandos de Symfony con `bin/console`. Pero cuando utilizamos la CLI de Symfony con una base de datos Docker, necesitamos ejecutar los comandos específicos de la base de datos a través de`symfony console` en su lugar. Es lo mismo que `bin/console`, pero da a la CLI de Symfony la oportunidad de añadir las variables de entorno.

## Crear la base de datos

Ya está El servidor de la base de datos se está ejecutando en un contenedor Docker y `DATABASE_URL` está apuntando a él. Para crear la base de datos, ejecuta:

```terminal
symfony console doctrine:database:create
```

¡Un error! ¡No te preocupes! El error nos está diciendo que la base de datos ya existe: aparentemente el servidor viene con una. Pero esto es bueno, ¡significa que nos estamos conectando a nuestro servidor de base de datos!

Bien, ya tenemos Doctrine y una base de datos. ¡Ahora necesitamos una tabla! Lo haremos a continuación saltando al mundo de las entidades y las migraciones.
