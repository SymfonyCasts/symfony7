# Cómo restringir la suplantación de identidad con un usuario

En nuestro panel de administración de usuarios, los administradores pueden cambiar a diferentes usuarios usando
la función de suplantación de identidad de Symfony —o « `switch_user` ». Funciona así: en
nuestra configuración, usamos una jerarquía de roles para asignar a cada administrador el rol « `ROLE_ALLOWED_TO_SWITCH` ».
Eso es lo que comprueba Symfony: si tienes ese rol, puedes cambiar a cualquier
usuario.

Quiero añadirle algo de lógica a esto. Las reglas: no puedes cambiar a tu propio usuario. Estoy
conectado como Janeway, y si hago clic en «cambiar a»... Ni siquiera quiero saber qué
pasa. Probablemente el universo implosione.

Y no quiero que los administradores puedan suplantar a otros administradores. En nuestra app, un
administrador podría suplantar a un capitán, pero no a otro administrador ni a un superadministrador.
Los superadministradores pueden suplantar a cualquiera, excepto a sí mismos.

Podemos hacer esto con nuestro propio «voter» personalizado, porque cuando Symfony comprueba ese rol,
también pasa al usuario de destino como sujeto de la comprobación « `isGranted()` ».

## Un atributo « `CAN_IMPERSONATE` » personalizado

Lo primero: quita « `ROLE_ALLOWED_TO_SWITCH` » de « `role_hierarchy` », porque vamos
a usar un «voter» para comprobar esto; ya no va a formar parte de « `ROLE_ADMIN`» 
.

El nombre de este rol se configura más arriba, en « `switch_user` », y la opción
se llama simplemente « `role` ». Vamos a usar uno personalizado que no incluya el
prefijo «`ROLE_` », porque queremos que el «voter» de roles integrado en Symfony lo ignore por completo
y deje esta tarea a nuestro «voter» personalizado. Llámalo « `CAN_IMPERSONATE` »:

Ahora, en `templates/user_admin/index.html.twig`, envuelve el enlace de suplantación de identidad en un
`{% if is_granted('CAN_IMPERSONATE', user) %}`, pasando al usuario del bucle actual
como sujeto. Añade `{% endif %}`... y sangra el código:

Otra cosa: nuestro `SuperAdminVoter` en `src/Security/Voter/` tiene esa lista `EXCLUSIONS`
. Añade `CAN_IMPERSONATE` a esa lista, para que los superadministradores no puedan saltarse nuestro nuevo votante.

Comprueba bien que esto funciona. Vuelve a la gestión de usuarios y actualiza la página. Efectivamente, ese
enlace ya no está: los superadministradores ya no pueden saltarse este control.

## Creando el `ImpersonationVoter`

En la terminal, crea un nuevo votante:

```terminal
symfony console make:voter
```

Llámalo `ImpersonationVoter`.

Vamos a echarle un vistazo. Elimina las constantes —no las vamos a usar— y
borra `supports()` y `voteOnAttribute()`.

En `supports()` y `return 'CAN_IMPERSONATE' === $attribute && $subject instanceof User`:

Añade algunos bloques de documentación a `voteOnAttribute()`: borra todos menos
el del sujeto. Gracias a esa comprobación de antes, sabemos que el sujeto va a ser nuestra
entidad`User`.

Ahora, obtén el `$currentUser` del token y asegúrate de que no sea nulo. Si no podemos obtener
al usuario actual, devuelve `false`. Ahora comprueba si el identificador de usuario del sujeto es el mismo que el
del usuario actual; esto es para que no podamos suplantarnos a nosotros mismos:

A continuación, copia esa comprobación de « `in_array()` » de nuestro votante superadministrador: si el usuario actual tiene
`ROLE_SUPER_ADMIN`, devuelve `true`. Los superadministradores pueden suplantar a cualquier otra persona;
esa es la excepción:

Recuerda que, cuando creamos el votante superadministrador, decidimos comprobar ese rol directamente
en el propio usuario, no a través de la jerarquía de roles.

## `isGrantedForUser()`

Pero ahora sí que queremos comprobar la jerarquía de roles, para ver si el usuario de destino es un
administrador. Para eso, crea un constructor... E inyecta el servicio `Security` como `$security`.

Volviendo a `voteOnAttribute()`, `return !$this->security->isGrantedForUser()`:

Normalmente solo has usado `isGranted()`. `isGrantedForUser()` te permite comprobar a un
usuario cualquiera en lugar del que está conectado actualmente; es muy útil en cosas como
los comandos de la CLI, donde no hay ningún usuario actual. Funciona exactamente igual que `isGranted()`,
excepto que el primer argumento es el usuario que estás comprobando, el segundo es el atributo y el tercero
es el sujeto opcional.

Pasa `$subject` como usuario y `ROLE_ADMIN` como atributo.

Vuelve a la página y actualízala. ¡Genial! No podemos cambiar a nuestra propia cuenta —esa regla funciona—,
pero sí podemos cambiar a la de un capitán.

## Más usuarios con los que jugar

Añadamos algunos usuarios más para ver mejor cómo funciona esta función. Ve a
`src/Story/AppStory.php` y, justo debajo de donde creamos a nuestros usuarios, añade
`UserFactory::createMany(5)` con sus roles establecidos en `ROLE_ADMIN`. Duplica
esta línea para añadir 5 más, pero con `ROLE_CAPTAIN`:

Así se crean cinco administradores y cinco capitanes, además de nuestros usuarios actuales. Cárgalos:

```terminal
symfony console foundry:load-fixtures
```

Vuelve a la app y actualiza la página; te desconectará porque el usuario ha cambiado. Inicia sesión con
«Janeway» otra vez, con la contraseña « `coffeeblack` ».

Antes de seguir, quiero arreglar algo que me está volviendo loco: estos
feos botones de acción. Vuelve a `user_admin/index.html.twig`, arregla el contenedor con `flex-start`, y
como ese botón tiene dos palabras, añade `whitespace-nowrap`:

Actualizar. Ya está, mucho mejor.

## Probando las reglas

Como soy superadministrador, no puedo cambiar a mi propia cuenta, pero sí a la de cualquier
otra persona, incluso a la de otros administradores: exactamente la regla que decidimos.

Ahora inicia sesión como uno de nuestros nuevos administradores para ver si tienen bloqueado el cambio a
otros administradores. Copia uno de sus correos, cierra sesión e inicia sesión de nuevo. Si vuelves a mirar
`AppStory` y `UserFactory`, hemos establecido la contraseña como `engage` para todos los usuarios, a menos que
la modifiquemos. Así que escribe eso: `engage`.

Ahora he iniciado sesión como este administrador. Vuelve a la lista de `/admin/user` y fíjate en esto:
podemos cambiar a los capitanes, pero no podemos cambiar al superadministrador ni a ninguno
de los demás administradores.

¡Genial! ¡Ya tenemos todas las funciones!

Siguiente paso: vamos a configurar un sistema de restablecimiento de contraseña.
