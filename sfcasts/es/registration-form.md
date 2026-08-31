# Crear un formulario de registro

Para terminar este curso, vamos a añadir una forma de que los nuevos usuarios se unan a la app. Y una forma bastante habitual
de hacerlo es mediante un formulario de registro.

Crearlo a mano sería un rollo… ¡pero por suerte hay una herramienta para eso!

## `make:registration-form`

Ve a la terminal y ejecuta:

```terminal
symfony console make:registration-form
```

«¿Quieres añadir un atributo de validación `#[UniqueEntity]`?» Sí. No podemos tener dos
usuarios con el mismo correo electrónico.

«¿Quieres enviar un correo para verificar la dirección de correo del usuario tras el registro?»
Elige «no» por ahora: eso es un poco más complicado.

«¿Quieres autenticar automáticamente al usuario tras el registro?» ¡Sí! Eso
lo iniciará sesión de forma automática, así que podremos ver cómo funciona.

«¿Quieres generar pruebas con PHPUnit?» No.

Vale, esta actualización ha creado una clase —nuestra entidad « `User` »—, dos clases nuevas y una
plantilla. Echemos un vistazo a todo esto.

## La restricción de « `UniqueEntity` »

Empecemos por la entidad `User`: `src/Entity/User.php`. Lo único que se ha hecho ha sido añadir una
restricción de validación «`#[UniqueEntity]` » en `email` aquí arriba.

Así que, durante el proceso de validación, antes de que se guarde el usuario, esto comprueba si
otro usuario ya tiene ese correo electrónico. Si es así, aparece este mensaje: «Ya hay
una cuenta con este correo electrónico». Muy útil.

Ya lo tenemos como restricción de Doctrine, pero esto es una restricción de validación.
La diferencia es que la restricción de Doctrine se aplica a nivel de la base de datos, por lo que un error
le daría un error 500 al usuario. La restricción de validación, en cambio, muestra un mensaje de error de validación
claro y sencillo.

## `RegistrationFormType`

Lo siguiente es el formulario. Lo encontrarás en `src/Form/RegistrationFormType.php`.

Es un formulario bastante estándar, pero hay algo que puede parecer un poco diferente: tenemos
algunos campos sin asignar. `agreeTerms` y `plainPassword`.

Fíjate en que ambos tienen `'mapped' => false`. Como no están asignados, no tenemos un
objeto al que aplicar nuestras restricciones de validación... así que el formulario te permite añadirlas directamente
al propio campo. Por eso estas restricciones están aquí.

Lo único que quiero añadir es nuestro campo « `name` »: `->add('name')`. Este es un campo mapeado,
así que se establecerá en la entidad «user».

## `RegistrationController`

Ahora busca « `src/Controller/RegistrationController.php` » y vamos a analizarlo.

En este método « `register()` », lo primero que hacemos es crear un nuevo « `User` »... y luego
crear nuestro formulario « `RegistrationFormType` » para ese usuario. A continuación, dejamos que el formulario se encargue de la
petición y comprobamos si se ha enviado y si es válida.

Si es así, extraemos `plainPassword` de los datos del formulario para obtener su valor sin procesar. Todos los
campos asignados —en nuestro caso, el correo electrónico y el nombre— ya están configurados en el usuario.

No hace falta que hagamos nada con `agreeTerms`. No está mapeado y solo tiene una restricción de `IsTrue`
. Así que, si el formulario es válido, sabemos que el usuario ha aceptado los términos.

Lo que pasa aquí es que estamos usando el generador de hash de contraseñas para aplicar el hash a la
contraseña sin cifrar. Si nunca lo has visto hacer manualmente: inyectas
`UserPasswordHasherInterface` y luego llamas a `hashPassword()`. Le pasas el usuario —que es
cómo determina qué generador de hash de contraseñas usar— y la contraseña sin cifrar.
Devuelve la contraseña con hash, así que se la asignamos al usuario.

Ahora tenemos un objeto de usuario totalmente válido.

Lo guardamos y lo actualizamos en la base de datos.

A continuación es donde harías cualquier otra cosa que quieras, como añadir un mensaje de aviso.

Por último, devolvemos ` `$security->login()` `: ese es el sistema de inicio de sesión programático.
Le pasa los nombres de nuestro autenticador y del cortafuegos... pero esos se pueden
detectar a partir de la petición, así que ni siquiera los necesitamos. Mejor mantenerlo sencillo.

Y la razón por la que lo devolvemos es que `login()` devuelve un `Response`: la
respuesta estándar que obtendrías tras iniciar sesión. Aunque no tienes por qué devolverlo. Puedes llamar a
`$security->login()` sin devolverlo y, a continuación, devolver tu propia respuesta —quizá una
redirección a algún sitio—.

Y, por supuesto, si el formulario no se envía correctamente, mostramos `register.html.twig` y
le pasamos el formulario.

## Mostrar el formulario

Esa plantilla es nuestro último archivo nuevo, así que échale un vistazo.

El generador ha creado un formulario que muestra cada campo por separado. Lo único que quiero
hacer es mostrar también nuestro campo `name`. Duplica el del correo electrónico... y cámbialo a
`name`.

Ahora, si miras la página de inicio, todavía no se ve ningún cambio. Lo que quiero es un
botón «Registrarse» junto al botón de inicio de sesión, y por supuesto, solo si aún no
has iniciado sesión.

