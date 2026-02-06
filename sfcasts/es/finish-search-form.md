# Enviar un formulario mediante GET

Ahora tenemos un formulario de búsqueda. Pero hay un pequeño problema. Se envía a través de POST porque es el método por defecto de Symfony. Normalmente, eso está muy bien. Pero para un formulario de búsqueda, POST no es la mejor opción. ¿Por qué? Porque cuando buscamos, queremos que la consulta sea visible en la URL. Hace que los resultados de la página se puedan compartir y marcar como favoritos. Además, es como nuestro antiguo formulario. Así que vamos a modificarlo y cambiar nuestro formulario a GET.

Para empezar, abre nuestra clase `PartSearchType`. En `setDefaults()`podemos ajustar las opciones de nuestro formulario. En la matriz vacía, añade una nueva clave llamada`method` y establécela en `Request::METHOD_GET`. En realidad, es sólo una forma rara de escribir la cadena `GET`:

[[[ code('a7bd19f947') ]]]

Tómate un momento para enviar el nuevo formulario con "Legacy". Fíjate en la URL. Los parámetros de consulta parecen un poco locos. En lugar del simple `query=legacy`, vemos `parts_search[query]=legacy` - esos `%5B` y `%5D`'s son simplemente `[]` codificados .

¿Qué ocurre? Por defecto, los formularios Symfony envían los datos como matrices anidadas, con la clave del nombre del formulario. Esto ayuda a evitar colisiones de nombres cuando tienes varios formularios en la misma página. Es un movimiento inteligente, pero para nuestro formulario de búsqueda, es exagerado y, seamos sinceros, un poco feo.

## Limpiar los parámetros de consulta

Esto es lo que haremos. Queremos aplanar los parámetros de consulta. Para deshacernos de este prefijo de nombre de formulario, podemos anular un método especial en el tipo de formulario.

De vuelta en `PartSearchType`, al final de esta clase, pulsaré`Cmd` + `N` y elegiré "Anular métodos", luego elegiré el método `getBlockPrefix()`. Dentro de él, simplemente devuelve una cadena vacía:

[[[ code('29ddb690bb') ]]]

Esto le dice a Symfony que no anteponga a nuestros campos el nombre del formulario. Vuelve a buscar y ¡bam! Tenemos parámetros de consulta limpios y planos. ¡Mucho mejor!

Pero aún no hemos llegado al final. Tenemos esta consulta `_token` en la URL. Es la protección CSRF haciendo su trabajo, pero es innecesaria para un formulario de búsqueda basado en GET. Sólo estamos filtrando una lista de productos, así que vamos a desactivarla para este formulario en `setDefaults()`.

Para ello, vuelve a `PartSearchType` y añade una nueva opción llamada`csrf_protection`, estableciéndola en `false`:

[[[ code('3d74bd13e7') ]]]

Vuelve a enviar el formulario y ¡bingo! Sólo tenemos el parámetro `query` en la URL, limpia e intencionadamente.

## Hacer que el campo de búsqueda sea opcional

¿Qué pasa si intentamos enviar el formulario con una consulta vacía? Hmm, un error de validación HTML5... Puede que eso esté bien para tu aplicación, pero yo preferiría que una búsqueda vacía significara "Muéstramelo todo". Podemos desactivar la validación utilizando el atributo `novalidate`en la etiqueta del formulario, pero una opción mejor es simplemente hacer que el campo sea opcional. Para el campo `query`, añade otro parámetro llamado `required`y ajústalo a `false`:

[[[ code('1003d7f4c3') ]]]

Actualiza la página, y si intentamos buscar con una entrada vacía, simplemente muestra la lista completa.

Sinceramente, ahora mismo, la búsqueda funciona debido a cierta lógica de negocio heredada en `PartController`. Concretamente, estamos obteniendo la consulta directamente de la petición con `$request->query->getString('query')`:

[[[ code('f7347b4aa1') ]]]

