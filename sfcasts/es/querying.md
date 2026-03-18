# Consulta de clases

Veamos cómo podemos consultar tipos específicos de naves. Aquí vemos una lista de todas las naves estelares. Pero, ¿y si sólo queremos mostrar exploradoras, o cargueros, o cargueros mineros? ¡Hay DQL para eso!

En primer lugar, vamos a distinguir visualmente los distintos tipos de naves, para que sepamos cuáles son exploradoras, cargueros, etc.

Recuerda que no podemos acceder directamente a la columna discriminador para obtener este valor. Ahora mismo, nuestra única opción es comprobar la instancia de la nave devuelta por`findAll` y ver si es un carguero, un explorador, etc. Esto es fácil de hacer en PHP, pero en Twig, no tanto...

Me gustaría poder llamar a `$starship->getType()` y que devolviera el valor del discriminador. ¡Aquí tienes un truco! Abre nuestra entidad nave estelar, y en el atributo `DiscriminatorMap`, corta la matriz del mapa. En la clase, añade `private const TYPE_MAP =`... pega, no olvides el punto y coma. En el atributo, pon como argumento `self::TYPE_MAP`.

Ahora, abajo, añade un nuevo método, `final public function getType()`, éste devolverá una cadena. Aquí utilizamos `final` para evitar que cualquier subclase modifique la lógica.

Nuestro `TYPE_MAP` tiene como clave los valores del discriminador que queremos devolver, y los valores son los nombres de las clases. Para obtener el valor del discriminador del objeto nave actual, podemos dar la vuelta a la matriz `TYPE_MAP`, de modo que los nombres de las clases sean las claves. Abajo en `getType`, `return array_flip(self::class)[static::class]`. Como recordatorio, `self::class` siempre devuelve la clase en la que está escrito el código, por lo que devolvería `Starship::class` para todas las naves. `static::class`
devuelve la clase del objeto real, por lo que devolvería `Scout::class` para un explorador, `Freighter::class` para un carguero, etc. Por lo tanto, aquí es importante utilizar `static::class` para la clave.

## Actualizar la interfaz de usuario

Con nuestro método `getType()` listo, actualizaremos la plantilla Twig para mostrarlo. Abre `templates/main/homepage.html.twig`. Antes del nombre de la nave, añadiré algún espacio, y añade `{{ ship.type }}`.

En nuestro navegador, actualiza y ¡listo! Podemos ver el nombre de la nave precedido del tipo. Aunque podría quedar más bonito...

Un truco: aunque tu sitio no sea multilingüe, puedes utilizar el componente de traducción. Utiliza estas cadenas de tipo como claves para tu idioma por defecto y personalízalas en tus archivos de traducción.

Para simplificar las cosas, me limitaré a pasarlas por algunos filtros de cadena para hacerlas más bonitas. De vuelta en la plantilla, añade `|replace({'_': ' '})|title`. Esto sustituye los guiones bajos por espacios y pone en mayúsculas la primera letra de cada palabra.

Actualiza el navegador y... ¡mucho mejor!

## Filtrar las Naves

Ahora, pasemos al filtrado propiamente dicho. Abre el `StarshipRepository` y añade un nuevo método`public function filterShips()`, tipo de retorno: `array`. Encima, añade un docblock para especificar que devuelve una matriz de `Starship`'s.

Dentro, `return $this->createQueryBuilder()`, alias `s`. Añade `->where('s INSTANCE OF '.Scout::class)`. Este `INSTANCE OF` es un operador DQL especial que comprueba si la entidad es una instancia de la clase especificada o de cualquier subclase. ¡Igual que el operador `instanceof` de PHP! Así que esto devolverá todos los exploradores y cualquier subclase de explorador (si tuviéramos alguna).

Termina con `->getQuery()->execute()`.

En `MainController::homepage()`, pasa de utilizar `findAll()` a `filterShips()`.

Actualiza la página de inicio y... Bien, ¡acabamos de ver los exploradores!

## Uso de parámetros

Al construir consultas, para evitar la inyección SQL, utilizamos parámetros en lugar de codificar valores en la cláusula where. Así que vamos a hacerlo.

Cambia la cláusula where por `s INSTANCE OF :class`, y debajo, añade `->setParameter('class', Scout::class)`.

Actualiza la página y... un error. "array_rand no puede estar vacío". Hmm, ok esto viene de nuestro `MainController`. Estamos utilizando `array_rand` para obtener una nave aleatoria, y está fallando porque el array `$ships` está vacío.

Es una putada, pero no podemos utilizar el nombre de la clase directamente como parámetro. Sin embargo, ¡hay una solución!

Volviendo a `StarshipRepository`, en `setParameter()`, para el segundo argumento, utiliza`$this->getEntityManager()->getClassMetadata(Scout::class)`. Esto devuelve un objeto de metadatos especial para la clase Explorador y es lo que Doctrine necesita para manejar correctamente el operador `INSTANCE OF`con parámetros.

Actualiza el navegador... y bien, ¡sólo los exploradores otra vez!

## NO INSTANCIA DE

Prueba a cambiar la clase a `Freighter::class` y actualiza la página de inicio. Ahora vemos los cargueros, pero también los cargueros mineros. Esto se debe a que en PHP, instancia de devuelve verdadero para la clase que estás viendo y cualquier subclase.

Para mostrar sólo los cargueros normales, necesitamos otra cláusula where. En nuestro método `filterShips()`, añade `->andWhere('s NOT INSTANCE OF :notclass')`. A continuación, duplica la línea `setParameter` y cambia `class` por `notclass` y `Freighter::class` por `MiningFreighter::class`.

Actualiza la página de inicio y... ¡ya está, sólo los cargueros normales!

Desgraciadamente, si el carguero tuviera 10 subclases, tendríamos que añadir 10 `NOT
INSTANCE OF`'s - una para cada subclase. No creo que haya una forma más elegante de conseguirlo, pero ¡házmelo saber en los comentarios si conoces alguna!

Antes de seguir adelante, vuelve a `MainController` y cambia de`filterShips` a `findAll` y confirma que lo tenemos todo de nuevo.

A continuación, veremos cómo podemos mostrar las propiedades específicas de cada tipo de nave en la plantilla Twig.
