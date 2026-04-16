# Herencia y relaciones de Doctrine

Es hora de hablar de las asociaciones, o relaciones entre entidades que utilizan la herencia Doctrine. En su mayor parte, funcionan como cabría esperar, pero hay una pega relacionada con la carga perezosa. A modo de recordatorio, cuando tienes una entidad que tiene una relación con otra entidad, por defecto, Doctrine no cargará realmente esa entidad hasta que llames a un método sobre ella. Esto ayuda al rendimiento, manteniendo al mínimo el número de consultas y el número de hidrataciones (instanciación de objetos).

En determinadas condiciones, la carga perezosa de una entidad en una jerarquía de herencia no funciona. Esto no rompe tu aplicación, pero implica consultas adicionales que podrían afectar al rendimiento.

Para entender estas condiciones, ¡creemos una relación! Nuestras Naves Estelares tendrán Partes Estelares.

En tu terminal, ejecuta:

```terminal
symfony console make:entity StarshipPart
```

## Crear relación con la Entidad Nave Estelar

Lo mantendremos super sencillo, sólo tendrá la relación Nave Estelar. Para el nombre de la nueva propiedad, utiliza `starship`. ¿El tipo? Utiliza `?` para ver todos los tipos de campo disponibles. Elige `relation` para utilizar el práctico asistente de relaciones. ¿A qué clase debe asociarse? `Starship`.

Ahora, ¿qué tipo de relación... Esta primera es la que queremos "Cada `StarshipPart`se relaciona con (tiene) un objeto `Starship` ". Y "Cada `Starship` puede relacionarse con (puede tener) muchos objetos `StarshipPart` ". Sí, `ManyToOne`, así que elige eso.

"¿Se permite que la propiedad `StarshipPart.starship` sea nula?", claro, elige la predeterminada.

¿Queremos añadir una propiedad `starshipParts` a la clase `Starship`? Sí

El nuevo nombre del campo en `Starship`? `starshipParts` es bueno. No necesitamos otra propiedad, así que pulsa intro para salir.

A continuación, crea la fábrica Foundry para esta entidad.

```terminal
symfony console make:factory
```

`1` para todos (y todos para 1). Genial, echemos un vistazo al código generado.

## Explorar las entidades y relaciones creadas

En `src/Entity`, aquí está nuestra nueva entidad `StarshipPart` y la relación `ManyToOne` con `Starship`:

[[[ code('eef24d8e51') ]]]

Y en `Starship`, tenemos la relación inversa `OneToMany` con `StarshipPart`como `Collection` de `StarshipParts`:

[[[ code('33c34960a3') ]]]

Nuestro `StarshipPartFactory` está bastante desnudo, ya que de momento no tenemos ningún campo obligatorio:

[[[ code('1a774a9ce1') ]]]

Vamos a añadir algunas partes a nuestro `AppStory`. Asegúrate de que este `FreighterFactory::createMany()` es el primero.

Para el segundo argumento, pasa una función anónima que devuelva un array. Dentro,`'starshipParts' => StarshipPartFactory::createRange(1, 3)`. Esto creará entre 1 y 3`StarshipParts` para cada `Freighter` y los asociará a él. La razón por la que utilizamos una función anónima es que queremos crear un nuevo conjunto de `StarshipParts` para cada `Freighter`. Si pasáramos directamente el array, crearía un conjunto de `StarshipParts` e intentaría asociarlos a todos los cargueros, lo que sería un problema...

Copia el segundo argumento y pégalo para las otras dos llamadas a fábrica:

[[[ code('2597d2907b') ]]]

Ahora vuelve a cargar los accesorios.

```terminal
symfony console foundry:load-fixtures
```

## Comprender la carga perezosa de colecciones

Vuelve a la página principal y actualízala. Todo parece igual y seguimos teniendo una única consulta. La consulta para seleccionar todas las naves estelares. Aunque cada nave tiene varias partes, éstas no se cargan porque no hemos intentado acceder a ellas, ¡son perezosas!

Ahora, abre nuestro `MainController::homepage()` y `dump($myShip->getStarshipParts())`:

[[[ code('1667f94a27') ]]]

Actualiza... y... ¿sigue habiendo una sola consulta? ¡Sí! Si miramos el volcado, podemos ver que es un `PersistentCollection` con `initialized: false`. Esto significa que es una colección perezosa. De nuevo, ¡no inicializará la colección hasta que llamemos a un método sobre ella! Así que vamos a hacerlo. Añade`->first()` para coger el primer elemento.

Actualiza... Ahora tenemos dos consultas. La primera es la consulta principal findAll para Starships, y la segunda es para seleccionar el primer `StarshipPart` para `myShip`. En el panel de depuración, efectivamente, aquí está la instancia `StarshipPart`.

Así pues, cuando tienes una entidad que está en una jerarquía de herencia, y tienes una relación con otra entidad que no está en una jerarquía de herencia, la carga perezosa funciona exactamente como cabría esperar.

