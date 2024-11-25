# Repositorio de Entidades de la Nave Estelar

Es hora de hablar de los Repositorios de Entidades: un lugar donde podemos "acoplar" consultas personalizadas para una entidad.

En nuestro controlador de la página de inicio, escribimos una consulta con el constructor de consultas para encontrar todas las naves. Esto está bien, pero si necesitáramos utilizar la misma consulta en otro lugar, tendríamos que duplicarla. Y si necesitáramos cambiarla, tendríamos que hacerlo en varios sitios. ¡Qué asco!

¡Repositorios de entidades al rescate! ¿No creó `make:entity` uno de estos? ¿Cómo accedemos a él?

En nuestro controlador de página de inicio, escribe `dd($em->getRepository(Starship::class))`, pasa a nuestra aplicación y actualiza. `App\Repository\StarshipRepository`. Vale, esta es una forma de acceder, ¡pero hay una forma mejor! Abre `src/Repository/StarshipRepository.php`.

En primer lugar, si tienes curiosidad por saber cómo sabe Doctrine que esta clase es el repositorio de nuestra entidad`Starship`, salta a `src/Entity/Starship.php`. Ah, el atributo `#[ORM\Entity]`tiene un argumento `repositoryClass: StarshipRepository::class`.

¡Vuelve a `StarshipRepository`! Se trata de un tipo especial de repositorio de entidades: un`ServiceEntityRepository`. Eso significa que es a la vez un repositorio de entidades y un servicio, ¡así que puede autocablearse! Esta es una de esas geniales integraciones Symfony/Doctrine.

¡Vamos a utilizarla!

En nuestro controlador de página de inicio, elimina este `dd()`. En lugar de inyectar `EntityManagerInterface`, inyecta `StarshipRepository $repository`. Esta consulta que escribimos antes, para obtener todos los barcos, es tan común que existe un acceso directo en el repositorio para ella: `findAll()`. Sustituye la consulta por `$repository->findAll()`. De vuelta a nuestra aplicación, actualiza la página de inicio para asegurarte de que sigue funcionando. ¡Genial!

Utiliza también este repositorio en nuestro controlador `StarshipController::show()`. Sustituye`EntityManagerInterface` por `StarshipRepository $repository`. Nuestro repositorio también tiene un método `find()`, pero como es el repositorio Starship, no necesitamos pasar la clase de entidad, sólo `$id`.

Vuelve a la aplicación, actualízala y haz clic en una nave estelar. Sigue funcionando, ¡perfecto!

De vuelta a nuestro controlador de la página de inicio, en lugar de encontrar todas las naves, quiero encontrar sólo las naves cuyo estado no sea `completed`. Sólo `waiting` o `in progress`. Ésta es una consulta perfecta para nuestro `StarshipRepository`. Ábrelo y añade un nuevo método `public function findIncomplete()` con un tipo de retorno `array`. Añade un docblock arriba para especificar que esto devolverá un array de objetos `Starship`.

Dentro, escribe `return $this->createQueryBuilder()`. Para el primer argumento, utiliza `e`. Esto es sólo un alias para nuestra entidad - lo necesitaremos en un segundo. Lo bueno de crear un constructor de consultas en un repositorio, es que no necesitas especificar `select()` o `from()` como hicimos en nuestro controlador - ¡se hace automáticamente! Necesitamos una cláusula `where` para filtrar los barcos completados. Escribe `->where('e.status != :status')`. `e.status` es el nombre de la propiedad de nuestra entidad. Este `:status` es un marcador de posición para un valor que estableceremos a continuación con`->setParameter(':status', StarshipStatusEnum::COMPLETED)`. Es importante utilizar siempre este sistema de marcador de posición/parámetro por un par de razones. En primer lugar, mejora el rendimiento al permitir que Doctrine almacene en caché la consulta. En segundo lugar, y lo que es más importante, ayuda a evitar ataques de inyección SQL cuando se utiliza la entrada del usuario en una consulta. Por último, llama a `->getQuery()` y `->getResult()`.

Ahora, en nuestro controlador de página de inicio, sustituye `findAll()` por `findIncomplete()`.

De vuelta en nuestra app, una vez que actualicemos, deberíamos ver desaparecer este barco completado. Actualizar... y... ¡desaparecido! Perfecto, ¡nuestra consulta funciona! Comprueba el perfilador, vemos la consulta y el parámetro que hemos utilizado.

De vuelta en nuestro controlador de la página de inicio, no me gusta esta lógica de `$myShip`. Simplemente estamos cogiendo un barco al azar de la lista anterior `$ships`. ¡Parece un trabajo perfecto para otro método del repositorio! En `StarshipRepository`, añade un nuevo método `public function findMyShip()`
que devuelva un objeto `Starship`. Podemos imaginar que este método tomaría un usuario o algo para encontrar su nave, pero por ahora, sólo devuelve `$this->findAll()[0]`para obtener la primera nave de la tabla.

Ahora, de vuelta en el controlador, sustituye esto por `$repository->findMyShip()`. Salta a la aplicación y actualiza. Ahora se considera mi nave. Mira el perfilador: ¡dos consultas! La primera encuentra todas las naves incompletas y la segunda es nuestra `findAll()`a partir de `findMyShip()`. ¡Perfecto!

¡A continuación, mejoraremos nuestros accesorios con otra biblioteca que nos permitirá inyectar un montón de naves estelares con el mínimo esfuerzo!
