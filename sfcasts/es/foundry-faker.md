# Mejores dispositivos con Foundry y Faker

En desarrollo, estamos utilizando esta clase `src/DataFixtures/AppFixtures.php` para crear algunos datos de fixture falsos. Esto funciona perfectamente, pero puede ser engorroso crear docenas o más entidades de esta forma. Además, crear tú mismo los datos falsos puede ser tedioso.

Utilizaremos un par de bibliotecas para ayudarte con esto En tu terminal, ejecuta:

```terminal
composer require --dev foundry
```

Desplázate hacia arriba para ver lo que se ha instalado. Los paquetes importantes son `zenstruck/foundry`, que nos proporciona una forma más rápida de crear entidades, y `fakerphp/faker`, que nos ayuda a generar datos falsos.

Ejecuta:

```terminal
git status
```

Para ver lo que crearon las recetas. Se ha añadido un bundle y una configuración. La configuración por defecto funciona bien fuera de la caja, así que no necesitamos mirarla.

Con Foundry, cada entidad puede tener una clase fábrica que ayude a generarla. Viene con un creador para ayudar a crear fábricas. Crearemos nuestra primera fábrica ejecutando:

```terminal
symfony console make:factory
```

Esto lista todas nuestras entidades que aún no tienen fábrica. Elige `Starship` y... ¡éxito! Vemos que se ha creado un nuevo archivo `src/Factory/StarshipFactory.php`. Compruébalo. En nuestro IDE, abre `src/Factory/StarshipFactory.php`.

En primer lugar, mira este método `class()`. Esto indica a Foundry qué clase de entidad representa esta fábrica. El método `defaults()` es donde podemos añadir valores por defecto para los campos. Mira esto: ¡el creador ha añadido valores por defecto para los campos de nuestra nave estelar! Es una buena práctica hacer que este método devuelva valores por defecto para todos los campos obligatorios de tu entidad. Veremos por qué en un minuto.

Echa un vistazo a estas llamadas a `self::faker()`. Así es como Foundry genera datos aleatorios. Para`name`, `captain` y `class`, está generando texto aleatorio. Para `status`, está seleccionando un elemento aleatorio de nuestros casos `StarshipStatusEnum`. El campo `arrivedAt` está generando cualquier fecha aleatoria, pero tenemos que modificarlo para que genere siempre una fecha en el pasado. ¡Aún no se ha inventado el viaje en el tiempo!

Sustituye `self::faker()->dateTime()` por `self::faker()->dateTimeBetween('-1 year', 'now')`.

El texto generado para `captain`, `class` y `name` será un poco aburrido. ¡Hagámoslo más divertido! En el directorio`tutorial/`, copia estas constantes y pégalas en la parte superior de la clase fábrica.

Ahora, para `captain`, cambia de `text()` a `randomElement()` y pasa `self::CAPTAINS`. Para`class`, utiliza `randomElement(self::CLASSES)` y para `name`, utiliza `randomElement(self::SHIP_NAMES)`.

¡Es hora de utilizar esta fábrica! En `src/DataFixtures/AppFixtures.php`, al principio del método `load()`, escribe `StarshipFactory::createOne()` con un array de propiedades para la primera nave estelar. Cópialas del código existente. Para las otras dos, pégalas y elimina el código antiguo.

Ya no necesitamos estas llamadas a `persist()` y `flush()` - ¡Foundry se encarga de esto por nosotros!

Recarga nuestros accesorios ejecutando:

```terminal
symfony console doctrine:fixtures:load
```

Elige `yes` y... ¡éxito! De vuelta en nuestra aplicación, actualiza la página y... parece la misma. Buena señal. Ahora, ¡vamos a crear un montón de naves estelares más!

Con las tres primeras, pasamos una matriz con todas las propiedades necesarias. Si no pasábamos ninguna, la fábrica utilizaría la predeterminada de `StarshipFactory::defaults()`. Si no pasábamos ninguna, utilizaría todas las predeterminadas. Aprovecha esto para crear otras 20 naves estelares con`StarshipFactory::createMany(20)`. Para cada una que cree, utilizará ahora todos los valores por defecto y, como éstas están utilizando faker, cada una utilizará datos aleatorios.

De vuelta en el terminal, carga de nuevo los accesorios:

```terminal
symfony console doctrine:fixtures:load
```

De vuelta en la aplicación, actualiza la página y... ¡mira! Ahora tenemos aquí toda una flota de naves, y sí, ¡todas tienen datos aleatorios!

¿Y si esta aplicación funcionara en una base estelar enorme y tuviéramos cientos o miles de naves? Esta página sería enorme y tardaría una eternidad en cargarse. A continuación, paginaremos estos resultados en trozos más pequeños.
