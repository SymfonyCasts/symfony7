# Modo Sudo mejorado con IS_AUTHENTICATED_RECENTLY

Abre `config/packages/security.yaml` y desplázate hacia abajo hasta `access_control`. Aquí tenemos una regla:
cualquiera que acceda a una ruta que empiece por `/admin` debe tener `ROLE_ADMIN` y estar`IS_AUTHENTICATED_FULLY`. Esa segunda parte significa que has iniciado sesión con tu contraseña durante
esta sesión, así que excluye a los usuarios que están recordados. Si solo estás recordado, te redirigen
a la página de inicio de sesión y tienes que confirmar tu contraseña.

He estado llamando a esto «modo sudo», como en macOS o Linux, donde vuelves a introducir tu
contraseña para obtener privilegios elevados. Pero ese nombre no es del todo acertado. El verdadero «modo sudo» tiene que
ver con cuánto tiempo hace que introdujiste tu contraseña por última vez, no con si el sistema te ha reconocido. El nuestro
simplemente se parecía un poco a eso.

## El verdadero modo «sudo»

En Symfony 8.2, tenemos un modo sudo de verdad, gracias a dos nuevos atributos:
`IS_AUTHENTICATED_RECENTLY` y `IS_AUTHENTICATED_VERY_RECENTLY`. El primero comprueba que
hayas introducido tu contraseña en las últimas dos horas. El segundo, en los últimos cinco minutos.
Y ambos implican `IS_AUTHENTICATED_FULLY`, por lo que un usuario que haya iniciado sesión nunca puede tener ninguno de
ellos. Siempre tienen que pasar por el proceso de inicio de sesión.

Probemos esto en nuestra app. Pero primero, tenemos que ocuparnos de algunas cosas.

## Excluir los nuevos atributos en `SuperAdminVoter`

Open `src/Security/Voter/SuperAdminVoter.php`. Esto permite que cualquiera con `ROLE_SUPER_ADMIN`
se salte la mayoría de las comprobaciones de `is_granted()` en nuestra app. Pero excluimos algunos atributos que no
tienen que ver realmente con los permisos, como `IS_AUTHENTICATED` y `IS_AUTHENTICATED_FULLY`: no
queremos que nuestro superadministrador se salte esos sin más. Añade los dos nuevos atributos a las
exclusiones: `AuthenticatedVoter::IS_AUTHENTICATED_RECENTLY` y
`AuthenticatedVoter::IS_AUTHENTICATED_VERY_RECENTLY`:

[[[ code('00c69ccc8620985a6ce4f963b2a29e9c') ]]]

## Corregir un error engañoso en `supports()`

También tenemos un error molesto en `supports()`. Vuelve a `security.yaml` y fíjate en nuestro`allow_if`. En cualquier sitio donde puedas hacer una comprobación de `is_granted()`, también puedes pasar una
expresión; eso es lo que estamos haciendo aquí. Se convierte en un objeto `Expression`,
y ese objeto se pasa a `supports()` como atributo. Pero nuestro argumento `$attribute` está definido como
`string`, y un `Expression` se puede convertir en una cadena… ¡así que PHP lo convierte sin avisar!
La cadena completa de la expresión llega aquí, no está en nuestras exclusiones, así que decimos «claro,
puedes saltártela». ¿El resultado? A los superadministradores nunca les aplica esta regla:
se la saltan por completo.

Para solucionarlo, cambia `string $attribute` por `mixed $attribute`. Ahora el objeto `Expression` se pasa tal cual, sin convertir su tipo. A continuación, añade una comprobación `is_string($attribute)` delante
de `in_array()`:

[[[ code('690ddf6488d250428ce9802b8bd858d8') ]]]

Eso soluciona el error.

## Recordar solo a los usuarios que lo han solicitado

Lo siguiente es nuestra plantilla de inicio de sesión: `templates/security/login.html.twig`. Si ya estás
autenticado cuando llegas aquí, ocultamos el campo del nombre de usuario: ya sabemos quién
eres. Y en la casilla «recordarme», si estás autenticado, siempre forzamos
que se active la opción «recordarme».

Antes eso estaba bien, porque `IS_AUTHENTICATED_FULLY` era lo único que enviaba a un
usuario autenticado aquí, lo que significaba que había que recordarlo; mantenerlo recordado
no hacía ningún daño. Pero ahora estos dos nuevos atributos también pueden llevarte aquí, y en
ese caso no deberíamos dar por hecho que quieres que te recuerden. Cambia `if app.user` por
`if is_granted('IS_REMEMBERED')`:

[[[ code('c8a9418dc70e52bcfd4937eb146f5037') ]]]

Ahora solo forzamos la opción «Recordarme» si ya estás registrado.

## Activar el modo Sudo

¡Es hora de activarlo! Vuelve a `security.yaml`, en la sección `access_control` `allow_if` , cambia
`IS_AUTHENTICATED_FULLY` por `IS_AUTHENTICATED_VERY_RECENTLY`:

[[[ code('66575797f16bc29c3641db05e40b3329') ]]]

Ten en cuenta que, con esta configuración, los usuarios tendrán que volver a introducir su contraseña cada cinco minutos —
cada dos horas con `IS_AUTHENTICATED_RECENTLY`. Si tus administradores trabajan en el área de administración
todo el día, ¡les resultará muy molesto tener que teclear su contraseña cada cinco minutos! Reserva estos
atributos para acciones realmente peligrosas. Se trata de encontrar un equilibrio entre la facilidad de uso y la seguridad.
Sinceramente, para esta ruta probablemente me quedaría con `IS_AUTHENTICATED_FULLY`: los
usuarios que han iniciado sesión confirman su contraseña una vez y ya están listos para el resto del día, hasta que
cierren el navegador.

## Probémoslo

¡Probémoslo! Ve a la página de inicio de sesión e inicia sesión como `janeway@starfleet.space`, con la contraseña
`coffeeblack`. A continuación, ve a una página de administración, como `/admin/user`. Ya estamos dentro.

Para ver cómo se activa la reautenticación, tenemos que esperar cinco minutos, ya que hemos usado
`IS_AUTHENTICATED_VERY_RECENTLY`. Mientras esperamos, te voy a enseñar cómo personalizar estos
valores. En tu terminal, ejecuta:

```terminal
symfony console config:dump security
```

Desplázate hasta la parte de arriba… allá vamos. `recent_authentication_lifetime` sirve para
`IS_AUTHENTICATED_RECENTLY` y el valor por defecto es `7200` segundos, es decir, dos horas. Y
`very_recent_authentication_lifetime` tiene por defecto `300`, que son nuestros cinco minutos. Así es como
los ajustas.

Voy a hacer un pequeño salto en el tiempo...

Vale, han pasado unos cinco minutos. Actualiza la página y... efectivamente, nos devuelve al formulario de inicio de sesión
para confirmar nuestra contraseña. Y como no nos recuerdan automáticamente, nos dan la opción
de que nos recuerden. Escribe `coffeeblack`, confirma... ¡y ya estamos dentro otra vez! Tenemos otros cinco
minutos en el panel de administración antes de tener que volver a autenticarnos.

A continuación, ¡vamos a ver algunas mejoras en el limitador de tasas de Symfony 8.2!
