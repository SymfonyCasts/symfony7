# Denegación de acceso a un votante

Vale, ¿has averiguado cuál es nuestro problema? En realidad es un problema de seguridad bastante grave. Echa
un vistazo a esto.

Hemos iniciado sesión como Picard y estamos editando la Enterprise; esa es nuestra nave, así que
no pasa nada. Pero fíjate en esto: cambia el ID de la URL a `2`... y ahora podemos editar
la nave de otra persona. ¡Mal asunto!

Además, si vamos a `/starship/new`, podemos crear una nave espacial nueva, sin
ningún problema. ¡Solo los administradores deberían poder hacer eso!

Así que en nuestras plantillas de Twig usamos esas comprobaciones de `is_granted()`, pero en este caso,
solo están ocultando los enlaces. En realidad, no nos impiden seguir
los enlaces. Para hacerlo, tenemos que denegar el acceso basándonos en esos mismos permisos
dentro mismo de `StarshipAdminController`.

## Denegar el acceso en el controlador

Empieza por el método `index()`. Aquí no necesitamos ningún permiso: cualquiera puede
acceder al índice.

Pero para `new()`, nuestra regla era que tienes que ser administrador. Así que añade
`#[IsGranted('ROLE_ADMIN')]`:

[[[ code('766adfc27a') ]]]

Sigue bajando hasta `show()`. «Show» está bien: cualquiera puede ver una nave espacial, sin problema.

Pero luego está `edit()`. Y recuerda: para el permiso `edit`, necesitamos la nave espacial como
sujeto. Así que usa ese mismo atributo `#[IsGranted]`. El permiso era
`edit`... y luego pasa un segundo argumento: el sujeto. Solo tienes que pasar la cadena
`starship`:

[[[ code('b9dc859bbe') ]]]

Siempre que haga referencia a un objeto que se haya inyectado en tu controlador, se
utilizará como sujeto. Así que esto usará el `Starship` que inyectamos en este
controlador. ¡Justo lo que queremos!

Luego hay otro sitio más: `delete()`. Lo mismo: `#[IsGranted]`, `delete`, y
el sujeto: `starship`:

[[[ code('5c61e58267') ]]]

Vuelve al navegador e intenta ir a la nueva página otra vez... Acceso denegado. Perfecto.
Todavía podemos editar nuestra propia nave, pero si cambiamos el ID a `2`... ahora nos sale una
excepción de acceso denegado. ¡Agujeros de seguridad solucionados!

## Los administradores no pueden editar nada

Una última cosa que quiero hacer: si has iniciado sesión como administrador, deberías poder
hacerlo todo: editar cualquier nave, borrar cualquier nave y, por supuesto, crear naves.

A ver cómo queda esto ahora mismo. Cierra sesión de Picard e inicia sesión como Janeway:
`janeway@starfleet.space`, contraseña `coffeeblack`.

Ahora ve a la gestión de naves. Vemos el botón «Crear nueva» —eso está bien— y
podemos hacer clic en él para acceder a la página. Perfecto. Pero no vemos las opciones de edición
para ninguna de las naves.

## Comprobación de roles dentro de un votante

La mejor forma de solucionar esto es añadir una comprobación del rol dentro de nuestro votante. Así que ve
a `StarshipVoter`.

Aquí arriba, ¿te acuerdas de cómo podemos comprobar los roles o atributos dentro de un
servicio? Añade un constructor.

Ahora, quizá pienses en usar el servicio auxiliar `Security` del que hablamos antes... pero
en realidad no deberías hacerlo aquí. Ya veremos por qué en un momento.

En su lugar, inyecta `private AccessDecisionManagerInterface $accessDecisionManager`:

[[[ code('d111220041') ]]]

Más abajo, antes de que se ejecute ninguna de nuestras lógicas de `voteOnAttribute()`, comprueba si el usuario
tiene `ROLE_ADMIN`. Escribe `if ($this->accessDecisionManager->decide())` —y el primer
argumento es `$token`.

Para el segundo argumento, usa `ROLE_ADMIN` envuelto en un array.

Dentro de la instrucción `if`, `return true`:

[[[ code('80d453a22d') ]]]

## ¿Por qué no el servicio « `Security` »?

En esencia, es la misma lógica que el método `isGranted()` del helper `Security`...

Entonces, ¿por qué no podemos usarlo sin más?

Cuando usas el servicio « `Security` », este utiliza el token actual. Pero dentro de un «voter»,
puede que tengamos un token diferente. Esto puede pasar en algunos casos extremos, así que es mejor
usar « `AccessDecisionManagerInterface` » cuando estés dentro de un «voter».

Vale, volvamos a nuestra lista de naves espaciales. Actualiza la página —y recuerda que hemos iniciado sesión como
Janeway...—; ahora vemos las acciones de editar y eliminar, y podemos usarlas.

## Optimización del rendimiento de los «voters»

Una última cosita que quiero enseñarte. En una app compleja, imagínate
tener cientos de «voters», y que se llame a `supports()` en todos ellos para cada
comprobación de permisos. Eso… puede resultar caro, pero hay formas de mejorar el rendimiento.

Vuelve a `StarshipVoter` y sobrescribe el método `supportsType()`.

Este `$subjectType` nos da el tipo PHP del sujeto; en nuestro caso, el nombre de la clase.
Así que escribe `return is_a($subjectType, Starship::class, true)` de la siguiente manera:

[[[ code('0a5e86f3b3') ]]]

Necesitamos ese `true` porque tienes que pasarlo cuando compruebas si una clase es una cadena
con `is_a()`.

Esto nos va a dar un pequeño aumento de rendimiento.

Ahora sobrescribe `supportsAttribute()`. Toma como argumento el `$attribute` que se está comprobando.
Copia la lógica de esto de `supports()` más arriba...

Y `return in_array($attribute, [self::EDIT, self::DELETE])`:

[[[ code('05ee6745c4') ]]]

Como ves, básicamente hemos dividido la lógica de `supports()` en dos métodos… y seguimos
necesitando `supports()`. El trabajo extra y la duplicación solo merecen la pena si tienes un montón de votantes
y realizas muchas comprobaciones de permisos.

¡Vale, lo siguiente: la suplantación de identidad de usuario!