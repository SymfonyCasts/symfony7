# Muchos a uno: el rey de las relaciones

Muy bien, amigos, hemos construido con éxito las entidades `Starship` y `StarshipPart`, y... están bien asentadas en la base de datos. Pero he aquí el enigma: ¿cómo unimos estas piezas a sus respectivas naves estelares? ¿Cómo damos a cada `StarshipPart` su legítimo hogar `Starship`? Aquí es donde entra en juego nuestro fiel comando `make:entity`. Qué fanfarrón. Busca tu terminal y ejecútalo:

```terminal
symfony console make:entity
```

## Construir relaciones: Piensa en objetos, no en ID

Ahora bien, si piensas en términos tradicionales de base de datos, podrías imaginarte una columna `starship_id` apareciendo en tu tabla `starship_part`. Y así será, pero no es así como pensamos las cosas en Doctrine, sino que nos centramos en relacionar objetos. Así que actualiza la entidad `StarshipPart`para añadir un campo para `Starship`.

Así que, a la hora de nombrar el campo, no lo llames`starshipId`. Doctrine quiere que pensemos en términos de clases y objetos. Y como un `StarshipPart` pertenecerá a un `Starship`dale a la entidad `StarshipPart` una propiedad `starship`. 

Para el tipo de campo, utiliza un tipo falso llamado "relación". ¡Esto pone en marcha un asistente! ¿Con qué clase estamos relacionando? 
Dilo conmigo: `Starship`.

## Elegir el tipo de relación adecuado

El asistente nos guía a través de los cuatro tipos diferentes de relaciones. Comprueba las descripciones: queremos un `ManyToOne` en el que cada parte pertenezca a un `Starship`, y cada `Starship`puede tener muchas partes. 

Cuando se nos pregunte si la propiedad `starship` puede ser nula, diremos "no". Queremos que cada parte pertenezca a una nave: no se permiten partes flotantes al azar.

## Añadir comodidad con una nueva propiedad

A continuación, el asistente hace una pregunta interesante:

> ¿Queremos añadir una nueva propiedad a `Starship` que nos permita
> decir `$starship->getParts()`?

Esto es totalmente opcional, pero estaría bien disponer de una forma tan sencilla de obtener todas las piezas de una nave. Además, no tiene inconveniente. Así que esto es un "sí" para mí dawg. Llama a la propiedad `parts`: corto y dulce. Para la eliminación de huérfanos, di "no". Ya hablaremos de eso más adelante. 

Pulsa intro para terminar. He confirmado antes de grabar, así que comprobaré los cambios con:

```terminal
git status
```

## Nuevas propiedades en StarshipPart y Starship

Vaya, vaya, vaya. ¡Parece que ambas entidades se han actualizado! En `StarshipPart`, tenemos una nueva propiedad `starship`. Pero en lugar de `ORM\Column`, es`ORM\ManyToOne` y su valor será un objeto `Starship`. También tenemos nuevos métodos `getStarship()` y `setStarship()`:

[[[ code('acafa3d74c') ]]]

En `Starship`, tenemos una nueva propiedad `parts` con`ORM\OneToMany`. Desplazándonos hacia abajo, vemos un práctico método `getParts()`. Pero en lugar de `setParts()`, nos han regalado los métodos `addPart()` y`removePart()`:

[[[ code('e545ebfb1f') ]]]

Estos te serán útiles cuando trabajes con Foundry, el sistema de formularios o si estás construyendo una API con el serializador de Symfony.

En el constructor, añade `$this->parts = new ArrayCollection()`:

[[[ code('744d1c32b4') ]]]

Este es un detalle que necesitamos, pero no es superimportante: `ArrayCollection` es un objeto que se ve y actúa como un array, lo que significa que podemos `foreach` sobre él o hacer otras cosas parecidas a un array.

Ah, y si lo piensas: `OneToMany` y `ManyToOne` son en realidad dos vistas de la misma relación. Si una parte pertenece a una nave estelar, entonces una nave estelar tiene muchas partes. Hemos añadido una relación, pero podemos verla desde dos perspectivas distintas. 

Pero aún no hemos terminado. Como `make:entity` ha añadido nuevas propiedades, seguro que tenemos que actualizar nuestra base de datos. Crea una migración:

```terminal
symfony console make:migration
```

## Comprobación de la migración

Ésta es una de mis migraciones favoritas. Altera `starship_part` para añadir una columna `starship_id`, que es una clave foránea sobre `starship`. Esto ocurrió porque Doctrine es un listillo. Añadimos una propiedad `starship` a`StarshipPart`, pero Doctrine sabía que la columna debía llamarse`starship_id`. Incluso nos ayudará a establecerlo, como veremos en el próximo capítulo. Vamos a migrar:

```terminal
symfony console doctrine:migrations:migrate
```

## Preparando la migración

¡Explota!

> La columna "starship_id" de la tabla "starship_part" no puede ser nula.

¿Recuerdas la tabla `starship_part`? Ya tiene 50 filas! La migración intenta añadir una nueva columna `starship_id` y establecerla en `null`. Pero eso no está permitido, gracias a la `nullable: false`. Borra esas 50 filas con:

```terminal
symfony console doctrine:query:sql "DELETE FROM starship_part"
```

Y vuelve a ejecutar la migración:

```terminal-silent
symfony console doctrine:migrations:migrate
```

## Siguiente paso: Uniendo los puntos

Entonces, ¿cómo enlazamos un objeto `StarshipPart` con su `Starship`? Abróchate el cinturón, ¡porque eso a continuación!
