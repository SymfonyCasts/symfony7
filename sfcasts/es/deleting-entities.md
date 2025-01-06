# Agujero negro: Borrar entidades

Oh-oh, acabamos de enterarnos de que esta nave, el USS Leafy Cruiser, ha caído en un agujero negro. Por suerte, no había a bordo ningún personaje querido a largo plazo, pero esta nave está ahora espaguetizada. Como ya no existe en esta realidad, tenemos que eliminarla de nuestra base de datos.

## `app:ship:remove` Comando

Vamos a crear un comando para gestionar esto. En tu terminal, ejecuta:

```terminal
symfony console make:command
```

Para el nombre, utiliza `app:ship:remove`. Esto ha creado una nueva clase de comando.

## Constructor de comandos

¡Ábrelo! `src/Command/ShipRemoveCommand.php`. El creador añadió algo de código repetitivo para nosotros. Actualiza la descripción a `Delete a starship`:

[[[ code('0aa83c68dc') ]]]

En el constructor, necesitamos inyectar dos cosas: `private ShipRepository $shipRepo` 
y `private EntityManagerInterface $em`:

[[[ code('f4b2e2ec67') ]]]

Cuando necesites encontrar o recuperar entidades, utiliza el repositorio. Cuando necesites gestionar entidades, como persistir, actualizar o eliminar, utiliza el gestor de entidades, o "EM" para abreviar.

## Configuración del comando

En el método `configure()`, elimina `addOption()`. Para `addArgument()`, cambia el nombre a `slug`, establece `InputArgument::REQUIRED`, y actualiza la descripción a `The slug of the starship`:

[[[ code('70a5d454be') ]]]

## Lógica de mando

Abajo en `execute()`, sustituye este `$arg1 =` por `$slug = $input->getArgument('slug')`:

[[[ code('20966f236b') ]]]

A continuación, tenemos que encontrar el barco por esta babosa. Cada `EntityRepository` ya tiene el método perfecto para ello. Escribe `$ship = $this->shipRepo->findOneBy()` pasando un array donde la clave es la propiedad a buscar y el valor es el valor a buscar: `['slug' => $slug]`:

[[[ code('f95b2f3919') ]]]

Al utilizar estos métodos de búsqueda preconfigurados, Doctrine escapa automáticamente los valores, por lo que no tienes que preocuparte por los ataques de inyección SQL.

Ajusta esta sentencia `if` a `if (!$ship)`. `findOneBy()` devuelve `null` si no se ha encontrado una entidad. Dentro, escribe `$io->error('Starship not found.')` y devuelve `Command::FAILURE`:

[[[ code('c5330da086') ]]]

Escribe un comentario para informar al usuario de que estamos a punto de eliminar la nave estelar.`$io->comment(sprintf('Removing starship %s', $ship->getName()))`:

[[[ code('8850664f5f') ]]]

Elimina esta repetición adicional y escribe `$this->em->remove($ship)`. Al igual que con `persist()`,`remove()` en realidad no elimina la entidad directamente, sino que la añade a una cola de entidades que se eliminarán cuando llamemos a `$this->em->flush()`:

[[[ code('8b3a99b7a0') ]]]

Añade un mensaje de éxito con `$io->success('Starship removed.')`:

[[[ code('21f4c427aa') ]]]

¡Comando realizado!

Vuelve a nuestra aplicación y actualiza la página para asegurarte de que el barco sigue ahí. Ahora, copia el slug de la URL.

## Ejecutar el comando

De nuevo en tu terminal, Ejecuta:

```terminal
symfony console app:ship:remove
```

Pega el slug copiado y ejecuta. ¡Éxito! Nave estelar eliminada. Ejecuta de nuevo el mismo comando.

```terminal-silent
symfony console app:ship:remove leafy-cruiser-ncc-0001
```

"Nave estelar no encontrada" ¡Perfecto! De nuevo en la aplicación, actualiza la página. 404. ¡La nave ha desaparecido de la base de datos!

¡Muy bien! Hemos visto cómo persistir y eliminar entidades. A continuación, veremos cómo actualizar la entidad nave estelar.
