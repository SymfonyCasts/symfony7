# Tecnología alienígena para accesorios: Fundición y Falsificación

Estamos utilizando `src/DataFixtures/AppFixtures.php` para crear datos de fixture falsos. Esto funciona bien. Pero, ¿dónde está lo guay y divertido? ¿Realmente queremos escribir código manual para añadir docenas o más entidades? Puntos para ti si has respondido: ¡diablos, no!

Para que esto pase de tedioso a terrorífico, busca tu terminal y ejecuta:

```terminal
composer require --dev foundry
```

Desplázate hacia arriba para ver lo que se ha instalado. Los paquetes importantes son `zenstruck/foundry` -como forma de crear muchas entidades rápidamente- y `fakerphp/faker` -una biblioteca para crear datos falsos de forma que no tengamos que depender de lorem ipsum y de nuestra propia falta de creatividad-.

Ejecuta

```terminal
git status
```

para ver lo que hicieron las recetas: habilitó un bundle y añadió un archivo de configuración. Esa configuración funciona bien nada más sacarla de la caja, así que no hace falta mirarla.

Con Foundry, cada entidad puede tener una clase de fábrica. Para ponerlas en marcha, ejecuta:

```terminal
symfony console make:factory
```

Esto lista todas las entidades que aún no tienen una fábrica. Elige `Starship` y... ¡éxito! Se ha creado una nueva clase `StarshipFactory`. Ve a verla:`src/Factory/StarshipFactory.php`.

Esta clase será muy buena para crear objetos `Starship`, muy útil en caso de que vuelvan los Borg. Primero, mira este método `class()`. Indica a Foundry con qué clase de entidad ayuda esta fábrica.  En `defaults()` es donde definimos los valores por defecto que utilizaremos al crear naves estelares. Te recomiendo que añadas valores por defecto para todos los campos obligatorios: te hará la vida más fácil.

¡Echa un vistazo a estas llamadas a `self::faker()`! Así es como generamos datos aleatorios. Para`name`, `captain` y `class`, es texto aleatorio, `status`, es un`StarshipStatusEnum` aleatorio y `arrivedAt` por defecto es cualquier fecha aleatoria Dado que aún no se ha inventado el viaje en el tiempo, sustituye `self::faker()->dateTime()` por `self::faker()->dateTimeBetween('-1 year', 'now')`.

El método `text()` de Faker nos dará un texto aleatorio, pero no necesariamente interesante. En lugar de servir bajo el capitán "desayuno de tarta de manzana", en el directorio `tutorial/`, copia estas constantes y pégalas en la parte superior de la clase fábrica. Luego, para `captain` utiliza `randomElement(self::CAPTAINS)`. Para`class`, `randomElement(self::CLASSES)` y para `name`, `randomElement(self::SHIP_NAMES)`.

¡Es hora de utilizar esta fábrica! En `src/DataFixtures/AppFixtures.php`, en `load()`, escribe `StarshipFactory::createOne()`. Pásale una matriz de valores de propiedad para la primera nave: copia estos del código existente: `name`, `class`, `captain`, `status`y `arrivedAt`.

Y elimina el código antiguo.

¡Bonificación! Elimina las llamadas a `persist()` y `flush()`: ¡Foundry se encarga de eso por nosotros!

¡Veamos qué hace esto! Recarga los accesorios:

```terminal
symfony console doctrine:fixtures:load
```

Elige `yes` y... ¡éxito! Vuelve atrás, actualiza y... parece lo mismo. ¡Buena señal! Ahora, ¡creemos una flota de naves!

Para las tres primeras, pasamos una matriz de valores... pero no hace falta que lo hagamos. Si no pasamos un valor, utilizará el método `StarshipFactory::defaults()`. Fíjate en lo peligroso que nos resulta: ¿acaba de aparecer un cubo Borg? Prepara 20 naves nuevas con `StarshipFactory::createMany(20)`.

De vuelta en el terminal, carga de nuevo los accesorios:

```terminal
symfony console doctrine:fixtures:load
```

Y en la aplicación, actualiza y... ¡fíjate! Ahora hay toda una flota de naves, y sí, ¡todas tienen datos aleatorios!

Ahora que los datos falsos parecen más reales, me pregunto: ¿y si nuestra aplicación se ejecutara en una enorme base estelar con cientos o miles de naves? Esto sería una página muy larga. A continuación, paginaremos estos resultados en trozos más pequeños.
