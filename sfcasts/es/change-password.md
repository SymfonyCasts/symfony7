# Añadir un formulario para cambiar la contraseña y UserPassword

Necesitamos una forma de que los usuarios que hayan iniciado sesión puedan cambiar su contraseña si lo desean.
Por desgracia, no hay ningún «maker» para esto, así que lo vamos a crear a mano, aunque
podemos usar un «maker» para crear el controlador. En la terminal:

```terminal
symfony console make:controller
```

Llámalo « `ChangePassword` ». Sin pruebas unitarias.

También voy a restablecer la base de datos, para que todos nuestros usuarios vuelvan a tener sus contraseñas predeterminadas
:

```terminal
symfony console foundry:load-fixtures
```

El generador ha creado `templates/change_password/index.html.twig` y un`ChangePasswordController`. Vamos a ordenar un poco la plantilla: en lugar de dejarla en su
propio subdirectorio, cámbiale el nombre a simplemente `change_password.html.twig` y muévela
a la carpeta `security/`; me parece que ese es su sitio. Después, borra el
directorio vacío.

## Preparando el controlador

Abre `src/Controller/ChangePasswordController.php`. Primero, cambia la ruta a
`/change-password`. Más abajo, actualiza la plantilla con la que acabamos de mover:
`security/change_password.html.twig`.

También voy a cambiar el nombre del método a `__invoke()`, porque en realidad no es un
«índice»: ahora es una clase invocable. Me gusta hacerlo cuando un controlador solo tiene
una acción y el nombre de la clase explica perfectamente lo que hace.

Ahora, las dependencias. Esta es una función para usuarios que han iniciado sesión, así que inyecta nuestra entidad « `User`» 
y añade el atributo « `#[CurrentUser]` » encima para que Symfony inyecte la
actual. Lo bueno de esto es que, como el método ahora requiere un usuario, cualquiera
que acceda a esta ruta sin haber iniciado sesión será redirigido a la página de inicio de sesión. Justo lo que
queremos.

A continuación, tenemos que guardar la nueva contraseña, así que añade `EntityManagerInterface $em`. Tenemos que
hasharla, así que `UserPasswordHasherInterface $hasher`. Y necesitamos el `Request` de HTTP
Foundation:

[[[ code('879d585119') ]]]

## Reutilizar el formulario

Ahora, crea el formulario. ¿Pero qué formulario?

Lo mejor es que el generador de restablecimiento de contraseña ya ha creado el formulario que necesitamos. Echa un vistazo
a `src/Form/ChangePasswordFormType.php`, del último capítulo. Eso es todo lo que
hace falta: la nueva contraseña, dos veces. Podemos reutilizarlo, y eso me encanta.

Así que: `$form = $this->createForm(ChangePasswordFormType::class)`, y luego
`$form->handleRequest($request)`. Si el formulario se envía y es válido, saca la
contraseña del formulario con `$form->get('plainPassword')->getData()`. Coge el usuario que
hemos introducido antes, `setPassword()`, y aplicale un hash con `$hasher->hashPassword($user, $password)`. Luego,
`$em->flush()`, añade un mensaje de éxito —«¡Tu contraseña ha cambiado!»— y redirige
a `app_homepage`. Para el `render` que viene a continuación, pasa el formulario a la plantilla:

[[[ code('45ff661c58') ]]]

Vamos a probarlo. Entra en la app y visita manualmente `/change-password`. Ahora mismo
estamos desconectados, así que esto debería redirigirnos a la página de inicio de sesión… ¡y así es!
Perfecto. Inicia sesión como `picard@enterprise.space`, con la contraseña `makeitso`.

¡Ups! ¿Sabes qué se nos ha olvidado? Tenemos que actualizar la plantilla. En
`templates/security/change_password.html.twig`, borra el contenido del cuerpo. Por ahora, muestra
el formulario con `{{ form(form) }}`:

