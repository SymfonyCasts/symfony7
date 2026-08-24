# Salir de la suplantación de identidad y IS_IMPERSONATOR

Ahora mismo, hemos iniciado sesión como Janeway y nos estamos haciendo pasar por Picard. Pero en
producción, sin esta barra de herramientas de depuración web, no quedaría claro que te
estás haciendo pasar por otra persona.

¡Y la verdad es que esto ya me ha jugado una mala pasada! Es muy fácil suplantar a alguien, olvidarte
de que lo estás haciendo y luego hacer un montón de cosas en la web que
nunca deberías haber hecho como ese usuario.

Por eso me gusta dejar muy claro en producción que estás suplantando a alguien,
y también ofrecer una forma clara de salir de esa suplantación.

## El atributo « `IS_IMPERSONATOR` »

Lo primero que hay que averiguar es cómo podemos comprobar si estamos suplantando a alguien.

¿Te acuerdas de cuando, al principio del curso, hablamos de los atributos de seguridad integrados?
Abre la clase ` `AuthenticatedVoter` ` del componente ` `Security` `. Ya hablamos de
la mayoría de ellos, pero pasamos por alto este
atributo ``IS_IMPERSONATOR` `. Este es el atributo que compruebas con ` `is_granted()` ` para
ver si estás suplantando a alguien. Así es como podemos saberlo.

## Una gran advertencia en rojo

Cierra eso y abre `templates/base.html.twig`. Justo en la parte superior, justo debajo de la
etiqueta de apertura `<body>`, añade `{% if is_granted('IS_IMPERSONATOR') %}`... y ciérralo con
`{% endif %}`.

Dentro, voy a pegar un trocito de código que puedes encontrar en el script de abajo:

[[[ code('4721134241') ]]]

Y… voy a limpiarlo un poco.

Lo que hace esto es añadir un borde rojo alrededor de toda la página, además de un pequeño texto en
la esquina superior izquierda que dice a quién estamos suplantando.

Vuelve a la página de inicio y actualízala... ¡Ya está! Ahora queda muy claro. Gran
advertencia. Me gusta esto. Vayas a la página que vayas, queda claro que nos estamos haciendo pasar por
Jean-Luc Picard.

## Añadir un enlace para salir de la suplantación

A continuación, si nos estamos haciendo pasar por alguien, quiero cambiar este botón de cerrar sesión por un enlace de «salir de
la suplantación».

Sigue en `base.html.twig`, desplázate hacia abajo hasta donde se muestran los botones de iniciar y cerrar sesión
. Arriba... antes de comprobar si es `ROLE_USER`, añade
`{% if is_granted('IS_IMPERSONATOR') %}`. Y para el `if` de abajo, cámbialo por `elseif`.

Después, aquí abajo, copia el código HTML del enlace de inicio de sesión y pégalo dentro de nuestro nuevo `if` para que tenga un estilo
similar. En el texto, escribe «Salir de la suplantación de identidad».

Y para el `href`, podemos usar otra función de Twig: `impersonation_exit_path()`:

[[[ code('1dee17d592') ]]]

Por cierto, hay versiones de estas funciones para `url`. Hacen exactamente lo mismo,
pero generan una URL absoluta en lugar de solo la ruta.

Así que usa ` `impersonation_exit_path()` ` sin argumentos. Y ya está.

Vuelve al navegador y actualiza la página. ¡Genial! Ahí está nuestro enlace «Salir de la suplantación».
Pasa el cursor por encima y, abajo a la izquierda, verás el enlace especial que va
a activar la salida de la suplantación.

## Redirección tras salir

Un pequeño matiz. Imagina que estamos en una página que no es la página de inicio… como la página «Piezas»… y hacemos clic en «Salir de
la suplantación». Se ha salido correctamente: hemos vuelto a ser nuestro usuario real, Janeway, y
ese borde rojo ha desaparecido. Pero seguimos en la página de piezas; no nos ha redirigido a la
página de inicio.

Si te acuerdas, en `config/packages/security.yaml`, configuramos esto:
`target_route`. Eso no se activa cuando sales de la suplantación de identidad.

Pero el primer argumento de `impersonation_exit_path()` es la ruta a la que quieres enviar al
usuario tras salir. Así que lo que me gusta hacer es enviarlo de vuelta al lugar donde eligió
el usuario suplantado: `impersonation_exit_path(path('app_user_admin_index'))`:

[[[ code('f514b27909') ]]]

Así, si quieren, pueden cambiar a otra persona.

Pruébalo. Ve manualmente a `/admin/user`... busca a Picard y pulsa «cambiar a». Todo
eso funciona. Ahora ve a «Parts»... y pulsa «Salir de la suplantación». ¡Genial! Ya estamos de vuelta en
la lista de administradores para cambiar a otra persona; más o menos donde empezamos.

Y eso es todo sobre la suplantación de identidad. Siguiente tema: ¡la limitación de inicios de sesión!
