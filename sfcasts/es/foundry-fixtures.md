# Fijaciones de Fundición para Herencia

Vale, nos quedamos con un error cuando intentamos cargar nuestros fixtures. Abre`src/Factory/StarshipFactory`. Básicamente, ahora que hemos hecho de `Starship` una MappedSuperclass abstracta, ya no se puede instanciar y persistir por sí misma.

Tenemos que crear fábricas independientes para nuestras dos nuevas entidades subestelares: `Freighter` y `Scout`. ¡Fácil! Ejecuta en el terminal:

```terminal
symfony console make:factory
```

Una característica ingeniosa de Foundry es que puedes crear fábricas individualmente, o puedes elegir "todas" y creará fábricas para todas las entidades que aún no tengan una. Selecciona "todas" y... ¡voilá! Ahora, si volvemos a nuestro código, tenemos dos nuevas y relucientes fábricas.

## Gestión de los valores predeterminados duplicados

Verás que nuestros valores predeterminados están básicamente duplicados, excepto la capacidad de carga del `Freighter` y el alcance de los sensores del `Scout`.

Podemos llevar los valores predeterminados compartidos hasta `StarshipFactory` y luego anular los valores predeterminados específicos en `FreighterFactory` y `ScoutFactory`.

Para ello, necesitamos reflejar la estructura de herencia que tenemos con nuestras entidades para las fábricas de Foundry.

## Hacer abstracta la StarshipFactory

Empieza con `StarshipFactory`. Hazlo abstracto para que quede claro que no debe utilizarse directamente. En el docblock, espolvorearemos algunos genéricos PHP para ayudar con el autocompletado. Añade nuestra propia plantilla con `@template T of Starship`. Esto indica que nuestra plantilla `T` sólo puede ser del tipo `Starship`, o cualquier subclase de `Starship`. A continuación, modifica la `@extends` a `PersistentProxyObjectFactory<T>` para que utilice nuestra plantilla. Ahora, cualquier subfábrica que extienda `StarshipFactory` puede especificar su propio tipo de nave estelar para `T`, y obtendremos autocompletado para ese tipo de nave estelar cuando utilicemos la fábrica.

***TIP
Si utilizas herramientas de análisis estático como PHPStan, esto también le ayudará a comprender mejor los tipos y a detectar cualquier problema relacionado con ellos.
***

Además, como es abstracto, podemos prescindir del método `class()`, que siempre tendrá que ser invocado por las subfábricas.

## Modificar FreighterFactory y ScoutFactory

Es hora de una de mis cosas favoritas, ¡eliminar código duplicado!

En `FreighterFactory`, que sea `extends StarshipFactory`, y en el docblock, cambia el `@extends` por `StarshipFactory<Freighter>`. Abajo, en `defaults()`, envuelve el array devuelto en un `array_merge()`. Primer argumento:`parent::defaults()`, ¡no olvides cerrar los paréntesis! Para el segundo argumento, podemos reducirlo a sólo `cargoCapacity`, ya que es lo único que difiere de los valores predeterminados en `StarshipFactory`.

Lo mismo para el `ScoutFactory`, `extends StarshipFactory`, `@extends StarshipFactory<Scout>`, y en `defaults()`, fusionar con `parent::defaults()` e incluir sólo el `sensorRange`.

¡Qué bien!

## Cargando de nuevo los accesorios

De vuelta en el terminal, ¡vamos a cargar de nuevo estos dispositivos!

```terminal
symfony console foundry:load-fixtures
```

El mismo error. Oh, duh... ¡hemos creado las nuevas fábricas, pero aún no las estamos utilizando!

Abre `src/Story/AppStory`. Esta es la historia por defecto que Foundry carga al cargar las instalaciones. Abajo, en el método `build()`, sustituye `StarshipFactory::createMany(3)`por `FreighterFactory::createMany(3)`. Además, duplica esta línea y cámbiala por`ScoutFactory::createMany(3)` para cargar también algunas Exploradoras. 6 naves estelares en total.

Ejecuta de nuevo el comando cargar accesorios:

```terminal-silent
symfony console foundry:load-fixtures
```

Fantástico, ¡está totalmente cargado!

## Corregir el error del controlador y mostrar las naves estelares

Vuelve a la página de inicio, donde aparecen las naves estelares, y mira lo que tenemos.

Uy, tenemos un error. Nuestro controlador sigue intentando cargar naves estelares desde nuestro `StarshipRepository`. Esto no funciona porque `Starship` ya no es una entidad válida. Es el mismo problema que tuvimos antes al utilizar directamente `StarshipFactory`.

Para solucionarlo, abre `src/Controller/MainController`. En el método `homepage()`, sustituye el`StarshipRepository` inyectado por `ScoutRepository`. Esto debería recuperar todas las naves exploradoras.

Actualiza la página de inicio... ¡y ya está! Éstas son nuestras tres Exploradoras.

¿Y los Cargueros? Bueno, también podríamos inyectar el `FreighterRepository`, buscarlos también y fusionarlos con los exploradores. Pero esto no es lo ideal, no se escalaría bien. Imagina que tuviéramos 20 tipos diferentes de naves estelares: tendríamos que inyectar 20 repositorios diferentes... ¡Qué asco!

Así es como tendrías que hacerlo con las Superclases Mapeadas. En realidad no es una limitación de las Superclases Mapeadas, simplemente no es su propósito. Están más pensadas para compartir propiedades comunes y mapeos entre entidades, no para consultar una jerarquía de entidades.

¿No sería estupendo poder inyectar la `StarshipRepository` y que devolviera tanto Exploradores como Cargueros? ¡Pues sí! Pero para ello tenemos que utilizar otro tipo de herencia. ¡Eso a continuación!
