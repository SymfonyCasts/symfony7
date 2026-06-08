# Comprender el cifrado de contraseñas

Vale, lo dejamos intentando iniciar sesión con un nombre de usuario y una contraseña válidos, pero no funcionó... Tomémonos un momento para entender por qué.

En el terminal, vuelca nuestra tabla de usuarios con:

```terminal
symfony console dbal:run-sql 'select * from user'
```

## ¡Las contraseñas en texto plano son malas!

El problema es que la contraseña de la base de datos se almacena en texto plano. En primer lugar, esto supone un enorme riesgo para la seguridad... Si los romulanos acceden a nuestra base de datos, ¡podrán ver todas las contraseñas de nuestros usuarios! 

La razón por la que nuestro inicio de sesión no funciona es que el sistema de seguridad de Symfony espera que esta contraseña sea hash, pero no lo es.

Así que ¡haz hash de esta contraseña! Ejecuta:

```terminal
symfony console security:hash-password
```

Esto nos da un campo de entrada oculto, escribe nuestra contraseña en texto plano: `makeitso`.

Esta es la versión hash de `makeitso`. Parece una locura, ¿verdad? Esto es lo que tenemos que almacenar en el campo de contraseña de nuestros usuarios en la base de datos.

## Hashing vs Cifrado

Tomémonos un momento para hablar del hashing. El hashing es un proceso unidireccional que convierte un valor, como una contraseña, en una cadena que puedes almacenar de forma segura, pero que no es realista revertir en el valor original. Esto es diferente de la encriptación, que está diseñada para ser reversible con la clave adecuada. En otras palabras, la encriptación es como encerrar algo en una caja y desbloquearlo más tarde, mientras que el hashing es más como transformarlo en una huella dactilar única. Puedes comparar huellas dactilares para comprobar si coinciden, pero no puedes reconstruir el valor original.

La salida del comando también nos dice qué hasher se está utilizando: existen varios algoritmos hash diferentes, y Symfony admite muchos de ellos. El hasher que se está utilizando es el `MigratingPasswordHasher`.

Se trata de un hasher especial que permite una función muy interesante en Symfony: ¡la migración de contraseñas!

## El hasher `auto` y la migración de contraseñas

En tu IDE, abre `config/packages/security.yaml` y comprueba la sección `password_hashers`. Tenemos un único hasher configurado para nuestros usuarios `PasswordAuthenticatedUserInterface`, y está establecido en `auto`. ¿Cubre esto nuestro `User` personalizado? Abre `src/Entity/User.php` y comprueba la declaración de la clase. Como implementa `PasswordAuthenticatedUserInterface`, está cubierto por el hasher `auto`.

¿Qué es el hasher `auto`? Esto te va a encantar! `auto` le dice a Symfony que "elija el mejor hasher disponible". La seguridad es un objetivo en movimiento y con el tiempo se desarrollan nuevos algoritmos hashing. Actualmente, el mejor algoritmo hasher es `bcrypt`. Cuando aparezca un algoritmo nuevo y mejor, Symfony empezará a aplicar automáticamente el hash a las contraseñas Este es el comportamiento que nos ofrece `auto`. Es importante tener en cuenta que actualizar tu versión de Symfony es lo que te permite aprovecharte de esto. ¡Otra buena razón para mantener actualizada tu versión de Symfony!

Vale, así que dentro de unos años sale un nuevo algoritmo hash, y los nuevos usuarios empiezan a utilizarlo, ¿qué pasa con nuestros antiguos usuarios? ¿Estarán atrapados con el hash antiguo y menos seguro? 

Esto te va a encantar por partida doble! `auto` también habilita el `MigratingPasswordHasher`. Cuando los usuarios se conectan, si se determina que su contraseña utiliza un algoritmo hash antiguo, ¡Symfony la actualizará al nuevo automáticamente!

## Rehacer contraseñas al iniciar sesión

¿Cómo funciona esto? Bien, cuando un usuario se conecta correctamente, Symfony comprueba el hash de su contraseña existente. Si detecta que el hash se generó utilizando un algoritmo antiguo, rehace la contraseña utilizando el nuevo. Recuerda, en este punto, Symfony todavía tiene acceso a la contraseña en texto plano, ya que el usuario acaba de enviarla para iniciar sesión. A continuación, actualiza el Usuario con el nuevo hash

Este proceso de actualización debe configurarse en tu aplicación. Pero como hemos utilizado el bundle maker para generar nuestra entidad de usuario, esto ya está configurado para nosotros. Abre `src/Repository/UserRepository.php`. Esta clase de repositorio generada es un poco diferente de las estándar de `make:entity`. Implementa `PasswordUpgraderInterface` del componente de seguridad. Esta interfaz tiene un único método: `upgradePassword()`. Este es el método al que se llama cuando se determina que la contraseña de un Usuario necesita un refrito. Acepta el Usuario en cuestión y la nueva contraseña con hash.

Echa un vistazo a lo que nos ha generado el maker-bundle en `UserRepository`. Primero se asegura de que se trata del objeto `User` correcto. A continuación, llama a `setPassword()` sobre el Usuario, pasándole el hash de la nueva contraseña. Por último, persiste y vacía el Usuario para guardarlo. ¡Pum! ¡El hash de la contraseña se ha actualizado!

Esta función de Symfony es fantástica para garantizar la seguridad de tu aplicación en el futuro

## Utilizar la Contraseña Hashed

Muy bien, ahora que tenemos nuestra contraseña hash, vamos a utilizarla. Copia el hash del terminal y abre `src/Story/AppStory.php`. Sustituye la contraseña en texto plano de `makeitso` por el hash copiado:

[[[ code('d18f691117') ]]]

De nuevo en el terminal, recarga nuestras instalaciones con:

