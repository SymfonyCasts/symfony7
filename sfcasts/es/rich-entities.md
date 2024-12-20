# Refactorizador cuántico: Entidades ricas

Echa un vistazo a nuestra entidad `Starship`. Es un montón de propiedades y getters y setters. Un poco aburrido, ¿verdad? ¡No tiene por qué serlo! Como las entidades son clases estándar de PHP, podemos añadir métodos explícitos y significativos que describan nuestra lógica de negocio.`goToWarp(7)` o `enterOrbitAround($millersPlanet)`. Se llaman métodos de entidad enriquecida. 

¡Vamos a crear uno!

En el último capítulo, añadimos este comando de facturación de barcos. Nuestra lógica de facturación está aquí, en el método `execute()`. Después de obtener el barco, actualizamos sus `arrivedAt` y `status`. Imagina que, en el futuro, añadimos un controlador de registro. Tendríamos que duplicar esta lógica allí. Además, si esta lógica cambia, como si necesitamos actualizar otro campo, tendríamos que acordarnos de actualizarla en varios sitios. ¡Qué asco!

La mejor manera es mover, o encapsular, esta lógica de registro en un método de la entidad`Starship`. Abre `src/Entity/Starship.php` y desplázate hasta la parte inferior. Crea un nuevo método: `public function checkIn()`. Haz que acepte un`?\DateTimeImmutable $arrivedAt = null` opcional y que devuelva `static`. Dentro, primero,`return $this`. Arriba, añade nuestra lógica de comprobación: `$this->arrivedAt = $arrivedAt`, y si no se ha pasado, `?? new \DateTimeImmutable('now')` para que por defecto sea la hora actual. A continuación, `$this->status = StarshipStatusEnum::WAITING`.

Vuelve a `ShipCheckInCommand` y sustituye la lógica por `$ship->checkIn()`. ¡Qué bien!

Para asegurarte de que sigue funcionando, vuelve a la página de inicio de la aplicación y actualízala. Encuentra un barco que no esté "esperando"... Ya está: "Pirata Estelar". Haz clic en él y copia el slug de la URL. De nuevo en tu terminal, Ejecuta:

```terminal
symfony console app:ship:check-in
```

pega el slug, ¡y ejecuta! ¡Éxito! De vuelta en la aplicación, actualiza. ¡Perfecto! La nave está ahora marcada como "esperando" y ha llegado hace 6 segundos.

Si te encuentras repitiendo operaciones comunes en tus entidades, considera la posibilidad de añadir, y luego utilizar, un método que describa el trabajo que se está realizando. Es una victoria fácil para la legibilidad y la mantenibilidad.

Bien tripulación, ¡eso es todo para este curso de Fundamentos de Doctrine! Si quieres mejorar tus conocimientos de Doctrine, [busca "Doctrine" en SymfonyCasts](https://symfonycasts.com/search?q=doctrine) para encontrar cursos más avanzados. La [documentación de Doctrine](https://www.doctrine-project.org/projects/doctrine-orm/en/3.3/index.html) también es un gran recurso.

hasta la próxima, ¡feliz programación!
