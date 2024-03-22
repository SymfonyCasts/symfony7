# CSS y JavaScript con Asset Mapper

¿Qué pasa con las imágenes, CSS y JavaScript? ¿Cómo funciona eso en Symfony?

## Las cosas públicas son... Público

En primer lugar, el directorio `public/` se conoce como la raíz de tu documento. Cualquier cosa dentro de `public/` es accesible para tu usuario final. Todo lo que no esté en `public/` no es accesible, ¡lo cual es genial! Ninguno de nuestros archivos fuente de alto secreto puede ser descargado por nuestros usuarios.

Así que si quieres crear un archivo CSS o un archivo de imagen o cualquier otra cosa, la vida puede ser tan simple como ponerlo en `public/`. Ahora puedo ir a `/foo.txt`... y vemos el archivo.

## Hola Mapeador de Activos

Sin embargo, Symfony tiene un gran componente llamado Asset Mapper que nos permite hacer efectivamente lo mismo... pero con algunas características importantes y extra. Tenemos unos cuantos tutoriales que profundizan en este tema: uno sobre el Mapeador de Activos específicamente y otro sobre cómo construir cosas con el Mapeador de Activos llamado [LAST Stack](https://symfonycasts.com/screencast/last-stack). Échales un vistazo para profundizar.

¡Pero vamos a sumergirnos en las amistosas aguas del Mapeador de Activos! Confirma todos tus cambios -yo ya lo he hecho- e instálalo con:

```terminal
composer require symfony/asset-mapper
```

Esta receta hace varios cambios... y recorreremos cada uno poco a poco, ya que son importantes.

Pero ya, si nos desplazamos y actualizamos, ¡nuestro fondo es azul! Inspecciona Element en tu navegador y ve a la consola. ¡También tenemos un registro de consola!

> Este log viene de `assets/app.js`. Bienvenido al mapeador de activos.

¡Muchas gracias!

## Los 2 superpoderes de Asset Mapper

Asset Mapper tiene dos grandes superpoderes. El primero es que nos ayuda a cargar CSS y JavaScript. La receta nos ha proporcionado un nuevo directorio `assets/` con un archivo `app.js` y otro `styles/app.css`. Como hemos visto, el registro de la consola procede de `app.js`. 

[[[ code('963c6fbd7c') ]]]

Así que este archivo se está cargando. Al parecer, también está incluyendo `app.css`, que es lo que nos da ese fondo azul.

[[[ code('240833ed57') ]]]

Más adelante hablaremos más sobre cómo se cargan estos archivos y cómo funciona todo esto. Pero por ahora, basta con saber que `app.js` y `app.css` están incluidos en la página.

El segundo gran superpoder de Asset Mapper es un poco más sencillo. La receta ha creado un archivo `config/packages/asset_mapper.yaml`. No hay mucho aquí:

[[[ code('391af354c5') ]]]

sólo `paths` apuntando a nuestro directorio `assets/`. Pero gracias a esta línea, cualquier archivo que pongamos en el directorio `assets/` estará disponible públicamente. Es como si el directorio `assets/` viviera físicamente dentro de `public/`. Esto es útil porque, sobre la marcha, Asset Mapper añade el versionado de activos: un importante concepto de frontend que veremos dentro de un minuto.

## Listado de activos y ruta lógica

Pero antes, dirígete a tu terminal y ejecuta otro nuevo comando `debug`:

```terminal
php bin/console debug:asset
```

Esto muestra todos los activos expuestos públicamente a través del Mapeador de Activos. Ahora mismo son sólo dos: `app.css` y `app.js`. 

Si descargas el código del curso de esta página y lo descomprimes, encontrarás un directorio `tutorial/`con un subdirectorio `images/`. Cortaré esto... y luego lo pegaré en`assets/`.

Así que ahora tenemos un directorio `assets/images/` con 5 archivos dentro. Y, por cierto, puedes organizar el directorio `assets/` como quieras.

Pero ahora, vuelve atrás y ejecuta de nuevo `debug:asset`:

```terminal-silent
php bin/console debug:asset
```

¡Los nuevos archivos están ahí!

## Representación de una imagen

A la izquierda, ¿ves esta "ruta lógica"? Es la ruta que utilizaremos para hacer referencia a ese archivo en Asset Mapper.

Te lo mostraré: vamos a renderizar una etiqueta `img` en el logotipo. Copia la ruta lógica `starshop-logo.png`. Luego dirígete a `templates/base.html.twig`. Justo encima del bloque del cuerpo -para que no quede anulado por el contenido de nuestra página- añade una etiqueta `<img>` con`src=""`. En lugar de intentar codificar una ruta, di `{{` y utiliza una nueva función Twig llamada `asset()`. Pásale la ruta lógica.

Ya está Vale, añadiré un atributo `alt`... para ser un buen ciudadano de la web. 

[[[ code('36753afdc0') ]]]

Probemos esto. Actualiza y... ¡estalla!

> ¿Has olvidado ejecutar` composer require symfony/asset`. Función desconocida "activo".

Recuerda: nuestra aplicación empieza siendo pequeña. Y luego, a medida que necesitamos más funciones, instalamos más componentes Symfony. Y a menudo, si intentas utilizar una función de un componente que no está instalado, te lo dirá. La función Twig`asset()` proviene de otro componente diminuto llamado `symfony/asset`. Todo lo que tenemos que hacer es seguir el consejo. Copia el comando `composer require`, pasa a tu terminal y ejecútalo:

```terminal-silent
composer require symfony/asset
```

Cuando termine, muévete y actualiza. ¡Ahí está nuestro logotipo!

## Versionado automático de activos

¿La parte más interesante? Ver el código fuente de la página y comprobar la URL:`/assets/images/starshop-logo-` y luego una larga cadena de letras y números, `.png`. Esta cadena se llama hash de la versión y se genera en función del contenido del archivo. Eso significa que si más adelante actualizamos nuestro logotipo, este hash cambiará automáticamente.

Esto es superimportante. A los navegadores les gusta almacenar en caché las imágenes, el JavaScript y los archivos CSS, lo que está muy bien: ayuda al rendimiento. Pero cuando cambiamos esos archivos, queremos que nuestros usuarios descarguen la nueva versión: no que sigan utilizando la versión obsoleta, almacenada en caché.

Pero como el nombre del archivo cambiará cuando actualicemos la imagen, ¡el navegador va a utilizar automáticamente el nuevo! Esto es así:
* El usuario va a nuestro sitio y descarga `logo-abc123.png`. Su navegador lo almacena en caché.
* En la siguiente visita, su navegador ve la etiqueta `img` para `logo-abc123.png`, encuentra el archivo en su caché y lo utiliza.
* Entonces llegamos nosotros, actualizamos ese archivo y lo desplegamos.
* La próxima vez que el usuario visite nuestro sitio, la etiqueta `img` apuntará a un nombre de archivo diferente: `logo-def456.png`. Y como el navegador no tiene ese archivo en su caché, lo descarga nuevo.

Se trata de un pequeño detalle, pero también es increíblemente importante para asegurarnos de que nuestros usuarios utilizan siempre los archivos más recientes. ¿Y lo mejor? Simplemente funciona. Ahora que te lo he explicado, no tendrás que volver a pensar en esto.

Ok equipo, vamos a instalar y empezar a usar Tailwind CSS a continuación.
