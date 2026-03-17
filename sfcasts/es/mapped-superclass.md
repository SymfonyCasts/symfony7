# Superclases mapeadas

¡Hola Amigos! Vamos a sumergirnos en el mundo de la Herencia Doctrine en Symfony. Ya sabes cómo las clases Doctrine representan tablas en nuestra base de datos, y sus instancias, u objetos, representan filas. ¿Pero sabías que podemos llevarlo aún más lejos? El ORM de Doctrine nos ofrece una ingeniosa función de herencia, que nos permite crear entidades que extienden clases abstractas o incluso otras entidades. Esto es muy útil para compartir propiedades comunes. Hay tres formas de hacerlo, y exploraremos cada una de ellas y sopesaremos sus pros y sus contras. Abróchate el cinturón, ¡empecemos!

## Configurar el proyecto

Para empezar, descarga el código del curso que aparece en la parte superior de este vídeo. Dentro del directorio de inicio, encontrarás un nuevo y reluciente proyecto. Sólo tienes que seguir el README para ponerlo en marcha en tu máquina local. Puede que te resulte familiar si has seguido alguno de nuestros cursos anteriores, pero asegúrate de descargar esta nueva copia en lugar de utilizar un proyecto existente.

Una vez que ejecutes el servidor Symfony, verás nuestra aplicación Starshop. Esta sencilla aplicación sólo lista las naves estelares de nuestra base de datos.

De vuelta a nuestro código, en el directorio `src\Entity`, encontrarás nuestra entidad `Starship` con propiedades generales relacionadas con las naves estelares. Nuestra misión hoy es crear distintos tipos de Nave Estelar, un carguero y una nave exploradora.

Podrías pensar que podríamos simplemente añadir una columna `type` a la entidad `Starship` y utilizarla para diferenciar los tipos. La cuestión es que cada tipo diferente tiene su propio conjunto de propiedades específicas. Por ejemplo, un carguero tendrá una propiedad `cargoCapacity`, mientras que una nave exploradora tendrá una propiedad `sensorRange`.

Así que las queremos como entidades propias, pero también tienen que compartir las propiedades comunes de una nave estelar.

## Creación de subnave estelar

En primer lugar, tenemos que crear algunas naves estelares secundarias. Saltemos al terminal y ensuciémonos las manos creando algunas entidades. Ejecuta la siguiente orden:

```terminal
symfony console make:entity Scout
```

Esta entidad Exploradora tendrá una propiedad única llamada `sensorRange`, que será un número entero... y no puede ser nulo. Una vez hecho esto, pulsa intro para salir.

A continuación, vamos a crear una entidad Carguero:

```terminal
symfony console make:entity Freighter
```

La propiedad única de esta entidad será `cargoCapacity`, también un entero... y no anulable. Eso es todo por ahora, así que termina.

De vuelta a nuestro código, ahora tenemos dos nuevas y relucientes entidades.

## Explorando las superclases mapeadas

El primer tipo de herencia que vamos a explorar es la superclase mapeada. Es como una clase abstracta en PHP, o más exactamente, una entidad abstracta. Nuestro `Starship` será la Superclase Mapeada. Hazlo `abstract`, lo que significa que no puede instanciarse directamente, sino que debe extenderse para poder utilizarse:

[[[ code('27de96a78e') ]]]

A continuación, sustituye el atributo `ORM\Entity` por `ORM\MappedSuperclass`:

[[[ code('957310e89f') ]]]

¡Ya está!

A continuación, nuestra entidad `Freighter`. Que sea `extends Starship`. No necesitamos el `id`, ya que se hereda de `Starship`, así que lo eliminaremos junto con el método `getId()`:

[[[ code('4da13ec44f') ]]]

Todo lo demás es propio de `Freighter`. Repite estos pasos para la entidad`Scout`:

[[[ code('ca22e1c73e') ]]]

## Actualizar el esquema y cargar los accesorios

Ahora, pasemos a la consola y veamos qué aspecto tendrá nuestra actualización del esquema. Ejecuta:

```terminal
symfony console doctrine:schema:update --dump-sql
```

La bandera `dump-sql` sólo muestra el SQL que se ejecutaría.

Al final, observa que eliminamos la tabla `starship`. Las superclases mapeadas no tienen sus propias tablas. En su lugar, sus propiedades son heredadas por las entidades hijas, que sí tienen sus propias tablas. Así que, en nuestro caso, las tablas `freighter` y `scout` tendrán todas las propiedades de una`Starship`, junto con sus propiedades únicas: `cargo_capacity` para la `freighter`y `sensor_range` para la `scout`.

Veamos cómo funciona esto cuando recarguemos los accesorios de nuestra aplicación. Ejecuta:

```terminal
symfony console foundry:load-fixtures
```

Aquí hay un problema: no podemos instanciar la clase abstracta `Starship`. Estamos utilizando fábricas Foundry, y como `Starship` es ahora abstracta, no puede crear esas entidades. No te preocupes, en la siguiente parte de nuestro viaje, ajustaremos nuestras instalaciones de Foundry para manejar la Herencia de Doctrine. ¡Permanece atento!
