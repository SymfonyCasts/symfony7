# Organización de los campos del formulario

Dediquemos un momento a discutir la organización de los campos de nuestro formulario, en particular el campo Notas, que se encuentra justo en medio de todo. Lo ideal sería que los campos obligatorios fueran los primeros, y que los opcionales esperaran pacientemente su turno al final.

Por defecto, Symfony muestra los campos del formulario en el orden en que están definidos dentro del tipo de formulario, sin magia ni sorpresas. Así que, si quieres un orden diferente, la solución más sencilla, como ya habrás adivinado, es también la menos emocionante: simplemente reordena los campos en el tipo de formulario.

Intentémoslo. Mueve el campo Notas al final, justo antes del botón de envío. Actualiza la página y ¡voilà! El campo Notas está ahora al final. ¡Problema resuelto!

## La opción de campo de formulario `priority` 

Pero, ¿y si no quieres mover físicamente los campos, especialmente cuando el orden debe cambiar dinámicamente en determinadas condiciones? Ahí es donde la opción `priority` viene al rescate.

Cada campo de formulario tiene un ajuste `priority` - el valor por defecto es 0. Si añadimos`priority` al campo `Starship` y lo establecemos en 10, Symfony renderizará ese campo antes. Un número mayor significa que se renderiza primero, mientras que un número menor significa que se renderiza después. Incluso puedes asignar una prioridad negativa si quieres que un campo se renderice más cerca del final.

Este truco es muy útil cuando el orden de los campos depende de condiciones que no pueden resolverse fácilmente reorganizando el código... O cuando quieres evitar grandes diferencias Git.

## Personalizar la disposición de los campos

Pero, ¿y si ordenar no es suficiente? ¿Y si quieres un diseño personalizado? ¿Podemos poner los campos cortos Nombre y Precio en la misma línea, por ejemplo? ¡La respuesta es sí! Symfony proporciona varias funciones de ayuda para renderizar formularios. Ya conocemos algunas de ellas.

Hasta ahora, hemos confiado en `{{ form_widget(form) }}` para renderizar todos los campos automáticamente. Pero cuando quieras tener más control, puedes renderizar los campos manualmente, uno a uno.

En lugar de renderizar todo el formulario a la vez, rendericemos el primer campo utilizando `form_row()` y pasemos a `form.starship` -seguimos queriendo que ese campo se renderice primero-. Después, añade un poco de HTML para crear una cuadrícula utilizando las clases CSS de Tailwind: `grid` `grid-cols-2` y `gap-4`.

Dentro, renderiza `form_row()` de nuevo para `form.name` y `form.price`. No te olvides de los campos restantes antes del botón hardcoded - simplemente añade`form_rest()` y pasa la variable `form` para renderizar cualquier campo que no hayamos renderizado aún.

Actualiza la página y verás que los campos Nombre y Precio están ahora felizmente uno junto al otro.

## Alinear los botones de envío en la misma línea

Pero espera, ¿podemos hacer lo mismo con los botones? ¡Claro que sí! De vuelta a la plantilla, representa el campo Notas con `form_row()` debajo de la cuadrícula, pasando `form.notes`. A continuación, justo antes del botón codificado, representa `form_row()` de nuevo pasando `form.createAndAddNew`.

Esta función `form_end()` renderiza los campos restantes y cierra la etiqueta del formulario. Para elegir dónde queremos renderizar los campos restantes, podemos utilizar `form_rest(form)`. Añadiré un comentario para explicarlo:

> Cualquier cosa que quieras añadir después de que se muestren todos los campos, pero antes del cierre </form>

## Visualización de campos y tratamiento de errores

¡Vuelve a actualizar la página! Hmm, los botones siguen sin estar alineados. Si inspeccionas el HTML, verás por qué: `form_row()` muestra un contenedor completo, que incluye el campo real, su etiqueta y también los errores, si los hay. Todo ello se envuelve con esta etiqueta `div`.

Normalmente, eso es estupendo para los campos, pero no en nuestro caso. Queremos deshacernos de esa envoltura extra de `div` y mostrar sólo el campo en sí, el botón en este caso. ¿La solución? En Symfony, los campos de formulario, es decir, los elementos HTML `input`, `button`, `select`, se denominan widgets. Así que, para renderizar sólo el elemento botón, sustituye `form_row()`para el botón por `form_widget()`.

