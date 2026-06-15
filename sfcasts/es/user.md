# Creación de la clase de usuario

Lo primero que necesitamos para autenticar a alguien es una clase de usuario especial. Se trata de una clase
que debe implementar la interfaz ` `UserInterface` ` proporcionada por Symfony. Symfony tiene algunas implementaciones listas para usar
para casos especiales, pero para la mayoría de las aplicaciones, querrás crear tu propia clase de usuario.

## `make:user`

La forma más sencilla de hacerlo es utilizando el bundle maker, que ofrece un pequeño asistente para ayudarte.
Ve al terminal y ejecuta:

```terminal
symfony console make:user
```

¿Cómo llamamos a esta clase? El nombre por defecto, ` `User``, tiene mucho sentido.

¿Queremos guardar este usuario en la base de datos? ¡Sí, claro que sí! Esto convertirá nuestra clase de usuario
en una entidad Doctrine.

A continuación, necesitamos una propiedad única para identificar a nuestros usuarios. Esto es lo que Symfony, y en nuestro
caso, Doctrine, utiliza para cargar usuarios desde la base de datos. « `email` » es una opción habitual, ya que estos deben
ser siempre únicos para el usuario. En ciertas aplicaciones, donde los datos del usuario pueden ser públicos, quizá
te interese usar un nombre de usuario único en su lugar. Como hacen GitHub, Slack y muchas otras plataformas sociales.

Ahora nos preguntan si nuestro `User` necesita una contraseña... ¿eh? ¿De verdad es opcional la contraseña?

Más o menos. Con cosas como SSO o LDAP, Symfony en realidad no necesita
almacenar ni comprobar ninguna contraseña. En su lugar, un sistema externo se encarga del inicio de sesión y, una vez que todo va bien,
simplemente le dice a tu aplicación: «Sí, este usuario está autenticado». En ese momento, Symfony confía en el resultado, y puedes
prescindir por completo de almacenar una contraseña.

Pero para nuestros propósitos, `yes`, sí queremos almacenar y verificar las contraseñas nosotros mismos.

Vale, si miramos qué se ha cambiado: se han creado una entidad `User` y un `UserRepository`, y se ha actualizado nuestra
configuración de`security.yaml`.

## Comprender la entidad de usuario

Echemos un vistazo a la entidad `User`. En tu IDE, abre `src/Entity/User.php`.

Lo primero que llama la atención es que la clase implementa dos interfaces. Fíjate en la primera,
`UserInterface`. Esta es la interfaz principal que todo usuario de seguridad debe implementar. Solo tiene
dos métodos: `getRoles()`, que devuelve una matriz de roles; hablaremos de ellos
más adelante. El segundo es `getUserIdentifier()`. Este devuelve una cadena que identifica de forma única al usuario.

Echa un vistazo a la segunda interfaz, `PasswordAuthenticatedUserInterface`. Esto
le indica a Symfony que nuestro sistema de autenticación requiere una contraseña y tiene un único método: `getPassword()`.
Si antes hubiéramos respondido que no a la pregunta sobre la contraseña, esta interfaz no se habría implementado.

Volviendo a `User`, vemos el atributo estándar de la clase de entidad de Doctrine: `ORM\Entity`. También tenemos este
atributo`ORM\UniqueConstraint`. Esto crea un índice único en la columna `email` de la base de datos. Esto
garantiza que no haya dos usuarios con el mismo correo electrónico. También mejora el rendimiento al buscar usuarios por correo electrónico,
lo cual es importante, ya que lo haremos muy a menudo. Básicamente, en cada petición en la que un usuario haya iniciado sesión.

Si bajamos un poco, tenemos nuestra propiedad estándar `id` y la propiedad `email`. La propiedad `roles` es una matriz (una
lista de cadenas) que se almacena como JSON en la base de datos.

Luego tenemos nuestra propiedad ` `password` ` y los getter y setter para ella.

Aquí hay algunas cosas interesantes que ha añadido el comando `make:user`.

La implementación de ` `getUserIdentifier()` ` devuelve la propiedad `email`, que, si te acuerdas, es lo que
elegimos como nuestro identificador único.

`getRoles()` tiene un poco de lógica extra. Recoge los roles que están guardados en la base de datos, pero también añade siempre
`ROLE_USER` a la lista. Es una convención común que todos los usuarios tengan al menos este rol.

