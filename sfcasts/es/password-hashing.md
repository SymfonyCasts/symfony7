# Entender el hash de las contraseñas

Vale, nos habíamos quedado en intentar iniciar sesión con un nombre de usuario y una contraseña válidos, pero no funcionó...
Dediquemos un momento a entender por qué.

En la terminal, muestra nuestra tabla de usuarios con:

```terminal
symfony console dbal:run-sql 'select * from user'
```

## ¡Las contraseñas en texto plano son un desastre!

El problema es que la contraseña está guardada en la base de datos en texto plano. Para empezar,
esto supone un riesgo de seguridad enorme... Si los romulanos consiguen acceder a nuestra base de datos, ¡podrán
ver todas las contraseñas de nuestros usuarios! 

La razón por la que no nos dejamos iniciar sesión es que el sistema de seguridad de Symfony espera
que esta contraseña esté cifrada con un hash, pero no lo está.

¡Así que vamos a aplicar un hash a esta contraseña! Ejecuta:

```terminal
symfony console security:hash-password
```

Esto nos da un campo de entrada oculto; escribe nuestra contraseña en texto plano: `makeitso`.

Esta es la versión con hash de `makeitso`. Parece una locura, ¿verdad? Esto es lo que tenemos que
almacenar en el campo de contraseña de nuestros usuarios en la base de datos.

## Hash frente a cifrado

Dediquemos un momento a hablar del hash. El hash es un proceso unidireccional que convierte un valor,
como una contraseña, en una cadena que puedes almacenar de forma segura, pero que, en la práctica, no se puede revertir
al valor original. Eso es diferente del cifrado, que está diseñado para ser reversible
con la clave adecuada. En otras palabras, el cifrado es como cerrar algo con llave en una caja y abrirla
más tarde, mientras que el hash es más bien como transformarlo en una huella digital única. Puedes comparar
huellas digitales para ver si coinciden, pero no puedes reconstruir el valor original.

La salida del comando también nos indica qué generador de hash se está utilizando: hay varios
algoritmos de hash diferentes, y Symfony es compatible con muchos de ellos. El generador de hash que se está utilizando es
el « `MigratingPasswordHasher` ».

Se trata de un generador de hash especial que habilita una función genial en Symfony: ¡la migración de contraseñas!

## El generador de hash « `auto` » y la migración de contraseñas

En tu IDE, abre `config/packages/security.yaml` y echa un vistazo a la sección `password_hashers`.
Tenemos un único generador de hash configurado para nuestros usuarios de `PasswordAuthenticatedUserInterface`, y está establecido en `auto`.
¿Esto cubre nuestro generador de hash personalizado `User`? Abre `src/Entity/User.php` y comprueba la declaración de la clase. Como
implementa `PasswordAuthenticatedUserInterface`, queda cubierto por el generador de hash `auto`.

¿Y qué es el generador de hash `auto`? ¡Esto te va a encantar! `auto` le dice a Symfony que «elija el mejor generador de hash disponible».
La seguridad es un objetivo en constante evolución y, con el tiempo, se desarrollan nuevos algoritmos de hash. Actualmente, el mejor algoritmo de hash
es `bcrypt`. Cuando salga un algoritmo nuevo y mejor, ¡Symfony empezará automáticamente a generar hashes de las contraseñas
usándolo! Eh… vale, ¿eso significa que todos mis usuarios con el hash antiguo ya no podrán iniciar sesión? ¡No! `auto` conoce
no solo el nuevo algoritmo de hash, sino también los antiguos. Puede verificar las contraseñas con el algoritmo antiguo, pero
todas las contraseñas nuevas se generarán con el nuevo.

Este es el comportamiento que nos ofrece `auto`. Es importante tener en cuenta que actualizar tu versión de Symfony es
lo que te permite aprovechar esto. ¡Otra buena razón para mantener tu versión de Symfony al día!

Vale, supongamos que dentro de unos años sale un nuevo algoritmo de hash y los nuevos usuarios empiezan a usarlo, ¿qué pasa con nuestros
usuarios antiguos? ¿Se quedan con el hash antiguo, menos seguro? 

