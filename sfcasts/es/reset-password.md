# Restablecer una contraseña olvidada

En cuanto tengas un sistema de autenticación que requiera una contraseña, necesitarás una forma de que los usuarios
puedan recuperar una contraseña olvidada. Vamos a crear el sistema estándar de «contraseña olvidada»
—y hay un paquete para ello—. En la terminal, ejecuta:

```terminal
symfony console make:reset-password
```

Este generador necesita unos cuantos paquetes: el [SymfonyCasts ResetPasswordBundle](https://github.com/SymfonyCasts/reset-password-bundle) y
[Symfony Mailer](https://symfony.com/doc/current/mailer.html), porque enviamos por correo esos enlaces para restablecer la contraseña. Copia ese comando,
añádele al principio `symfony` y pégalo:

```terminal-silent
symfony composer require symfonycasts/reset-password-bundle symfony/mailer
```

Ya están instalados los dos. Voy a borrar la pantalla y a ejecutar de nuevo el «maker»:

```terminal-silent
symfony console make:reset-password
```

¿A qué ruta deberían ser redirigidos los usuarios después de que su contraseña se haya
restablecido correctamente? Por defecto es `app_home`, pero la ruta de nuestra página de inicio es `app_homepage`. A continuación, el
correo electrónico del remitente —es decir, nuestra app la que envía el correo—; así que usa `support@starfleet.space`,
con el nombre `Starfleet Support`. ¿Pruebas unitarias? No.

Esto genera un montón de archivos, y lo primero que nos dice es que creemos una
migración:

```terminal
symfony console make:migration
```

Búscala en « `migrations/` » en tu IDE y pon como descripción
«`add reset_password_request table` »:

[[[ code('1fef97f38c') ]]]

A continuación, vuelve a cargar los fixtures:

```terminal
symfony console foundry:load-fixtures
```

## La entidad «Token»

Echemos un vistazo a algunos de los archivos que se han creado. Así es como funciona este sistema: cuando
realizas una petición de restablecimiento, se genera un token, y ese token se almacena en la base de datos
bajo una nueva entidad: `ResetPasswordRequest`.

Cada token está vinculado a un usuario, así que sabemos quién ha realizado la petición del restablecimiento. Y utiliza
«`ResetPasswordRequestTrait` » del bundle SymfonyCasts, que añade cosas como un
hash seguro del token y su fecha de caducidad: todo lo que el bundle necesita.

Junto con la entidad, tenemos `ResetPasswordRequestRepository`. Es un
repositorio de servicios estándar, pero implementa `ResetPasswordRequestRepositoryInterface` y
usa un trait para añadir algunos métodos especiales. El método principal de la interfaz es
el código estándar para crear ese token para el usuario.

## Los dos formularios

El creador también ha hecho unos cuantos formularios. El primero es`ResetPasswordRequestFormType`: muy sencillo, solo te pide tu correo. Este formulario
no está vinculado a ningún objeto, así que la restricción « `NotBlank` » se aplica directamente al campo « `email` ».
Aquí también podrías añadir una restricción « `Email` ».

Lo siguiente es `ChangePasswordFormType`. Cuando haces la petición de restablecimiento, recibes un correo
con un enlace y, si tu token es válido, te lleva al formulario para cambiar tu contraseña.
Aquí tenemos una restricción `PasswordType` envuelta en una `RepeatedType`, así que se repite dos veces:
«nueva contraseña» y «confirma la nueva contraseña». Las restricciones también se aplican al tipo de formulario:
`NotBlank`, `Length`, además de `PasswordStrength` y `NotCompromisedPassword`; de estos dos
hablaremos un poco más adelante. `RepeatedType` exige que los
dos campos coincidan, así que eso ya lo tenemos de serie.

## Desempaquetando el controlador

A continuación, el nuevo controlador: `src/Controller/ResetPasswordController.php`. Tiene unas cuantas
rutas.

La primera es `request()`, el formulario para recuperar la contraseña. Crea el formulario a partir de nuestro
tipo de formulario de petición, gestiona la petición y, si se envía y es válida, recoge el
correo electrónico y llama a `processSendingPasswordResetEmail()` —un método que veremos
en un momento—. De lo contrario, muestra la plantilla de petición.

Una vez enviado el correo, te redirige a la página `checkEmail()`, que simplemente te indica
que revises tu correo.

Una cosa interesante: ¿te acuerdas de la enumeración de usuarios del capítulo sobre mensajes de error de autenticación
—cuando alguien sondea la web para averiguar qué direcciones tienen cuentas?—. El
formulario de restablecimiento es otro lugar obvio para hacer pruebas. Así que, si no se encuentra ningún usuario con la dirección de correo
que han introducido, simplemente fingimos que el correo se ha enviado. Generamos un token falso para enviarlo
a la plantilla, que parece uno auténtico.

### El token en la sesión

Lo siguiente es la ruta `reset()`: ahí es donde te lleva ese enlace especial del correo.
Y hace algo ingenioso. Si el token está en la URL, lo guarda en la
sesión y te redirige a esta misma ruta sin el token en la URL, para luego
leerlo de nuevo desde la sesión.

¿Para qué molestarse? Porque una URL es un lugar poco seguro para guardar un secreto. Se queda en el
historial del navegador, se envía en el encabezado `Referer` a cualquier script de terceros de la
página, y es lo que la gente pega en los chats cuando necesita ayuda. Trasladar el token
a la sesión en cuanto llega significa que la barra de direcciones no contiene nada confidencial
durante el resto del proceso.

Si no hay ningún token en la URL ni en la sesión,`createNotFoundException()`.

A continuación, usamos un helper del bundle para validar el token y recuperar los datos del usuario. Esto
garantiza que el token sea válido y no haya caducado. Si hay algún
problema, añadimos un error al flash y te redirigimos al formulario de petición.

Ahora, el caso normal. Si el token es válido, creamos el `ChangePasswordFormType`, gestionamos
la petición del formulario y, si se envía y es válida... un token de restablecimiento solo se puede usar una vez,
así que lo eliminamos de la base de datos. A continuación, cogemos la contraseña sin cifrar validada, la aplicamos
al usuario —la variable `$user` proviene de la validación del token anterior—, la guardamos
en la caché, hacemos un poco de limpieza y redirigimos a la página de inicio.

## Iniciar sesión automáticamente

Una cosa rápida: este sistema no inicia sesión al usuario después de que haya restablecido su contraseña.
Se le redirige a la página de inicio, pero tiene que iniciar sesión manualmente con su nueva contraseña.
Creo que está bien iniciar sesión de forma automática: sabes que es él, porque ha hecho clic en un enlace de un
correo enviado a su dirección. Es casi como un enlace de inicio de sesión.

Arriba, en el método ` `reset()` `, ordena un poco estos parámetros, porque vamos a añadir uno
nuevo: inyecta ` `Security` ` del bundle de seguridad de Symfony:

[[[ code('65046bdb5a') ]]]

Luego, en la ruta normal, justo antes de redirigir a la página de inicio, añade
`$this->addFlash('success', 'Your password has been reset, you are now logged in!');` y
`$security->login($user);`:

[[[ code('97aea1b8e4') ]]]

Creo que así la experiencia de usuario es mejor.

## Envío del correo electrónico

Al final está `processSendingPasswordResetEmail()`. Intentamos encontrar al usuario
asociado a ese correo electrónico y, si no lo encontramos, le redirigimos a la página de verificación de correo electrónico
de todos modos, que muestra el mismo mensaje que si se hubiera enviado el correo. No hay enumeración:
la respuesta es idéntica en ambos casos.

Si el usuario existe, creamos un token de restablecimiento. Al generarlo puede producirse una excepción.
Por ejemplo, si ha realizado una petición de restablecimiento hace muy poco tiempo.

Esta sección comentada te permite explicar el motivo al usuario. Está comentada porque
mostrar ese mensaje puede reabrir la enumeración que habíamos cerrado antes: alguien envía
el formulario dos veces seguidas para la misma dirección, y un «ya has realizado una petición
hace poco» en el segundo intento significa que la cuenta existe. Si la dirección es desconocida, se muestra la
misma página genérica en ambas ocasiones.

En su lugar, redirigimos a la página de comprobación de correo con ese mismo mensaje genérico.

Si no hay ningún error, tenemos un token válido, así que ahora creamos el correo: el `from` con
nuestra dirección y nombre de remitente, el `to` con el correo del usuario, un asunto y una plantilla HTML. Pasamos
el token de restablecimiento a la plantilla, lo enviamos, hacemos un poco de limpieza y redirigimos.

## Las plantillas

Ha sido bastante información que asimilar; echemos un vistazo a las plantillas de `templates/reset_password/`.

Primero, `check_email.html.twig`: lo que ven tras enviar la petición. Fíjate en
`resetToken` aquí: sacamos la fecha de caducidad directamente del token para decirles cuánto tiempo
les queda, además de un enlace para volver a intentarlo. Voy a añadir un estilo más bonito; lo puedes encontrar en
el script de abajo:

[[[ code('ab8ba748a1') ]]]

Lo siguiente es la plantilla de correo electrónico en sí. Parece bastante sosa… pero la dejaremos así por
ahora; lo importante es que genera una URL absoluta a la ruta de restablecimiento
de contraseña con el token —eso es en lo que hace clic el usuario— y también le indica cuándo
caduca.

Luego está `request.html.twig`, el formulario que te pide tu correo. Aquí también voy a añadir un poco de estilo:

[[[ code('4aad2e1cd0') ]]]

Y por último, el formulario de restablecimiento: donde acabas después de hacer clic en un enlace válido. Voy a
dejar este también más bonito:

[[[ code('65524e963b') ]]]

Una cosa más: el formulario de inicio de sesión necesita un enlace de «¿Has olvidado tu contraseña?». En
`templates/security/login.html.twig`, justo debajo del formulario, voy a pegar un fragmento de código
que puedes encontrar en el script de abajo:

[[[ code('b8df3de82d') ]]]

Si el usuario aún no ha iniciado sesión, verá el enlace.

## Probando todo el conjunto

En la app, ve a la página de inicio de sesión. Ahí está: «¿Has olvidado tu contraseña?». Haz clic en él,
introduce `picard@enterprise.space` y envía el formulario.

Aquí tienes nuestra página para consultar tu correo: nuestro enlace caduca en una hora, que es el valor
por defecto del bundle.

En producción recibirías el correo, pero en desarrollo, por defecto, el sistema de correo de Symfony no
envía nada realmente —¡aunque sí los registra en el profiler! No
aparecerá en esta petición, porque nos han redirigido, así que está en la respuesta de redirección anterior
a esta. Haz clic en el pequeño acceso directo a esa clave del perfilador, ve al perfil de la petición anterior
y… ¡un correo! Ahí está nuestro mensaje, de esa plantilla que vimos.

Copia el enlace y pégalo en el navegador. ¿Ves cómo te ha redirigido a la versión
sin el token en la parte superior? ¡Así es como funciona el truco de la sesión!

Introduce una nueva contraseña —algo un poco más seguro, como `makeitso1engage?` —, repítela… y pulsa
«Restablecer contraseña». Genial: «Tu contraseña se ha restablecido, ¡ya has iniciado sesión!».
Y hemos iniciado sesión como `picard@enterprise.space`. Ha funcionado a la perfección.

## Configuración del bundle

Si quieres saber más sobre cómo personalizar estas plantillas de correo electrónico y sobre el Symfony Mailer,
échale un vistazo a nuestro [curso sobre Mailer](https://symfonycasts.com/screencast/mailtrap), cuyo enlace encontrarás más abajo.

Para la configuración del bundle, la única opción obligatoria es el repositorio, para que
sepa en qué repositorio se encuentra esa entidad de token. Para ver la configuración completa, ejecuta:

```terminal
symfony config:dump symfonycasts_reset_password
```

Además del `request_password_repository` obligatorio, puedes establecer un `lifetime` —el
tiempo que los tokens permanecen válidos, una hora (`3600` segundos) por defecto— y un `throttle_limit`,
que indica la frecuencia con la que se puede realizar una petición para restablecer el sistema. Por defecto, es una petición por hora.

Luego está `enable_garbage_collection`. Cuando la gente realiza peticiones de restablecimientos y nunca utiliza
los tokens, esa tabla no hace más que crecer. La recolección de basura elimina los tokens caducados
para mantenerla ordenada. No te pierdas la [documentación de este bundle](https://github.com/SymfonyCasts/reset-password-bundle#readme).
Te dejo el enlace más abajo.

Así, un usuario puede registrarse con una contraseña y hacer una petición para obtener una nueva si se la ha olvidado.
A continuación: vamos a añadir una forma de que puedan cambiarla mientras ya están conectados.
