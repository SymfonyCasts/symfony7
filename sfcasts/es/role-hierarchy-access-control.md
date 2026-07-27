# Jerarquía de roles y control de acceso

¿Sabías que los roles pueden tener roles secundarios? ¡Pues sí! Y un rol principal hereda todos los roles secundarios.
A esto se le llama jerarquía de roles.

Para ilustrar la jerarquía de roles, vamos a crear el que probablemente sea el rol principal más habitual:
`ROLE_ADMIN`. Los usuarios con este rol se considerarán administradores y podrán
hacer más cosas que un usuario estándar, como un capitán.

Primero, vamos a crear un usuario administrador. Abre `src/Story/AppStory`, busca dónde estamos creando
a Picard y duplícalo. Para el nombre, usa `Kathryn Janeway`... el correo electrónico,
`janeway@starfleet.space`. Es almirante en el mando de la Flota Estelar, así que tiene
sentido que tenga privilegios de administrador. Pon su contraseña en `coffeeblack` y su rol
en `ROLE_ADMIN`:

[[[ code('10228e1bca') ]]]

En tu terminal, vuelve a cargar los ajustes con:

```terminal
symfony console foundry:load-fixtures
```

## Comprobación del acceso de administrador

Ve al navegador y haz clic en el enlace «Piezas». Aunque no sea capitana,
Janeway debería poder acceder a la página de piezas como administradora.

Inicia sesión como ella con el correo electrónico: `janeway@starfleet.space` y la contraseña: `coffeeblack`.

Estamos en la página de piezas, pero nos sale un error 403 de acceso denegado porque
Janeway no tiene el rol « `ROLE_CAPTAIN` ». Aunque sí tiene el rol « `ROLE_ADMIN` », este rol
aún no tiene nada especial.

Lo que tenemos que hacer es configurar `ROLE_CAPTAIN` como un subrolo de `ROLE_ADMIN`.

Para ello, abre « `config/packages/security.yaml` » y, en la sección « `security` »,
añade « `role_hierarchy` ». Debajo de eso, añade la clave « `ROLE_ADMIN` ». Esta clave es el
papel principal. Debajo de eso, podemos añadir una lista de papeles secundarios. Así que añade « `ROLE_CAPTAIN` » como secundario:

[[[ code('6b11b08905') ]]]

## Comprobando los cambios

Ahora actualiza la página `/parts`... ¡Genial! ¡Ya tenemos acceso!

Si abres el panel del perfilador de seguridad, verás que, efectivamente, sus roles son `ROLE_ADMIN` y
`ROLE_USER`. Pero hay una nueva sección más abajo: «Roles heredados». Esto indica que ha
heredado `ROLE_CAPTAIN`.

En el registro de decisiones de acceso, puedes ver que, cuando se comprobó « `ROLE_CAPTAIN` », se concedió el acceso.

## Control de acceso de administrador

Nuestra web tiene el concepto de una sección de administración. Son páginas cuyas URL empiezan por `/admin`.
Estas páginas están repartidas entre varias clases de controlador.

Volviendo al IDE, voy a cerrar algunos archivos... Ahora, abre `src/Controller/AdminController`. Esta
ruta a nivel de clase añade el prefijo `/admin` a todas las rutas de este controlador:

[[[ code('b78fa1a7d0') ]]]

Si ahora abres `StarshipAdminController`, verás que esta tiene el prefijo `/admin/starship`:

[[[ code('20118b4eba') ]]]

Ahora todos estos controladores son públicos, pero en realidad solo deberían estar disponibles para los administradores. Por
lo que aprendiste en el último capítulo, sabes que podríamos añadir el atributo `#[IsGranted('ROLE_ADMIN')]` a cada una de
estas clases. Pero… eso podría generar mucha repetición, y si tuvieras docenas de controladores de administración,
podrías olvidarte de alguno.

Una alternativa es indicarle a Symfony que cualquier URL que empiece por `/admin` requiera `ROLE_ADMIN` para
acceder. A esto se le llama «control de acceso» y se configura en nuestro archivo `security.yaml`:

[[[ code('992db8f2e6') ]]]

Bajo `security`, Flex ha añadido este esbozo de la sección `access_control`. Como lo que queremos hacer es tan
habitual, Flex ya tiene un ejemplo comentado de cómo hacerlo. Descomenta el primer ejemplo:

[[[ code('797325e267') ]]]

Aquí estamos configurando `path` como `^/admin`. Se trata de una expresión regular para coincidir con la ruta. El `^`
significa «empieza por», así que esta expresión coincidirá con cualquier ruta que «empieza por `/admin` », pero no
coincidirá si `/admin` aparece en medio de la ruta, como en `/starship/admin/edit`.

La clave « `roles` » es donde especificas el rol que se necesita para acceder a esta ruta. Y sí, « `ROLE_ADMIN`» 
es el rol que quieres exigir.

## Comprobando los permisos de administrador

De vuelta en el navegador, estamos autenticados como Janeway, y ella tiene `ROLE_ADMIN`. Así que debería poder
acceder a las páginas de administración. Pruébalo visitando `/admin/startship`. ¡Sí, tenemos acceso!

Para comprobar que esto funciona, cierra sesión... e intenta acceder a `/admin/starship`. Te redirigen a la página de inicio de sesión,
¡genial! Ahora inicia sesión como Picard con `picard@enterprise.space`, contraseña `makeitso`. Recuerda que él no tiene
`ROLE_ADMIN`.

403: acceso denegado... ¡Perfecto!

A continuación, veremos cómo acceder al usuario que está conectado actualmente en nuestros servicios y controladores.
