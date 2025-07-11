# Relación Muchos-Muchos

Muy bien, tenemos una entidad `Starship` y una entidad `Droid`configuradas y listas para mezclarse. ¿Cómo conseguimos que estas dos entidades se conecten?

Imagínatelo así: Cada `Starship` va a necesitar un equipo de `Droids` para que las cosas funcionen sin problemas... y para el alivio cómico ocasional. Cada `Droid`, a su vez, debería poder servir a muchos `Starships`. Olvídate de la base de datos y céntrate en los objetos. Nuestra entidad `Starship`necesita una propiedad `droids` que contenga una colección de todos los `Droid`s que tiene asignados.

¡Genial! Vuelve a tu terminal y ejecuta:

```terminal
symfony console make:entity
```

Actualiza `Starship` y añade una propiedad `droids`. Utiliza "relación" para entrar en el práctico asistente. Esta vez, necesitamos una relación `ManyToMany`:

> Cada `Starship` puede tener muchos `Droids`, y cada `Droid` puede servir en muchos
> `Starships`. ¡Suena perfecto!

A continuación, nos pregunta si queremos mapear el lado inverso de la relación. Esto es preguntarnos si queremos dar a nuestro `Droids` la capacidad de listar todos los `Starships` a los que está conectado: `$droid->getShips()` eso suena útil. Así que digamos "sí". Para el nuevo nombre de campo dentro de `Droid`, `starships` servirá perfectamente. 

Observa que ha actualizado tanto `Starship` como `Droid`. Echa un vistazo a los cambios en cada uno.

## La magia "Muchos a muchos

En `Starship`, ahora tenemos una nueva propiedad `droids`, que es una`ManyToMany`. También ha inicializado `droids` en `ArrayCollection` y ha añadido los métodos `getDroids()`, `addDroid()` y `removeDroid()`:

[[[ code('d248b94310') ]]]

Si estás pensando que esto se parece mucho a una relación `OneToMany`, ¡ding, ding! ¡Pídete una pizza! ¡Porque lo es!

En `Droid`, la historia es parecida. Tenemos una propiedad `starships`, que es una `ManyToMany`, y se inicializa en el constructor. Luego tenemos las mismas`getStarships()`, `addStarship()`, y `removeStarship()`:

[[[ code('bac832adec') ]]]

Genera la migración para esto. Vuelve al terminal y ejecuta:

```terminal
symfony console make:migration
```

## Desvelar la tabla de unión

¡Maravilloso! Echa un vistazo a lo que ha generado: es fascinante. ¡Tenemos una nueva tabla llamada `starship_droid`! Incluye una clave ajena `starship_id` para`starship` y una clave ajena `droid_id` para `droid`:

[[[ code('f94c11804b') ]]]

Así es como se estructura una relación `ManyToMany` en la base de datos: con una tabla join. La verdadera magia de Doctrine es que sólo tenemos que pensar en objetos. Un objeto `Starship`tiene muchos objetos `Droid`, y un objeto `Droid` tiene muchos objetos `Starship`. Doctrine se encarga de los tediosos detalles de guardar esa relación en la base de datos.

Antes de continuar, ejecuta esa migración. Vuelve al terminal y hazlo:

```terminal
symfony console doctrine:migrations:migrate
```

¡Genial! Ya tenemos una nueva y reluciente tabla de unión. Vale... ¿pero cómo relacionamos los objetos `Droid` con los objetos `Starship`? Eso a continuación... ¡y te va a encantar!
