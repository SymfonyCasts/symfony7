# Insertar Datos mediante Fijaciones

Ya tenemos nuestra tabla de base de datos, ¡pero ahora necesitamos datos! Cuando trabajas en tu entorno de desarrollo, es útil tener un conjunto de datos falsos para sembrar tu base de datos: cosas con las que puedes jugar mientras construyes. A esto lo llamamos datos fijos.

En nuestro caso, ¡sería estupendo rellenar previamente nuestra tabla con algunas naves estelares! Doctrine tiene incluso un paquete que añade estos datos falsos! Ejecuta en tu terminal:

```terminal
composer require --dev orm-fixtures
```

Hemos utilizado `--dev` porque sólo necesitamos fixtures en nuestro entorno de desarrollo. Desplázate hacia arriba para ver lo que se ha instalado: `doctrine/data-fixtures` y`doctrine-fixtures-bundle`. Ejecuta

```terminal
git status
```

para ver lo que añadieron las recetas. Cosas estándar de Flex, añadió un bundle, pero también este directorio `src/DataFixtures`. Vamos a comprobarlo: abre`src/DataFixtures/AppFixtures.php`. Este método `load()` es donde podemos crear nuestros accesorios. Elimina lo que hay ahí para que podamos empezar de cero.

## Crear entidades

Para añadir entidades a la base de datos estés donde estés, ¡es refrescantemente sencillo! Primero, crea el objeto como de costumbre:`$ship1 = new Starship()` - el de `App\Entity`.

[[[ code('a55e56fca1') ]]]

En un episodio anterior, creamos este servicio `StarshipRepository` en `src/Model/`. Ábrelo. Tenemos un método `findAll()` que crea estos objetos Starship sobre la marcha. ¡Utilizaremos estos datos para nuestros accesorios!

Copia el segundo argumento de la primera Nave Estelar: ése es el nombre. De vuelta en `AppFixtures`, llama a `$ship1->setName('USS LeafyCruiser (NCC-0001)')`. Haz lo mismo en `$class`: `$ship1->setClass('Garden')`, `$captain`:`$ship1->setCaptain('John Luke Pickles')`, `$status`:`$ship1->setStatus(StarshipStatusEnum::IN_PROGRESS)` y no olvides importar el enum. Por último, `$arrivedAt`: `$ship1->setArrivedAt(new \DateTimeImmutable('-1 day'))`.

[[[ code('40b5bda087') ]]]

Para las otras dos naves, copiaré y pegaré algo de código del directorio `tutorial/`.

[[[ code('e21d621b7b') ]]]

Ahora tenemos tres objetos nave, pero todavía no se ha guardado nada, ni se ha persistido en la base de datos. Pero interesante, Doctrine nos pasa un `ObjectManager`. Éste es el corazón de Doctrine. Lo utilizaremos para guardar, recuperar, actualizar y eliminar objetos, nuestras entidades, de la base de datos. ¡Qué pasada!

## Persistir entidades

Para utilizarlo, después de haber creado nuestros objetos nave, escribe `$manager->persist($ship1)`,`$manager->persist($ship2)`, y `$manager->persist($ship3)`. Pero `persist()` aún no los inserta realmente: sólo los pone en cola para ser guardados.

[[[ code('e21d621b7b') ]]]

## Descarga

Para ejecutar algunas consultas INSERT y conseguir que estas naves se acoplen, escribe `$manager->flush()`.

[[[ code('c8249a651d') ]]]

`flush()` es realmente genial: mira todos los objetos que están en cola para ser persistidos y los escribe en la base de datos con una consulta SQL eficiente. En este caso, insertará las tres naves estelares en una sola consulta ¡Superguay!

## Cargar dispositivos

¡Accesorios listos! ¿Cómo ejecutamos este código? Ejecuta:

```terminal
symfony console doctrine:fixtures:load
```

Comprueba que realmente queremos cargar nuestras instalaciones fijas, porque también borrará todos los datos existentes. Elige `yes` y... ¿Éxito?

Ejecuta de nuevo la consulta SQL sin procesar:

```terminal
symfony console doctrine:query:sql 'SELECT * FROM starship'
```

¡Tenemos naves! ¡Estupendo!

Uf, ¡ya tenemos una base de datos con datos! A continuación, refactorizaremos los controladores de nuestra aplicación para extraer naves estelares de nuestra base de datos y mostrarlas en la página. ¡Esto será mucho más fácil de lo que imaginas!
