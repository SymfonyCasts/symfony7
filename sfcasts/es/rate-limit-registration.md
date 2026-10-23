# Limitación de la frecuencia de envíos en el formulario de registro

En el último curso, añadimos una limitación de intentos de inicio de sesión a nuestro formulario de inicio de sesión para restringir el número de
intentos que alguien puede hacer. Ahora quiero limitar el número de registros en la página de registro, para que nadie
pueda quedarse ahí registrando mil usuarios.

## Arreglando ese campo de nombre de usuario

Pero primero, vamos a arreglar ese extraño campo de nombre de usuario que hay al final del formulario. Abre
`templates/registration/register.html.twig` y, justo después de la línea del correo electrónico, duplica
esa línea y cámbiala por `username`:

[[[ code('820ec6d9c2') ]]]

Pan comido. Actualiza la página… perfecto.

## Configurar el limitador

Ahora necesitamos un limitador de tasa. Ya instalamos el componente del limitador de tasa cuando
configuramos la limitación de accesos, así que ya está disponible. Para configurarlo, ve a
`config/packages/` y crea un nuevo archivo YAML llamado `rate_limiter.yaml`. Dentro, la configuración
se encuentra bajo la clave `framework` y, a continuación, `rate_limiter`. Llama al nuestro `registration`.

Todo limitador de frecuencia necesita un « `policy` », y Symfony te ofrece tres entre los que elegir.

`fixed_window` Te permite X intentos en un tiempo Y: un intento por hora, cinco
intentos cada cinco minutos, ese tipo de cosas.

`sliding_window` funciona con la misma idea, salvo que la ventana se desplaza según el momento en que se producen las peticiones.
Esto evita que alguien lance un montón de peticiones al final de una ventana y
luego vuelva a lanzarlas inmediatamente al inicio de la siguiente.

Y `token_bucket`: empiezas con un «bucket» de X tokens, cada acceso consume uno, y cuando
el «bucket» está vacío, ¡no se permiten más accesos! Luego, el «bucket» se rellena a un ritmo fijo.

El más fácil de entender —y el más flexible, creo— es `sliding_window`.
Usa ese. Luego necesitamos un `limit` de `1` y un `interval` de `10 minutes`. Así que: solo puedes
crear un usuario cada 10 minutos:

[[[ code('0bffa32719') ]]]

## Inyectando la fábrica de limitadores

Ahora pasemos a `src/Controller/RegistrationController.php`. Tenemos que inyectar un nuevo
servicio. Voy a ampliar un poco esta lista de argumentos para que tengamos más espacio. El servicio que
queremos es `RateLimiterFactoryInterface`, y lo llamaremos `$limiterFactory`:

[[[ code('febc14a593') ]]]

Pero espera un momento: queremos nuestro limitador configurado, no uno cualquiera. Eso significa un
autowiring con nombre mediante el atributo `#[Target]`. Para averiguar cuál debe ser la cadena de destino,
ve a la terminal y ejecuta:

```terminal
symfony console debug:autowiring ratelimiter
```

Los dos primeros resultados son los limitadores de inicio de sesión que configuramos en el último curso. Y este
de aquí —el limitador de registro— es el que acabamos de configurar. Podemos ver el objetivo
justo aquí: `registration`. Cópialo.

De vuelta en el controlador, encima del argumento ` `RateLimiterFactoryInterface` `, añade un
atributo ``#[Target]` ` con la cadena ` `registration``:

[[[ code('5dcf8f9b9f') ]]]

Fíjate en que estamos inyectando la fábrica, no el limitador en sí.

## Usar un token

Ahora crea el limitador desde la fábrica. ¿Dónde debería aplicarse la limitación de frecuencia? Una vez que
se haya enviado el formulario y sea válido, justo antes de crear realmente el nuevo
usuario en la base de datos.

Así que, al principio de ese bloque, escribe ` `$limit = $limiterFactory->create()``. ` `create()`` 
toma la clave de este limitador, es decir, lo que debería ser diferente para cada usuario. La
opción más sencilla es ` `$request->getClientIp()``. Eso nos da el propio limitador y, ahora,
consumimos un token —o, en realidad, registramos un acceso—: ` `->consume()``. Por defecto, eso consume un
único token:

[[[ code('bd4ddea7dc') ]]]

Lo que devuelve `consume()` es un objeto especial de tipo `RateLimit` con algunos métodos
interesantes. Podemos preguntar si nos han aceptado. Podríamos llamar a `ensureAccepted()`, que
lanza un `RateLimitExceededException` si no ha sido así. Y nos da información muy
útil: cuándo puedes volver a intentarlo, cuántos tokens quedan, cuál es el límite. Incluso
puedes llamar a `wait()` para quedarte ahí a la espera hasta que haya tokens disponibles. ¡Genial!

Lo haremos de forma amigable. Escribe `if (!$limit->isAccepted())` y, dentro, añade un flash:
`$this->addFlash('error', 'Too many registration attempts. Please try again later.')`.
Después, redirígelos a la página de registro para que esperen allí:
`return $this->redirectToRoute('app_register')`:

[[[ code('c78e06e063') ]]]

## Haciendo que la caché sea real

Hay algo que tenemos que hacer antes de probar esto: los limitadores de velocidad guardan su estado en la
caché por defecto. Y antes cambiamos nuestra caché por defecto. Abre
`config/packages/cache.yaml`... sí: `app: cache.adapter.array`, que es una caché en memoria
que se olvida de todo al final de la petición. Cámbiala a
`cache.adapter.filesystem`:

[[[ code('b8af97756f') ]]]

Es hora de probarlo. Ve a la página de registro y crea una nueva cuenta: correo electrónico
`worf@enterprise.space`, nombre de usuario `worf`, nombre `Worf` y, para la contraseña, algo
complejo: `makeitso1engage?`. Acepta las condiciones y envía el formulario. Perfecto: ya estamos registrados e
iniciados sesión como Worf.

Ahora cierra sesión, vuelve a hacer clic en «Registrarse» e intenta con un segundo usuario: `natasha@enterprise.space`,
nombre de usuario `natasha`, nombre `Natasha Yar`, la misma contraseña, acepta los términos... y
ahí lo tienes:

> Demasiados intentos de registro. Inténtalo de nuevo más tarde.

De hecho, estamos sujetos a un límite de intentos; no hemos podido superarlo. En unos 10 minutos, podremos
registrarnos de nuevo.

Y esto es todo por hoy en «Seguridad en Symfony: ¡Vamos más allá!». Gracias por programar conmigo. Si
tienes alguna pregunta, déjanosla en los comentarios.

Hasta la próxima. ¡Feliz programación!
