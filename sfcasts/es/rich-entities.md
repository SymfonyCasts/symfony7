# Refactorizador cuántico: Entidades ricas

Echa un vistazo a nuestra entidad `Starship`. Es un montón de propiedades y getters y setters. Un poco aburrido, ¿verdad? ¡No tiene por qué serlo! Como las entidades son clases PHP estándar, podemos añadir métodos explícitos y significativos que describan nuestra lógica de negocio, como`goToWarp(7)` o `enterOrbitAround($millersPlanet)`. Éstos se denominan métodos de entidad enriquecida. 

Probemos esto y exploremos las ventajas.

Nuestra lógica de facturación `Starship` vive actualmente en el método `ShipCheckInCommand::execute()`. Después de obtener el barco, actualizamos sus `arrivedAt` y `status`. ¿Y si, en el futuro, añadimos un controlador de facturación? Tendríamos que duplicar esta lógica allí. Y si la lógica de "registro" cambia -por ejemplo, si necesitamos actualizar otro campo-, tendríamos que acordarnos de cambiarla en varios sitios. Eso no es ciencia ficción.

Lo mejor es trasladar, o encapsular, esta lógica de registro a un método de la entidad. Abre `src/Entity/Starship.php` y desplázate hasta el final. Crea un nuevo: `public function checkIn()`. Haz que acepte un`?\DateTimeImmutable $arrivedAt = null` opcional y devuelva `static`, que es una forma elegante de decir "devuelve el objeto actual".

`return $this`. Arriba, añade la lógica de comprobación: `$this->arrivedAt = $arrivedAt`, y si no se ha pasado, `?? new \DateTimeImmutable('now')`. A continuación, `$this->status = StarshipStatusEnum::WAITING`.

Vuelve a `ShipCheckInCommand` y sustituye la lógica por `$ship->checkIn()`. ¡Vaya, está claro! Ahora el comando se lee como una historia: "Encuentra la nave, luego regístrala".

Para asegurarte de que sigue funcionando, vuelve a la página principal y actualízala. Encuentra una nave que no esté "esperando"... Allá vamos: "Pirata Estelar". Haz clic en ella y copia el slug de la URL. De vuelta a tu terminal, Ejecuta:

```terminal
symfony console app:ship:check-in
```

pega el slug, ¡y ejecuta! ¡Éxito! De vuelta en la aplicación, actualiza. ¡Perfecto! La nave está ahora marcada como "esperando" y ha llegado hace 6 segundos.

Si te encuentras repitiendo operaciones comunes en tus entidades, considera la posibilidad de añadir, y luego utilizar, un método que describa el trabajo que se está realizando. Es una victoria fácil para la legibilidad y la mantenibilidad.

Bien tripulación, ¡eso es todo para este curso de Fundamentos de Doctrine! Si quieres mejorar tus conocimientos de Doctrine, [busca "Doctrine" en SymfonyCasts](https://symfonycasts.com/search?q=doctrine) para encontrar cursos más avanzados. La [documentación de Doctrine](https://www.doctrine-project.org/projects/doctrine-orm/en/3.3/index.html) también es un gran recurso. Y como siempre, si tienes alguna pregunta, estamos a tu disposición en los comentarios.

hasta la próxima, ¡feliz programación!
