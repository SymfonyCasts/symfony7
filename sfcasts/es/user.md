# Crear la clase de usuario

Lo primero que necesitamos para autenticar realmente a alguien es una clase especial de usuario. Se trata de una clase que debe implementar `UserInterface` proporcionada por Symfony. Symfony tiene algunas implementaciones para casos especiales, pero para la mayoría de las aplicaciones, querrás crear tu propia clase de usuario.

## `make:user`

La forma más sencilla de hacerlo es utilizando el bundle maker, que proporciona un pequeño asistente para ayudarte. Dirígete al terminal y ejecuta:

```terminal
symfony console make:user
```

¿Cómo deberíamos llamar a esta clase? El nombre por defecto, `User`, tiene mucho sentido.

¿Queremos almacenar este usuario en la base de datos? Sí, ¡queremos! Esto hará que nuestra clase usuario sea una entidad Doctrine.

A continuación, necesitamos una propiedad única para identificar a nuestros usuarios. Esto será lo que Symfony, y en nuestro caso Doctrine, utilice para cargar los usuarios desde la base de datos. `email` es una opción común, ya que siempre deben ser únicos para el usuario. En ciertas aplicaciones, en las que los detalles del usuario pueden ser públicos, puedes considerar utilizar un nombre de usuario único en su lugar. Como hacen GitHub, Slack y muchas otras plataformas sociales.

Ahora nos preguntan si nuestro `User` necesita una contraseña... ¿eh? ¿Es realmente opcional una contraseña?

Más o menos. Con cosas como SSO o LDAP, Symfony en realidad no necesita almacenar o comprobar una contraseña en absoluto. En su lugar, un sistema externo gestiona el inicio de sesión, y una vez que está satisfecho, simplemente le dice a tu aplicación: "sip, este usuario está autenticado" En ese momento, Symfony confía en el resultado, y puedes omitir por completo el almacenamiento de una contraseña.

Pero para nuestros propósitos, `yes`, sí queremos almacenar y verificar las contraseñas nosotros mismos.

Bien, si nos fijamos en lo que ha cambiado: se han creado una entidad `User` y `UserRepository`, y se ha actualizado nuestra configuración de`security.yaml`.

## Comprender la entidad Usuario

Echemos un vistazo a la entidad `User`. En tu IDE, abre `src/Entity/User.php`.

Lo primero que debes observar es que la clase implementa dos interfaces. Indaga en la primera,`UserInterface`. Ésta es la interfaz principal que todo usuario de seguridad debe implementar. Sólo tiene dos métodos: `getRoles()`, que devuelve una matriz de roles -hablaremos de ellos más adelante. El segundo es `getUserIdentifier()`. Devuelve una cadena que identifica unívocamente al usuario.

Profundiza en la segunda interfaz, `PasswordAuthenticatedUserInterface`. Esto permite a Symfony saber que nuestro sistema de autenticación requiere una contraseña y tiene un único método: `getPassword()`. Si hubiéramos dicho antes que no a la pregunta de la contraseña, esta interfaz no estaría implementada.

Volviendo a `User`, vemos el atributo estándar de la clase de entidad Doctrine: `ORM\Entity`. También tenemos este atributo`ORM\UniqueConstraint`. Esto crea un índice único en la columna `email` de la base de datos. Esto garantiza que no haya dos usuarios con el mismo correo electrónico. También mejora el rendimiento cuando se buscan usuarios por correo electrónico, lo que es importante porque lo haremos a menudo. Básicamente, todas las peticiones en las que un usuario está conectado.

Desplazándonos hacia abajo tenemos nuestra propiedad estándar `id` y la propiedad `email`. La propiedad `roles` es una matriz (una lista de cadenas) que se almacena como JSON en la base de datos.

Luego tenemos nuestra propiedad `password` y los getters y setters correspondientes.

Aquí tienes algunas cosas interesantes que ha añadido el comando `make:user`.

La implementación de `getUserIdentifier()` devuelve la propiedad email, que, si recuerdas, es lo que elegimos como identificador único.

`getRoles()` tiene un poco de lógica extra. Coge los roles que están guardados en la base de datos, pero también añade siempre`ROLE_USER` a la lista. Es una convención común que todos los usuarios tengan al menos este rol.

