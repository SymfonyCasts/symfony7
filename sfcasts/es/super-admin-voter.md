# Crear un votante con derechos de superadministrador

Nuestra app tiene el concepto de «admin», y hemos usado una jerarquía de roles para asegurarnos de que
un «admin» también tenga un « `ROLE_CAPTAIN` » y un « `ROLE_ALLOWED_TO_SWITCH` ». Y en nuestro
``StarshipVoter``, tenemos esta «vía de escape» en ` `voteOnAttribute()``: si eres un
«admin», puedes hacer lo que quieras.

Esto funciona y es genial para un control muy preciso: cuando tienes el concepto de
«admin» y controlas específicamente lo que puede hacer.

Pero muchas de las apps en las que he trabajado tienen el concepto de «superadministrador»: alguien de
TI o un administrador de la web que puede hacer lo que quiera. Un nivel por encima del administrador.

Quizá pienses que puedes gestionarlo con una jerarquía de roles, pero hay tantos sitios
y tantos «votantes» que tendrías que actualizar. Lo que me gusta hacer en su lugar es crear un «votante»
específico para los superadministradores.

## Un nuevo votante

Primero, en `src/Story/AppStory.php`, cambia Janeway de `ROLE_ADMIN` a
`ROLE_SUPER_ADMIN`:

Ya hemos actualizado los fixtures, así que en la terminal, recárgalos con:

```terminal
symfony console foundry:load-fixtures
```

Ahora crea nuestro «voter» personalizado:

```terminal
symfony console make:voter
```

Llámalo `SuperAdminVoter`.

Vuelve al IDE y búscalo: `src/Security/Voter/SuperAdminVoter.php`. Borra
todo: las constantes, `supports()` y `voteOnAttribute()`.
Empezaremos desde cero.

Para `supports()`, vamos a admitir todo, así que simplemente devuelve `true`:

Cualquier atributo, cualquier tema: da igual, este votante lo apoya.

## Votación sobre ROLE_SUPER_ADMIN

Aquí viene lo importante: `voteOnAttribute()`. Lo que comprobamos aquí es si tienen permiso o no.
Hay

Hay varias formas de hacerlo. Recuerda que en `StarshipVoter` usamos el
gestor de decisiones de acceso para decidir sobre el token; podríamos inyectar eso y hacer
exactamente lo mismo. Lo hicimos así porque `ROLE_ADMIN` podría formar parte de una jerarquía de roles,
por lo que quizá no esté configurado directamente en el usuario.

Pero voy a decir que los superadministradores tienen que tener `ROLE_SUPER_ADMIN` configurado directamente
en el usuario. Eso hace que esto sea muy sencillo:`return in_array('ROLE_SUPER_ADMIN', $token->getRoleNames(), true);`

`$token->getRoleNames()` es básicamente un atajo para obtener los roles de nuestro usuario y
`true` nos ofrece una coincidencia estricta.

Solo recuerda que, al comprobar los roles de esta forma, solo se comprueban los roles que están directamente asignados al usuario,
no la jerarquía de roles.

## Excluir los atributos de autenticación

Esto casi basta… pero tenemos un problema… Algunos atributos no se basan en permisos…
¿Se te ocurre cuáles son?

Echa un vistazo a las constantes de `AuthenticatedVoter`. Son atributos que puedes comprobar, pero
no tienen que ver con los permisos, sino con cómo te has autenticado. ¡No queremos que los superadministradores
puedan saltarse estas comprobaciones! Si lo hicieran, nuestro modo «sudo» que añadimos en el último capítulo
¡quedaría completamente sin efecto!

Vuelve a `SuperAdminVoter`, añade un archivo « `private const EXCLUSIONS` » y, dentro, añade todas
esas constantes de `AuthenticatedVoter`. `IS_AUTHENTICATED`... `IS_AUTHENTICATED_FULLY`...
`IS_AUTHENTICATED_REMEMBERED`... `IS_IMPERSONATOR`... `IS_REMEMBERED`... y... `PUBLIC_ACCESS`:

Uno, dos, tres, cuatro, cinco, seis... y por aquí, uno, dos, tres, cuatro, cinco, seis.
¡Genial, los tengo todos!

Ahora, en lugar de devolver « `true` » en « `supports()` », devuelve
«`!in_array($attribute, self::EXCLUSIONS, true)` »:

Este votante ya nunca se ejecutará cuando comprobemos si existe alguno de esos atributos.

Tu app puede diferir en algunos aspectos: quizá tengas otros roles o permisos
de los que no quieras que se encargue el votante superadministrador. Si es así, solo tienes que
añadirlos a esta lista.

## Probémoslo

Ve a la página de inicio. Estoy conectado... pero como hemos recargado los fixtures, debería
desconectarme al actualizar la página. Perfecto.

Inicia sesión como `janeway@starfleet.space` —ella es nuestra superadministradora— con la contraseña
`coffeeblack`.

Ve a `/admin/user`. Efectivamente, seguimos pudiendo acceder a esta página aunque
se requiera un administrador: nuestra superadministradora «voter» nos está concediendo acceso.

Otra cosa que hay que comprobar: ve a `/starship`. Si te acuerdas, los usuarios normales solo pueden
editar su propia nave, pero los administradores pueden editar cualquier nave. Y sí, ahora tenemos esa misma
funcionalidad, ya que somos superadministradores. Edita una de las naves espaciales.

## Viendo cómo funciona en el Profiler

Veamos cómo funciona. Ve a la barra de herramientas de depuración web, haz clic en el
perfilador de seguridad y echa un vistazo a la pestaña «Decisión de acceso». Podemos ver todos los
votantes que se están utilizando.

Abajo, en el registro de decisiones de acceso, vemos que se ha concedido `ROLE_ADMIN`. No tenemos
ese rol ni directamente ni a través de la jerarquía de roles. Haz clic en «Mostrar detalles del votante». El `RoleHierarchyVoter`
nos lo denegó, pero el `SuperAdminVoter` nos lo concedió.

La siguiente nos muestra que se nos concedió permiso para editar una nave estelar. «Mostrar detalles del votante» nos muestra
que `StarshipVoter` nos lo concedió. Esto se debe a la comprobación de `ROLE_ADMIN` en `StarshipVoter`.`StarshipVoter` comprobó si teníamos `ROLE_ADMIN`, y `SuperAdminVoter` nos lo concedió.

Aquí abajo, estas comprobaciones de `IS_IMPERSONATOR` fueron denegadas. ¡Genial! No tenemos ese atributo,
¡y nuestro votante superadministrador no lo concedió automáticamente!

¡Nuestro votante superadministrador funciona de maravilla!

A continuación: mejoremos la experiencia de usuario de nuestro proceso de inicio de sesión.
