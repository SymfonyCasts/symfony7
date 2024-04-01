# Tailwind CSS

¿Qué pasa con el CSS? Eres libre de añadir el CSS que quieras a `app/styles/app.css`. Ese archivo ya está cargado en la página.

¿Quieres utilizar CSS de Bootstrap? Consulta la documentación de Asset Mapper sobre cómo hacerlo. O, si quieres usar Sass, hay un [symfonycasts/sass-bundle](https://github.com/symfonycasts/sass-bundle) que te lo pone fácil. No obstante, te recomiendo que no te lances a usar Sass demasiado rápido, ya que muchas de las funciones por las que Sass es famoso pueden hacerse ahora en CSS nativo, como las variables CSS e incluso el anidamiento CSS.

## Hola Tailwind

¿Cuál es mi elección personal para un framework CSS? Tailwind. Y parte de la razón es que Tailwind es increíblemente popular. Así que si buscas recursos o componentes preconstruidos, vas a tener mucha suerte si utilizas Tailwind.

Pero Tailwind es un poco extraño en un sentido: no es simplemente un gran archivo CSS que pones en tu página. En su lugar, tiene un proceso de construcción que escanea tu código en busca de todas las clases Tailwind que estés utilizando. Luego vuelca un archivo CSS final que sólo contiene el código que necesitas.

En el mundo Symfony, si quieres utilizar Tailwind, hay un bundle que lo hace realmente fácil. Gira tu terminal e instala un nuevo paquete: `composer require`
`symfonycasts` - hey los conozco - `tailwind-bundle`:

```terminal-silent
composer require symfonycasts/tailwind-bundle
```

Para este paquete, la receta no hace nada más que activar el nuevo bundle. Para poner en marcha Tailwind, una vez en tu proyecto, ejecuta:

```terminal
php bin/console tailwind:init
```

Esto hace tres cosas. En primer lugar, descarga un binario de Tailwind en segundo plano, algo en lo que nunca tendrás que pensar. En segundo lugar, crea un archivo `tailwind.config.js`en la raíz de nuestro proyecto. Esto indica a Tailwind dónde tiene que buscar en nuestro proyecto las clases CSS de Tailwind. Y tercero, actualiza nuestro `app.css` para añadir estas tres líneas. Éstas serán sustituidas por el código real de Tailwind en segundo plano por el binario.

## Ejecutar Tailwind

Por último, hay que compilar Tailwind, así que tenemos que ejecutar un comando para hacerlo:

```terminal
php bin/console tailwind:build -w
```

Esto escanea nuestras plantillas y genera el archivo CSS final en segundo plano.
El `-w` lo pone en modo "vigilar": en lugar de construir una vez y salir, vigila nuestras plantillas en busca de cambios. Cuando detecte alguna actualización, reconstruirá automáticamente el archivo CSS. Lo veremos en un minuto.

Pero ya deberíamos ver una diferencia. Vamos a la página de inicio. ¿Lo has visto? El código base de Tailwind ha hecho un reinicio. Por ejemplo, ¡nuestro `h1` es ahora diminuto!

## Ver Tailwind en acción

Probemos esto de verdad. Abre `templates/main/homepage.html.twig`. Encima de `h1`, hazlo más grande añadiendo una clase: `text-2xl`.

[[[ code('83fd013fdd') ]]]

En cuanto guardemos eso, podrás ver que tailwind se dio cuenta de nuestro cambio y reconstruyó el CSS. Y cuando actualizamos, ¡se hizo más grande!

Nuestro archivo fuente `app.css` sigue siendo super sencillo: sólo esas pocas líneas que vimos antes. Pero mira el código fuente de la página y abre el `app.css` que se está enviando a nuestros usuarios. ¡Es la versión construida de Tailwind! Entre bastidores, existe cierta magia que sustituye esas tres líneas de Tailwind por el código CSS real de Tailwind.

## Ejecutar automáticamente Tailwind con el binario symfony

Y... ¡eso es todo! Simplemente funciona. Aunque hay una forma más fácil y automática de ejecutar Tailwind. Pulsa Ctrl+C en el comando Tailwind para detenerlo. A continuación, en la raíz de nuestro proyecto, crea un archivo llamado `.symfony.local.yaml`. Se trata de un archivo de configuración para el servidor web binario `symfony` que estamos utilizando. Dentro, añade `workers`, `tailwind`, y luego `cmd` configurados en una matriz con cada parte de un comando: `symfony`, `console`, `tailwind`, `build`, `--watch`, o podrías utilizar `-w`: es lo mismo.

Aún no he hablado de ello, pero en lugar de ejecutar `php bin/console`, también podemos ejecutar `symfony console` seguido de cualquier comando para obtener el mismo resultado. Hablaremos de por qué te conviene hacerlo en un futuro tutorial. Pero por ahora, considera que `bin/console` y `symfony console` son lo mismo.

Además, al añadir esta clave `workers`, significa que en lugar de que tengamos que ejecutar el comando manualmente, cuando iniciemos el servidor web `symfony`, éste lo ejecutará por nosotros en segundo plano.

Observa. En tu primera pestaña, pulsa Ctrl+C para detener el servidor web... luego vuelve a ejecutar

```terminal
symfony serve
```

para que vea el nuevo archivo de configuración. Mira: ¡ahí está! ¡Está ejecutando el comando tailwind en segundo plano!

Podemos aprovecharnos de esto inmediatamente. En `homepage.html.twig`, cambia esto a`text-4xl`, gira y... ¡funciona! Ya ni siquiera tenemos que pensar en el comando`tailwind:build`.

[[[ code('112955d5b6') ]]]

Y como estilizaremos con Tailwind, elimina el fondo azul.

## Copiar en plantillas estilizadas

Vale, este tutorial no trata sobre Tailwind ni sobre cómo diseñar un sitio web. Créeme, no quieres que Ryan dirija la carga del diseño web. Pero sí quiero tener un sitio bonito... y también es importante pasar por el proceso de trabajar con un diseñador.

Así que imaginemos que otra persona ha creado un diseño para nuestro sitio. E incluso nos han dado algo de HTML con clases de Tailwind para ese diseño. Si descargas el código del curso, en un directorio de `tutorial/templates/`, tenemos 3 plantillas. Uno a uno, voy a copiar cada archivo y pegarlo sobre el original. No te preocupes, veremos lo que ocurre en cada uno de estos archivos. 

[[[ code('fdf4754c05') ]]]

Haz `homepage.html.twig`... 

[[[ code('1579c9212f') ]]]

y finalmente `show.html.twig`.

[[[ code('abb920fb7d') ]]]

***TIP
Si copias los archivos (en lugar del contenido de los archivos), puede que el sistema de caché de Symfony no note el cambio y no veas el nuevo diseño. Si eso ocurre, borra la caché ejecutando `php bin/console cache:clear`.
***

Voy a borrar por completo el directorio `tutorial/` para no confundirme y editar las plantillas equivocadas.

Vale, ¡vamos a ver qué ha hecho esto! Actualizar. ¡Tiene un aspecto precioso! Me encanta trabajar dentro de un diseño bonito. Pero... algunas partes están rotas. En `homepage.html.twig`, ésta es nuestra cola de reparación de barcos... que queda muy bien... ¡pero no hay código Twig! El estado está codificado, el nombre está codificado y no hay bucle.

[[[ code('5f290d953a') ]]]

A continuación: tomemos nuestro nuevo diseño y hagámoslo dinámico. También aprenderemos a organizar las cosas en parciales de plantilla e introduciremos un enum PHP, que son divertidos.