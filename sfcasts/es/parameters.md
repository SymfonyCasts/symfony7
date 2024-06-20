# Parámetros

Antes hemos hablado de nuestro contenedor y de todos los objetos de servicio que tiene. Para verlos, podemos ejecutar:

```terminal
bin/console debug:container
```

Pero estos servicios no son lo único que hay en nuestro contenedor. También contiene parámetros. Ejecuta el mismo comando que antes, pero esta vez, añade la opción `--parameters`:

```terminal-silent
bin/console debug:container --parameters
```

Básicamente son variables a las que puedes hacer referencia en tu código. La mayoría son internas, pero hay algunos parámetros básicos que pueden resultarte útiles. Por ejemplo, cualquiera de estos que empiece por `kernel.`, como `kernel.environment`, que se establece en la variable de entorno `APP_ENV`. O este `kernel.project_dir` que se establece en la ruta al directorio raíz del proyecto.

## Parámetros Symfony

¿Cómo obtenemos esto de nuestro contenedor? En realidad tenemos un método abreviado especial para ello en nuestro controlador. En el directorio `/src`, abre `Controller/MainController.php`. En el método `homepage()`, justo al principio, escribe `dd($this->getParameter())`. Y dentro, escribe el nombre del parámetro: `'kernel.project_dir'`. Como puedes ver, PhpStorm (con el plugin Symfony), ya lo ha autocompletado por nosotros. Muy bien.

[[[ code('c1856c1dbc') ]]]

De vuelta al navegador, actualiza y... ¡ahí está nuestra ruta! La mayoría de las veces, necesitaremos inyectar parámetros en los servicios y podemos hacerlo con una sintaxis especial. ¡Te lo enseñaré! Abre `config/packages/twig.yaml`. Puedes ver que tenemos `twig.default_path` que está configurado como `%kernel.project_dir%/templates`. Este `%[parameter name]%` es una sintaxis especial que se utiliza para referirse a un parámetro en los archivos `.yaml`.

[[[ code('fd0f2937f6') ]]]

A continuación: Vamos a crear un parámetro personalizado y a aprender a obtenerlo del contenedor de servicios de diferentes maneras.
