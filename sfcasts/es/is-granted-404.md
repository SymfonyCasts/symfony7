# Devolver un 404 en lugar de un 403 con IsGranted

¡Hola, amigos! Bienvenidos a «Seguridad en Symfony: ¡Vamos más allá!»
Este curso es la continuación directa de nuestro curso «Seguridad: conceptos básicos»; empieza justo
donde acabó aquel, así que te recomiendo encarecidamente que lo hagas primero. Este
curso trata temas adicionales: las funciones más avanzadas que no pudimos cubrir allí.

Si has seguido ese curso y aún tienes el código, puedes retomar
justo donde lo dejamos. Si lo necesitas, descárgate el código de este curso en la parte superior de
la página. En el archivo zip, abre el directorio « `start/` » en tu IDE favorito y sigue las instrucciones de `README` para
configurarlo todo. Ejecuta:

```terminal
symfony serve -d
```

...para poner la aplicación en marcha.

## Un recorrido rápido por Starshop

Aquí está nuestro Starshop. Si has seguido el curso «Conceptos básicos de seguridad», ya estás acostumbrado a
esto: tenemos un enlace de registro, un enlace de inicio de sesión y algunos usuarios predefinidos en nuestros
fixtures. Abre `src/Story/AppStory.php` para verlos. Tenemos a
`picard@enterprise.space`, con contraseña `makeitso` —un capitán que tiene su propia nave espacial— y a
`janeway@starfleet.space`, con contraseña `coffeeblack`, que es administrador. También creamos un montón de
naves espaciales.

Inicia sesión como `picard@enterprise.space` con la contraseña `makeitso`.

Ahora ve a `/starship` para ver la lista de todas las naves espaciales. Si te acuerdas del
último curso, lo configuramos para que solo el dueño de una nave pueda editarla. Pulsa el
botón «editar» y fíjate en la URL: estamos en `/starship/1/edit`. Hemos añadido autorización
para que los usuarios no puedan editar la nave espacial de otra persona. Cambia ese ID por `2`... y nos sale un
error 403.

## Un 403 revela información

Una cosa que tiene el 403 es que sí que revela un poco de información:
nos dice que esta nave espacial probablemente sí existe en el sistema. Se filtra.

Quizá hayas visto esto en GitHub. Ve a un repositorio que sabes que existe pero que es
privado —cuando no tienes autorización para verlo—, GitHub
te da un 404. Eso evita que se filtre qué repositorios, o en nuestro caso, qué
naves espaciales, existen en el sistema.

## Devolver un 404 en lugar de un 403

Podemos imitar ese comportamiento muy fácilmente con nuestro atributo `#[IsGranted]`. Abre
`src/Controller/StarshipAdminController.php`. Para crear una nueva nave espacial,
necesitamos `ROLE_ADMIN`. Está bien dejarlo como un 403 normal, no filtra nada.

Pero en `edit()`, tenemos esto: `#[IsGranted('edit', subject: 'starship')]`. Pasa
otro parámetro a ese atributo: `statusCode: 404`:

[[[ code('1e868656b5') ]]]

Actualiza la página… y nos sale el mismo mensaje de error… Pero fíjate aquí arriba: ¡ahora es un 404!

Recuerda que esta es nuestra página de excepción de desarrollo. En producción, esto sería un
404 totalmente genérico —como cualquier otra página 404 del sitio—, así que no verías este
mensaje. Es una forma estupenda de evitar que se filtre la existencia del contenido de tu sitio.

Comprueba una cosa más: copia esta URL... y cierra sesión. Pégala en el navegador mientras
estamos en modo anónimo. También vemos un 404. Si se hubiera devuelto el error 403 por defecto, nos habrían
redirigido a la página de inicio de sesión tal y como está
configurada nuestra seguridad ahora mismo, y eso aún podría considerarse una pequeña filtración.
En cambio: un 404. Nadie que no esté autorizado a editar esta nave espacial puede ni siquiera saber que existe.

## Las otras opciones de « `IsGranted` »

Hay algunos otros parámetros relacionados con las excepciones en este atributo. Échale un vistazo a
`IsGranted`. Podemos establecer un `message` personalizado y también podemos configurar `exceptionCode` —
ese es el código real de la excepción de PHP. Normalmente no hace falta configurarlos.
Y recuerda que, por defecto, los mensajes de excepción no se muestran a los usuarios finales. Pero ten en cuenta que están ahí.

Una cosa más: también tenemos esta acción `delete()`, así que haz lo mismo aquí: `statusCode: 404`:

[[[ code('3c1c4914d3') ]]]

Genial: ya hemos ocultado por completo estas dos rutas a los usuarios no autorizados.

Siguiente paso: ¡los estados de las cuentas! Vamos a crear el concepto de desactivar usuarios.
