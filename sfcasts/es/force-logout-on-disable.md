# Forzar el cierre de sesión a los usuarios con discapacidad

Ya hemos conseguido que un usuario inhabilitado no pueda volver a iniciar sesión. Pero, ¿qué
pasa si ya ha iniciado sesión y está navegando por la web y un administrador decide inhabilitar
su cuenta? Lo que queremos es que se le cierre la sesión automáticamente.
En cuanto te desactiven, ya no deberías poder hacer nada, independientemente
de si ya has iniciado sesión o no.

Veamos cómo podemos conseguirlo.

Primero, ve a `src/Story/AppStory.php` y no desactivés a Picard por defecto:
vuelve a cambiarlo a `createOne()`:

[[[ code('782e0dd6dc') ]]]

Ahora vuelve a cargar nuestros fixtures:

```terminal
symfony console foundry:load-fixtures
```

Picard ya no está desactivado. Vuelve a la página de inicio de sesión e inicia sesión como
`picard@enterprise.space` con la contraseña `makeitso`. Genial, ya hemos iniciado sesión como
Picard.

## Desactivar a un usuario que ya ha iniciado sesión

Ahora quiero simular que un administrador desactiva su cuenta. Ejecuta un poco de SQL sin procesar para hacerlo:

```terminal
symfony console dbal:run-sql "UPDATE user SET disabled_at = datetime('now') WHERE email = 'picard@enterprise.space'"
```

Estoy usando SQLite, así que la función para obtener la marca de tiempo actual es `datetime('now')`.
Si usas MySQL o Postgres, es `NOW()`, así que, dependiendo de tu configuración,
quizá tengas que ajustar esto un poco.

Pulsa Intro... una fila afectada. Perfecto. Picard ya debería estar desactivado.

Vuelve a la app en el navegador y actualiza la página... y seguimos conectados. Así que ese
comprobador de usuario no se ejecuta en cada petición.

## Cambiar algo que supervisa el cortafuegos

Hay un par de formas de forzar el cierre de sesión cuando se desactiva a un usuario. La más fácil es
cambiar algo del usuario que se detecte en cada petición.

Quizá recuerdes que ya lo hicimos un poco en el último curso: cuando cambias la
contraseña de un usuario, ese usuario se desconecta automáticamente. Así es como podríamos gestionar una
función de «desconectarse de otros dispositivos»: simplemente volviendo a generar el hash de la contraseña.

Así que la forma más fácil de conseguirlo es en nuestra entidad ` `User` `. Tenemos este método ` `getRoles()``, 
y se comprueba en cada petición. Añade ` `if (!$this->isEnabled())` ` y,
dentro, ` `$roles[] = 'ROLE_DISABLED';``:

[[[ code('dafa6eb46b') ]]]

Ahora bien, este rol en realidad no significa nada. Solo lo usamos para cambiar el
valor de retorno de `getRoles()`, y eso basta para decir «oye, este usuario ha cambiado, así que
tenemos que cerrarle la sesión».

Vuelve a nuestra app y actualiza la página... Efectivamente, nos hemos desconectado, porque ese usuario
ha cambiado.

## ¿Funciona con la opción «Recordarme»?

Una cosa que quiero comprobar bien es que esto funcione con «Recordarme». Debería,
pero asegurémonos. Vuelve a la consola y vuelve a cargar los fixtures:

```terminal-silent
symfony console foundry:load-fixtures
```

Inicia sesión como `picard@enterprise.space` con la contraseña `makeitso`, y esta vez,
asegúrate de que la opción «Recordarme» esté marcada. Ya hemos vuelto a iniciar sesión.

Ahora revisa la página, ve a la pestaña «Aplicación», busca la cookie `PHPSESSID` y
elimínala. Así solo queda la cookie de «Recordarme», así que deberíamos activar el sistema de
«Recordarme». Cierra esto y actualiza la página. Pasa el cursor por encima del usuario en la barra de herramientas de depuración web:
efectivamente, estamos usando el token de «Recordarme».

Ahora aplica nuestro pequeño truco de la base de datos para desactivar al usuario: la misma consulta SQL:

```terminal-silent
symfony console dbal:run-sql "UPDATE user SET disabled_at = datetime('now') WHERE email = 'picard@enterprise.space'"
```

Una fila afectada. Vuelve atrás, actualiza… y ya nos hemos desconectado. Funciona tanto con
la autenticación basada en sesión como con la de «recordarme».

Próximamente: los mensajes que mostramos cuando hay un error de autenticación y cómo podemos
personalizarlos.