En la parte inferior hay un método mágico `__serialize()`. Siempre que se serializa un usuario para almacenarlo en la sesión, se llama a este método. Devuelve las propiedades y valores del usuario como una matriz. Este método sustituye la contraseña real por un hash de la contraseña. Esta es una buena práctica para evitar que los datos sensibles, la contraseña, queden expuestos.

## Personalizar la entidad de usuario

Quiero que nuestros usuarios tengan un nombre, así que vamos a personalizar nuestra entidad `User`. Recuerda que nuestro `User` es una entidad Doctrine, así que podemos utilizar el maker-bundle para añadirle propiedades.

En tu terminal, ejecuta:

```terminal
symfony console make:entity
```

Selecciona nuestra entidad `User` existente para modificarla. Añade una propiedad `name` como `string`y con la longitud de campo `255` por defecto. Asegúrate de que no puede ser nulo en la base de datos: queremos que todos los usuarios tengan un nombre.

Comprueba que la entidad se ha actualizado... Aquí está el getter y setter para `name`... y aquí arriba está la propiedad. ¡Genial!

## Comprender las actualizaciones de `security.yaml` 

El comando `make:user` también actualizó nuestra configuración de seguridad. Abre `config/packages/security.yaml` para ver los cambios.

En primer lugar, ha sustituido el proveedor de usuarios en memoria por `app_user_provider`, que se establece como proveedor de `entity` para nuestra clase `App\Entity\User`. El `property` por el que buscar a los usuarios se establece a nuestra elección: `email`.

Abajo, en nuestro cortafuegos `main`, ha cambiado el antiguo proveedor `users_in_memory` por nuestro nuevo `app_user_provider`. Esto significa que cuando Symfony necesite cargar un usuario en nuestro cortafuegos `main`, lo obtendrá de nuestro`UserRepository`, básicamente, llamando a `findOneByEmail()`.

¡Bastante ingenioso!

## Migración de usuarios

Antes de que se nos olvide, en nuestro terminal, vamos a hacer una migración con:

```terminal
symfony console make:migration
```

Abre la nueva migración en el directorio `migrations`... En el método `up()`, vemos el SQL para crear la tabla `user`. Establece la descripción en: `Add User entity`:

[[[ code('c317523d93') ]]]

Ahora, ejecuta la migración con:

```terminal
symfony console doctrine:migrations:migrate
```

Muy bien, ¡ha funcionado!

## `UserFactory`

¿Cómo conseguimos que los usuarios entren en nuestra aplicación? Exploraremos un formulario de registro más adelante, pero por ahora, vamos a utilizar una fábrica de Foundry para el desarrollo local.

Crea la fábrica con:

```terminal
symfony console make:factory
```

Selecciona nuestra entidad `User`.

Abre `src/Factory/UserFactory.php`. ¡Creo que podemos mejorar un poco estos valores por defecto!

Para `email`, utiliza `self::faker()->unique()->email()`:

[[[ code('512c2454b7') ]]]

Llamar a `unique()` antes que a `email()` garantiza que cada correo generado sea único.

Para `name`, utiliza `self::faker()->name()` para generar un nombre aleatorio de aspecto real:

[[[ code('bebb8e663f') ]]]

Para `password`, en lugar de una cadena Faker aleatoria, ponle un valor conocido, ¿qué tal `engage`:

[[[ code('d2270de05a') ]]]

De esta forma, si creamos un montón de usuarios, ¡ya sabremos la contraseña de todos ellos!

Por último, tenemos que crear un usuario en nuestras instalaciones. Abre `src/Story/AppStory.php`. En la parte superior del método `build()`, añade`UserFactory::createOne()`. Dentro de un array, añade `'email' => 'picard@enterprise.space'`, `'name' => 'Jean-Luc Picard'`, y `'password' =>`... ¿Qué te parece `makeitso`?

[[[ code('6b8037eb8a') ]]]

`earlgrayhot` ¡habría sido demasiado obvio!

Ahora carga los accesorios en tu terminal con:

```terminal
symfony console foundry:load-fixtures
```

Para volver a comprobar que nuestro usuario se ha añadido correctamente, ejecuta:

```terminal
symfony console dbal:run-sql 'select * from user'
```

Bien, ¡aquí está! Pero hmm... tenemos un grave problema de seguridad aquí... no te preocupes, lo arreglaremos pronto... pero ¿puedes adivinar cuál es?

A continuación, crearemos un formulario de inicio de sesión para que nuestros usuarios puedan conectarse
