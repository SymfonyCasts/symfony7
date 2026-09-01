# Limitar los intentos de inicio de sesión

Es hora de hablar de la limitación de intentos de inicio de sesión: una forma de limitar el número de intentos fallidos.
Esto protege tu app, a nivel de aplicación, contra alguien
que intente forzar una cuenta mediante un ataque de fuerza bruta —probando todas las combinaciones de contraseñas posibles hasta que consiga
acceder—.

## Cómo activarlo `login_throttling`

Para activar esta función, en tu IDE, abre `config/packages/security.yaml`. Abajo,
en nuestro cortafuegos « `main` », añade `login_throttling: true`:

[[[ code('a3ce12bb52') ]]]

Ahora actualiza la página de inicio de sesión… ¡y nos sale un error! La limitación de accesos requiere el componente limitador de
tasa de Symfony. Copia el `composer require` del mensaje de error, ve a
tu terminal, anteponle `symfony` … y pégalo:

```terminal
symfony composer require symfony/rate-limiter
```

Perfecto.

## Hacer que la limitación funcione localmente

De vuelta en nuestro IDE, hay otra cosa que cambiar. Por defecto, el limitador de tasa usa
nuestra configuración de caché para almacenar su estado. Así que abre `config/packages/cache.yaml`.

Aquí abajo, nuestra caché de `app` utiliza el adaptador de matriz, una caché en memoria
que no se mantiene entre peticiones. Este es el valor por defecto para nuestro entorno de desarrollo.
En producción, el valor por defecto es `filesystem`, que sí se mantiene entre peticiones.

Como quiero mostrarte cómo funciona la limitación de inicios de sesión a nivel local, voy a cambiarlo temporalmente
a `filesystem`:

[[[ code('32ca77dbca') ]]]

En general, no te conviene esto: que la limitación de accesos te estorbe durante
el desarrollo es un rollo.

## Activando el limitador

Actualiza la página e intenta iniciar sesión como `picard@enterprise.space`. Para la contraseña,
usa `makeitgo` —una contraseña incorrecta a propósito—. Pulsa Intro.

Eso cuenta como un intento fallido. `makeitgo`. Dos. Tres. Cuatro. Cinco. Seis...

Y ahora hemos activado el limitador de frecuencia:

> Demasiados intentos fallidos de inicio de sesión, vuelve a intentarlo dentro de 1 minuto.

Así que el límite por defecto es de cinco intentos fallidos por minuto. Pasado un minuto, se reinicia y puedes volver a
intentarlo. Esto realmente evita que alguien lance miles de peticiones por segundo a
esta página para intentar forzar el inicio de sesión por fuerza bruta.

## La configuración de « `login_throttling` »

Echemos un vistazo a las opciones de configuración que tenemos para esta función. Vuelve a tu terminal y ejecuta:

```terminal
symfony console config:dump security firewalls
```

A continuación, desplázate hacia arriba hasta encontrar la sección « `login_throttling` »… aquí la tienes.

Las dos opciones más importantes son « `max_attempts` » y « `interval` ». Como te he dicho, el
valor por defecto es de cinco intentos en un minuto, y así es como puedes ajustarlo.

Ahora, así es como funciona realmente el limitador de inicios de sesión predeterminado: crea dos limitadores de frecuencia.

El primero se basa en la dirección IP y el nombre de usuario; ese es el que establece el límite de cinco
intentos por minuto. Así que no se puede intentar iniciar sesión con el mismo nombre de usuario desde la misma IP más de cinco
veces antes de que se active el límite.

Pero también crea un segundo limitador, llamado «limitador de tasa global». Este se basa
solo en la dirección IP, y su límite es `max_attempts` multiplicado por cinco, es decir, 25 por defecto.
Es otra capa de seguridad para evitar que alguien pruebe miles de combinaciones diferentes de nombres de usuario
desde la misma IP.

Sigue bajando. El limitador de tasa puede usar opcionalmente un bloqueo; en `lock_factory`
puedes configurarlo. Un bloqueo ayuda cuando varias peticiones llegan al mismo limitador de tasa
prácticamente al mismo tiempo. Sin él, dos peticiones podrían ver que aún no se ha
alcanzado el límite y que ambas se permiten pasar.

`cache_pool` Aquí es donde eliges la caché que usa el limitador de tasa. En producción, te conviene que
sea algo persistente; por defecto, se basa en nuestro grupo de `cache.app`. Es
importante que sea una caché compartida entre todos tus servidores de aplicaciones (si tienes varios),
y que no se reinicie durante la implementación.

`storage_service` te permite anular por completo el sistema de almacenamiento del limitador de tasa.

Y, por último, justo en la parte superior, « `limiter` » es donde puedes pasar tu propio servicio y
tomar el control por completo. Si utilizas esto, ninguna de las otras opciones tiene sentido.
Como puedes ver en el comentario, tiene que ser un servicio que implemente
``RequestRateLimiterInterface``.

## Ajustar los límites

Ahora juega un poco con estos ajustes. Ve a `security.yaml` y configura `max_attempts` en `3`, y`interval` en `10 minutes`:

[[[ code('607b5a451f') ]]]

Así que ahora el limitador de IP y nombre de usuario permite tres intentos en 10 minutos... y el
limitador global de IP —recuerda, multiplicado por cinco— permite 15 intentos en 10 minutos desde la
misma IP.

## Ten cuidado con las IP compartidas

Una cosa con la que hay que tener cuidado —y la razón por la que existe el limitador global— son
las direcciones IP compartidas. Si uno de tus clientes es una gran empresa, como un banco, es posible que todos
sus empleados se conecten a Internet a través de la misma IP. Así que no lo restrinjas
demasiado. Imagina que todo el mundo inicia sesión a las nueve de la mañana y un montón
de ellos se equivoca al teclear la contraseña: podrías bloquear a un montón de gente de golpe.
Por eso precisamente el limitador global es más generoso que el que se aplica por nombre de usuario.

Una cosa más que debes tener en cuenta: se trata de una limitación de tasa a nivel de aplicación. Cada
petición sigue llegando a tu app y sigue utilizando recursos del servidor, incluso cuando se ve
limitada. Así que no confíes en esto como una verdadera protección contra DDoS. Para eso, necesitas algo
como Cloudflare, que detiene el ataque antes de que llegue a tu servidor.

Y esto es todo sobre la limitación de inicios de sesión. A continuación: ¡los eventos de seguridad!
