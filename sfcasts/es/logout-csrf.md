# Proteger el cierre de sesión con CSRF

Tenemos un pequeño problema con nuestro enlace de cierre de sesión. Puedes cerrar sesión simplemente
yendo a `/logout` en tu navegador. Esto significa que, en teoría, otra web
podría enviar esta petición en tu nombre, lo que te obligaría a salir de
tu sesión. Para verlo en acción, copia la dirección del enlace y pégala en
una nueva pestaña. Cierra esta pestaña y actualiza la original. Ahora ya
estamos desconectados.

Lo primero que debemos hacer es dejar de permitir `GET` para la ruta `/logout`.

Las peticiones que cambian el estado nunca deberían usar el método `GET`. Los navegadores, los rastreadores,
los previsualizadores de enlaces y otras herramientas pueden realizar peticiones `GET` automáticamente porque
se espera que sean seguras.

Recuerdo haber leído sobre alguien que creó una app web para controlar el abridor inteligente de la puerta de su garaje
. Quería una forma fácil de abrirla y cerrarla a distancia, así que creó una ruta `/toggle-garage-door`
. Al acceder a esa ruta, la puerta del garaje se abría si estaba cerrada, o se cerraba si estaba abierta.
Por desgracia, como aceptaba peticiones `GET`, una precarga del navegador o una vista previa de un enlace demasiado entusiasta podían
activarla automáticamente. Es gracioso… hasta que estás de vacaciones, compruebas que la puerta del garaje
está cerrada y, en cambio, tu navegador la abre.

Cerrar sesión no es tan dramático, pero se aplica el mismo principio. Al cerrar sesión se cambia el estado de la aplicación,
así que no debería ocurrir solo porque algo haya decidido seguir un enlace. Vamos a solucionarlo cambiando nuestro
enlace de cierre de sesión para que utilice una petición `POST`.

## Cambiar el enlace de cierre de sesión a una petición POST

En nuestro `SecurityController`, busca el atributo `Route` para el método `logout()`. Añade
`methods: 'POST'`. ¿Se ha solucionado? Veamos.

Vuelve a nuestra app, inicia sesión... Ahora haz clic en «Cerrar sesión»...

¡Error! Un error 405 «Método no permitido». ¿Por qué? Nuestro enlace de cierre de sesión es solo un enlace normal, así que usa `GET`.
Hemos solucionado el problema del cambio de estado, ¡pero ahora nuestra propia web no nos deja cerrar sesión!

¿Cómo podemos convertirlo en un enlace de envío? La forma más sencilla es convertirlo en un botón de envío dentro de un
formulario.

## Crear un formulario oculto para cerrar sesión

Ve a `base.html.twig`. Justo después del enlace de cierre de sesión, añade una etiqueta `<form>`. `action="{{ logout_path() }}"`
y `method="post"`. Por defecto, los formularios son elementos de bloque, así que añade `class="inline"` para convertirlo en un elemento en línea.

Dentro, añade un `<button>` y un `type="submit"`. Quiero que tenga los mismos estilos que los enlaces de al lado, así que añade
`class=""` y copia y pega las clases del enlace de arriba. ¿Y el texto del botón? `Logout`.

Ya está, elimina el antiguo enlace de cierre de sesión de arriba.

Vuelve al navegador y actualiza la página... Ahora haz clic en «Cerrar sesión»... ¡Efectivamente, ya hemos cerrado sesión!

## Personalizar el estilo del botón de cerrar sesión

Vuelve a iniciar sesión… y pasa el cursor por encima del enlace `Logout`, que ahora es un botón. Fíjate en que no tiene
el mismo cursor que los demás enlaces. Por defecto, los botones no tienen el mismo cursor que los enlaces.
Para que el usuario no note la diferencia, añade `cursor-pointer` a la clase del botón.

Actualiza la página... ahora el botón «Cerrar sesión» es totalmente indistinguible de los demás enlaces.

## Implementación de la protección CSRF

El hecho de que nuestra ruta de cierre de sesión requiera ahora una petición `POST` no significa que otros sitios no puedan 
activarla. Para evitarlo por completo, tenemos que implementar la protección CSRF para ella.

Abre `config/packages/security.yaml`. En la sección del cortafuegos de `main`, busca la clave `logout`.
Añade `enable_csrf: true`.

Probémoslo. Vuelve al navegador y haz clic en «Cerrar sesión»... Mmm, no ha pasado nada...
No nos ha desconectado… ¡pero eso es bueno! Para cerrar sesión necesitamos un token CSRF, pero
no hemos pasado ninguno. Esto es lo que pasaría si otra web intentara desconectarnos:
no podría.

Por supuesto, queremos que nuestro propio sitio pueda cerrar nuestra sesión...

Podemos hacerlo con un campo de entrada oculto en nuestro formulario de cierre de sesión. Será muy
parecido al de nuestro formulario de inicio de sesión. Así que abre `templates/security/login.html.twig`, busca
el campo oculto y cópialo.

En `base.html.twig`, busca el formulario de cierre de sesión y pégalo ahí dentro.

El valor del atributo `name` es importante. Comprobemos cuál debe ser
echando un vistazo a la configuración por defecto.

En tu terminal, ejecuta:

```terminal
symfony console config:dump security
```

Esta es la configuración predeterminada completa del bundle `security`... ¡y es enorme!
Redúcela ejecutando de nuevo el mismo comando, pero añadiendo `firewalls` al final:

```terminal-silent
symfony console config:dump security firewalls
```

Esto solo muestra la sección « `firewalls` ». Desplázate hacia arriba hasta que encuentres la clave « `logout` »...
«`csrf_parameter` » es lo que estamos buscando... y « `_csrf_token` » es el valor por defecto. Y eso es
lo que tenemos en nuestro formulario de cierre de sesión.

Quiero usar la protección CSRF sin estado, así que mantendré este atributo `data-controller`.
Para el valor, dentro de `csrf_token()`, usa `logout` como ID del token.

Ahora tenemos que habilitar la protección CSRF sin estado para este ID. Abre
`config/packages/csrf.yaml` y... ¡genial!, `logout` está habilitado por defecto.

¡Ya está! Vuelve al navegador, actualiza la página... y haz clic en «Cerrar sesión»... ¡Genial!
Ha funcionado, nos hemos desconectado, lo que significa que nuestra configuración de CSRF funciona como esperábamos.

A continuación, vamos a habilitar la función «recordarme» para nuestro formulario de inicio de sesión.
