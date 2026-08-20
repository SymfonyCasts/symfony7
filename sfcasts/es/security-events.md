# Eventos de seguridad: seguimiento del último inicio de sesión

El sistema de seguridad de Symfony genera un montón de eventos a los que puedes estar atento y reaccionar
. Estos son los más comunes:

El método `InteractiveLoginEvent` se invoca después de que un usuario se autentique correctamente
de forma interactiva, por ejemplo, al enviar un formulario de inicio de sesión.

El método ` `LoginFailureEvent` ` se invoca tras un intento fallido de autenticación. Puedes utilizarlo para
registrar los intentos fallidos o personalizar la respuesta de error.

El método ` `LogoutEvent` ` se activa antes de que un usuario cierre sesión. Puedes usarlo para realizar tareas de limpieza
o personalizar la respuesta al cierre de sesión.

El método `SwitchUserEvent` se invoca tras cambiar a un usuario suplantado o salir de él.

Hay más eventos además de estos, pero son para situaciones más avanzadas.

Lo que quiero hacer en nuestra app es registrar la fecha y hora del último inicio de sesión de cada usuario. Esta
es una función bastante habitual.

## Añadir el campo « `lastLogin` »

Lo primero que necesitamos es un campo en nuestra entidad « `User` » para guardar esa marca de tiempo. En
tu terminal, ejecuta:

```terminal
symfony console make:entity
```

Esto será para nuestra entidad « `User` », así que la vamos a modificar. Llama al campo « `lastLogin` ». Para el
tipo de campo, pulsa « `?` » para ver todas las opciones... y elige « `datetime_immutable` ». ¿Puede este
campo ser nulo? Sí, si alguien nunca ha iniciado sesión, será nulo. Y ya está.

Por cierto, siempre deberías usar `datetime_immutable` en lugar de `datetime` para tus campos de fecha y hora
. La versión inmutable es más segura y menos propensa a errores: si modificas una fecha mutable
in situ, Doctrine no lo verá como un cambio y tu actualización nunca se guardará.

Ahora genera la migración:

```terminal
symfony console make:migration
```

Vuelve a tu editor y busca esa migración. Aquí la tienes. Establece la descripción en
`add last login to user`.

Vuelve a la terminal y ejecuta:

```terminal
symfony console doctrine:migrations:migrate
```

¡Perfecto!

## Mostrarlo en la gestión de usuarios

Cuando un administrador esté en la página de lista de usuarios, quiero que esto aparezca como una columna. Abre
`templates/user_admin/index.html.twig`... y busca el encabezado de la tabla «Name». Duplícalo
y llámalo «Último inicio de sesión».

A continuación, más abajo, donde mostramos el nombre del usuario, duplica también esa línea.
Dentro, escribe `{{ user.lastLogin ? user.lastLogin|date('Y-m-d H:i:s') : 'Never' }}`.
Esto comprueba primero si `user.lastLogin` tiene algún valor. Si lo tiene, lo formatea con el filtro `date`
. Si no lo tiene, muestra «never».

Vuelve a ese listado en tu navegador y actualízalo. Vale, eso ha desbordado un poco nuestra columna «acciones»,
pero no nos preocupemos por eso ahora mismo. Podemos ver la columna «Último inicio de sesión», y pone
«nunca» para cada usuario.

## `make:listener`

Ahora necesitamos un detector de eventos… y hay un «maker» para eso. En tu terminal, ejecuta:

```terminal
symfony console make:listener
```

Llámalo « `LastLoginListener` ». Esto nos da una lista enorme de eventos entre los que elegir… y
el que queremos es « `security.interactive_login` ».

Este es el evento que se activa después de que un usuario inicie sesión de forma activa. Por ejemplo, al enviar nuestro
formulario de inicio de sesión. No se activa cuando alguien se autentica de otra forma, como a través de su
cookie de «recordarme». Y para una marca de tiempo de «último inicio de sesión», eso es exactamente lo que queremos:
que te recuerden entre sesiones no es realmente iniciar sesión.

Ve a buscar la nueva clase en `src/EventListener/LastLoginListener.php`.

Lo único que en realidad no necesitamos aquí es el argumento `event` en el
atributo`#[AsEventListener]`: Symfony puede determinar el evento a partir de la indicación de tipo del
método.

Antes de escribir nada de lógica, comprueba bien que esto esté realmente registrado. En tu
terminal, ejecuta:

```terminal
symfony console debug:event
```

Esto muestra todos nuestros oyentes y el evento al que están atentos. El último de la lista es
`security.interactive_login`... y, efectivamente, ahí está nuestro `LastLoginListener`. Ya estamos listos.

## Configurar la marca de tiempo

Ahora vamos a conectarlo todo. Primero, añade un constructor... e inyecta
`private EntityManagerInterface $em`.

Aquí abajo, para ver qué podemos extraer del evento, entra en `InteractiveLoginEvent`.
Podemos obtener el `Request`... y podemos obtener el token de autenticación; recuerda que es un objeto que envuelve al
usuario. Así que extrae el usuario de ahí:
`$user = $event->getAuthenticationToken()->getUser();`

Esto podría ser nulo. Probablemente nunca lo será —no tendría mucho sentido
en un evento de inicio de sesión—, pero técnicamente es posible. Así que añade `if (!$user instanceof User)`, incorporando
nuestra entidad `User`, y simplemente `return`. No hagas nada.

Y si sí que tenemos un usuario, establece la marca de tiempo:
`$user->setLastLogin(new \DateTimeImmutable('now'));`

Lo último que nos queda es guardar ese cambio en la base de datos:
`$this->em->flush();`

## Probémoslo

¡Pruébalo! Ahora mismo estamos conectados como Janeway, así que primero cierra la sesión... y luego vuelve a iniciar sesión
como `janeway@starfleet.space`, con la contraseña `coffeeblack`.

Ahora vuelve a esa página de `/admin/user` otra vez… ¡y ya está! Nuestro último inicio de sesión se ha
registrado y guardado. Una función muy chula.

Próximo paso: ¡crear un sistema de registro de usuarios!
