# Desactivar usuarios con un UserChecker personalizado

Vamos a añadir el concepto de usuarios desactivados. Son usuarios que tienen algún tipo de
marcador que los identifica como desactivados. Cuando están desactivados, no pueden iniciar
sesión. Y si ya han iniciado sesión, se les cerrará la sesión de inmediato.

Puedes pensar en esto como una eliminación temporal. Eliminar al usuario de nuestra base de datos
tendría, en la práctica, el mismo efecto, pero desactivarlo te permite volver a activarlo más adelante
por cualquier motivo.

## Añadir el indicador « `disabledAt` »

Lo primero que necesitamos es un indicador en nuestra entidad `User`. Añádelo con el bundle Maker:

```terminal
symfony console make:entity
```

Elige `User`. Ahora, para el nombre de la propiedad… podríamos añadir un indicador booleano « `enabled` »,
pero lo que me gusta hacer con este tipo de indicadores es convertirlos en marcas de tiempo. Si
tienen una fecha y hora, significa «true». Si es nulo, significa «false».

Usa « `disabledAt` ».

Esto te da un poco más de control. Con un «verdadero/falso», no puedes ver cuándo
se desactivaron. Pero con esto, `disabledAt` es la hora exacta en la que se desactivaron,
así que tienes más visibilidad sobre cuándo cambió el estado.

Cámbialo a « `datetime_immutable` ». ¿Puede este campo ser « `null` »? ¡Sí! Porque recuerda que un
valor «`null` » significa que están habilitadas. Y… eso es todo.

Ahora haz rápidamente la migración:

```terminal
symfony console make:migration
```

Vuelve a nuestro IDE y busca la migración. En la descripción, pon
`add user.disabledAt column`:

[[[ code('2e404a663f') ]]]

En `src/Entity/User.php`, efectivamente, ahí está nuestra propiedad `disabledAt`, y
está en `null` por defecto, justo lo que queremos. Cuando se crean usuarios, queremos que
estén habilitados. Aquí abajo tenemos el setter y el getter. Voy a añadir un método auxiliar
para que sea más fácil saber si están habilitados o deshabilitados,`public function isEnabled(): bool` y, dentro de él, `return null === $this->disabledAt;`:

[[[ code('235cffb08d') ]]]

## Un estado « `disabled()` » para Foundry

Foundry te permite añadir estados a tu fábrica, y es bastante sencillo
de hacer. Quiero añadir un estado « `disabled` » a nuestra fábrica de usuarios para que podamos crear usuarios
que estén desactivados desde el momento de su creación.

Ve a « `src/Factory/UserFactory.php` » y añade « `public function disabled(): self` ».
Dentro: « `return $this->with(['disabledAt' => new \DateTimeImmutable()]);` »:

[[[ code('6df4503981') ]]]

Ahora haz que Picard sea un usuario desactivado por defecto. Ve a `src/Story/AppStory.php`. Para
Jean-Luc Picard, `UserFactory::createOne()` crea directamente el usuario. Para añadir un
estado, cambia `createOne` por `new()`, llama a `disabled()` sobre él y, a continuación, llama a `create()`:

[[[ code('6c0c8589dc') ]]]

Podríamos haber mantenido el `createOne()` y haber establecido la propiedad `disabledAt` en el array,
pero usar estados es más reutilizable y, en mi opinión, más legible.

Vuelve a la consola y vuelve a cargar los fixtures:

```terminal
symfony console foundry:load-fixtures
```

Picard debería aparecer marcado como desactivado. Pero ese indicador aún no hace nada; hay que
comprobarlo...

## Creando el `UserChecker`

¡Y para eso sirve precisamente un servicio de « `UserChecker` »!

Crea una nueva clase en nuestro directorio « `src/` ». Llámala
«`UserChecker` », y el espacio de nombres será « `App\Security` », así que estará en un subespacio de nombres « `Security`» 
.

Crea « `final` » y añade « `implements UserCheckerInterface` ». Esta interfaz tiene dos
métodos que tienes que implementar. Está « `checkPreAuth()` », que se ejecuta cuando se ha
obtenido el usuario correcto, pero antes de que se compruebe que la contraseña es válida. Y
está « `checkPostAuth()` », que se llama después de que se haya verificado que las credenciales
son correctas.

No vamos a usar ` `checkPostAuth()``, así que elimínalo. Dentro de ` `checkPreAuth()``,
primero ` `if (!$user instanceof User)`` y luego ` `return``. Debajo de eso,
``if (!$user->isEnabled())``, y dentro de ` `throw new DisabledException();``:

[[[ code('23b07073ee') ]]]

## Excepciones de estado de la cuenta

`DisabledException` es una excepción de estado de cuenta. Si te fijas bien, verás
que hereda de `AccountStatusException`: este es el tipo de excepción
que lanzas en un verificador de usuario, ya sea en el método pre-auth o en el post-auth.

Puedes implementar tu propia excepción de estado de cuenta, pero hay un montón de
excepciones listas para usar. Por ejemplo, « `CredentialsExpiredException` » se podría lanzar si
tienes un sistema que caduca las credenciales al cabo de 30 o 60 días. También están
«`AccountExpiredException` » y « `LockedException` ». La que estamos usando es
«`DisabledException` ».

También está `CustomUserMessageAccountStatusException` —un nombre un poco largo—, que te permite
lanzar una excepción de estado genérica. Yo elegiría una de las específicas o crearía
la tuya propia, para que quede más claro cuál es el estado real de la cuenta.

## Activar el verificador de usuarios

Este servicio existe, pero de momento no hace nada: no está configurado automáticamente
de ninguna manera. Tenemos que habilitarlo en nuestra configuración de seguridad. Abre
`config/packages/security.yaml` y, debajo de nuestro cortafuegos « `main` », añade
`user_checker: App\Security\UserChecker`:

[[[ code('846c7c6c58') ]]]

Ese es el ID del servicio. Recuerda que, cuando Symfony realiza el autowiring de clases, utiliza el
nombre de la clase como ID del servicio.

¡Y ya está! Intenta iniciar sesión como Picard. Ve a la página de inicio de sesión, introduce
`picard@enterprise.space` y `makeitso`.

Aparece el mensaje genérico «Credenciales no válidas». En este caso, sí que he usado la contraseña correcta.
Pero como Picard está desactivado, no puede iniciar sesión: hemos lanzado esa excepción de estado de la cuenta.
Hablaremos de cómo personalizar los mensajes de error un poco más adelante.

Así que, por ahora, los usuarios desactivados no pueden iniciar sesión. Pero, ¿qué pasa si ya han iniciado sesión
y se les desactiva durante la sesión? A continuación: vamos a asegurarnos de que a un usuario desactivado
se le cierre la sesión en el momento en que se le desactive.