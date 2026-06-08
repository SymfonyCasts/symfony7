# Crear un formulario de inicio de sesión

Ya tenemos configurado nuestro proveedor de usuarios y un usuario en nuestra base de datos. ¡Ahora necesitamos una forma de iniciar sesión! Hay varias formas de hacerlo, pero nos centraremos en la autenticación basada en sesión con un formulario de inicio de sesión HTML.

Este es un método bastante común y Symfony tiene un autenticador incorporado para ello. Sólo tenemos que generar un poco de código para integrarlo en nuestra aplicación.

## `make:security:form-login`

Por suerte, el maker-bundle proporciona un asistente para ayudarte

En tu terminal, ejecuta:

```terminal
symfony console make:security:form-login
```

Primero se nos pedirá que creemos una clase controladora para nuestras rutas de inicio/cierre de sesión.`SecurityController` es un buen nombre, ¡vamos con ello!

¿Queremos una URL `/logout`? ¡Sí! Queremos que nuestros usuarios puedan cerrar la sesión.

¿Generar pruebas PHPUnit? No, ahora no.

Vale, esto generó dos nuevos archivos en nuestro proyecto: el `SecurityController` y una plantilla`login.html.twig`. También actualizó nuestro archivo `security.yaml`.

Vamos a explorarlos.

## Examinando `SecurityController`

En primer lugar, abre `src/Controller/SecurityController.php`. Éste tiene un método `login()` que gestiona la ruta `/login`. Estamos inyectando este servicio `AuthenticationUtils`, que es un ingenioso ayudante de autenticación.

Dentro, estamos estableciendo una variable `error` al último error de autenticación de esas utilidades. Si hubo un error durante el proceso de inicio de sesión, esto contendrá el mensaje de error específico, como "Credenciales no válidas" o "Cuenta desactivada". Si no hubo errores, será nulo.

A continuación, estableceremos la variable `lastUsername`, también de esas utilidades. Cada vez que un usuario intenta iniciar sesión, Symfony realiza un seguimiento del nombre de usuario que utilizó en su último intento de inicio de sesión. Como veremos más adelante, lo utilizamos para rellenar previamente el campo del nombre de usuario en el formulario de inicio de sesión, lo que evita que el usuario tenga que volver a rellenar ese campo si se equivoca al escribir su contraseña.

Por último, estamos renderizando la nueva plantilla `login.html.twig`, pasándole las variables `last_username` y `error`.

Es importante tener en cuenta que este método no maneja realmente la lógica de inicio de sesión en sí. Sólo se llama cuando el usuario visita la página `/login` como una petición GET normal (como hacer clic en un enlace). Cuando el usuario envía el formulario de inicio de sesión, el sistema de seguridad se hace cargo y procesa la petición de inicio de sesión. Si el inicio de sesión se realiza correctamente, el usuario se autentica e inicia sesión. Si el inicio de sesión falla, el usuario es redirigido de nuevo aquí con el mensaje de error correspondiente.

A continuación, tenemos este método y ruta `logout()`. Lo único que hace es lanzar una excepción. ¿Qué? ¿Significa esto que cada vez que un usuario cierra la sesión, recibe un error 500? ¡No! Esta ruta es básicamente un marcador de posición para que podamos personalizar la ruta y el nombre de `Route`. El sistema de seguridad de Symfony intercepta esta ruta antes de que llegue al método. Si algo está mal configurado con tus ajustes de cierre de sesión y se llega a este método, entonces se lanzará esta excepción, indicando que tienes un error.

## Comprobación del archivo `security.yaml` 

Comprobemos los cambios en la configuración de seguridad en `config/packages/security.yaml`. Bajo nuestro cortafuegos `main`, tenemos esta nueva configuración `form_login`. Esto le dice a Symfony que utilice el autenticador de inicio de sesión de formulario incorporado. Hay un montón de opciones que podemos configurar aquí, pero los valores por defecto son bastante buenos y estándar. El `login_path` se establece en nuestra ruta `app_login` que vimos en el `SecurityController`. Aquí es donde se envía a los usuarios cuando necesitan iniciar sesión - como cuando intentan acceder a una página protegida. El `check_path` también está configurado como `app_login`. Aquí es donde se envía el formulario de inicio de sesión. No es un problema que sea la misma ruta, porque envías las credenciales utilizando el método `POST`. Esto hace que el sistema de seguridad procese el intento de inicio de sesión en lugar de mostrar el formulario de inicio de sesión. Por último, vamos a activar la protección contra la Falsificación de Peticiones en Sitios Cruzados (CSRF) para nuestro formulario de inicio de sesión.

A continuación, habilitaremos y configuraremos la función de cierre de sesión estableciendo `logout_path` en la ruta`app_logout` que configuramos en `SecurityController`.

## La plantilla `login.html.twig` 

En cuanto al otro archivo nuevo, abre `templates/security/login.html.twig`. Amplía nuestro diseño base y configura el bloque del título.

En el bloque del cuerpo, estamos renderizando el formulario. Fíjate en el atributo `method="post"`. Esto es importante para activar el `check_path` al enviar. Recuerda que estamos pasando las variables `last_username` y `error` a esta plantilla desde nuestra `SecurityController`.

Dentro, primero estamos comprobando si hay un error, y si es así, renderizándolo. Esto utiliza el sistema de traducción, de modo que si tu aplicación está localizada, los mensajes de error estándar se traducirán automáticamente a la configuración regional del usuario.

A continuación, comprobamos si un usuario ya ha iniciado sesión. `app.user` devuelve el usuario que ha iniciado sesión actualmente, o `null` si no hay ninguno. Si está conectado, muestra su `userIdentifier` (su correo electrónico en nuestro caso), y un enlace de cierre de sesión.

