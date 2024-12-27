# Actualizaciones de barcos: Actualización de una entidad

Nuestro negocio de reparación de naves va viento en popa Ahora tenemos algunos clientes que repiten y quieren algunas mejoras posteriores. Necesitamos una forma de registrar una nave estelar existente cuyo estado sea completado.

La lista de nuestra página de inicio sólo muestra las naves incompletas, así que tenemos que encontrar una completada. Ejecuta en tu terminal:

```terminal
symfony console doctrine:query:sql 'SELECT slug, status FROM starship'
```

`lunar-marauder-1` es una nave completada. Copia la babosa, y de vuelta en la aplicación, visita `/starships/lunar-marauder-1`. Ya está. Para ver mejor la actualización, vamos a mostrar la fecha `arrivedAt` en la página de presentación. En `templates/starship/show.html.twig`, copia estas etiquetas `h4` y `p`. Pégalas a continuación. Actualiza el contenido de `h4` a `Arrived At`y el de `p` a `{{ ship.arrivedAt|ago }}`.

Vuelve a la aplicación, actualiza y ¡listo! Este barco está completado y llegó hace 1 mes.

Para registrar una nave cuando llega, vamos a crear otro comando. En tu terminal, ejecuta:

```terminal
symfony console make:command
```

Para el nombre, utiliza `app:ship:check-in`. Abre entonces una nueva clase de comando: `src/Command/ShipCheckInCommand.php`. Actualiza la descripción - `Check-in ship` - y para el constructor, necesitamos lo mismo que para el comando eliminar. Abre eso, copia el constructor y pégalo sobre `ShipCheckInCommand::__construct()`. También encontraremos la nave por slug, así que copia el método `configure()` de `ShipRemoveCommand` y pégalo también.

La primera parte de `execute()`, encontrar la nave por slug, también es la misma. Cópiala y pégala. Actualiza el comentario IO a "Comprobando nave estelar...".

Es hora de la lógica real de "registro". Primero, actualiza la fecha de llegada a la hora actual con `$ship->setArrivedAt(new \DateTimeImmutable('now'))`. A continuación, establece el estado a "esperando" con `$ship->setStatus(StarshipStatusEnum::WAITING)`. Estos campos se han actualizado en el objeto, pero aún no en la base de datos. Para ejecutar la consulta `UPDATE`, a continuación

llama, lo has adivinado, a `$this->em->flush()`.

¡Espera, espera, espera! Cuando persistimos o eliminamos una entidad, teníamos que llamar a un método -como `persist` o `remove` en el gestor de entidades- para que Doctrine supiera nuestra intención. ¿Aquí no? ¡No! Doctrine es superinteligente. Arriba, cuando encontramos la entidad, Doctrine empezó a rastrearla. Cuando llamamos a `flush()`, ve que ha sido modificada y determina el mejor SQL para actualizar la base de datos. ¡Asombroso!

Por último, añade un mensaje de éxito: "Nave estelar registrada".

De vuelta a nuestra aplicación, ésta es la nave que queremos registrar. Copia el slug de la url. En tu terminal, ejecuta el nuevo comando con:

```terminal
symfony console app:ship:check-in
```

pega el slug y ¡ejecuta! ¡Éxito! De vuelta en la app, actualiza. Ahora el barco está marcado como "esperando" y ha llegado hace 9 segundos. ¡Ha funcionado!

Vuelve a la lógica de registro dentro de `ShipCheckInCommand`. Estamos llamando a setters para actualizar dos campos. A continuación, vamos a encapsular esta lógica en un método de la entidad`Starship`.

TODO: vuelve a añadir el teaser para entidades ricas.