Justo al final hay un método mágico de ` `__serialize()` `. Cada vez que se serializa un usuario para guardarlo en la sesión, se llama a este método. Devuelve las propiedades y los valores del usuario como una matriz. Esta lógica rara para generar la contraseña reemplaza la contraseña real por su hash. Esta es una buena práctica para evitar que se expongan datos confidenciales, como la contraseña.

## Personalización de la entidad User

Quiero que nuestros usuarios tengan un nombre, así que personalicemos nuestra entidad `User`. Recuerda que nuestra `User` es una entidad Doctrine,
por lo que podemos usar el bundle maker para añadirle propiedades.

En tu terminal, ejecuta:

```terminal
symfony console make:entity
```

Selecciona nuestra entidad `User` existente para modificarla. Añade una propiedad `name` como `string`
y con la longitud de campo predeterminada `255`. Asegúrate de que no pueda ser nula en la
base de datos: queremos que todos los usuarios tengan un nombre.

Comprueba que la entidad se ha actualizado... Aquí están los métodos getter y setter para `name`... y
aquí arriba está la propiedad. ¡Genial!

## Comprender las actualizaciones de `security.yaml` 

El comando `make:user` también actualizó nuestra configuración de seguridad. Abre `config/packages/security.yaml` para
ver los cambios.

En primer lugar, ha sustituido el proveedor de usuarios en memoria por `app_user_provider`, que está configurado como proveedor de `entity` para nuestra clase `App\Entity\User`. El `property` para buscar usuarios se ha establecido según nuestra elección: `email`.

En nuestro firewall `main`, ha cambiado el antiguo proveedor `users_in_memory` por nuestro nuevo `app_user_provider`.
Esto significa que cuando Symfony necesite cargar un usuario en nuestro firewall `main`, lo obtendrá de nuestro
`UserRepository`, básicamente llamando a `findOneByEmail()`.

¡Qué ingenioso!

## Migración de usuarios

Antes de que se nos olvide, en nuestra terminal, hagamos una migración con:

```terminal
symfony console make:migration
```

Abre la nueva migración en el directorio `migrations`... En el método `up()`, vemos el SQL para crear la tabla `user`.
Establece la descripción en: `Add User entity`:

[[[ code('c317523d93') ]]]

Ahora, ejecuta la migración con:

```terminal
symfony console doctrine:migrations:migrate
```

¡Genial, ha funcionado!

## `UserFactory`

Entonces, ¿cómo incorporamos usuarios a nuestra app? Más adelante veremos un formulario de registro,
pero por ahora, usemos una fábrica de Foundry para el desarrollo local.

Crea la fábrica con:

```terminal
symfony console make:factory
```

Selecciona nuestra entidad `User`.

Abre `src/Factory/UserFactory.php`. ¡Creo que podemos mejorar un poco estos valores predeterminados!

Para `email`, usa `self::faker()->unique()->email()`:

[[[ code('512c2454b7') ]]]

Llamar a « `unique()` » antes de « `email()` » garantiza que cada correo electrónico generado sea único.

Para `name`, usa `self::faker()->name()` para generar un nombre aleatorio que parezca real:

[[[ code('bebb8e663f') ]]]

Para `password`, en lugar de una cadena aleatoria de Faker, ponlo en un valor conocido, ¿qué tal `engage`:

[[[ code('d2270de05a') ]]]

De esta forma, si creamos un montón de usuarios, ¡ya sabemos la contraseña de todos ellos!

Por último, tenemos que crear un usuario en nuestros fixtures. Abre `src/Story/AppStory.php`. En la parte superior del método `build()`, añade
`UserFactory::createOne()`. Dentro de una matriz, añade `'email' => 'picard@enterprise.space'`, `'name' => 'Jean-Luc Picard'`,
y `'password' =>`... ¿Qué tal `makeitso`?

[[[ code('6b8037eb8a') ]]]

`earlgrayhot` ¡habría sido demasiado obvio!

Ahora carga los fixtures en tu terminal con:

```terminal
symfony console foundry:load-fixtures
```

Para comprobar que nuestro usuario se ha añadido correctamente, ejecuta:

```terminal
symfony console dbal:run-sql 'select * from user'
```

¡Genial, aquí está! Pero mmm... tenemos un grave problema de seguridad aquí... no te preocupes, lo arreglaremos pronto... pero
¿puedes adivinar cuál es?

A continuación, crearemos un formulario de inicio de sesión para que nuestros usuarios puedan iniciar sesión de verdad.