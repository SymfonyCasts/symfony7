# Enviar un formulario de búsqueda mediante GET

Ahora tenemos un formulario de búsqueda. Pero hay un pequeño problema. Se envía a través de POST porque es el método por defecto de Symfony. Normalmente, eso está muy bien. Pero para un formulario de búsqueda, POST no es la mejor opción. ¿Por qué? Porque cuando buscamos, queremos que la consulta sea visible en la URL. Hace que los resultados de la página se puedan compartir y marcar como favoritos. Además, es como nuestro antiguo formulario. Así que vamos a modificarlo y cambiar nuestro formulario a GET.

Para empezar, abre el archivo `PartSearchType.php`. En `setDefaults()`podemos ajustar las opciones de nuestro formulario. En la matriz vacía, añade una nueva fila escribiendo`method` y establécela en `Request::METHOD_GET`. Esto es en realidad una forma sencilla, aprobada por Symfony, de decir una simple cadena `GET`.

Tómate un momento para enviar el formulario ahora. ¡Voilà! La consulta aparece ahí mismo en la URL como parámetros de consulta. Pero espera, esos parámetros de consulta tienen un aspecto un poco extraño. En lugar del simple `query=legacy`, vemos`parts_search[query]=legacy` - esos caracteres `%%` son simplemente `[]` codificados.

¿Qué ocurre? Por defecto, los formularios Symfony envían los datos como matrices para ayudar a evitar colisiones de nombres cuando tienes varios formularios en la misma página. Es un movimiento inteligente, pero para nuestro formulario de búsqueda, es exagerado y, seamos sinceros, un poco feo.

## Limpiar los parámetros de consulta

Esto es lo que haremos. Queremos simplificar los parámetros de consulta. Para deshacernos de este prefijo de nombre de formulario, podemos sobrescribir un método especial en el tipo de formulario.

De vuelta en `PartSearchType.php`, al final de esta clase, pulsaré`Cmd` + `N` y elegiré "Sobrescribir métodos", luego elegiré el método `getBlockPrefix()`. Dentro de él, simplemente devuelve una cadena vacía. Esto le dice a Symfony que no anteponga a nuestros campos el nombre del formulario. Vuelve a buscar y ¡bam! Tenemos parámetros de consulta limpios y planos. ¡Mucho mejor!

Pero aún no hemos llegado al final. Ahora tenemos una consulta `_token` en la URL. Es la protección CSRF haciendo su trabajo, pero es innecesaria para un formulario de búsqueda basado en GET. Sólo estamos filtrando una lista de productos, así que vamos a desactivarla para este formulario en `setDefaults()`.

Para ello, vuelve a `PartSearchType.php` y añade una nueva opción llamada`csrf_protection`, estableciéndola en `false`.

Vuelve a enviar el formulario y ¡bingo! Sólo tenemos el parámetro `query` en la URL, limpia e intencionadamente.

## Hacer que el campo de búsqueda sea opcional

Un último ajuste. Si intentamos enviar el formulario con un campo vacío, el navegador nos detendrá con un error de validación HTML5. Puede que eso esté bien para tu aplicación, pero yo prefiero que una búsqueda vacía signifique "Muéstramelo todo". Podemos desactivar la validación utilizando el atributo `novalidate` en la etiqueta del formulario, pero una opción mejor es simplemente hacer que el campo sea opcional. Para el campo`query`, añadamos otro parámetro llamado `required` y configurémoslo como`false`.

Actualiza la página y si intentamos buscar con una entrada vacía, simplemente muestra la lista completa.

Sinceramente, ahora mismo, la búsqueda funciona debido a cierta lógica de negocio heredada en `PartController`. Concretamente, estamos obteniendo la consulta directamente de la petición como `$request->query->get('query')`. Y para un pequeño formulario de búsqueda, eso está totalmente bien. Pero ya que estamos aprendiendo el componente Formulario de Symfony, hagámoslo a la manera de Symfony.

## Manejo adecuado de formularios en Symfony

Vamos a comentar la línea `$query = $request->query->get('query')`, manteniéndola como ejemplo. A continuación, inicializaremos una nueva variable `query` con el valor `null`. A continuación, le decimos al formulario que gestione la petición con`$searchForm->handleRequest($request)`.

Comprobamos el combo habitual con una sentencia `if`:`$searchForm->isSubmitted()`, `&& $searchForm->isValid()`. El campo `query` no está mapeado, pero ya sabemos cómo acceder a él. Establece la variable `$query` en`$searchForm->get('query')->getData()`.

## Toques finales y potencia turbo

Ya casi está. Hagamos el último retoque en la plantilla. Copia el icono SVG y colócalo debajo de todo el formulario. El posicionamiento CSS adecuado debería seguir funcionando, así que no necesitamos colocarlo entre las etiquetas de apertura y cierre del formulario.

Ahora ya podemos eliminar por completo el antiguo formulario. Actualiza la página de nuevo para ver nuestro formulario, y si lo buscamos, ¡todavía funciona! Tiene el mismo aspecto, pero ahora funciona completamente con formularios Symfony.

Por defecto, Symfony utiliza `TextType` para el campo, pero como esto es una búsqueda, seamos un poco más semánticos y cambiémoslo por un `SearchType` especial. En `PartSearchType`, sustituye `TextType` por `SearchType`. No ha cambiado nada a la primera, pero si empiezas a escribir, hay un pequeño y práctico botón X que te permite borrar fácilmente el campo de entrada.

## Activar Turbo en el sitio web

Si recuerdas, desactivé Turbo al principio de este curso para simplificar las cosas. Pero ahora es el momento de liberar su poder. Abre `app.js` y descomenta la línea Turbo en el archivo `assets/js`. Vuelve a actualizar la página.

Ahora, la navegación por el sitio se realiza a través de AJAX impulsado por Turbo. Puedes verlo en acción en la WDT al hacer clic en los enlaces. ¿Increíble aún? El envío de formularios también se realiza mediante Turbo, y los errores de formulario siguen funcionando perfectamente.

Si vas a Crear nueva parte de nave estelar e intentas enviar el formulario vacío, verás que se envió vía AJAX, pero también vemos los mensajes de error. Esto se debe a que pasamos todo el objeto `Form` a la plantilla, no sólo el `FormView`. Esto permite a Symfony y a Turbo renderizar todo correctamente en la petición posterior. Turbo hace que nuestra aplicación sea más rápida, fluida y envolvente. ¡Y esto es sólo el principio! Si quieres profundizar más, te recomiendo que eches un vistazo a nuestro [curso de Turbo] independiente (https://symfonycasts.com/screencast/turbo).

## Para terminar

¡Muy bien, amigos! Ya dominas oficialmente los fundamentos del componente Formulario de Symfony. Ya sabes cómo instalarlo y configurarlo, crear tipos de formularios, procesarlos con las funciones de ayuda de Twig y gestionar los envíos correctamente con`Form::handleRequest()`. Hemos visto cómo se enlazan los formularios con las entidades de Doctrine, cómo funciona la validación, cómo personalizar los campos y atributos, y cómo dar estilo a todo con los temas de formulario integrados en Symfony.

Estamos listos para construir formularios reales, listos para producción, con confianza. Así que, ¡a construir formularios increíbles! Son esenciales en tus aplicaciones. Y disfruta de la magia de los formularios Symfony este invierno.
