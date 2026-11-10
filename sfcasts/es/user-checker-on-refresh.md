# Volver a comprobar los usuarios con user_checker_on_refresh

Los usuarios de nuestra app se pueden desactivar. Así es como funciona: la entidad « `User` » tiene un
campo de marca de tiempo llamado «`disabledAt` ». Cuando está en « `null` », el usuario está activado. Cuando tiene un valor,
está desactivado. Ya he desactivado a nuestro usuario «Picard» para que podamos ver cómo queda. Ve
a la página de inicio de sesión e inicia sesión con el nombre de usuario `picard@enterprise.space` y la contraseña `makeitso`... y
veremos «Esta cuenta está desactivada».

## El verificador de usuarios

¿Cómo funciona esto? En `config/packages/security.yaml`, hemos configurado un `user_checker` —uno
personalizado que hemos creado para nuestra app—. Entra en `src/Security/UserChecker.php`. Después de que
se compruebe la contraseña al iniciar sesión, `checkPostAuth()` comprueba si el usuario está habilitado
y, si no lo está, lanza un `DisabledException`. Eso es lo que impide que pueda
iniciar sesión.

Esto va genial para el inicio de sesión, pero el verificador de usuarios solo se ejecuta durante el inicio de sesión. Entonces, ¿qué pasa con un
usuario al que se le desactiva la cuenta mientras ya está conectado? Veamos qué ocurre ahora. Inicia
sesión como `janeway@starfleet.space`, con la contraseña `coffeeblack`: ella no está desactivada. Ya estamos dentro,
y si actualizamos la página, seguimos sin problemas.

## Desactivar a un usuario que ya ha iniciado sesión

Ahora imaginemos que un administrador desactiva su cuenta. Lo haremos directamente desde la terminal:

```terminal
symfony console dbal:run-sql "UPDATE user SET disabled_at = datetime('now') WHERE email = 'janeway@starfleet.space'"
```

Una fila afectada. Ten en cuenta que esta sintaxis es para SQLite; si usas otra
base de datos, tendrás que ajustar la consulta.

Volvamos al navegador, actualiza la página... ¡y ya nos hemos desconectado! Así que sí que funciona, pero estamos usando
un pequeño truco para conseguirlo. Recuerda que el verificador de usuarios no se ejecuta en cada
petición. En nuestra entidad ` `User` `, busca ` `getRoles()``. Aquí vamos a añadir un rol ficticio:
si el usuario no está habilitado, añadimos `ROLE_DISABLED`. No se usa en ningún otro sitio. En cada
petición, el usuario almacenado en la sesión se compara con una copia nueva cargada desde la
base de datos, y los roles son uno de los elementos que se comparan. Si son diferentes, el usuario
se desconecta. Así es como nos las apañamos.

## Hola `user_checker_on_refresh`

En Symfony 8.2, puedes hacer que tu verificador de usuarios se ejecute en cada petición. Eso significa
que podemos eliminar por completo este código improvisado:

[[[ code('d95f2d908108c732f21a4887346ba192') ]]]

¿Cómo? Symfony 8.2 envía un nuevo ` `CheckRefreshedUserEvent` ` justo después de que se
actualice el usuario, y un listener puede usarlo para marcar al usuario como modificado, lo que le desconecta.
Symfony incluye uno, `RefreshedUserCheckerListener`, que ejecuta tu verificador de usuario y,
si lanza una excepción, marca al usuario como modificado. Solo tenemos que activarlo.

En `security.yaml`, añade una nueva opción 8.2 a nuestro firewall `main`:`user_checker_on_refresh: true`:

[[[ code('7f6133791892bd3e6f9a4b93dfa2fc75') ]]]

Ahora el verificador de usuarios se ejecuta cada vez que se actualiza el usuario, es decir, en cada petición. Una cosa
que debes tener en cuenta: si activas esto, asegúrate de que tu verificador de usuarios sea rápido. Hacer un montón de
búsquedas en la base de datos ahí dentro ralentizaría todas y cada una de las peticiones autenticadas.

## Probémoslo

¡Vamos a probarlo! Actualiza los datos de prueba para que nadie esté desactivado:

```terminal
symfony console foundry:load-fixtures
```

Ahora vuelve a iniciar sesión como `janeway@starfleet.space`, con la contraseña `coffeeblack`. ¡Ya estamos dentro! Ahora ejecuta
nuestra consulta de nuevo para desactivarla:

```terminal-silent
symfony console dbal:run-sql "UPDATE user SET disabled_at = datetime('now') WHERE email = 'janeway@starfleet.space'"
```

Actualiza la página y... ¡ya nos hemos desconectado! Se ha llamado al verificador de usuarios y ya no necesitamos ese
pequeño truco para que el usuario actualizado parezca diferente.

A continuación: ¡Symfony 8.2 añade un auténtico «modo sudo»!
