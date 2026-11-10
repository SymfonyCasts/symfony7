# Cómo crear limitadores de tasa con RateLimiterBuilder

Esto último no tiene que ver estrictamente con la seguridad, pero se podría decir que sí. En`src/Controller/RegistrationController.php`, usamos la limitación de tasa para que nadie pueda registrar
nuevos usuarios una y otra vez. Y Symfony 8.2 añade algunas mejoras interesantes a los limitadores de tasa.

En nuestra acción `register()`, inyectamos un ` `RateLimiterFactoryInterface``, utilizando el
autowiring por nombre — `#[Target('registration')]` — para obtener el de ` `registration` `. Eso está
configurado en `config/packages/rate_limiter.yaml`. Pero este es un limitador de tasa de uso único:
solo lo usamos para el registro. Así que resulta un poco molesto configurarlo en un sitio
y luego tener que saltar a otra parte para usarlo en el controlador.

## Hola `RateLimiterBuilder`

En la versión 8.2 hay un nuevo `RateLimiterBuilder` que te permite crear limitadores de tasa directamente en el código.
Elimina el argumento `#[Target]` y, en su lugar, inyecta `RateLimiterBuilder` y llámalo
`$limiterBuilder`:

[[[ code('220188f02db8200d8e80d1ac57a53d7b') ]]]

Más abajo, amplía un poco la línea donde creamos el límite. En lugar de `$limiterFactory`,
que ya no existe, usa `$limiterBuilder`. Sus métodos te permiten crear cualquiera de los
limitadores directamente en el código: `slidingWindow()`, `compound()` —una combinación de varios limitadores—,`fixedWindow()`, `noop()` —un limitador de tasa nulo— y `tokenBucket()`. Todos los
limitadores de tasa disponibles están aquí.

Elige `slidingWindow()`. Lo primero que necesita es un ID. Usa el nombre que teníamos en la
configuración: `registration`. Echa un vistazo a `slidingWindow()` y verás que necesita un límite
y un intervalo, igual que en la configuración. Nuestro límite es `1` y nuestro intervalo es
`'10 minutes'`:

[[[ code('508a6d4c1189694e0790759601ae98ed') ]]]

¡Y ya está! Ya no necesitamos la configuración en absoluto. Y como es el único limitador de tasa
que hemos configurado, borra por completo `config/packages/rate_limiter.yaml`.

Solo incluye un limitador de tasa como este en el código si realmente es algo puntual. Si usas el mismo limitador de tasa
en varios sitios, sigue configurándolo como hacíamos antes. Ese enfoque no
va a desaparecer: no se va a dejar de usar.

## Probémoslo

¡Probémoslo! Ve a la página de registro y regístrate como `worf@enterprise.space`,
nombre de usuario `worf`, nombre `Worf`, con una contraseña compleja. Acepta los
términos y... ¡genial! Ya estamos registrados e iniciados sesión como Worf.

Ahora cierra la sesión y regístrate de nuevo, esta vez como `natasha@enterprise.space`, con el nombre de usuario`natasha`, el nombre `Natasha Yar` y otra contraseña compleja; acepta los términos y… ¡nos han
limitado la frecuencia de acceso! Efectivamente, funciona exactamente igual que antes, solo que te hace la vida
un poco más fácil.

## ¡Y ya está!

¡Y esto es todo en nuestro repaso a las novedades de seguridad de Symfony 8.2! Como dije al
principio, la versión 8.2 está resultando ser una de las mayores actualizaciones de Symfony hasta la fecha. Échale un vistazo al
[blog de Symfony](https://symfony.com/blog/) para ver entradas sobre todas las nuevas funciones: hay
docenas de ellas, y es una forma estupenda de ponerte al día. Hay incluso más funciones de seguridad
que no he mencionado aquí.

Si tienes alguna pregunta sobre todo esto, estamos aquí para ayudarte en los comentarios.

Bueno, amigos...

¡Hasta la próxima, y feliz programación!
