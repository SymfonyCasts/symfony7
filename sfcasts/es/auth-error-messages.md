# Personalizar los mensajes de error de autenticación

Hablemos de los diferentes errores de seguridad que pueden aparecer en la página de inicio de sesión.

Intenta iniciar sesión como `picard@enterprise.space` con la contraseña `enterprise`, que es
incorrecta. Aparece el mensaje «Credenciales no válidas». Tiene sentido.

Ahora intenta iniciar sesión con un correo que no existe: `picard@voyager.space` con la
contraseña `makeitso`. También aparece «Credenciales no válidas».

Ahora desactiva a Picard y mira qué mensaje aparece. Ejecuta nuestro comando especial para desactivarlo:

```terminal
symfony console dbal:run-sql "UPDATE user SET disabled_at = datetime('now') WHERE email = 'picard@enterprise.space'"
```

Tenemos que poner todo esto entre comillas dobles porque usamos comillas simples dentro. Una fila
afectada.

Intenta iniciar sesión como Picard con el correo electrónico correcto — `picard@enterprise.space` — y la
contraseña correcta, `makeitso`. Y… también nos sale «Credenciales no válidas».

## Revelando los errores reales

Así que cada vez que haya cualquier problema, vemos «Credenciales no válidas». Esa es la
configuración predeterminada a propósito: evita el problema de la enumeración de usuarios, en el que alguien entra
en tu página de inicio de sesión y comprueba miles de correos electrónicos para ver si existen en el sistema
o no.

Puedes desactivar esto. Ve a `config/packages/security.yaml` y, en la parte superior,
añade `expose_security_errors: all`:

Vuelve atrás e introduce la contraseña correcta, `makeitso`. «La cuenta está desactivada».
Ahora vemos el error real.

Vuelve a cargar los fixtures para que Picard vuelva a estar activado:

```terminal
symfony console foundry:load-fixtures
```

Ahora usa una contraseña incorrecta: `enterprise`. «Credenciales no válidas».
Tiene sentido: las credenciales no eran válidas.

Pero prueba con un correo que no exista: `picard@voyager.space`, contraseña `makeitso`.
«No se ha encontrado el nombre de usuario». ¡Un error diferente!

A esto me refería con lo de la enumeración de usuarios. Alguien podría hacer un ataque masivo y averiguar
quién tiene una cuenta en esta web. Puede que pienses que no es para tanto, pero
potencialmente podría serlo. Imagina un sitio en el que el mero hecho de tener una cuenta sea privado: una
bolsa de empleo o una comunidad de apoyo para una enfermedad. Confirmar que una dirección está
registrada es como darle esa información a cualquiera que la pida. E incluso en un sitio sin importancia, una lista de
nombres de usuario que sin duda existen hace que un ataque de adivinación de contraseñas resulte mucho más fácil.

## El término medio: account_status

Hay un término medio. « `expose_security_errors` » es en realidad una enumeración: echa un vistazo a
`ExposeSecurityLevel`. Podemos ver « `none` » —que es el valor por defecto—, « `all` », que es
a lo que lo cambiamos, y « `account_status` ».

Probemos con esa:

Vuelve atrás e intenta `picard@voyager.space` con la contraseña `makeitso`. «Credenciales
no válidas». Perfecto: ya no se ve que tenemos un usuario que no existe.

Ahora una contraseña incorrecta: `picard@enterprise.space` con `enterprise`. Seguimos viendo
«Credenciales no válidas». Tiene sentido.

Ejecuta nuevamente nuestro comando SQL para desactivar Picard:

```terminal-silent
symfony console dbal:run-sql "UPDATE user SET disabled_at = datetime('now') WHERE email = 'picard@enterprise.space'"
```

Inicia sesión como `picard@enterprise.space` con la contraseña correcta, `makeitso`. «La cuenta
está desactivada». Pero si usas una contraseña incorrecta… seguimos viendo «La cuenta está desactivada».

Quizá esto no sea tan grave como revelar que un nombre de usuario no existe;
probablemente no tendrás decenas de miles de cuentas desactivadas que alguien pueda explotar. Pero
sigue siendo un pequeño agujero de seguridad que permite cierta enumeración de usuarios.

## Trasladar la comprobación a checkPostAuth()

La razón de esto es cómo hemos configurado nuestro verificador de usuarios. Lo encontrarás en
`src/Security/UserChecker.php`.

Echa un vistazo a la interfaz: `checkPreAuth()` comprueba el usuario antes de la autenticación,
y `checkPostAuth()` lo comprueba después de la autenticación. Así que, si trasladamos nuestra comprobación
a después de la autenticación, no se ejecutará hasta que se produzca la autenticación, es decir, hasta que se
compruebe la contraseña. De esta forma, el mensaje solo se mostrará a quienes tengan la contraseña correcta,
y eso es justo lo que queremos.

Copia esto de `checkPreAuth()` y pégalo en `checkPostAuth()`:

Ahora prueba `picard@enterprise.space` con la contraseña correcta, `makeitso`. Veremos
«La cuenta está desactivada». Pero con la contraseña incorrecta: «Credenciales no válidas». Exactamente
lo que queremos.

Creo que ahí está la clave. Quizá no quieras que un usuario desactivado vea que
su cuenta está desactivada; eso es posible, y ya lo hemos visto antes. Pero en la mayoría de
los casos, está bien que lo vean para que puedan averiguar por qué y ponerse en contacto con el servicio de asistencia.

## Personalizar el mensaje

Ahora echemos un vistazo a estos mensajes de error y a cómo podemos personalizarlos. Fíjate en
`templates/security/login.html.twig`: el `messageKey` se extrae de la
excepción y se traduce en el dominio `security`. ¡Así que está traducido!
Aunque tu sitio web no sea multilingüe, puedes usar esta función para
personalizar un solo idioma.

Veamos rápidamente cómo funciona: copia «Credenciales no válidas», ve
al directorio `vendor/`, busca el texto en los archivos y pégalo ahí.

Lo primero es `BadCredentialsException`. Esta es la excepción real que se lanza, y
puedes ver que « `messageKey` » es lo que se está traduciendo. Todas las excepciones de autenticación
usan un `messageKey` como este.

También lo vemos en los archivos XLIFF de `security`: las traducciones para los distintos
idiomas. Fíjate en la versión en inglés: simplemente se traduce palabra por palabra.

Así que podemos personalizarlo. Entra en `translations/` —yo tengo instalado el traductor de Symfony—
y crea un nuevo archivo llamado `security.en.yaml`. Pega «Invalid
credentials.» entre comillas y cambia el mensaje por «Tu correo electrónico o contraseña es incorrecta.»:

Vuelve a la página de inicio de sesión e inténtalo de nuevo: `picard@enterprise.space` con una
contraseña incorrecta. ¡Ahí está, nuestro nuevo mensaje!

Puedes hacer lo mismo con todos los mensajes de excepción. ¡Pruébalo con el mensaje de cuenta desactivada!

Próximo paso: el modo «sudo», que confirma la contraseña de un usuario antes de que realice
operaciones confidenciales.