¡Esto te va a encantar doblemente! Los proveedores de usuarios cuentan con el concepto de actualización de contraseñas. Cuando los usuarios inician sesión, si se
determina que su contraseña utiliza un algoritmo de hash antiguo, ¡Symfony la actualizará al nuevo automáticamente!

## Rehash de contraseñas al iniciar sesión

¿Cómo funciona eso? Pues bien, cuando un usuario inicia sesión con éxito, Symfony comprueba el hash de su contraseña actual.
Si detecta que el hash se generó con un algoritmo antiguo, vuelve a calcular el hash de la contraseña utilizando el
nuevo. Recuerda que, en este momento, Symfony todavía tiene acceso a la contraseña en texto plano, ya que el usuario acaba de
introducirla para iniciar sesión. ¡A continuación, actualiza el usuario con el nuevo hash!

Este proceso de actualización hay que configurarlo en tu app. Pero como hemos usado el bundle «maker» para generar nuestra entidad de usuario,
ya está todo configurado. Abre `src/Repository/UserRepository.php`. Esta clase de repositorio generada es un poco diferente
de las estándar de `make:entity`. Implementa `PasswordUpgraderInterface` del componente de seguridad.
Esta interfaz tiene un único método: `upgradePassword()`. Este es el método que se invoca cuando se determina
que hay que volver a generar el hash de la contraseña de un usuario. Acepta el usuario en cuestión y la nueva contraseña con hash.

Echa un vistazo a lo que el «maker-bundle» nos ha generado en `UserRepository`. Primero, simplemente se asegura de que, efectivamente,
se trata del objeto `User` correcto. A continuación, llama a `setPassword()` sobre el usuario, pasando el nuevo hash de la contraseña. Por último,
persiste y vacía la caché del usuario para guardarlo. ¡Y ya está! ¡El hash de la contraseña se ha actualizado!

¡Esta función integrada de Symfony es fantástica para garantizar que la seguridad de tu app siga siendo segura en el futuro!

## Usar la contraseña con hash

Vale, ahora que ya tenemos nuestra contraseña con hash, vamos a darle uso. Copia
el hash desde la terminal y abre `src/Story/AppStory.php`. Sustituye la
contraseña en texto plano de `makeitso` por el hash que has copiado:

[[[ code('d18f691117') ]]]

Vuelve a la terminal y actualiza nuestros datos de prueba con:

```terminal
symfony console foundry:load-fixtures
```

Vuelve a nuestra página de inicio de sesión... actualiza la página... introduce `makeitso` como contraseña... ¡y envía!

No hay ningún error, bueno, mi navegador me dice que la contraseña es incorrecta, ¡pero estamos en la página de inicio
y hemos iniciado sesión con éxito! Abajo, en la barra de herramientas de depuración web, podemos ver nuestro identificador de usuario: `picard@enterprise.space`.
Al pasar el cursor por encima, nos muestra más detalles sobre el usuario: roles, roles heredados y clase de token. No te preocupes,
hablaremos de todo esto un poco más adelante. ¡Y qué guay, incluso tenemos un enlace para cerrar sesión!

## Contraseñas más prácticas para los fixtures

Una cosa que resulta un poco molesta de nuestros fixtures es tener que aplicar manualmente el hash a la contraseña cada vez que
creamos un usuario. Desde luego, no resulta obvio que este hash tan larguísimo sea para la contraseña
`makeitso`.

Me gustaría poder usar la contraseña en texto plano al crear fixtures.

¡Podemos hacerlo! Abre `src/Factory/UserFactory.php`. Las fábricas de Foundry también son
servicios de Symfony, ¡así que podemos inyectarles otros servicios!

En el constructor, inyecta `private UserPasswordHasherInterface $passwordHasher`:

[[[ code('20067b51a0') ]]]

Este es el servicio que aplica el hash a la contraseña según nuestra configuración de seguridad.

En el método `default()`, seguiremos manteniendo esta contraseña en texto plano como valor por defecto. Haremos el hash de la
contraseña en un hook de Foundry. Estos se definen en `initialize()`. Descomenta la
línea`afterInstantiate()` para activar el hook. Esto se ejecuta justo después de que se
cree el objeto, pero antes de que se guarde en la base de datos. Este es el momento de hacer el hash de la contraseña.
La llamada de retorno acepta el objeto creado, en este caso, un `User`.