Abre `templates/base.html.twig` y desplázate hacia abajo hasta que encuentres esa gran instrucción `if` para nuestros enlaces. Tenemos el enlace de suplantación de identidad, el botón de cerrar sesión y este de iniciar sesión.
Copia el enlace de inicio de sesión, pega una copia justo encima, llámalo «Registrarse» y configura la ruta
a `app_register`.

Vuelve al navegador y actualiza la página de inicio. Genial, ahí está «Registrarse».
Haz clic en él y… esto tiene un aspecto un poco cutre.

En `register.html.twig`, voy a pegar una versión más bonita… La encontrarás en el script de abajo.

## Desactivar la validación HTML5

Ahora quiero probar la validación de este formulario. Si pulsamos «Registrarse», nos sale... la validación HTML5
del navegador.

Cuando pruebo la validación a mano, me gusta desactivar la validación HTML5 para poder
ver realmente cómo funcionan los validadores de Symfony. En la plantilla, en `form_start`,
añade un atributo ` `novalidate` ` con el valor ` `true``...

Actualiza... y sale un error. A Twig no le ha gustado eso. Se me ha olvidado una coma... No... sigue sin estar bien...

Ay, caramba, « `novalidate: true` » tiene que ir dentro del array « `attr` ».

¡Ya está!

Vale, la validación está desactivada. Pulsa «Registrarse» y… ¡tenemos algunos errores de validación!
Estos dos provienen de nuestro tipo de formulario. Pero también deberíamos ver errores en el correo electrónico y
el nombre. Ambos son campos obligatorios y hay que configurarlos en nuestra entidad « `User` ».

## Añadir restricciones de validación

Abre « `src/Entity/User.php` » y empieza por la propiedad « `$email` ». Añade « `#[Assert\NotBlank]` ».

Aquí se ha añadido un « `Assert` » duplicado, así que lo voy a quitar. Esto ha importado un
alias «`Assert` », así que no hace falta importar cada restricción por separado.

No necesitamos ningún argumento. Podrías pasar un `message` para que sea un poco más fácil de entender,
pero por ahora deja el valor por defecto.

El otro es `name`, así que añade lo mismo ahí: `#[Assert\NotBlank]`.

Vuelve al formulario, actualiza la página y pulsa «Registrarse»... vemos un error de validación en cada
campo. ¡Perfecto!

A continuación, el campo del correo electrónico debería contener realmente una dirección de correo. Introduce algo que no sea
una dirección de correo —como `picard` — y pulsa «Registrarse».

Los otros campos nos han dado errores de validación, pero este no. Necesitamos una
restricción de correo electrónico. Así que vuelve a la entidad `User`, ve a la propiedad `email` y añade
`#[Assert\Email]`.

Vuelve a enviarlo… genial: «Este valor no es una dirección de correo válida».

Hay otra restricción que comprobar: esa `#[UniqueEntity]` en el correo. Ya
tenemos un usuario con `picard@enterprise.space` como correo en la base de datos, así que introduce
eso y regístrate...

«Ya existe una cuenta con este correo». Ha funcionado a la perfección.

## Registrar a un usuario real

Ahora registra a un nuevo usuario de verdad: correo `natasha@enterprise.space`, nombre «Natasha Yar» y
la contraseña puede ser cualquiera. Yo usaré `stayawayfromarmus`. Marca «Acepto los términos»...
¡y regístrate!

Volvemos a la página de inicio, vemos el botón para cerrar sesión y, si miramos hacia abajo en la
barra de herramientas de depuración web... efectivamente, estamos autenticados como `natasha@enterprise.space`. Nuestro
registro y el inicio de sesión programático funcionan correctamente.

## ¿El inicio de sesión programático cuenta como interactivo?

Asegurémonos de que el inicio de sesión programático se considere un inicio de sesión interactivo y active
nuestro detector de último inicio de sesión.

Pulsa «Cerrar sesión» y, a continuación, inicia sesión como `janeway@starfleet.space`, con la contraseña `coffeeblack`. Después ve
a `/admin/user`... ¡y aquí abajo ves que nuestro nuevo usuario tiene su último inicio de sesión configurado correctamente! Así que
un inicio de sesión programático se considera un inicio de sesión interactivo.

## Una última cosa: enumeración de usuarios

Quiero terminar este curso con un debate. Hay quien no consideraría esto un problema de seguridad
—quizá sea más bien una cuestión de refuerzo de la seguridad—, pero echa un vistazo a esto.

Cierra sesión y vuelve a la página de registro. Imaginemos que estamos intentando averiguar si alguien
que conocemos usa esta web... alguien como Picard. En realidad no estamos intentando registrarnos:
solo queremos saber si este correo tiene una cuenta aquí.

Escribe `picard@enterprise.space`, pulsa «Registrarse» y —como vimos antes— el mensaje de error nos dice muy
claramente que ya hay una cuenta.

A esto se le llama enumeración de usuarios.

Me encantaría que lo comentáramos en la sección de comentarios. ¿Es esto un problema de seguridad? ¿Cómo podríamos
tener un sistema de registro que no permita esto?

Bueno, esto es todo por hoy en este curso básico de seguridad. ¡Gracias por acompañarme!

¡Hasta la próxima, y feliz programación!
