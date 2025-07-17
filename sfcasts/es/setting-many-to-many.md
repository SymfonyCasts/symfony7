# Establecer relaciones de muchos a muchos

Muy bien, vamos a sumergirnos en la parte final de `ManyToMany`. En una esquina, tenemos la entidad `Starship`, que está vinculada mediante una relación`ManyToMany` con la entidad `Droid`. Esta relación nos proporciona una tabla adicional llamada "tabla de unión" que lleva la cuenta de los droides que se han subido a cada nave estelar. Pero, ¿cómo asignamos un `Droid` a un `Starship`? Salta a `AppFixtures`.

## Añadir algunos droides

En primer lugar, vamos a añadir algunos droides a la mezcla. Añadiré código que construya tres droides. Importa la clase con una opción rápida o `Alt` + `Enter`:

[[[ code('2b57660268') ]]]

Y... ¡ya tenemos droides! Nada del otro mundo: crear un nuevo`Droid`, establecer las propiedades necesarias, persistir y vaciar. 

## Asignación de droides a naves estelares

Ahora pasemos a la parte divertida: asignar un `Droid` a un `Starship`. Crea una variable `Starship` y prepárate para la magia:

[[[ code('7a0cd0d073') ]]]

La forma de relacionar estas dos entidades es sorprendentemente sencilla, y te parecerá un déjà vu de nuestra relación `OneToMany`. ¡Apuesto a que incluso puedes adivinarlo!

Antes del `flush()` es: `$starship->addDroid($droid1)`. Haz lo mismo con los otros dos droides: `$starship->addDroid($droid2)` y`$starship->addDroid($droid3)`:

[[[ code('f307dc3e93') ]]]

La tripulación está lista para sus tortitas hechas con droides, ¡así que probemos esto!

```terminal
symfony console doctrine:fixtures:load
```

Sin errores. Para ver si realmente funciona, ejecuta:

```terminal
symfony console doctrine:query:sql 'SELECT * FROM droid'
```

Como esperábamos: tres filas, una por cada droide que hemos creado. Ahora, echa un vistazo a la tabla de unión, `starship_droid`.

```terminal-silent
symfony console doctrine:query:sql 'SELECT * FROM starship_droid'
```

¡Guau! Tres filas, una por cada asignación de droide a nave.

## La magia de Doctrine

La verdadera magia es que, con Doctrine, sólo tenemos que preocuparnos de relacionar un objeto `Droid` con un objeto `Starship`. Luego, se encarga del resto, gestionando la inserción y eliminación de filas en la tabla de unión. 

Después de la descarga, sabemos que tenemos tres filas en la tabla de unión. Ahora, tras la descarga, elimina una asignación:`$starship->removeDroid($droid1)`:

[[[ code('943c689e3d') ]]]

Recarga los accesorios y comprueba la tabla de unión.

```terminal-silent
symfony console doctrine:query:sql 'SELECT * FROM droid'
```

¡Sólo quedan dos filas! Doctrine ha eliminado la fila de nuestro droide eliminado. 

## Caras Propias vs Inversas

Un toque final en `ManyToMany`: ¿recuerdas cuando hablamos de lados propios e inversos de una relación? Como vimos, nuestros métodos sincronizan el otro lado de la relación, añadiendo el `Droid` al `Starship`cuando llamamos a `addDroid()`:

[[[ code('3c785d5357') ]]]

Así que el lado propio no importa mucho.

Pero, ¿cuál es el lado propietario? En un `ManyToMany`, cualquiera de los dos lados podría ser el lado propietario. 

Para averiguar quién es el propietario, mira la opción `inversedBy`. Dice `ManyToMany` y `inversedBy: starships`, lo que significa que la propiedad `Droid.starships` es el lado inverso. 

Ahora bien, esto es casi trivial, pero si eres un maniático del control y quieres dictar el nombre de la tabla de unión, puedes añadir un atributo `JoinTable`. Pero recuerda que tiene que ir en el lado propietario. Aparte de eso, no te preocupes: no es gran cosa.

A continuación, vamos a utilizar la nueva relación para representar los droides asignados a cada nave.