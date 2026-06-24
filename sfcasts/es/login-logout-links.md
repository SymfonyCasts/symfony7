# app.user y los enlaces de inicio y cierre de sesión

Antes, cuando creamos nuestro formulario de inicio de sesión, hablamos brevemente de esta variable de Twig: `app.user`
Vamos a profundizar un poco más en ella. `app` es una variable global de Twig, lo que significa
que está disponible en todas nuestras plantillas. Contiene mucha información útil sobre
la petición actual, incluido el usuario actual (si está disponible).

En `base.html.twig`, justo antes del bloque «body», muestra su contenido con `{{ dump(app.user) }}`.

Vuelve al navegador y actualiza la página de inicio. Se muestra el contenido de `null`. Tiene sentido, ya que
aún no hemos iniciado sesión. Ve a `/login` e inicia sesión con nuestro buen amigo `picard@enterprise.space`,
contraseña: `makeitso`. Volvemos a la página de inicio y ahora el volcado contiene una instancia
de nuestro objeto `User`. Concretamente, el objeto de usuario de Jean-Luc. Esta es una forma rapidísima
de acceder a cualquier información sobre el usuario que haya iniciado sesión actualmente. Pero también es una forma fácil
de comprobar si un usuario ha iniciado sesión. Si `app.user` es nulo, el usuario no ha iniciado sesión.
Si no es nulo, el usuario ha iniciado sesión.

## Añadir enlaces de inicio y cierre de sesión

Vamos a usar la variable ` `app.user` ` para añadir enlaces de inicio y cierre de sesión en
la parte superior de nuestra página. Vuelve a `base.html.twig` y, primero, elimina el enlace `dump()`.

Busca el enlace «Contacto»; añadiremos nuestros nuevos enlaces justo después de él. Empieza creando un bloque «if»
utilizando `{% if app.user %}`. A continuación, añade una instrucción `{% else %}`, seguida de un `{% endif %}`
para cerrar el bloque. Copia el enlace «Contacto» de arriba para asegurarte de que el estilo sea coherente. Pégalo
dentro de `if`. Aquí, `app.user` es verdadero, lo que significa que el usuario ha iniciado sesión, así que queremos
mostrar el enlace de cierre de sesión. Cambia el texto del enlace a «Cerrar sesión» y su href a `{{ path('app_logout') }}`.
Recuerda que esta ruta `app_logout` proviene del método `logout()` de nuestro `SecurityController`.

A continuación, copia el enlace «Cerrar sesión» y pégalo dentro de `else`. Cambia `path` por `app_login` y el texto
del enlace por «Iniciar sesión».

Ya está, ¡vamos a probarlo!

Actualiza la página de inicio... Seguimos conectados y, efectivamente, vemos el enlace de cierre de sesión. Haz clic en él...
nos hemos desconectado y ahora vemos el enlace de inicio de sesión. Haz clic en él... y vuelve a iniciar sesión...

¡Genial! ¡Nuestra experiencia de usuario ha mejorado mucho!

## Generar el enlace de cierre de sesión

Hay otra forma de generar el enlace de cierre de sesión. Vuelve a `base.html.twig` y sustituye `path('app_logout')`
por `logout_path()`. Fíjate en que también hay una función `logout_url()`. La diferencia es que `logout_path()`
genera una ruta absoluta, como `/logout`, mientras que `logout_url()` genera una URL absoluta, como
`https://starshop.dev/logout`. En la mayoría de los casos, `logout_path()` es suficiente.

Vuelve al navegador... actualiza la página... pasa el cursor por encima del enlace «Cerrar sesión»... y, en la esquina inferior izquierda, el enlace
sigue siendo `/logout`.

Quizá te preguntes para qué sirve esta función. ¿Por qué usaría `logout_path()` en lugar de
`path('app_logout')`? Para la mayoría de las apps, no hay diferencia. En apps avanzadas con varios cortafuegos,
al usar `logout_path()` se detecta automáticamente el cortafuegos actual y se genera el enlace de cierre de sesión correcto. 

Haz clic en la función para ver su definición en `LogoutUrlExtension`. `getLogoutPath()` acepta un parámetro `$key`, 
que es la clave del cortafuegos definida en `config/packages/security.yaml`, en la sección de cortafuegos.
Al pasar una clave, se pueden generar enlaces de cierre de sesión para diferentes cortafuegos.

## `ROLE_USER` y `is_granted()`

Hay otro método habitual para comprobar si un usuario ha iniciado sesión. Nuestros usuarios tienen el concepto de «roles». En la
barra de herramientas de depuración web, si pasas el cursor por la pestaña «Usuario», verás que nuestro usuario actual tiene un rol: `ROLE_USER`. Hemos
configurado que todos los usuarios autenticados tengan este rol.

Volviendo a nuestra instrucción « `if` » en `base.html.twig`, sustituye `app.user` por `is_granted('ROLE_USER')`. Esta función
comprueba si el usuario actual tiene el rol especificado. Solo los usuarios autenticados tendrán este rol.

Si actualizamos la página de inicio, todo sigue funcionando como se espera. Podemos cerrar sesión... iniciar sesión... y los enlaces
cambian como se espera.

Profundizaremos más en los roles y en `is_granted()` en un capítulo futuro.

Todavía nos queda un pequeño problema de seguridad con nuestro enlace de cierre de sesión. ¡Vamos a solucionarlo ahora mismo!
