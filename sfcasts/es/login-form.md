# Crear un formulario de inicio de sesión

Ya tenemos configurado nuestro proveedor de usuarios y un usuario en nuestra base de datos. ¡Ahora necesitamos
una forma de que inicien sesión! Hay varias formas de hacerlo, pero nos
centraremos en la autenticación basada en sesiones con un formulario de inicio de sesión en HTML.

Es un método bastante habitual y Symfony tiene un autenticador integrado para ello.
Solo tenemos que generar un poco de código para integrarlo en nuestra app.

## `make:security:form-login`

Por suerte, el bundle «maker» ofrece un asistente que te echa una mano.

En tu terminal, ejecuta:

```terminal
symfony console make:security:form-login
```

Lo primero que nos pide es crear una clase de controlador para nuestras rutas de inicio y cierre de sesión.
«`SecurityController` » es un buen nombre, ¡vamos a quedarnos con ese!

¿Queremos una URL `/logout`? ¡Sí! Queremos que nuestros usuarios puedan cerrar sesión.

¿Generar pruebas de PHPUnit? No, ahora mismo no.

Vale, esto ha generado dos archivos nuevos en nuestro proyecto: el `SecurityController` y una
plantilla`login.html.twig`. También ha actualizado nuestro archivo `security.yaml`.

Vamos a echarles un vistazo.

## Al examinar el `SecurityController`

Primero, abre `src/Controller/SecurityController.php`. Este tiene un método `login()` que gestiona la ruta `/login`. Estamos inyectando este servicio `AuthenticationUtils`,
que es un práctico ayudante de autenticación.

En su interior, asignamos la variable `error` al último error de autenticación de esas utilidades.
Si se produjo un error durante el proceso de inicio de sesión, esta variable contendrá el mensaje de error específico,
como «Credenciales no válidas» o «Cuenta desactivada». Si no hubo errores, será nula.

A continuación, establecemos la variable ` `lastUsername` `, también de esas utilidades. Cada vez que un usuario intenta iniciar sesión,
Symfony guarda el nombre de usuario que utilizó en su último intento de inicio de sesión. Como veremos enseguida, lo usamos
para rellenar automáticamente el campo del nombre de usuario en el formulario de inicio de sesión; así, el usuario no tiene que volver a escribirlo si
ha cometido un error al teclear la contraseña.

Por último, mostramos la nueva plantilla `login.html.twig`, pasándole las variables `last_username` y `error`.

Es importante señalar que este método no gestiona realmente la lógica de inicio de sesión en sí. Solo se llama cuando
el usuario visita la página `/login` mediante una petición GET normal (por ejemplo, al hacer clic en un enlace). Cuando el usuario envía el formulario de inicio de sesión,
el sistema de seguridad toma el control y procesa la petición de inicio de sesión. Si el inicio de sesión se realiza con éxito, el usuario queda autenticado
y conectado. Si falla, se le redirige de nuevo aquí con el mensaje de error correspondiente.

A continuación, tenemos este método y esta ruta `logout()`. Lo único que hace es lanzar una excepción. ¿Qué? ¿Significa esto que
cada vez que un usuario cierre sesión, le salga un error 500?! ¡No! Esta ruta es básicamente un marcador de posición para que podamos
personalizar la ruta y el nombre de `Route`. El sistema de seguridad de Symfony intercepta esta ruta antes de que llegue
al método. Si hay algún error en la configuración de tu cierre de sesión y se ejecuta este método, se lanzará esta excepción,
lo que indicará que tienes un error.

## Revisando el archivo `security.yaml` 

Vamos a comprobar los cambios en la configuración de seguridad en `config/packages/security.yaml`. Bajo nuestro «firewall» `main`,
tenemos esta nueva configuración `form_login`. Esto le indica a Symfony que utilice el autenticador de inicio de sesión mediante formulario integrado. Hay
un montón de opciones que podemos configurar aquí, pero los valores por defecto son bastante buenos y estándar. El `login_path` está configurado
en nuestra ruta `app_login` que vimos en el `SecurityController`. Aquí es donde se redirige a los usuarios cuando tienen que iniciar sesión,
por ejemplo, cuando intentan acceder a una página protegida. El `check_path` también está configurado en `app_login`. Aquí es donde
se envía el formulario de inicio de sesión. No supone ningún problema que sea la misma ruta, ya que envías las credenciales mediante el método `POST`.
Esto hace que el sistema de seguridad procese el intento de inicio de sesión en lugar de mostrar el formulario de inicio de sesión. Por último, estamos
activando la protección contra la falsificación de peticiones entre sitios (CSRF) para nuestro formulario de inicio de sesión.

A continuación, activamos y configuramos la función de cierre de sesión estableciendo `logout_path` en la
ruta`app_logout` que hemos configurado en `SecurityController`.

## La plantilla `login.html.twig` 

Pasemos al otro archivo nuevo: abre `templates/security/login.html.twig`. Este amplía nuestro diseño base y
establece el bloque del título.

En el bloque «body», mostramos el formulario. Fíjate en el atributo « `method="post"` ». Esto es importante para
activar la ruta « `check_path` » al enviar el formulario. Recuerda que estamos pasando las variables « `last_username` » y « `error` » a esta
plantilla desde nuestro « `SecurityController` ».

En su interior, primero comprobamos si hay algún error y, si es así, lo mostramos. Para ello se utiliza el sistema de traducción, por lo que,
si tu aplicación está localizada, los mensajes de error estándar se traducirán automáticamente al idioma del usuario.

A continuación, comprobamos si un usuario ya ha iniciado sesión. `app.user` devuelve el usuario que ha iniciado sesión actualmente, o `null` si
no hay ninguno. Si ha iniciado sesión, muestra su `userIdentifier` (su correo electrónico en nuestro caso) y un enlace para cerrar sesión.

