# Relación de User Doctrine

En la página de inicio de nuestra app, tenemos el concepto de «MyShip». Se supone que es
la nave espacial del usuario actual. Sin embargo, por ahora, solo es un marcador de posición.
Abre `src/Controller/MainController` y, en el método `index()`, estamos configurando
`$myShip = $repository->findMyShip()`:

[[[ code('e5255fa649') ]]]

Si nos metemos en este método, vemos que el código simplemente coge la primera nave de la base de datos:

[[[ code('6932122c3d') ]]]

¿No sería genial que esto estuviera realmente vinculado al usuario?

Primero, vuelve a `MainController` y cambia `$myShip` por `null`:

[[[ code('2e1a2bb61c') ]]]

Esto simula que no hay ningún usuario conectado o que el usuario actual no tiene ninguna nave.

Vuelve a la página de inicio y actualízala... Perfecto, la sección «Mi nave»
simplemente desaparece cuando no hay ninguna nave.

## Relación con la nave

Recuerda que nuestra « `User` » es una entidad de Doctrine. Esto significa que podemos añadirle
relaciones normales de Doctrine. Quiero que « `User` » pueda tener una « `Starship` ».

¡Podemos usar la herramienta Maker para crear esta relación! En tu terminal,
ejecuta:

```terminal
symfony console make:entity User
```

`User` ya existe, así que el «maker» solo te pide que le añadas campos.

Para la nueva propiedad, llámala « `starship` ». ¿Tipo de campo? Escribe « `?` » para ver todas las opciones.
Desplázate hacia arriba hasta «Relaciones»... ¡Vaya, vamos a usar el asistente! Escribe « `relation` ».

¿Con qué clase debería estar relacionada esta entidad? « `Starship` ».

¿Qué tipo de relación es esta? La primera: «Cada usuario tiene una nave espacial» y
«Cada nave espacial puede tener varios usuarios». `ManyToOne`. Elige esa.

¿Queremos que `User.starship` sea nulo? Sí, porque un usuario no tiene por qué
estar asignado a una nave espacial.

¿Queremos que la `Starship` pueda acceder a todos sus usuarios? ¡Sí!

¿Cómo se llama el campo para el lado inverso? « `users` » está bien.

Listo.

## Añadir una migración

Como hemos actualizado una entidad, tenemos que añadir una migración, así que ejecuta:

```terminal
symfony console make:migration
```

Genial, busquemos esa migración en nuestro código. Aquí está. Pon la descripción como
«`Add starship to user` ». Aquí abajo está el SQL para actualizar la tabla de usuarios en la
base de datos.

Vuelve a la terminal y ejecuta la migración con:

```terminal
symfony console doctrine:migrations:migrate
```

## Actualizando nuestros fixtures

Quiero que Jean-Luc tenga la Enterprise como nave espacial. Así que vamos a actualizar nuestros fixtures.
Voy a cerrar esta migración y abrir `src/Story/AppStory`. Busca dónde creamos a Picard.
Podemos crear su nave espacial justo aquí, dentro de este `createOne()`.

Añade ` `'starship' => StarshipFactory::new()` ` con un array dentro. Si te estás preguntando en qué
se diferencia el método `new()` de `createOne()`, ¡buena observación! `createOne()` creará el
objeto y lo guardará en la base de datos inmediatamente. `new()` crea la fábrica, pero el
objeto no se creará hasta que se haya creado el objeto de la fábrica principal, en este caso, `UserFactory`.
Es una buena práctica usar `new()` al crear fábricas en línea como esta.

Vale, dentro de: `'name' => 'USS Enterprise (NCC-1701-D)'`, `'class' => 'Galaxy'`,
`'captain' => 'Jean-Luc Picard'`. El campo «captain» de `Starship` es solo una cadena,
¡pero podría ser perfectamente otra relación con `User`! Por último,
`'status' => StarshipStatusEnum::IN_PROGRESS`:

[[[ code('9c485d4e8e') ]]]

¡Genial! Vuelve a la terminal y recarga los fixtures con:

```terminal
symfony console foundry:load-fixtures
```

## Recuperar la nave espacial del usuario

¡Ahora vamos a recuperar «mi nave» del usuario que está conectado actualmente! Vuelve a `MainController::index()`,
y establece `$myShip` en `$this->getUser()?->getStarship()`:

[[[ code('721abba05a') ]]]

El método `getUser()` es otro ayudante que te ofrece `AbstractController`.
Devuelve el usuario que ha iniciado sesión actualmente o `null` si no hay ninguno. `?->` es
el operador a prueba de nulos, así que si `getUser()` devuelve `null`, no intentará llamar a
`getStarship()`. Simplemente devolverá `null`.

Ahora actualiza la página de inicio. No hay ningún error, así que inicia sesión como Picard. Correo electrónico: `picard@enterprise.space`, contraseña:
`makeitso`. ¡Genial! ¡La barra lateral ha vuelto y podemos ver nuestra nave, la Enterprise!

A continuación, veremos otras formas de acceder al usuario que ha iniciado sesión actualmente en nuestros servicios y
controladores.
