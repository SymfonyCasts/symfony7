# Modo sudo: se requiere autenticación completa

Seguro que ya lo has visto antes. En sitios como GitHub, cuando vas a realizar una
operación delicada, te pide que confirmes tu contraseña. Vamos a incorporar eso a nuestra
app.

A veces se le llama «modo sudo», en referencia al comando `sudo` de Linux.

Si te acuerdas del último curso, vimos « `AuthenticatedVoter` » y sus
atributos integrados. El que nos interesa es « `IS_AUTHENTICATED_FULLY` ». Puedes exigirlo,
y así, si un usuario solo está registrado, se le redirigirá a la página de inicio de sesión
antes de que pueda continuar.

Anota ese nombre. Puedes usarlo con cualquier ` `#[IsGranted]``, pero quiero aplicar esta
restricción a toda nuestra sección de administración —todo lo que esté bajo ` `/admin` `—.

## Una expresión de `access_control` 

Ve a `config/packages/security.yaml`. Aquí abajo, en `access_control`, ya tenemos
una regla: para cualquier cosa que empiece por `/admin`, exigimos `ROLE_ADMIN`.

Quizá pienses que puedes usar simplemente un array aquí... y puedes… pero
no es lo que quieres: es un «o». ¡Así que esto permitiría tanto a los administradores como a cualquier
usuario totalmente autenticado acceder a la sección de administración!

Como es tan ambiguo, el uso de un array aquí está quedando obsoleto en Symfony 8.2.
Solo podrás usar un único rol.

En su lugar, usa una expresión. Elimina el rol y usa
`allow_if: "is_granted('ROLE_ADMIN') and is_granted('IS_AUTHENTICATED_FULLY')"`:

[[[ code('7dce874097') ]]]

Una expresión nos ofrece una forma mucho menos ambigua de decir «el usuario debe ser un administrador Y debe
estar totalmente autenticado».

Vuelve a nuestra página de inicio y actualiza la página. ¡Error! Necesitamos tener instalado el lenguaje de expresiones...
Copia el comando «Composer require» y pégalo en la terminal:

```terminal
composer require symfony/expression-language
```

Actualiza de nuevo... y ya está.

## Viendo cómo funciona

Ahora inicia sesión... como `janeway@starfleet.space`, con la contraseña `coffeeblack`, y asegúrate
de que la casilla «Recordarme» esté marcada.

Ahora ve a `/admin/user`. ¡Ya lo vemos! Pero fíjate en lo que pasa si borramos la
cookie de sesión. Inspecciona la página, búscala en la pestaña «Aplicación», bórrala, cierra la ventana y actualiza la página.

Nos devuelve a la página de inicio de sesión. Y puedes ver ese pequeño mensaje que dice que
ya hemos iniciado sesión. Así que tenemos que volver a iniciar sesión: `janeway@starfleet.space`,
`coffeeblack`. Efectivamente, ya estamos dentro.

## Solo te pide la contraseña

Si te has fijado, era un poco molesto tener que volver a escribir también tu correo electrónico.
Borra de nuevo la cookie de sesión, cierra esto y actualiza la página.

Si has usado esto en GitHub, solo te pide la contraseña. Vamos a hacer que esto sea
un poco más fácil de usar.

Busca la plantilla: `templates/security/login.html.twig`. En la parte superior, envuelve el título
en un `{% if app.user %}`, con un `{% else %}` y un `{% endif %}`. Si no han
iniciado sesión, en el «else», mete el título normal ahí dentro. Si han iniciado sesión, copia solo
el `h1` y cámbialo por «Confirma tu contraseña»:

[[[ code('2c8b51b7e6') ]]]

Un poco más abajo, tenemos este mensaje que les dice que han iniciado sesión. Borra
eso por completo; ya no queremos que se vea.

Ahora cambia el campo de correo electrónico por un campo oculto, porque ya sabemos cuál es el correo
. Lo mismo de siempre: `{% if app.user %}`, `{% else %}`, `{% endif %}`. Mueve todo esto dentro de
la etiqueta `else`. Y dentro de la etiqueta `if`, lo único que necesitamos es `<input type="hidden">` con `name`
que coincida con el de aquí abajo — `_username` — y un valor de
`{{ app.user.userIdentifier }}`, que es el correo electrónico en nuestro caso:

[[[ code('ad0cfafb0e') ]]]

Actualiza la página para ver cómo queda. ¡Genial! Así queda mucho mejor.

Ya nos ha reconocido, así que podemos quitar la casilla «Recordarme» y simplemente obligar al sistema a
que nos vuelva a reconocer. Debajo, después de la contraseña, haz lo mismo: un `{% if app.user %}`,
un `{% else %}` y un `{% endif %}`. La casilla de verificación va en el `else`, y dentro del`if`, otro campo de entrada oculto, llamado `_remember_me` con el valor `1`, que se
interpreta como marcado:

[[[ code('b40ff35e6f') ]]]

Lo último: cambia el botón «Iniciar sesión». Resultado: `app.user ? 'Confirm' : 'Sign in'`:

[[[ code('c7020d9a16') ]]]

Actualiza la página. ¡Genial! ¡Mira qué aspecto tan profesional tiene! Contraseña: `coffeeblack`, y… ya estamos dentro.

Asegúrate de que nuestra página de inicio de sesión habitual sigue funcionando. Haz clic en «Cerrar sesión» y, a continuación, vuelve a iniciar sesión.

Efectivamente, volvemos al formulario normal, porque aún no hemos iniciado sesión.`janeway@starfleet.space`, contraseña `coffeeblack`. Perfecto: ya hemos vuelto a entrar.

Eso es el modo «sudo» en pocas palabras.

A continuación: vamos a crear un usuario especial que permita a los superadministradores hacer… básicamente lo que quieran.