A continuación hay un campo de entrada de tipo correo electrónico para el nombre de usuario, que ya viene rellenado con la variable `last_username`. Fíjate
en el atributo `name="_username"`. Esto es importante porque el autenticador de inicio de sesión busca este nombre de campo específico
al procesar el intento de inicio de sesión.

Lo mismo ocurre con el campo de contraseña que hay más abajo. Tiene que llamarse `_password` para que el autenticador lo reconozca.
Estos nombres se pueden configurar en las opciones de `form_login` en `security.yaml`.

A continuación, tenemos un campo de entrada oculto para el token CSRF. El valor de este token se genera con la función `csrf_token()`
. De nuevo, el atributo «name», `_csrf_token`, es importante para que el autenticador lo reconozca y también se puede configurar
en `form_login`, en `security.yaml`.

Hay algo de código comentado relacionado con la función «recordarme», que veremos más adelante.

Por último, aquí está el botón de enviar.

## Estilo del formulario de inicio de sesión

¡Veamos qué pinta tiene esto! En nuestra app, entra en `/login`... Esto está bien... ¡pero vamos a darle un toque más chulo!

En el directorio `tutorial`, abre `login.html.twig` y copia todo. Vuelve a nuestra
plantilla y sustituye todo por el código copiado. Este código también está en el script de abajo:

[[[ code('f30d27a746') ]]]

Actualiza la página de inicio de sesión... ¡Genial, mucho mejor!

## Probando el formulario de inicio de sesión

Primero probemos con unas credenciales incorrectas. Correo: `invalid@invalid.com`. Contraseña: `invalid`. Pulsa Intro para enviar y...

Vale, esta ventana emergente de «Cambia tu contraseña» es de mi navegador. No le gusta esta contraseña. Voy a darle a «Aceptar» para
cerrarla; solo estamos en fase de desarrollo. ¡Eh, no, nunca la guardes!

Aparece el mensaje de error esperado: «Credenciales no válidas», y fíjate: el campo de correo electrónico ya está rellenado con el
nombre de usuario que acabamos de probar. Aunque actualicemos la página, recuerda nuestro último nombre de usuario. ¡Qué práctico!

Ahora probemos la protección CSRF. Para esto, tendremos que meternos de lleno en las herramientas de desarrollo. Haz clic con el botón derecho
en cualquier parte del formulario y elige «Inspeccionar». En el DOM, busca el campo de entrada oculto «csrf_token». Cambia el valor por
algo que no sea válido, como `invalid`.

Introduce cualquier cosa en la contraseña... Cerramos las herramientas de desarrollo... y pulsamos «Iniciar sesión». Voy a cerrar
otra vez esa ventana emergente cutre de la contraseña... Y genial, aparece el error «Token CSRF no válido».

Este mensaje de error es bastante técnico, no creo que un usuario normal lo entendiera. Aquí tienes una
tarea para ti: ¿cómo podrías personalizar este mensaje de error para que sea más fácil de entender? Por ejemplo: «Lo sentimos, algo
ha salido mal. Inténtalo de nuevo». ¡Deja tus respuestas en los comentarios de abajo!

## Tokens CSRF sin estado

Quizá te hayas fijado en algo sobre el valor del token CSRF antes de que lo cambiáramos a `invalid`. Búscalo
de nuevo en las herramientas de desarrollo y échale un vistazo. Es simplemente «csrf-token»... ¿no debería ser algo
aleatorio y único? Sí, si estuviéramos usando tokens CSRF tradicionales basados en sesión. Las versiones más recientes de
Symfony te permiten usar los tokens CSRF sin estado (es decir, sin sesión), que son más modernos.

Echa un vistazo a nuestra plantilla `login.html.twig` y busca el campo de entrada oculto para el token. Fíjate en que el valor se
genera con `csrf_token('authenticate')`. `authenticate` es un identificador de token, que se usa para distinguir la
finalidad de este token. Si abrimos `config/packages/csrf.yaml` y miramos esta configuración de `stateless_token_ids`,
vemos que `authenticate` aparece aquí. Esto significa que cualquier token CSRF generado con el identificador `authenticate`
usará el sistema sin estado.

Además, volviendo a nuestra plantilla de inicio de sesión, el campo oculto del token tiene un atributo `data-controller="csrf-protection"`.
Se trata de un controlador de Stimulus que proporciona la receta Flex para el bundle Stimulus. Lo puedes encontrar en
`assets/controllers/csrf_protection_controller.js`. No es necesario para que el sistema de tokens CSRF sin estado
funcione, pero refuerza la seguridad del sistema. Échale un vistazo a nuestro [vídeo corto de YouTube](https://www.youtube.com/shorts/URNBEATIzSQ)
sobre el tema para saber más.

¡Vale, volvamos a nuestro formulario de inicio de sesión! Intentemos iniciar sesión con un correo y una contraseña válidos.

En la terminal, echa un vistazo a nuestra base de datos para refrescar la memoria sobre el usuario:

```terminal
symfony console dbal:run-sql 'select * from user'
```

Ah, sí, es nuestro amigo Jean-Luc Picard. Vuelve al formulario, oculta las herramientas de desarrollo, actualiza la página... e introduce su
correo: `picard@enterprise.space`. Contraseña: `makeitso`. Pulsa «Iniciar sesión»... Sí, sí, esta contraseña tampoco es válida...

Mmm, nos sale el error «Credenciales no válidas». Sé que no me he equivocado al escribir... ¿Te acuerdas de esa
vulnerabilidad de seguridad que mencioné en el último capítulo...? ¿Ya has averiguado cuál es?

¡La arreglaremos a continuación!
