# Consultas cósmicas: la clase Repositorio

Es hora de hablar de los Repositorios de Entidades: un lugar donde "acoplamos" consultas personalizadas para una entidad.

En el controlador de la página de inicio, escribimos una consulta con el constructor de consultas para encontrar todos los barcos. Esto está bien, pero si necesitáramos utilizar la misma consulta en otro lugar, tendríamos que duplicarla. Y si quisiéramos cambiarla, tendríamos que hacerlo en varios sitios. ¡Qué asco!

## Obtener el servicio de repositorio

¡Repositorios de entidades al rescate! Espera, ¿no había creado `make:entity` uno de estos? ¡Lo hizo! Para obtener el objeto de repositorio de una entidad, prueba con`dd($em->getRepository(Starship::class))`.

Vuelve a la aplicación y actualízala. ¡Genial! Tenemos un objeto `App\Repository\StarshipRepository`. Echa un vistazo a esta clase: `src/Repository/StarshipRepository.php`.

Primero, si tienes curiosidad por saber cómo sabe Doctrine que esta clase es el repositorio de la entidad`Starship`, salta a `src/Entity/Starship.php`. Ah, el atributo `#[ORM\Entity]`tiene `repositoryClass: StarshipRepository::class`.

Cada entidad -como `Starship` - tiene su propia clase repositorio. Está vacía para empezar, pero pronto la llenaremos con consultas personalizadas. Además, ¡es un servicio! Eso significa que podemos autoconectarlo.

En el controlador de la página de inicio, elimina este `dd()`. Simplifiquemos: sustituye `EntityManagerInterface`por `StarshipRepository $repository`. Esta consulta que escribimos antes, para obtener todos los barcos, es tan común que todos los repositorios tienen un atajo para ella: `findAll()`.

¡Mucho más bonito! De vuelta en la aplicación, actualiza. ¡Sigue funcionando!

Usémoslo también en `StarshipController::show()`. Sustituye`EntityManagerInterface` por `StarshipRepository $repository`. ¡Cada repositorio también viene pre-construido con un método `find()`! Y como éste es el repositorio `Starship`, no necesitamos pasar la clase de entidad, sólo la `$id`.

Vuelve a la aplicación, actualízala y haz clic en una nave estelar. Sigue funcionando, ¡perfecto!

## Consultas personalizadas en el repositorio

De vuelta en el controlador de la página de inicio, en lugar de encontrar todas las naves, ¿qué pasa si sólo necesitamos encontrar naves cuyo estado no sea `completed`: así que sólo `waiting` o `in progress`. ¡Necesitamos una consulta personalizada! Pero esta vez, en lugar de escribirla en el controlador, vamos a organizarla en el repositorio.

Añade un nuevo método `public function findIncomplete()` que devuelva un `array`. Incluye un docblock para que nuestro IDE sepa que será un array de objetos `Starship`.

Dentro, `return $this->createQueryBuilder('e')`. Esto es sólo un alias para la entidad - lo necesitaremos en un segundo. Lo bueno de crear un constructor de consultas en un repositorio, es que no necesitas especificar el `select()` o `from()` como en el controlador. Se hace automáticamente. Todo lo que tenemos que hacer es añadir`->where('e.status != :status')`. `e.status` es el nombre de la propiedad en la entidad `Starship`y `:status` es un marcador de posición para un valor. Pásale un valor con`->setParameter(':status', StarshipStatusEnum::COMPLETED)`.

Este tonto `:status` y el inmediato `setParameter(':status', ...)` son importantes. Nunca incluyas el valor real en la consulta por dos razones: en primer lugar, Doctrine puede optimizar ligeramente el rendimiento de la consulta cuando se utilizan marcadores de posición; en segundo lugar, y más importante, ¡los marcadores de posición evitan los ataques de inyección SQL! Si pensabas que El Borg era malo, ¡realmente odiarás los ataques de inyección SQL! Para terminar la consulta, añade `->getQuery()` y `->getResult()`.

De vuelta en el controlador de la página de inicio, sustituye `findAll()` por `findIncomplete()`.

Vuelve a girar. Deberíamos ver desaparecer esta nave completada. ¡Lo hacemos! ¡La consulta funciona! Comprueba el perfilador: vemos la consulta y el parámetro que hemos utilizado.

## Otra consulta personalizada, otro método de repositorio

De vuelta en el controlador, no me gusta esta lógica de `$myShip`. Y no es porque estemos falseando la idea de "mi nave" al coger sólo la primera. Es porque, sea cual sea la lógica, ésta debería estar en el repositorio para que podamos encontrar "mi nave" siempre que la necesitemos.

En `StarshipRepository`, añade un nuevo método `public function findMyShip()` que devuelva un objeto `Starship`. Podemos imaginar que este método tomaría un usuario o algo para encontrar su nave, pero por ahora, sólo devuelve `$this->findAll()[0]`para obtener la primera nave de la tabla.

De vuelta en el controlador, sustituye esto por `$repository->findMyShip()`.

Así se lee mejor Vuelve a la aplicación y actualízala. Sigue funcionando! Mira el perfilador: ¡dos consultas! La primera encuentra todas las naves incompletas y la segunda es la `findAll()`de `findMyShip()`. ¡Perfecto!

A continuación, mejoremos nuestros accesorios y hagámoslos 100 veces más divertidos con una biblioteca llamada Foundry. Esto nos permitirá crear toda una flota de naves estelares como si tuviéramos un replicador. ¡Hagámoslo!