Y para un pequeño formulario de búsqueda, eso está totalmente bien. Pero ya que estamos aprendiendo el componente Formulario de Symfony, hagámoslo a la manera de Symfony.

## Manejo adecuado de formularios en Symfony

Comenta esa línea. A continuación, inicializa una nueva variable `query` con el valor `null`. A continuación, dile al formulario que gestione la petición con`$searchForm->handleRequest($request)`:

[[[ code('3e70a3812b') ]]]

Añade nuestra comprobación habitual de envío de formularios. `if ($searchForm->isSubmitted()``&& $searchForm->isValid())` . El campo `query` se considera no mapeado porque el formulario no está asociado a un objeto. Dentro de `if`, establece la variable`$query` en `$searchForm->get('query')->getData()`:

[[[ code('1032103be5') ]]]

Esto captura el campo de consulta y recupera su valor enviado.

***TIP
Para obtener todos los datos del formulario a la vez, como una matriz, puedes utilizar`$searchForm->getData()`. 
***

## Toques finales y potencia turbo

Ya casi está. Hagamos el último retoque en la plantilla. Copia el icono SVG y colócalo debajo de todo el formulario. El posicionamiento CSS adecuado debería seguir funcionando, así que no necesitamos colocarlo entre las etiquetas de apertura y cierre del formulario. Ahora ya podemos eliminar por completo el antiguo formulario:

[[[ code('1acdeb3ea7') ]]]

Actualiza la página de nuevo para ver nuestro formulario, y si lo buscamos, ¡todavía funciona! Tiene el mismo aspecto, pero ahora funciona completamente con Symfony Forms.

De vuelta en `PartSearchType.php`, una última mejora. Por defecto, si no especificas el tipo de campo, Symfony utiliza `TextType`. Pero como se trata de un campo de búsqueda, seamos un poco más semánticos y cambiémoslo por uno especial `SearchType`:

[[[ code('536137acbc') ]]]

De vuelta en el navegador, actualiza de nuevo la página. A primera vista no ha cambiado nada, pero si empiezas a escribir, hay un pequeño y práctico botón X que te permite borrar fácilmente la entrada.

## Activar Turbo en el sitio web

Si recuerdas, desactivé Turbo al principio de este curso para simplificar las cosas. Pero ahora es el momento de desatar su poder. Abre `assets/app.js` y descomenta `import '@hotwired/turbo';`:

[[[ code('2db26c9d5e') ]]]

Ahora, la navegación por el sitio se realiza mediante AJAX con Turbo. Puedes verlo en acción en la barra de herramientas de depuración web al hacer clic en los enlaces. El envío de formularios también se realiza mediante Turbo, y los errores de formulario siguen funcionando perfectamente.

En la lista de piezas, si vas a "Crear nueva pieza" e intentas enviar el formulario vacío... Sí, es una petición AJAX y seguimos viendo los errores de validación esperados. Turbo hace que nuestra aplicación sea más rápida, fluida e inmersiva. ¡Y esto es sólo el principio!

***SEEALSO
Si quieres profundizar más, te recomiendo que eches un vistazo a nuestro [curso Turbo] independiente (https://symfonycasts.com/screencast/turbo).
***

## Para terminar

¡Muy bien, amigos! Ya dominas oficialmente los fundamentos del componente Formulario de Symfony. Ya sabes cómo instalarlo y configurarlo, crear tipos de formularios, procesarlos con las funciones de ayuda de Twig y gestionar los envíos correctamente con `Form::handleRequest()`. Hemos visto cómo se enlazan los formularios con las entidades Doctrine, cómo funciona la validación, cómo personalizar los campos y atributos, y cómo dar estilo a todo con los temas de formulario Symfony incorporados.

Estamos listos para construir formularios reales, listos para producción, con confianza. Así que, ¡a construir formularios increíbles! Son esenciales en tus aplicaciones. Y disfruta de la magia de los formularios Symfony.

hasta la próxima, ¡feliz programación!