[[[ code('308cb27c0c') ]]]

Actualiza la página. Todavía no tiene estilo —ya nos ocuparemos de eso en un momento—, pero ya tenemos nuestros nuevos campos de
contraseña y de confirmación de contraseña, igual que en la página de restablecimiento de contraseña.

## Solicitar la contraseña actual

Una cosa que deberías hacer cuando un usuario cambia su contraseña es pedirle que introduzca
la que tiene ahora.

Hay varias formas de hacerlo. En el controlador podríamos añadir un`#[IsGranted('IS_AUTHENTICATED_FULLY')]` para saber que estás en ese modo «sudo». Pero la
forma más habitual es añadir un campo de contraseña actual al formulario.

Podemos hacerlo, pero tenemos que hacer que sea opcional, porque no queremos ese campo
cuando un usuario está restableciendo su contraseña. No sabe cuál es su contraseña actual,
esa es precisamente la cuestión.

En `ChangePasswordFormType`, en la sección `configureOptions()`, crea una nueva opción
llamada `require_current` con el valor predeterminado `false`. Cada vez que añadas una opción, debes
especificar también de qué tipo es: `$resolver->setAllowedTypes('require_current', 'bool')`, para que
solo pueda ser un valor booleano:

[[[ code('e1369db882') ]]]

Ahora, en la parte superior de `buildForm()` —quiero que este sea el primer campo—, añade
`if ($options['require_current'])` y, dentro, añade un campo `currentPassword` como
`PasswordType`.

Para las opciones, empieza por las restricciones. Primero, un « `NotBlank` » con el mensaje
«Introduce tu contraseña actual». La segunda es una que quizá no hayas visto:
«`UserPassword` ». Esta restricción de validación simplemente exige que el campo coincida con la
contraseña actual del usuario. El hash y la verificación ya se gestionan por nosotros, ¡genial!

A continuación, cambia `mapped` por `false`, ya que no está vinculado a ningún objeto, y pon la etiqueta
en «Contraseña actual»:

[[[ code('77fb62e866') ]]]

Para asegurarte de que es realmente opcional, vuelve a la app y actualiza la página. Ya no aparece, y
¡eso es bueno! Significa que hay que habilitar el campo, así que no hace falta
cambiar nada en nuestro proceso de restablecimiento de contraseña.

Para habilitarlo, vuelve a `ChangePasswordController` y pasa un array de opciones donde
creamos el formulario: `require_current` establecido en `true`:

[[[ code('c4fe4b776b') ]]]

Actualiza la página... perfecto, ahí está.

## Estilo y pruebas

Vamos a añadir un poco de estilo para que quede más bonito. En la plantilla de cambio de contraseña,
pega este código; lo encontrarás en el script de abajo:

[[[ code('844a233610') ]]]

Actualiza la página. Mucho mejor.

Ahora vamos a hacer que este formulario dé error a propósito. En la contraseña actual, introduce `engage`, pero
en realidad es `makeitso`. Y para la nueva contraseña, usa `1234` en el primer campo y`4321` en el otro, para que no coincidan. Pulsa «Cambiar contraseña» y… ahí lo
tienes: «El valor debe ser la contraseña actual del usuario». La verdad es que podríamos hacer que ese
mensaje fuera un poco más amable.

Vale, ¿cuál es la contraseña actual? `makeitso`. Y para nuestra nueva contraseña,`makeitso1engage?` dos veces. Cambiar contraseña… ¡perfecto! «¡Tu contraseña se ha
cambiado!»

Quizá deberíamos comprobarlo bien. Cierra sesión y luego inicia sesión como `picard@enterprise.space` con la nueva
contraseña `makeitso1engage?`. Genial, todo ha funcionado.

A continuación: echemos un vistazo a esas restricciones específicas de la contraseña en nuestro formulario de cambio de contraseña
y eliminemos algunas repeticiones en la validación de la contraseña.