Ahora vayamos en sentido contrario...

## Explorar las implicaciones de la herencia de doctrinas

En `MainController::homepage()`, inyecta `StarshipPartRepository $starshipPartRepository` y`dump($starshipPartRepository->find(1))`:

[[[ code('a72550d644') ]]]

`1` debería ser el ID del primer `StarshipPart` de nuestra base de datos.

Actualiza la página... y tenemos dos consultas: la select principal y la consulta `find()`en la que estamos cargando el `StarshipPart`. Mira el `StarshipPart` que se ha volcado. Observa que la propiedad `starship` es una instancia de `Freighter`. Doctrine sabe que este `Freighter` ya fue instanciado con nuestra primera consulta, la findAll, así que lo reutilizó aquí.

Para demostrar la carga perezosa, asegurémonos de que este carguero no se reutiliza.

En el controlador, sustituye el `StarshipRepository` por `MiningFreighterRepository`:

[[[ code('615ee0c9bc') ]]]

Esto reduce las naves obtenidas a sólo `MiningFreighters`.

Vuelve a la página de inicio. Recuerda que, en nuestra última petición, cargamos un `StarshipPart` y el `Freighter` con el que estaba relacionado ya estaba instanciado.

Actualiza la página. Ahora sólo hay dos naves, la `MiningFreighters`. Mira el recuento de consultas: ahora son 3.

La primera es el findAll para el `MiningFreighters`. La segunda es nuestro `find(1)` para el `StarshipPart`. Y la tercera es buscar el `Starship` para el `StarshipPart` encontrado anteriormente. Se está cargando ansiosamente. Si `Starship` no utilizara la Herencia Doctrine, esta tercera consulta no sería necesaria. Sólo se cargaría el `Starship` cuando llamáramos a un método sobre él.

Éste es el inconveniente de utilizar este tipo de relación con la Herencia de Doctrine... En realidad no puede evitarse, y más adelante explicaré por qué.

## Una posible solución

Existe un escenario en el que puedes conseguir que la carga perezosa funcione para esta relación. La entidad asociada en la jerarquía debe ser una hoja, lo que significa que no puede tener ninguna entidad hija.

Para ver esto en acción, haremos que nuestro `StarshipPart` esté relacionado con `Scout` en lugar de con `Starship`.`Scout` es una hoja en nuestra jerarquía.

En `Starship`, corta la propiedad de relación y el constructor. Pégalo en `Scout`. En `Starship`, corta los 3 métodos relacionados con la relación y pégalos también en `Scout`:

[[[ code('d6fb5a6be1') ]]]

Ahora, en `StarshipPart`, cambia el typehint de la relación de `Starship` a `Scout` en todos los lugares:

[[[ code('37ea3cf47c') ]]]

Por último, en `AppStory`, elimina el segundo argumento de los `FreighterFactory` y `MiningFreighterFactory``createMany` 's ya que éstos ya no pueden funcionar:

[[[ code('76d97a0710') ]]]

Ahora, vuelve a cargar nuestras instalaciones...

```terminal-silent
symfony console foundry:load-fixtures
```

Vuelve a la página de inicio y... ¡Vaya! Volvemos a tener sólo dos consultas. Ya no tenemos esa tercera consulta ansiosa.

En el panel de depuración, la propiedad `starship` es esta extraña clase `Proxies/__CG__`. Se trata de un proxy para nuestro Scout que permite la carga diferida. Sólo cuando llamemos a un método, consultará realmente a la base de datos para cargar el `Scout` asociado.

Entonces... ¿por qué esta limitación?

## ¿Por qué no puede ser siempre perezosa?

La razón por la que las entidades que tienen hijos en una jerarquía de herencia no pueden cargarse perezosamente se debe a cómo funcionan las relaciones de base de datos y la instanciación de objetos PHP. En la tabla sólo se almacena el ID de la entidad relacionada. En el caso de una entidad con hijos, el ID por sí solo no basta para determinar de qué entidad se trata. Si es `Starship`, ¿es `Freighter`, `MiningFreighter` o `Scout`? Necesita saberlo para instanciar el objeto correcto. Y la única forma de saberlo es consultar la tabla de la relación para obtener el valor de la columna discriminadora. Por lo tanto, necesita cargar la relación inmediatamente, o con avidez.

Vale, pero ¿por qué las entidades sin hijos no tienen esta limitación? Porque Doctrine sabe exactamente qué tipo de entidad es la relación Si es una `Scout`, Doctrine puede crear el proxy perezoso para ella. Lo mismo ocurriría con `MiningFreighter`.

## Reflexiones finales

Con esto concluimos nuestro curso sobre la herencia de Doctrine. Siempre que seas consciente de sus limitaciones, puede ser una herramienta poderosa para modelar tu dominio Sólo tienes que ser consciente de tus relaciones y de cómo pueden afectar al rendimiento.

Si tienes alguna pregunta, estamos a tu disposición en los comentarios.

Hasta la próxima, ¡feliz codificación!