A continuación se muestra un campo de entrada de tipo correo electrónico para el nombre de usuario, que se rellena previamente con la variable `last_username`. Fíjate en el atributo `name="_username"`. Esto es importante porque el autenticador de inicio de sesión del formulario busca este nombre de campo específico cuando procesa el intento de inicio de sesión.

Lo mismo ocurre con el campo de la contraseña. Necesita llamarse `_password` para que el autenticador lo reconozca. Estos nombres pueden configurarse en las opciones `form_login` en `security.yaml`.

A continuación, tenemos un campo de entrada oculto para el token CSRF. El valor de este token se genera con la función `csrf_token()`. De nuevo, el atributo de nombre, `_csrf_token`, es importante para que el autenticador lo reconozca y puede configurarse también en `form_login` en `security.yaml`.

Hay algo de código comentado relacionado con la funcionalidad "recuérdame" que trataremos más adelante.

Por último, aquí está el botón de envío.

## Estilo del formulario de inicio de sesión

Veamos qué aspecto tiene En nuestra aplicación, visita `/login`... Esto está bien... ¡pero mejorémoslo!

En el directorio `tutorial`, abre `login.html.twig` y copia todo. Vuelve a nuestra plantilla y sustituye todo por el código copiado. Este código también está en el script de abajo:

[[[ code('f30d27a746') ]]]

Actualiza la página de inicio de sesión... Bien, ¡mucho mejor!

## Probar el formulario de inicio de sesión

Probemos primero con unas credenciales erróneas. Correo electrónico: `invalid@invalid.com`. Contraseña: `invalid`. Pulsa intro para enviar y...

Vale, esta ventana emergente "Cambia tu contraseña" es de mi navegador web. No le gusta esta contraseña. Le daré a OK para cerrarlo - estamos en desarrollo. Uhh, no, ¡nunca lo guardes!

Tenemos el esperado mensaje de error "Credenciales no válidas" y compruébalo, nuestro campo de correo electrónico se rellena previamente con el nombre de usuario que acabamos de probar. Incluso si actualizamos la página, recuerda nuestro último nombre de usuario. ¡Muy útil!

Ahora vamos a probar la protección CSRF. Para ello tenemos que ensuciarnos las manos en las herramientas para desarrolladores. Haz clic con el botón derecho en algún lugar del formulario y elige "Inspeccionar". En el DOM, busca el campo de entrada oculto csrf_token. Cambia el valor a algo inválido, como `invalid`.

Rellena la contraseña con cualquier cosa... Vamos a cerrar las herramientas de desarrollador... y a darle a "Iniciar sesión". Volveré a cerrar el asqueroso popup de la contraseña... Y bien, vemos el error "Invalid CSRF token".

Este mensaje de error es bastante técnico, no creo que el usuario medio lo entienda. Aquí tienes una tarea para ti: ¿cómo puedes personalizar este mensaje de error para que sea más fácil de usar? Por ejemplo "Lo sentimos, algo ha ido mal. Inténtalo de nuevo" Escribe tus respuestas en los comentarios

## Tokens CSRF sin estado

Puede que hayas notado algo en el valor del token csrf antes de que lo cambiáramos a `invalid`. Búscalo de nuevo en las herramientas de desarrollo y échale un vistazo. Es sólo "csrf-token"... ¿no debería ser algo aleatorio y único? Sí, si estuviéramos utilizando tokens CSRF tradicionales basados en sesión. Las versiones más recientes de Symfony te permiten utilizar los más modernos tokens CSRF sin estado (es decir, sin sesión).

Echa un vistazo a nuestra plantilla `login.html.twig` y encuentra el campo de entrada oculto para el token. Observa que el valor se genera con `csrf_token('authenticate')`. `authenticate` es un identificador de token, utilizado para distinguir la intención de este token. Si abrimos `config/packages/csrf.yaml` y miramos esta configuración `stateless_token_ids`, veremos que `authenticate` aparece aquí. Esto significa que cualquier token csrf generado con el id `authenticate`utilizará el sistema sin estado.

Además, en nuestra plantilla de inicio de sesión, el campo oculto del token tiene un atributo `data-controller="csrf-protection"`. Se trata de un controlador Stimulus que proporciona la receta de Flex para el bundle Stimulus. Puedes encontrarlo en`assets/controllers/csrf_protection_controller.js`. Esto no es necesario para que funcione el sistema de token CSRF sin estado, pero endurece el sistema. Echa un vistazo a nuestro [corto de YouTube](https://www.youtube.com/shorts/URNBEATIzSQ) sobre el tema para saber más.

Bien, ¡volvamos a nuestro formulario de acceso! Intentemos iniciar sesión con un correo electrónico y una contraseña válidos.

En el terminal, comprueba nuestra base de datos para refrescar la memoria sobre el usuario:

```terminal
symfony console dbal:run-sql 'select * from user'
```

Ah, sí, es nuestro amigo Jean-Luc Picard. De vuelta al formulario, oculta las herramientas de desarrollo, actualiza la página... e introduce su correo electrónico: `picard@enterprise.space`. Contraseña: `makeitso`. Pulsa "Iniciar sesión"... Sí, sí, ésta también es una mala contraseña...

Hmm, aparece el error "Credenciales no válidas". Sé que no he cometido un error tipográfico... ¿Recuerdas la vulnerabilidad de seguridad que mencioné en el último capítulo? ¿Has averiguado cuál es?

¡Lo arreglaremos a continuación!
