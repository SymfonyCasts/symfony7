# Agujero negro: Borrar entidades

Oh-oh, nos acaban de informar de que esta nave, el USS Leafy Cruiser, ha caído en un agujero negro. Por suerte, no había nadie a bordo, pero esta nave está ahora espaguetizada. Como ya no existe en esta realidad, tenemos que eliminarla de nuestra base de datos.

Crearemos un comando para ello. En tu terminal, ejecuta:

```terminal
symfony console make:command
```

Para el nombre, utiliza `app:ship:remove`. Esto ha creado una nueva clase de comando.

En tu IDE, abre `src/Command/ShipRemoveCommand.php`. El creador ha añadido algo de código repetitivo para nosotros. En primer lugar, actualiza la descripción a `Delete a starship`. En el constructor, necesitamos inyectar dos cosas: `private ShipRepository $shipRepo`
y `private EntityManagerInterface $em`. Cuando necesites encontrar o recuperar entidades, utiliza el repositorio. Cuando necesites gestionar entidades, como persistir, actualizar o eliminar, utiliza el gestor de entidades, o "EM" para abreviar.

En el método `configure()`, elimina esta llamada `addOption()`. En `addArgument()`, cambia el nombre a `slug`, pon `InputArgument::REQUIRED`, y actualiza la descripción a `The slug of the starship`.

Abajo en `execute()`, sustituye este `$arg1 =` por `$slug = $input->getArgument('slug')`.

Ahora, necesitamos encontrar la nave por esta babosa. Cada repositorio de entidades tiene el método perfecto para esto. Escribe `$ship = $this->shipRepo->findOneBy()`. Dentro, pasa un array donde la clave es la propiedad a buscar y el valor es el valor a buscar en ese campo: `['slug' => $slug]`. Al utilizar estos métodos de búsqueda "out-of-the-box", Doctrine escapa automáticamente los valores, por lo que no tienes que preocuparte por los ataques de inyección SQL.

Ajusta esta sentencia `if` a `if (!$ship)`. `findOneBy()` devuelve `null` si no se ha encontrado una entidad. Dentro, escribe `$io->error('Starship not found.')` y devuelve `Command::FAILURE`.

Escribe un comentario para informar al usuario de que estamos a punto de eliminar la nave estelar.`$io->comment(sprintf('Removing starship %s', $ship->getName()))`.

Elimina esta repetición adicional y escribe `$this->em->remove($ship)`. Al igual que con `persist()`,`remove()` en realidad no elimina la entidad directamente, sino que la añade a una cola de entidades que se eliminarán cuando llamemos a `$this->em->flush()`.

Añade un mensaje de éxito con `$io->success('Starship removed.')`. ¡Comando ejecutado!

Vuelve a nuestra aplicación y actualiza la página para asegurarte de que el barco sigue ahí. Ahora, copia el slug de la URL. De nuevo en tu terminal, ejecuta:

```terminal
symfony console app:ship:remove
```

Pega el slug copiado y ejecuta. Ya está Nave estelar eliminada. Ejecuta de nuevo el mismo comando.

```terminal
symfony console app:ship:remove leafy-cruiser-ncc-0001
```

"Nave estelar no encontrada" ¡Perfecto! De nuevo en la aplicación, actualiza la página. 404. ¡La nave ha desaparecido de la base de datos!

Ya hemos visto cómo persistir y eliminar entidades. A continuación, veremos cómo actualizar la entidad nave estelar.
