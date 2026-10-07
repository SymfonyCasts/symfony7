# Redirigir tras iniciar sesión con _target_path

Cuando no hemos iniciado sesión e intentamos acceder a un recurso protegido —por ejemplo, `/admin/user` —
nos redirige a la página de inicio de sesión. Inicia sesión como nuestro administrador, `janeway@starfleet.space`
con la contraseña `coffeeblack`... y, tras iniciar sesión, nos redirige al recurso al que
intentábamos acceder. Esa es la funcionalidad predeterminada.

Pero fíjate en esto. Cierra sesión. Digamos que estamos en una página de naves espaciales y hacemos clic en «Iniciar sesión». Esta
vez, inicia sesión como `picard@enterprise.space`, con la contraseña `makeitso`.

Recuerda: cuando hicimos clic en «Iniciar sesión», estábamos en una página de naves espaciales. Pero ahora, tras iniciar
sesión, hemos vuelto a la página de inicio. Estaría genial que volviéramos a la página en la que estábamos
cuando hicimos clic en «Iniciar sesión».

## El callejón sin salida de `use_referer` 

Ve a la terminal y echa un vistazo a las opciones de configuración de nuestro formulario de inicio de sesión:

```terminal
symfony console config:dump security firewalls
```

Desplázate hacia arriba hasta encontrar « `form_login` ». Aquí está. Tenemos « `default_target_path` », y está
configurado en nuestra raíz: la página de inicio. Por eso acabamos ahí después de iniciar sesión.

Pero también tenemos esta opción `use_referer`. Vamos a echarle un vistazo. En
`config/packages/security.yaml`, en la sección `form_login`, cambia `use_referer` por `true`:

[[[ code('c248b104d3') ]]]

Vuelve al navegador y cierra sesión... ¡Ups!, tengo un error tipográfico en mi configuración... No es `user_referer`,
sino `use_referer`.

Ahora actualiza la página... y visita la página de una nave espacial. Ah, asegúrate primero de haber cerrado sesión.

Haz clic en «Iniciar sesión» en esta página e inicia sesión como `picard@enterprise.space`, con la contraseña `makeitso`.

Y… ya estamos de vuelta en la página de inicio.

No ha funcionado... `use_referer` le indica a Symfony que redirija al encabezado HTTP `Referer` que los navegadores pasan
cuando haces clic en los enlaces. Cuando visitamos la página de inicio de sesión por primera vez, está configurado correctamente... pero
en cuanto enviamos el formulario de inicio de sesión, lo hemos perdido... Probablemente podrías hacer que funcionara
con algún truco ingenioso, pero vamos a hacerlo más sencillo.

## Pasar `_target_path` en el enlace

Vuelve al IDE y elimina `use_referer`.

Echa otro vistazo a la configuración en la terminal. Hay otra opción: `target_path_parameter`, y está
configurada como `_target_path`. Podemos establecerla en la sesión o como parámetro de consulta.
Vamos a usar el parámetro de consulta.

Abre `templates/base.html.twig` y busca dónde generamos el enlace de inicio de sesión
—justo aquí—. Estamos generando una ruta hacia la ruta `app_login`. Añade unos parámetros:
`_target_path` establecido en `app.request.pathInfo`:

[[[ code('f4bff617c4') ]]]

Nuestra ruta `app_login` no requiere ningún parámetro de ruta, así que cualquier dato adicional se añade como parámetro de consulta.
¡Justo lo que queríamos!

En el navegador, cierra sesión primero y luego haz clic en una nave espacial. Ahora pasa el cursor por encima de «Iniciar sesión»:
abajo, a la izquierda, puedes ver que `_target_path` está configurado como la ruta de esta página.
Haz clic en «Iniciar sesión» y lo verás aparecer en la barra de direcciones. Eso le indica a Symfony que redirija allí
después.

`picard@enterprise.space`, `makeitso` —¡y así lo hace!

## Un nombre de parámetro más bonito

Lo único que no me gusta es que `_target_path` suena un poco a cosa interna. Quizá
sea solo cosa mía. Pero hay una forma de personalizarlo: la opción `target_path_parameter`
establece qué parámetro queremos usar.

Vuelve a « `security.yaml` » y configura « `target_path_parameter` » como « `referrer` »:

[[[ code('e2db3fe628') ]]]

Y en `base.html.twig`, usa el mismo nombre:

[[[ code('46428f3036') ]]]

Ve a una página de naves espaciales. Ahora, si hacemos clic en «Iniciar sesión», efectivamente, estamos usando `referrer` —
y creo que queda un poco mejor. `picard@enterprise.space`, contraseña
`makeitso`, y funciona.

## Un enlace canónico para los motores de búsqueda

Otra cosita más. Como ahora cada página tiene un enlace de inicio de sesión único, queremos
decirles a los motores de búsqueda que no rastreen todos y cada uno de ellos. La mejor forma de hacerlo es con un
enlace canónico.

En `base.html.twig`, en el archivo `head`, añade un bloque `metadata`... Déjalo vacío aquí:

[[[ code('a735c732ef') ]]]

Ahora, dentro de `templates/security/login.html.twig`, sobrescribe ese bloque y añade una
etiqueta`link` con `rel="canonical"`. La `href` tiene que ser una URL absoluta, así que
génala con `url()` y la ruta `app_login`:

[[[ code('0aa21f2ad1') ]]]

Seguro que los motores de búsqueda son lo suficientemente inteligentes como para no necesitar esto, pero es una buena práctica ser explícito.

A continuación: veamos cómo podemos iniciar sesión con un correo electrónico o con un nuevo campo de nombre de usuario
que añadiremos a nuestros usuarios.
