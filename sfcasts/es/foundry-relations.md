# Establecer relaciones en la Fundición

Vale, tenemos un par de piezas y unas cuantas naves estelares, pero para llenar nuestra flota de datos de pruebas: Quiero mucho más. Éste es un trabajo perfectamente adecuado para nuestro buen amigo Foundry. Elimina el código manual y, en cualquier lugar, di:`StarshipPartFactory::createMany(100)`:

[[[ code('6cacafa665') ]]]

Y prueba las instalaciones:

```terminal
symfony console doctrine:fixtures:load
```

¡Uh-oh!

> `starship_id` no puede ser nulo en `starship_part`. 

Esto se remonta hasta `StarshipPartFactory`, en el método `defaults()`. Estos son los datos que se pasan a cada nuevo `StarshipPart`cuando se crea. La regla de oro es hacer que `defaults()` devuelva una clave para cada propiedad necesaria del objeto. Ahora mismo, obviamente nos falta la propiedad `starship`, así que vamos a añadirla. Establece `starship`, no `starship_id`, en un método ingenioso llamado `Starship::randomOrCreate()` y pásale un array:

[[[ code('137fb4ea84') ]]]

## Preparando el escenario para las piezas de la nave estelar

En la página de inicio, sólo vamos a listar las naves estelares con estado "en curso" o "en espera". Para asegurarnos de que estas piezas están relacionadas con una nave con estado "en progreso", añade una clave `status` en el array configurado como `StarshipStatusEnum::IN_PROGRESS`:

[[[ code('9e219841ff') ]]]

Este `randomOrCreate()` es un método impresionante: primero busca en la base de datos un `Starship` que coincida con estos criterios (una nave "en progreso"). Si encuentra una, la utiliza. Si no, crea una con ese estado.

Prueba ahora los accesorios.

```terminal-silent
symfony console doctrine:fixtures:load
```

¡No hay errores! Comprueba la base de datos:

```terminal
symfony console doctrine:query:sql "SELECT * FROM starship_part"
```

Fíjate bien... Vale! tenemos 100 piezas vinculadas cada una a un `Starship` aleatorio, que debería ser un `Starship` con estado "en curso". ¡Creo que han sido mis 5 minutos más productivos!

## Tomar el control en Foundry

Pero, ¿y si necesitamos más control? ¿Y si queremos asignar las 100 piezas a la misma nave? Sé que suena un poco inútil, pero nos ayudará a entender mejor Foundry y las relaciones.

Empieza por obtener una variable de nave: `$ship = StarshipFactory::createOne()`:

[[[ code('ebffee3193') ]]]

Luego, en `StarshipPartFactory::createMany()`, pasa un segundo argumento para especificar que queremos que `starship` se establezca en esta nave concreta:

[[[ code('132318b79c') ]]]

Vuelve a cargar los accesorios. 

```terminal-silent
symfony console doctrine:fixtures:load
```

Y ¡listo! Ahora todas las piezas están relacionadas con la misma nave. Y si consultamos el `Starship`, tenemos 23: las 20 de abajo, más las 3 extra que hemos añadido. ¡Todo está encajando!

## El giro argumental de la Fundición

Aquí es donde las cosas se ponen interesantes. En `StarshipPartFactory`, en lugar de utilizar `randomOrCreate()`, cambia a `createOne()`:

[[[ code('2a92dbb62a') ]]]

Carga de nuevo las instalaciones. 

```terminal-silent
symfony console doctrine:fixtures:load
```

Y... consulta todas las naves. 

```terminal-silent
symfony console doctrine:query:sql "SELECT * FROM starship"
```

Vaya, ¡de repente tenemos una flota! 123 naves para ser exactos. 
¿Qué ha ocurrido?

Para cada pieza, se llama a `defaults()`. Así que para las 100 piezas, se está activando esta línea, que crea y guarda un `Starship`, aunque nunca utilicemos ese `Starship` porque lo anulamos momentos después.

¿La solución? Cambiar esto por `StarshipFactory::new()`:

[[[ code('73e9fcc7b6') ]]]

Esta es la salsa secreta: crea una nueva instancia de la fábrica, no un objeto en la base de datos. Pruébalo:

```terminal
symfony console doctrine:fixtures:load
```

Consulta las naves.

```terminal-silent
symfony console doctrine:query:sql "SELECT * FROM starship"
```

¡Perfecto! Volvemos a tener 23. 

## Las fábricas son recetas de objetos

¡Dato curioso! Podemos utilizar estas instancias de fábrica como recetas para crear objetos.`StarshipFactory::new(['status' => StarshipStatusEnum::STATUS_IN_PROGRESS])`no crea un objeto en la base de datos. No: `new()` significa una nueva instancia de la fábrica. Y cuando pasas una fábrica para una propiedad, Foundry retrasa la creación de ese objeto hasta que se necesite, si es que se necesita. Por tanto, sólo si no se anula `Starship`, creará un nuevo `Starship` con el estado "en curso" y lo guardará. En realidad, ésta es la mejor práctica a la hora de establecer relaciones en Foundry: establecerlas en una instancia de fábrica.

Limpia nuestras instalaciones eliminando la anulación:

[[[ code('0162e686b1') ]]]

Y... vuelve a `randomOrCreate()`

[[[ code('ad363a91f5') ]]]

Porque, seamos sinceros, es un método bastante útil.

Recarga las instalaciones una última vez para asegurarte de que no hemos roto nada

```terminal-silent
symfony console doctrine:fixtures:load
```

No Nos esforzaremos más la próxima vez.
