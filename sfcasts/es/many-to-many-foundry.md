# Muchos a muchos con Foundry

¿Recuerdas en `AppFixtures` cuando asignábamos manualmente un `Droid` a un`Starship`? ¡Aquello era divertido! Pero ahora, quiero crear un ejército de `droids`, una flota de `starships` y asignarlos todos a la vez.

Deshazte de esas asignaciones manuales de `Droid` y `Starship` en `AppFixtures`.

## Crear el ejército droide y la flota de naves estelares

Acércate a la parte inferior donde creamos `starships` y `parts`. Ahora también necesitamos un montón de `droids`:`DroidFactory::createMany(100)`. 

A continuación, asigna `droids` a `DroidFactory::randomRange(1, 5)`:

[[[ code('3d67ea2ef7') ]]]

Esto asignará entre 1 y 5 `droids` aleatorios a cada `Starship`.

## La magia de Symfony

Tal vez te hayas dado cuenta de algo: aquí estamos estableciendo una propiedad `droids`, ¡pero en`Starship`, no tenemos un método `setDroids()`! Normalmente, esto provocaría un error furioso. Pero funcionará! Foundry ve que tenemos un método `addDroid()`, y lo llama en su lugar, uno a uno para cada `Droid`.

## Ejecuta la prueba

¡Es hora de ver esto en acción! Busca tu terminal y ejecuta:

```terminal
symfony console doctrine:fixtures:load
```

¿No hay errores? Yo también estoy un poco sorprendido, ejem, encantado. Echa un vistazo a `droids` con:

```terminal
symfony console doctrine:query:sql 'SELECT * FROM droid'
```

100 droides alocados y adorables. Echa también un vistazo a la tabla `starship_droid`:

```terminal-silent
symfony console doctrine:query:sql 'SELECT * FROM starship_droid'
```

Debería parecer que hay un conjunto aleatorio de droides asignado a cada `starship`

## Espera, ¡algo no va bien!

Pero espera un momento. Estos droides "aleatorios" -¿percibes mis comillas sarcásticas? - ¡no son aleatorios en absoluto! Son los 3 mismos droides una y otra vez. El problema es que `randomRange(1, 5)` sólo se llama una vez: así que está asignando los mismos 1 a 5 droides aleatorios a cada`Starship`. No es la variedad que esperábamos.

## Cierres y Fundición

Arréglalo pasando un cierre: `StarshipFactory::createMany()`, 100,`fn() => [ 'droids' => DroidFactory::randomRange(1, 5)])`:

[[[ code('cec7afa4d8') ]]]

Foundry ejecutará la llamada de retorno para las 100 naves. Esto significa que `randomRange(1, 5)`se llamará 100 veces, lo que nos dará un rango verdaderamente aleatorio para cada nave. 

¡Pruébalo! Vuelve a ejecutar las fijaciones y carga la consulta SQL:

```terminal-silent
symfony console doctrine:fixtures:load
symfony console doctrine:query:sql 'SELECT * FROM starship_droid'
```

Disfruta de la gloria de un conjunto de droides asignados a naves estelares realmente aleatorio.

También podríamos haberlo arreglado moviendo la clave `droids` a`StarshipFactory` en el método `defaults()`. Pero me gusta conservar`defaults()` para las propiedades necesarias. Y como `droids` no son técnicamente necesarias -¡buena suerte limpiando el baño sin ellas! -
Me gusta mantenerlas fuera de `defaults()` y establecerlas donde utilicemos`StarshipFactory`.

A continuación, aprenderemos a hacer JOIN entre relaciones `ManyToMany`. Una vez más, Doctrine se encarga del trabajo pesado por nosotros.
