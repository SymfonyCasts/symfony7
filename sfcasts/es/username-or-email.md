# Iniciar sesión con un nombre de usuario o un correo electrónico

Quiero añadir el concepto de nombre de usuario: una cadena única para cada usuario. Lo has visto
en un montón de redes sociales, porque no quieres compartir los correos electrónicos de la gente. Si
tienes una web con un perfil de usuario público, probablemente querrás un nombre de usuario para ello.

Y lo que quiero para nuestros usuarios es que puedan iniciar sesión tanto con su correo electrónico como con
su nombre de usuario.

## Añadir el campo « `username` »

Primero, añade una columna « `username` » a nuestra entidad « `User` ». En la terminal, usa el bundle «Maker»:

```terminal
symfony console make:entity
```

Esto es para « `User` »; la nueva propiedad es « `username` », será un « `string` » con una
longitud de campo de « `255` ». ¿Puede este campo estar en nulo en la base de datos? No, cada usuario necesita un
nombre de usuario. Y ya está.

Ups, no quiero añadir una propiedad `exit`... Simplemente forzaré el cierre del comando.

Ve a nuestra entidad de usuario en `src/Entity/User.php`. Voy a comprobar
que no se haya añadido la propiedad `exit`... bien, no se ha añadido.

Arriba, duplica la restricción de unicidad,
porque queremos que los nombres de usuario también sean únicos: añade el sufijo
«`_USERNAME` » al nombre y cambia el campo a « `username` ». Luego duplica la
restricción de validación «`UniqueEntity` » para « `username` » con el mensaje «Ya hay
una cuenta con este nombre de usuario»:

[[[ code('a7f565bb56') ]]]

En la propia propiedad, añade `#[Assert\NotBlank]`:

[[[ code('c9c455046b') ]]]

Ahora que nuestra entidad está totalmente actualizada, crea una migración con:

```terminal
symfony console make:migration
```

Ve a la carpeta `migrations/` y búscala. Para la descripción, usa
`add user.username column`:

[[[ code('9acb161eb5') ]]]

## Fixtures y formularios

Ajusta la fábrica de Foundry. En `src/Factory/UserFactory.php`,
duplica el valor predeterminado `email`, llámalo `username` y usa el generador Faker `->userName()`:

[[[ code('f9250fc928') ]]]

En `src/Story/AppStory.php`, añade nombres de usuario a nuestros usuarios. Para Picard, pon su
nombre de usuario simplemente como `picard`. Y para Janeway, `janeway`. Pan comido:

[[[ code('9d4961dfd9') ]]]

Hemos actualizado nuestros fixtures, así que recárgalos:

```terminal
symfony console foundry:load-fixtures
```

Ahora ajusta el formulario de registro. En `src/Form/RegistrationFormType.php`, después de
`email`, añade `username`:

[[[ code('e45e6b8b30') ]]]

Luego, la plantilla de inicio de sesión, `templates/security/login.html.twig`. Cambia la descripción
para que mencione el nombre de usuario, cambia la etiqueta a «Correo electrónico o nombre de usuario» y cambia el
campo de entrada `type` por `text` para que no obliguemos a nadie a poner un correo electrónico ahí:

[[[ code('24d9ca4f11') ]]]

## El truco de `UserLoaderInterface` 

El truco para cargar datos por correo electrónico O por nombre de usuario es bastante sencillo: tenemos que hacer un pequeño ajuste en nuestro
repositorio de usuarios.

En `src/Repository/UserRepository.php`, implementa otra interfaz:`UserLoaderInterface`. Esta proviene del puente Doctrine de Symfony y tiene un
único método, así que impleméntalo aquí abajo: `loadUserByIdentifier()`.

Toma una cadena `$identifier` —en nuestro caso, lo que haya en el campo de nombre de usuario de nuestro
formulario de inicio de sesión— y devolvemos `null` si no se encuentra, o un `UserInterface`. Como
nuestra entidad `User` implementa `UserInterface`, podemos usar simplemente el generador de consultas habitual
de Doctrine. Empieza con `return $this->createQueryBuilder('u')`, luego `->where('u.email = :identifier')` y
`->setParameter('identifier', $identifier)`. Por último, `->getQuery()->getOneOrNullResult()` para rematar.

Esto funcionará exactamente igual que nuestro sistema actual. Pero para añadir la posibilidad de usar
tanto el correo electrónico como el nombre de usuario, solo tienes que añadir `->orWhere('u.username = :identifier')` al generador de consultas.

[[[ code('db67759d02') ]]]

Eso es todo lo que tenemos que hacer aquí. Pero ahora que nuestro repositorio de usuarios implementa
`UserLoaderInterface`, hay una cosa más. En `config/packages/security.yaml`, arriba
en la parte superior, bajo `app_user_provider`, tenemos que eliminar la clave `property`:

[[[ code('e23325902d') ]]]

Así se recurre al método `loadUserByIdentifier()` de nuestro repositorio.

## Probando las dos opciones

Vuelve a la app y actualiza nuestra página de inicio de sesión. Genial: «Correo electrónico o nombre de usuario».

Intenta iniciar sesión solo con `picard` y la contraseña `makeitso`. ¡Ya estamos dentro!

En la barra de herramientas de depuración web, sigue apareciendo el correo electrónico, no el nombre de usuario. Eso es
porque nuestra entidad « `User` » implementa « `UserInterface` », que tiene « `getUserIdentifier()`» 
—y estamos usando el correo electrónico para eso. Así que, dondequiera que se muestre un identificador de usuario,
será el correo electrónico. Si tienes contenido público, no uses el identificador de usuario
para mostrar públicamente quién es alguien: usa `getUsername()` en su lugar.

Para asegurarte de que funciona en ambos sentidos, cierra la sesión y vuelve a iniciar sesión como
`picard@enterprise.space` con la contraseña `makeitso`. ¡Ya está! Funciona en ambos sentidos.

Siguiente paso: vamos a mejorar nuestro sistema de suplantación de identidad de usuarios.