Dentro, escribe `$user->setPassword()`, y dentro de eso, `$this->passwordHasher->hashPassword()`. El
primer argumento de este método es el objeto `$user`; lo necesitas para determinar el
algoritmo de hash correcto para este usuario. Solo tenemos uno, pero se pueden configurar varios.
El segundo argumento es la contraseña en texto plano. Usa `$user->getPassword()`. Recuerda que,
en este momento, la contraseña asignada al usuario sigue estando en texto plano. Termina con un punto y coma
al final, ¡y ya está!

[[[ code('df9be89f27') ]]]

En `AppStory`, cuando se cree este objeto de usuario, su contraseña quedará establecida como `makeitso`:

[[[ code('f12d791ad4') ]]]

Nuestro hook de `afterInstantiate()` la sustituirá por la versión con hash y, a continuación, la guardará en la
base de datos.

Vuelve a la terminal y vuelve a cargar nuestros fixtures:

```terminal-silent
symfony console foundry:load-fixtures
```

Vuelve al navegador y actualiza la página de inicio. ¿Has visto lo que ha pasado? ¡Nos ha desconectado!

¡No es un error, es una característica! De hecho, es una característica de seguridad. Al recargar nuestros fixtures,
el hash de la contraseña de nuestro usuario ha cambiado. Nuestra contraseña en texto plano sigue siendo `makeitso`, pero el
hash ha cambiado. Cada vez que aplicas un hash a una contraseña, aunque sea la misma, obtienes un hash diferente.

## ¡Salt... y Peppa!

Los buenos algoritmos de hash de contraseñas incluyen un valor aleatorio llamado «salt», para que la misma contraseña no
genere siempre el mismo hash. Esto ayuda a protegerte contra ataques precalculados en los que se comparan contraseñas comunes
con hashes conocidos. A esto se le llama ataque de tabla de arcoíris. El «salt» rompe ese atajo.

Symfony vio que habíamos cambiado la contraseña, así que, por seguridad, nos desconectó.

¿Alguna vez has visto la función «cerrar sesión en otros dispositivos» en otra app? ¡Así es como podrías
implementar lo mismo en Symfony! Cuando el usuario elige esta opción, confirma su contraseña,
tú la conviertes en hash y actualizas el hash en la base de datos. ¡Y listo! ¡Todas las demás sesiones acaban de quedar
inválidas!

Vamos a volver a iniciar sesión con `picard@enterprise.space`, contraseña: `makeitso`... Sí, ya sé, ya sé,
es una mala contraseña. ¡Hemos vuelto a la página de inicio y hemos iniciado sesión con éxito!

¡Ahora es mucho más fácil trabajar con nuestro fixture de usuario!

## Optimizar la velocidad de las pruebas

Por diseño, el proceso de hash de las contraseñas es intencionadamente lento y consume muchos recursos de la CPU. Se trata de una
medida de seguridad. Intentar adivinar las contraseñas originales por fuerza bruta lleva mucho más tiempo.

Si tienes muchas pruebas de funcionalidad o de integración que impliquen el hash de contraseñas, como crear usuarios
e iniciar sesión, esto puede ralentizar tu conjunto de pruebas de forma significativa, sin motivo alguno. No hay
ningún riesgo de seguridad en tus pruebas.

¡No te preocupes, Symfony te tiene cubierto! Vuelve a nuestro archivo « `security.yaml` » y desplázate hacia abajo hasta
la sección « `when@test` ». Esto anula el generador de hash de contraseñas configurado anteriormente en nuestro entorno « `test` ».
Estas opciones ajustan el coste y el tiempo que lleva generar el hash de una contraseña, haciéndolo mucho más rápido, prácticamente
instantáneo.

Asegúrate de que estos valores bajos nunca se utilicen en tu entorno de producción. Podrías aumentar estos valores
en producción, pero, para la mayoría de las aplicaciones, los valores predeterminados ya ofrecen un buen equilibrio entre seguridad
y rendimiento.

A continuación, ¡vamos a ajustar nuestra plantilla base para incluir enlaces de inicio y cierre de sesión en la barra de navegación!
