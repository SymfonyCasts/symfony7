# Entidad de la nave estelar

Tenemos una base de datos y podemos conectarnos a ella, pero... ¡no tiene ninguna tabla!

El ORM de Doctrine utiliza clases PHP para representar tablas en la base de datos, por ejemplo, si necesitas una tabla para productos, creas una clase `Product`. Doctrine llama a estas clases "entidades", pero en realidad no son más que aburridas clases estándar de PHP. ¡Me gusta lo aburrido!

En nuestra aplicación StarShop, necesitamos hacer un seguimiento de las Naves Estelares... así que necesitamos una tabla `Starship`... así que necesitamos una clase de entidad `Starship`. ¿Qué aspecto tiene una Nave Estelar? En el último tutorial, creamos una clase modelo `Starship` en el directorio `src/Model`. Ábrela. Decidimos que cada Nave Estelar tiene un `id`, `name`, `class`,`captain`, `status`, y `arrivedAt`.

Esto es casi una entidad Doctrine: sólo le falta alguna configuración que ayude a Doctrine a entender cómo asignar esta clase a una tabla de la base de datos. Podríamos añadirlo fácilmente a mano. Pero... tenemos una herramienta que puede hacerlo por nosotros: ¡el MakerBundle!

## `make:entity`

Ejecuta:

```terminal
symfony console make:entity
```

Para el nombre, utiliza `Starship`. No estamos utilizando Symfony UX Turbo, así que responde`no` a esa pregunta. Esto ya ha creado una clase `Starship` en el directorio `Entity/`y una clase `StarshipRepository`. Hablaremos de ello más adelante.

¡Pero no hemos terminado! Este comando es impresionante: pregunta interactivamente qué propiedades -o columnas, si quieres pensar así- necesita nuestra entidad. Vuelve al modelo de la Nave Estelar para ver lo que necesitamos. MakerBundle añadirá `id` automáticamente, así que salta a `name`. ¿Tipo de campo? Utiliza el predeterminado: `string`. ¿Longitud del campo? `255` está bien. ¿Este campo puede ser nulo en la base de datos? No, todas las naves estelares necesitan un nombre.

El siguiente es `class`, será igual que `name`... luego `captain` también es un simple `string`. Siguiente: `status`. Doctrine propone por defecto un `string`, pero... mira nuestro modelo `Starship`, `status` es un enum. ¿Cómo podemos asignarlo a una columna? De vuelta en el terminal, pulsa `?` para ver los distintos tipos que podemos añadir. En la parte inferior... ¡ `enum`! Úsalo. `Enum class`? Utiliza el nombre de clase completo de nuestra enum: `App\Model\StarshipStatusEnum`.

¿Este campo puede almacenar varios valores? No, una Nave Estelar sólo puede tener un estado a la vez. ¿Este campo puede ser nulo? No

Por último, añade `arrivedAt`. ¡Genial! Maker utiliza por defecto `datetime_immutable`en lugar de `string`. Esto se debe a que hemos añadido `At` como sufijo al nombre de nuestra propiedad. ¿Puede ser nulo este campo? No.

## `[ORM\Entity]`

Echemos un vistazo a nuestra recién creada entidad `Starship`: en `src/Entity/`.

Fíjate: se trata de una clase PHP estándar con propiedades... y una cosa especial: algunos atributos PHP:

El atributo `#[ORM\Entity]` de la clase indica a Doctrine que no se trata sólo de una aburrida clase PHP, sino de una entidad que debe asignarse a una tabla de nuestra base de datos. El nombre de la tabla puede personalizarse, pero utilizaremos el predeterminado, que es el nombre de la clase en forma de serpiente: `starship`.

## `[ORM\Column]`

Fíjate en las propiedades: cada una tiene `#[ORM\Column]`. Esto indica a Doctrine que estas propiedades son columnas de nuestra tabla. En cuanto al tipo, Doctrine es inteligente y lo adivina a partir de la sugerencia de tipo. Por ejemplo, `id`será de tipo entero, `name` será de tipo cadena y `arrivedAt` será de tipo sello de tiempo. ¡Qué bien!

[[[ code('778782b9e6') ]]]

`id` tiene unos cuantos atributos extra que lo marcan como clave primaria y le dice a la base de datos que lo autogenere como un entero autoincrementado.

Ah, y podemos eliminar el argumento `length` de las columnas de cadena: éste es el valor por defecto.

[[[ code('33ac5f1685') ]]]

La propiedad `status` es de tipo `StarshipStatusEnum`, pero Doctrine la almacenará como cadena en la base de datos. ¡Genial! En realidad, podemos eliminar el argumento `enumType`: ¡Doctrine también puede adivinarlo a partir del tipo de propiedad!

[[[ code('781f8cde58') ]]]

Más abajo, el creador generó getters y setters para todas nuestras propiedades. Nuestro antiguo modelo `Starship` tenía dos métodos extra: `getStatusString()` y`getStatusImageFilename()`. Cópialos de la clase modelo... y en la parte inferior de la clase entidad, ¡pega!

[[[ code('38830a7cda') ]]]

¡Entidad lista! Incluso podemos volver a comprobar nuestro trabajo. En tu terminal, ejecuta:

## Validación del esquema

```terminal
symfony console doctrine:schema:validate
```

Esto significa que Doctrine ve y puede leer nuestros atributos. Entonces... ¿nuestra base de datos está desincronizada?

Tenemos una clase de entidad... pero en realidad no tenemos la tabla `starship` en la base de datos.

Hay algunas formas de introducir la tabla en la base de datos, pero la mejor forma son las migraciones. ¡Eso a continuación!
