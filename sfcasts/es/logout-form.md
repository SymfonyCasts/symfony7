# Cómo mostrar un botón de cierre de sesión con logout_button()

Echemos un vistazo a nuestro sistema de cierre de sesión. Abre `src/Controller/SecurityController.php`. Tenemos
este esbozo de `logout()` donde solo definimos la ruta; el sistema de seguridad se encarga de todo
por completo. Pero fíjate en que hemos establecido `methods: 'POST'`, así que solo una petición POST puede realmente
cerrar tu sesión. Eso está genial: si una petición GET pudiera hacerlo, los escáneres de enlaces y similares podrían
cerrarte la sesión.

Ahora abre `config/packages/security.yaml`. Debajo del cortafuegos de `main`, configuramos
`logout`. Establecemos su `path` en nuestra ruta de cierre de sesión, y también configuramos `enable_csrf: true` —
otra cosa que siempre deberías hacer al cerrar sesión.

## El formulario de cierre de sesión creado a mano

A continuación, abre `templates/base.html.twig` y desplázate hacia abajo hasta encontrar el enlace de cierre de sesión. Aquí
está. Como el cierre de sesión requiere un POST, vamos a crear un formulario a mano. Dentro, mostramos el token CSRF,
generado con `csrf_token('logout')`. `logout` es el ID de token predeterminado que
usa el sistema de seguridad, así que es una de esas cosas que tienes que saber cuando creas
la plantilla. Luego, por supuesto, mostramos el botón de cierre de sesión.

## Hola `logout_button()`

En Symfony 8.2, todo esto se puede sustituir por una nueva función de Twig: `logout_button()`. Si
te fijas en el código PHP que lo muestra, verás que toma un `label`, cuyo valor por defecto es
`Log out`, y luego el cortafuegos `key`. También puedes pasar `attr` —atributos HTML para el
botón— y `formAttr` —atributos HTML para el propio elemento de formulario—. Y no
tienes que preocuparte por el ID del token CSRF, ni siquiera por la ruta a tu ruta de cierre de sesión. Lo sabe
todo automáticamente leyendo tu configuración.

Volviendo a la plantilla, dentro de ` `logout_button()` `, establece el primer argumento, la etiqueta, en ` `Logout``, tal y
como te mostramos a continuación. Tanto nuestro botón como el formulario tienen clases, así que tenemos que pasarlas como
atributos. Añade `attr` y, como esto contiene todos los atributos, necesita un segundo
nivel: `{class: '...'}`. Copia y pega ahí las clases del botón. Luego haz lo mismo
con `formAttr`, estableciendo `class` en `inline`. ¡Genial! Ahora elimina el formulario antiguo:

[[[ code('46b4d9ef40') ]]]

## Probémoslo

¡Probémoslo! En la app, inicia sesión como `picard@enterprise.space`, con la contraseña
`makeitso`. Haz clic con el botón derecho en el botón de cerrar sesión y elige «Inspeccionar» para ver qué se muestra.
Efectivamente, genera el mismo código HTML que teníamos antes, incluyendo el token CSRF correcto.

Para comprobar que funciona, haz clic en él… ¡y ya nos hemos desconectado!

A continuación, veamos cómo se ha mejorado la suplantación de identidad en Symfony 8.2.
