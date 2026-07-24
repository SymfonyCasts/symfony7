# Fixtures de Foundry para Inheritance

Vale, nos quedamos con un error cuando intentamos cargar nuestros fixtures. Abre
`src/Factory/StarshipFactory`. Básicamente, ahora que hemos convertido `Starship` en una
clase abstracta MappedSuperclass, ya no se puede instanciar ni persistir por sí misma.

Tenemos que crear fábricas independientes para nuestras dos nuevas entidades de subnaves espaciales:
la `Freighter` y la `Scout`. ¡Pan comido! En la terminal, ejecuta:

```terminal
symfony console make:factory
```

Una característica genial de Foundry es que puedes crear fábricas individualmente,
o simplemente puedes elegir «all» y creará fábricas para todas las
entidades que aún no tengan una. Selecciona «all» y... ¡voilà! Ahora, si
volvemos a nuestro código, tenemos dos fábricas nuevas y relucientes.

## Gestión de valores predeterminados duplicados

Te darás cuenta de que nuestros valores por defecto están básicamente duplicados, excepto la
capacidad de carga del `Freighter` y el alcance de los sensores del `Scout`.

Podemos trasladar los valores predeterminados compartidos a la « `StarshipFactory` » y luego simplemente sobrescribir
los valores específicos en la « `FreighterFactory` » y la « `ScoutFactory` ».

Para ello, tenemos que replicar la estructura de herencia que tenemos con nuestras entidades
para las fábricas de Foundry.

## Convertir StarshipFactory en abstracta

Empieza por `StarshipFactory`. Hazla abstracta para que quede claro que no debe
usarse directamente. En el docblock, añadiremos algunos genéricos de PHP para ayudar
con el autocompletado. Añade nuestra propia plantilla con `@template T of Starship`.
Esto indica que nuestra plantilla `T` solo puede ser de tipo `Starship`, o cualquier
subclase de `Starship`. A continuación, modifica `@extends` por `PersistentProxyObjectFactory<T>` para
usar nuestra plantilla:

[[[ code('748965f26b') ]]]

Ahora, cualquier subfábrica que extienda ` `StarshipFactory` ` puede especificar su propio
tipo `Starship` para ` `T``, y tendrás autocompletado para ese tipo `Starship` cuando uses la fábrica.

***TIP
Si usas herramientas de análisis estático como PHPStan, esto también les ayudará a entender mejor los tipos
y a detectar cualquier problema relacionado con ellos.
***

Además, como es abstracto, podemos prescindir del método ` `class()` `: siempre tendrá que ser
sobrescrito desde las subfábricas.

## Modificando FreighterFactory y ScoutFactory

¡Es hora de hacer una de mis cosas favoritas: eliminar código duplicado!

En ` `FreighterFactory``, ponlo así: ` `extends StarshipFactory``,
y en el docblock, cambia ` `@extends` ` por ` `StarshipFactory<Freighter>``:

[[[ code('9df947dcad') ]]]

Más abajo, en `defaults()`, envuelve el array devuelto en un `array_merge()`. Primer argumento:
`parent::defaults()`, ¡no te olvides de cerrar los paréntesis! Para el segundo argumento,
podemos reducirlo solo a `cargoCapacity`, ya que es lo único que difiere
de los valores por defecto en `StarshipFactory`:

[[[ code('f0e9345f84') ]]]

Lo mismo con `ScoutFactory`, `extends StarshipFactory`, `@extends StarshipFactory<Scout>`,
y en `defaults()`, fusiona con `parent::defaults()` e incluye solo `sensorRange`:

[[[ code('da3fd0b8d4') ]]]

¡Genial!

## Cargando los fixtures de nuevo

Volvamos a la terminal y probemos a cargar estos fixtures otra vez.

```terminal
symfony console foundry:load-fixtures
```

Mmm, el mismo error. Ah, claro… ¡hemos creado las nuevas fábricas, pero aún no las estamos usando!

Abre `src/Story/AppStory`. Esta es la historia predeterminada que carga Foundry al
cargar los fixtures. En el método `build()`, sustituye `StarshipFactory::createMany(3)`
por `FreighterFactory::createMany(3)`. Además, duplica esta línea y cámbiala por
`ScoutFactory::createMany(3)` para cargar también algunos Scouts. 6 naves espaciales en total:

[[[ code('ea54781502') ]]]

Ejecuta nuevamente el comando de carga de fixtures:

```terminal-silent
symfony console foundry:load-fixtures
```

¡Genial, ya está todo cargado!

## Corregir el error del controlador y mostrar las naves espaciales

Vuelve a nuestra página de inicio, donde aparecen las naves espaciales, y echa un vistazo a lo que tenemos.

Vaya, tenemos un error. Nuestro controlador sigue intentando cargar naves espaciales
desde nuestro `StarshipRepository`. Esto no funciona porque `Starship` ya no es
una entidad válida. Es el mismo problema que tuvimos al usar directamente `StarshipFactory`
antes.

Para solucionarlo, abre `src/Controller/MainController`. En el método `homepage()`, sustituye el`StarshipRepository` inyectado por `ScoutRepository`:

[[[ code('acf8d6bf0b') ]]]

Ahora debería cargar todas las naves espaciales Scout.

Actualiza la página de inicio... ¡y ya está! Estas son nuestras tres naves Scout.

¿Y qué hay de los cargueros? Bueno, también podríamos inyectar el `FreighterRepository`,
recogerlos también y fusionarlos con los Scout. Pero esto no es lo ideal,
no se adaptaría bien a un mayor volumen. Imagina que tuviéramos 20 tipos diferentes de naves espaciales: tendríamos
que inyectar 20 repositorios diferentes... ¡Qué asco!

Así es como tendrías que hacerlo con las superclases mapeadas. En realidad no es una
limitación de las superclases mapeadas, simplemente no es para lo que sirven. Están pensadas más bien
para compartir propiedades y mapeos comunes entre entidades, no para realizar consultas a través de
una jerarquía de entidades.

¿No sería genial si pudiéramos seguir inyectando la clase « `StarshipRepository` » y que devolviera
tanto «Scouts» como «Freighters»? ¡Pues sí que podemos! Pero para ello, tenemos que usar un tipo diferente
de herencia. ¡Eso a continuación!
