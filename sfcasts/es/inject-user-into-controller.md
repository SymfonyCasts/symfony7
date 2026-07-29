# Cómo recuperar el usuario en servicios y controladores

Lo dejamos en el momento en que usábamos el método auxiliar ` `getUser()` ` de `AbstractController`
para obtener el usuario que está conectado actualmente. A continuación, recuperamos el `Starship` del usuario con
 ``getStarship()` ` (usando el operador `null-safe` por si no hay ningún usuario conectado).
Fíjate en esta advertencia que nos da PhpStorm al respecto...

«Llamada potencialmente polimórfica...»

Básicamente, te está diciendo que el objeto devuelto por `getUser()` podría no tener el
método`getStarship()`.

Para ver por qué, ve a la definición de ` `getUser()` `. Su tipo de retorno es ` `UserInterface`` 
(o `null`). Esto devuelve nuestra entidad ` `User` `, que implementa ` `UserInterface``,
pero PhpStorm no lo sabe. Ve que hay un objeto en nuestra
app que sí implementa ` `UserInterface` ` con un método ` `getStarship()` `: nuestra entidad ` `User` `.
Simplemente te está avisando de que no puede estar seguro de que se trate, de hecho, de este objeto.

Vamos a asegurarnos de que sepa que se trata de este objeto.

## Inyectar User en un servicio

Primero, usaremos una forma alternativa de obtener el usuario actual. Puede que necesites acceder al
usuario actual en otro servicio. Y ese servicio no tendrá acceso a los métodos auxiliares de `AbstractController`
.

En nuestro método ` `index()` `, inyecta ` `Security``, el de `SecurityBundle`, ` `$security``:

[[[ code('80ce3be22b') ]]]

Este es un servicio auxiliar que puede acceder al usuario que ha iniciado sesión actualmente.

A continuación, asigna la variable ` `$myShip` ` a ` `$security->``. Echa un vistazo a los
métodos disponibles: ` `getUser()` ` recupera el usuario actual. También tenemos ` `isGranted()` ` para
realizar comprobaciones de autorización.

Hay otros ayudantes para casos de uso más avanzados... Y échales un vistazo a estos dos últimos:
`login()` y `logout()`. Sirven para iniciar y cerrar sesión de los usuarios mediante programación sin
tener que pasar por el flujo estándar. Los veremos un poco más adelante.

Elige `getUser()` y luego escribe `?->getStarship()` para recuperar la nave espacial del usuario (si tiene alguna):

[[[ code('184dba5cd3') ]]]

Básicamente, es exactamente la misma lógica que teníamos antes, pero utilizando un servicio. Ya no dependemos
de `AbstractController`.

## Asegurarnos de que el usuario es nuestro usuario

Aunque seguimos teniendo la misma advertencia. Así que vamos a solucionarlo.

Primero, añade `$myShip = null` y, justo debajo, `$user = $security->getUser()`. Ahora añade
`if ($user instanceof User)` y, dentro, `$myShip = $user->getStarship()`. El `instanceof`
nos protege contra un usuario `null` y garantiza que el objeto sea, efectivamente, nuestra entidad `User`:

[[[ code('57c12f8ec8') ]]]

Ahora ya puedes borrar el código antiguo que aparece a continuación.

Es hora de probarlo: ve al navegador y actualiza la página de inicio. Genial, la barra lateral
sigue funcionando. Cierra sesión... y la barra lateral ha desaparecido. Sigue funcionando como esperábamos.

## Resolución de argumentos de usuario

Para obtener el usuario actual en un servicio, es necesario usar el servicio `Security`... Pero como
estamos en un controlador, podemos hacer algo más sencillo y, en mi opinión, más elegante. Los controladores
tienen una característica especial llamada «resolventes de argumentos». Permiten inyectar ciertos objetos en
el controlador, aunque no sean servicios. El objeto `Request` es uno de ellos. No es
un servicio, así que no puedes inyectarlo en otro servicio, pero sí en un controlador.
Hay un resolutor de argumentos de petición que permite esto. También hay un resolutor de argumentos de usuario.

Sustituye la inyección de ` `Security` ` por ` `UserInterface $user``:

[[[ code('7a064d7edd') ]]]

¡Ahora este parámetro usará el resolutor de argumentos de usuario! A continuación, podemos eliminar la línea `$user =`.

Vuelve al navegador y actualiza la página de inicio... Mmm, nos redirige a la página de inicio de sesión...

Cuando inyectas el usuario de esta forma, haces que este controlador requiera un usuario que haya iniciado sesión. Se lanza una
excepción de acceso denegado si no hay ninguno, por lo que nos redirige a la página de inicio de sesión. Básicamente es
el mismo comportamiento que si hubiéramos añadido una comprobación de `IS_AUTHENTICATED`.

Sin embargo, esto no es lo que queremos para nuestra página de inicio, ya que debería ser accesible para cualquiera, haya iniciado sesión o no.
¿Cómo podemos permitir esto sin dejar de usar el resolutor del argumento «user»? Haz que el argumento «user» sea nulo
añadiendo un prefijo « `?` » a « `UserInterface` »:

[[[ code('7a987e0a18') ]]]

Ahora vuelve a la página de inicio… ya no hay redirección ni barra lateral. Ahora inicia sesión como Picard… Correo electrónico:
`picard@enterprise.space`, contraseña: `makeitso`. ¡Genial, la barra lateral ha vuelto y muestra nuestra nave!

¡Me encanta! ¡Nuestra definición del método `index()` es genial y muy expresiva!

## `#[CurrentUser]` Atributo

¡Pero podemos hacerlo aún mejor!

Sustituye la indicación de tipo « `UserInterface` » por nuestra entidad real « `User` »... asegúrate de que siga siendo nula.

Esto todavía no funciona del todo. Tenemos que echarle una mano al resolutor de argumentos de usuario. Encima del parámetro,
añade el atributo ` `#[CurrentUser]` `:

[[[ code('3e1885c606') ]]]

Creo que sigue siendo claro: le dice que inyecte el usuario actual
para este parámetro. Y seguro que tenemos el objeto de usuario correcto, ya que la indicación de tipo del método lo garantiza.

A continuación, podemos simplificar toda esta lógica con solo « `$myShip = $user?->getStarship()` »:

[[[ code('bdedb42bc6') ]]]

¡Genial!

Vuelve al navegador… y actualiza la página de inicio… vemos la barra lateral correcta porque hemos iniciado sesión. Cierra sesión…
y la barra lateral desaparece.

No creo que este método ` `index()` ` pueda ser más expresivo. Los objetos ` `StarshipRepository` ` y
``Request` ` son obligatorios. Y ahora, el ` `User` ` actual es opcional.

A continuación, ¡veremos los votantes de seguridad y los permisos!