Actualiza de nuevo y... ¡bien! Por fin los botones están alineados.

Puedes renderizar manualmente todos los componentes de un `form_row()` de esta forma. `form_widget()` renderiza el elemento campo, `form_label()` renderiza el elemento etiqueta, `form_errors()` renderiza una lista de errores (si los hay), y `form_help()` renderiza el texto de ayuda (si lo hay).

## Mostrar los errores globales del formulario que faltan

Pero ten cuidado: hemos introducido un problema. Corrompe de nuevo el token CSRF... y envía el formulario... Vemos los errores de nombre y precio, pero no el mensaje de error CSRF esperado.

En la barra de herramientas de depuración web, la pestaña del validador muestra 2 errores, pero la pestaña del formulario muestra 3. ¿Qué ocurre? Abre el panel del validador. Vale, vemos nuestros errores de nombre y precio.

Ahora ve a la pestaña formularios. Aquí vemos 3 errores: los 2 errores de campo y el error CSRF adjunto al propio formulario. Éste es un error de validación a nivel de formulario, por eso no aparece en el panel del validador.

En nuestra plantilla, cuando antes utilizábamos form_widget(form), Symfony renderizaba automáticamente los errores a nivel de formulario, o globales, por nosotros. Pero ahora que estamos renderizando los campos manualmente, tenemos que acordarnos de renderizar esos errores globales también manualmente. Así que, justo después de `form_start()`, añade `form_errors(form)`.

Bien, vamos a intentarlo de nuevo. De nuevo en el navegador, actualiza la página... corrompe el token CSRF... y envía.

¡Genial! Vuelven todos los errores esperados.

## Cómo hacer que el formulario sea más fácil de usar

Para hacer que el formulario sea más fácil de usar y ayudar a los usuarios a evitar errores de validación por adelantado, puedes añadir sugerencias directamente al campo utilizando una opción especial de`help`. Por ejemplo, digamos a los usuarios que las piezas gratuitas no están permitidas añadiendo una opción `help` al campo Precio con un mensaje significativo:

> ¡No permitimos piezas gratuitas! Por favor, fija un precio

Cuando actualices el formulario, verás este útil mensaje en un sutil texto gris debajo del campo.

## Añadir atributos de campo de formulario en la plantilla

¿Recuerdas cuando añadimos clases CSS al botón Enviar en el tipo de formulario? Eso funciona, pero no es lo ideal - los diseñadores probablemente no quieran tocar tu código PHP, y puede que ni siquiera sepan lo que es un tipo de formulario, o cómo funciona en una aplicación Symfony. Por lo tanto, las decisiones de estilo realmente no pertenecen ahí.

En su lugar, traslademos esos estilos a la plantilla para que nuestros diseñadores puedan cambiarlos fácilmente. Lo comentaré en el tipo de formulario, copiaré la larga línea de clases CSS y me iré a la plantilla. Para la llamada a `form_widget(form.createAndAddNew)`, añade un segundo argumento, un hash de opciones.

Aquí puedes pasar las mismas opciones que pasas en el tipo de formulario. Así, queremos una opción`attr` establecida en otro hash. Dentro, añade la opción `class`... y pega las clases CSS que copiamos antes. 

Cuando actualices la página, verás el mismo estilo, pero con una separación más limpia. Recuerda que las opciones definidas en las plantillas Twig sobrescriben todo lo establecido en el tipo de formulario.

## Resaltar los campos obligatorios del formulario con CSS

Los mensajes de ayuda son geniales, pero a veces quieres que los campos obligatorios destaquen visualmente. Añadamos un pequeño truco CSS para mostrar un asterisco rojo junto a cada campo obligatorio de nuestro formulario. Abre `assets/style/app.css` y al final, copia/pega el siguiente fragmento del script de abajo:

Actualiza el formulario... ¡Genial! Ahora todos los campos obligatorios tienen un bonito asterisco rojo. No sólo para este formulario, sino para todos los formularios de nuestra aplicación. ¡Genial!

Y así de fácil, has pasado de un formulario predeterminado de Symfony a un diseño de formulario totalmente personalizado, fácil de diseñar y a prueba de errores. No está mal para un capítulo, ¿verdad?

A continuación, vamos a acelerar la creación de formularios para diferentes operaciones CRUD en nuestras entidades aprovechando MakerBundle de nuevo. ¡Permanece atento!
