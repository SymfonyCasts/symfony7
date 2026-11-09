# Protección contra la suplantación de identidad con POST y CSRF

Echemos un vistazo al sistema de suplantación de identidad de nuestra web. Inicia sesión como superadministrador:
`janeway@starfleet.space`, contraseña `coffeeblack`. Ve a `/admin/user` para ver una lista de
todos los usuarios de nuestra web, cada uno con una opción «cambiar a». Tenemos un mecanismo de validación que se
asegura de que no podamos cambiar a nuestra propia cuenta; por eso no aparece «cambiar a» en mi propia fila.

Pero pasa el cursor por encima de uno de estos enlaces y fíjate en la esquina inferior izquierda de tu navegador. Tenemos
unos cuantos problemas. Primero, es una petición GET, así que cualquier cosa que siga a los enlaces, como que el navegador
precargue páginas, podría activarla. Segundo, el correo del usuario aparece justo ahí en la URL,
así que acaba en sitios como los registros de tu servidor web y de tu proxy. Y tercero, no hay protección contra CSRF
. Symfony 8.2 nos ofrece una forma de resolver los tres. ¡Vamos a implementarlo!

## Una ruta dedicada a la suplantación de identidad

Primero, necesitamos una ruta. En `src/Controller/SecurityController.php`, tenemos el stub `logout()`
. En realidad nunca se llama; solo existe para que tengamos un sitio donde poner el
atributo`#[Route]`. Podríamos añadir aquí una ruta de suplantación de la misma manera. Pero una
alternativa más limpia es configurarla en `config/routes.yaml`. Debajo de las rutas existentes,
añade `app_impersonate_user`, con `path: /impersonate` y `methods: [POST]`:

[[[ code('306a98292e') ]]]

No necesitamos un controlador, porque el sistema de suplantación se encarga de todo,
igual que ocurre con el cierre de sesión. Y tampoco necesitamos ningún `access_control` para esta ruta;
una vez más, el sistema de suplantación se encarga de eso. Lo único que necesitamos es una ruta que
requiera el método POST.

## Configuración `switch_user`

Ahora abre `config/packages/security.yaml` y busca la configuración `switch_user`. `target_route`
le indica adónde redirigirte tras el cambio (la página de inicio) y `role` es el rol personalizado
que necesitamos para suplantar la identidad. Añade `path` configurado para nuestra nueva ruta, `app_impersonate_user`. Ahora
la suplantación de identidad solo se produce en esa ruta y, como requiere POST, también lo hace
la suplantación. A continuación, activa CSRF con `enable_csrf: true`:

[[[ code('cb5671d7a1') ]]]

## La función `impersonation_button()` 

Como ahora se trata de una petición POST con CSRF, necesitamos un formulario. Y al igual que la
función Twig ``logout_button()` ` del capítulo anterior, ahora también hay una
función ``impersonation_button()` `.

Abre `templates/user_admin/index.html.twig` y desplázate hacia abajo hasta donde mostramos el
enlace de suplantación. Estamos usando el `impersonation_path()` original para el enlace «cambiar a».
Arriba, añade `impersonation_button()`, que funciona de forma muy parecida al botón de cerrar sesión.
Si le echas un vistazo, verás que toma la etiqueta, el identificador de usuario y, a continuación, un
URI de destino opcional: adónde enviar al usuario después de que se haya suplantado. Si no lo configuras,
se recurre a la ruta `app_homepage` de nuestra configuración. Y, igual que con el botón de cerrar sesión,
puedes pasar `attr` para el botón y `formAttr` para el formulario.

Pon la etiqueta en `switch to`. A continuación, añade `attr` y, como se trata del conjunto completo de atributos HTML,
configura `class` en su interior. Copia todas las clases del enlace antiguo y pégalas
ahí. Ahora elimina el enlace antiguo:

[[[ code('2af01eff95') ]]]

## La función « `impersonation_exit_button()` »

El otro sitio donde lo necesitamos es en el enlace de salida, en `templates/base.html.twig`. Desplázate un
poco hacia arriba… aquí está. `is_granted('IS_IMPERSONATOR')` significa que ahora mismo
nos estamos haciendo pasar por alguien, y dentro tenemos un enlace creado con
`impersonation_exit_path()`. Arriba, añade `impersonation_exit_button()`. Este solo
necesita una etiqueta, luego el URI de destino opcional, además de `attr` y `formAttr`. Estamos
pasando un URI de destino a `impersonation_exit_path()`, así que haremos lo mismo con el botón.

Pon la etiqueta en `Exit Impersonation`. El segundo argumento es la ruta de abajo,
que nos lleva de vuelta a la página de administración de usuarios cuando salimos. Por último, pasa `attr` con `class`, y luego
copia las clases del enlace antiguo y pégalas ahí dentro:

[[[ code('89d7e19b97') ]]]

Ahora borra el enlace antiguo.

Ah, y acabo de darme cuenta de que nos hemos olvidado de algo en el`impersonation_button()` del administrador de usuarios. Hemos pasado la etiqueta y los atributos, ¡pero no el
identificador de usuario! Añade `user.userIdentifier` como segundo argumento.

## Probémoslo

Vuelve a la página de administración de usuarios y actualiza la página. Pasa el cursor por encima de uno de los dos interruptores; ahora es un botón...
Inspecciona el código y verás que es un formulario completo, con su `action`
apuntando a `/impersonate`. Hay un campo que contiene el usuario al que nos estamos cambiando;
como se trata de un POST, eso es lo que mantiene el identificador fuera de la URL. Luego tenemos el
token CSRF y el botón de envío. ¡Perfecto!

Cierra eso e intenta suplantar a Picard: haz clic en «cambiar a»... ¡perfecto! Estamos en la página de inicio y
hemos suplantado su identidad con éxito. Ahora haz clic en «Salir de la suplantación» y deberíamos volver
a la página de administración de usuarios. Y... ¡ya está! Ha funcionado. ¡Genial!

A continuación, ¡vamos a ver algunas mejoras en el verificador de usuarios de Symfony 8.2!