```terminal
symfony console foundry:load-fixtures
```

Vuelve a nuestra página de inicio de sesión... actualiza... introduce `makeitso` como contraseña... ¡y envía!

No hay error, bueno, mi navegador se queja de la contraseña incorrecta, ¡pero estamos en la página de inicio e iniciamos sesión correctamente! Abajo, en la barra de herramientas de depuración web, podemos ver nuestro identificador de usuario: `picard@enterprise.space`. Al pasar el ratón por encima, se nos muestran más detalles sobre el usuario. Roles, roles heredados y clase de token. No te preocupes, estas cosas se tratarán un poco más adelante. Y genial, ¡incluso tenemos un enlace de cierre de sesión!

## Contraseñas más cómodas

Una cosa que resulta un poco molesta de nuestros dispositivos es tener que introducir manualmente la contraseña cada vez que creamos un usuario. Desde luego, no es obvio que este hash tan largo sea para la contraseña`makeitso`.

Me gustaría poder utilizar la contraseña en texto plano al crear dispositivos.

¡Podemos hacerlo! Abre `src/Factory/UserFactory.php`. Las fábricas de Foundry también son servicios de Symfony, ¡así que podemos inyectarles otros servicios!

En el constructor, inyecta `private UserPasswordHasherInterface $passwordHasher`:

[[[ code('20067b51a0') ]]]

Este es el servicio que procesa la contraseña en función de nuestra configuración de seguridad.

Abajo, en el método `default()`, seguiremos manteniendo esta contraseña en texto plano como valor por defecto. Aplicaremos el hash a la contraseña en un gancho Foundry. Éstos se definen en `initialize()`. Descomenta la línea`afterInstantiate()` para activar el gancho. Se ejecuta justo después de crear el objeto, pero antes de guardarlo en la base de datos. El callback acepta el objeto creado, en este caso, un `User`.

Dentro, escribe `$user->setPassword()`, y dentro de éste, `$this->passwordHasher->hashPassword()`. El primer argumento de este método es el objeto `$user`, esto es necesario para determinar el algoritmo hash correcto para este usuario. Sólo tenemos uno, pero se pueden configurar varios. El segundo argumento es la contraseña en texto plano. Utiliza `$user->getPassword()`. Recuerda que, en este punto, la contraseña configurada para el usuario sigue siendo de texto plano. Termina con un punto y coma al final, ¡y ya está!

[[[ code('df9be89f27') ]]]

En `AppStory`, cuando se cree este objeto usuario, tendrá su contraseña establecida en `makeitso`:

[[[ code('f12d791ad4') ]]]

Nuestro gancho `afterInstantiate()` la sustituirá por la versión con hash y la guardará en la base de datos.

De vuelta en el terminal, vuelve a cargar nuestras instalaciones:

```terminal-silent
symfony console foundry:load-fixtures
```

Vuelve al navegador y actualiza la página de inicio. ¿Has visto lo que ha pasado? ¡Se nos ha cerrado la sesión!

Eso no es un error, ¡es una característica! De hecho, una función de seguridad. Cuando recargamos nuestras instalaciones, el hash de la contraseña de nuestro usuario cambió. Nuestra contraseña en texto plano sigue siendo `makeitso`, pero el hash cambió. Cada vez que haces un hash de una contraseña, aunque sea la misma, obtienes un hash diferente.

## Sal... ¡y Peppa!

Los buenos algoritmos de hash de contraseñas incluyen un valor aleatorio llamado sal, para que la misma contraseña no produzca siempre el mismo hash. Esto ayuda a protegerse contra ataques precomputados en los que se comparan contraseñas comunes con hashes conocidos. Esto se denomina ataque de tabla arco iris. La sal rompe ese atajo.

Symfony vio que nuestra contraseña había cambiado, así que, por seguridad, nos cerró la sesión.

¿Has visto alguna vez una función de "cierre de sesión de otros dispositivos" en otra aplicación? ¡Así es como podrías implementar lo mismo en Symfony! Cuando el usuario elige esta opción, confirma su contraseña, tú le aplicas un hash y actualizas el hash en la base de datos. ¡Pum! ¡Todas las demás sesiones acaban de dejar de ser válidas!

Volvamos a iniciar sesión con `picard@enterprise.space`, contraseña: `makeitso`... Sí, lo sé, lo sé, mala contraseña. ¡Hemos vuelto a la página principal y hemos iniciado sesión con éxito!

¡Ahora es mucho más fácil trabajar con nuestra fijación de Usuario!

## Optimizar la velocidad de las pruebas

Por diseño, el proceso de hash de las contraseñas es intencionadamente lento y consume mucha CPU. Se trata de una característica de seguridad. Intentar adivinar las contraseñas originales por fuerza bruta lleva mucho más tiempo.

Si tienes muchas pruebas de características o de integración que impliquen el descifrado de contraseñas, como la creación de usuarios y el inicio de sesión, esto puede ralentizar significativamente tu conjunto de pruebas, en realidad sin motivo. No hay riesgo de seguridad en tus pruebas.

No te preocupes, ¡Symfony te tiene cubierto! Vuelve a nuestro archivo `security.yaml` y desplázate hasta la sección `when@test`. Esto anula el hash de contraseñas configurado anteriormente en nuestro entorno `test`. Estas opciones ajustan el coste y el tiempo que tarda el hash de una contraseña, haciéndolo mucho más rápido, prácticamente instantáneo.

Asegúrate de que estos valores bajos no se utilicen nunca en tu entorno de producción. Podrías aumentar estos valores en producción, pero, para la mayoría de las aplicaciones, los valores por defecto ya son un buen equilibrio entre seguridad y rendimiento.

A continuación, ¡ajustaremos nuestra plantilla base para incluir enlaces de inicio y cierre de sesión en la barra de navegación!
