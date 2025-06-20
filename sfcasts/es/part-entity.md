# Parte Entidad

Ya tenemos naves estelares que aparecen en la página de inicio gracias a la entidad`Starship` que construimos en el último tutorial. Pero ahora ha llegado el momento de mejorar. Necesitamos hacer un seguimiento de las piezas individuales utilizadas en cada `Starship`. Éste es el plan: cada parte pertenecerá exactamente a un`Starship`, y cada `Starship` tendrá muchas partes. Pero antes de sumergirnos en las relaciones, tenemos que empezar por lo más sencillo: ¡necesitamos una nueva entidad para hacer un seguimiento de estas partes! Enciende tu terminal, abre una nueva pestaña (ya que nuestro servidor está zumbando en la otra), y ejecuta:

```terminal
symfony console make:entity
```

Llámalo `StarshipPart`, omite la emisión, y dale unos cuantos campos: `name`
será una cadena y no será anulable, `price` será un número entero (en créditos, por supuesto), y tampoco será anulable. Por último, añade un campo `notes` que será de tipo `text` (por lo que puede ser más largo), y será anulable. Una vez que hayas añadido estos campos, crea una nueva migración para la entidad copiando y pegando`symfony console make:migration`.

## Ejecutar migraciones y añadir marcas de tiempo

Ahora, si compruebas tus migraciones, verás sólo la nueva que hemos creado: He limpiado las migraciones antiguas del curso pasado.

[[[ code('0ded265acf') ]]]

Así que ésta es toda sobre `StarshipPart`. Ejecútala con:

```terminal
symfony console doctrine:migrations:migrate
```

¡La tabla está en la base de datos! Pero hay dos campos que me gusta añadir a todas mis entidades: `createdAt` y `updatedAt`. Puedes verlos dentro de `Starship`, bajo `TimestampableEntity`.

[[[ code('026a51f1be') ]]]

Cópialo y pégalo justo encima de `StarshipPart`. Ambas propiedades se establecen automáticamente gracias a una biblioteca que instalamos en el último tutorial. Y como hemos añadido dos campos nuevos, ¡necesitamos una migración!

```terminal
symfony console make:migration
```

Pues migra:

```terminal
symfony console doctrine:migrations:migrate
```

## Utilizar fábricas para crear objetos ficticios

En el último tutorial, utilizamos una biblioteca genial llamada `Foundry` para crear rápidamente un montón de datos ficticios. Vamos a hacer lo mismo con`StarshipPart`. El paso 1 -ya que aún no tenemos una- es generar una fábrica para la entidad con:

```terminal
symfony console make:factory
```

Ve a verlo en `src/Factory/StarshipPartFactory.php`. Añade algunos valores por defecto para cada campo, pero podemos hacerlo más interesante. En la parte superior de `StarshipPartFactory`, pegaré algo de código con partes de ejemplo (puedes cogerlo del bloque de código de esta página). Sustituye también el retorno en `defaults()` por código que utilice esos datos. 

[[[ code('68657d878e') ]]]

Por último, utiliza esto en los accesorios. En la parte inferior, crea 50 piezas aleatorias utilizando`StarshipPartFactory::createMany(50)`. 

[[[ code('01ee7a226f') ]]]

De nuevo en el terminal, ejecuta:

```terminal
symfony console doctrine:fixtures:load
```

Y comprueba las nuevas piezas con:

```terminal
symfony console doctrine:query:sql
```

Y luego `select * from starship_part`

Y con sólo unas pocas líneas de delicioso código, tenemos 50 piezas aleatorias en la base de datos. A continuación: empecemos a vincular estas piezas a sus respectivas naves creando nuestra primera relación: la importantísima relación `ManyToOne`.
