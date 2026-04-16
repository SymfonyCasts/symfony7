# Bonificación: Función DQL personalizada

¿Sigues aquí? ¡Estupendo! Tengo algo que enseñarte. ¿Recuerdas el método`filterShips()` que creamos en nuestro `StarshipRepository`? Para obtener sólo los cargueros, tuvimos que añadir `NOT INSTANCEOF` para excluir los cargueros mineros, que son una subclase. Si tuvieras una gran jerarquía de clases, esto podría resultar bastante lioso.

## Perfeccionar el método `filterShips()` 

Veamos si podemos mejorar esto. Primero, elimina la parte `notclass` y el parámetro. Me parece que esto podría funcionar... `s = :class` así que vamos a intentarlo:

[[[ code('091c6563cf') ]]]

Antes de probarlo, recuperemos el uso de `filterShips` en nuestro`MainController::homepage()`. Revertiremos algunos cambios anteriores, inyectaremos de nuevo el`StarshipRepository` y llamaremos a `filterShips()` en lugar de a `findAll()`:

[[[ code('1a049c8710') ]]]

Ve al navegador y actualiza la página de inicio... Hmm, tenemos este error "array_rand... array cannot be empty". Ya lo hemos visto antes, es lo que ocurre si nuestro método `filterShips()`devuelve un array vacío. Comprobemos la consulta para ver qué está pasando.

## Examinar la consulta

Es sutil, pero fíjate en la cláusula `WHERE`: `s0_.id = ?`. Este `s0_` es un alias de tabla SQL interna que utiliza Doctrine: es nuestra tabla `starship`. Esperaba que utilizara nuestra columna discriminadora, pero utiliza el identificador. Supongo que cuando utilizas sólo un alias en DQL, utiliza por defecto el id de la entidad.

Vale, eso no ha funcionado. Probemos otra cosa. ¿Podemos añadir la columna discriminante en nuestro DQL? Otra vez lo mismo, abre la entidad `Starship` para comprobar el atributo `DiscriminatorColumn`...`ship_type`. Así que pon nuestro DQL en `s.ship_type = :class`:

[[[ code('788b53a345') ]]]

y actualiza la página de inicio...

No, tampoco podemos hacer eso. A nivel de DQL, Doctrine no conoce la columna discriminadora, está intentando encontrar una propiedad llamada `ship_type` en nuestra entidad `Starship`, que no existe.

Voy a volver rápidamente a la versión `s = :class`...

[[[ code('824f4292f0') ]]]

Muy bien, de vuelta a la mesa de dibujo.

## Crear nuestra propia función

Lo que tenemos que hacer es crear nuestra propia función DQL. Quiero que tenga este aspecto:`TYPE(s) = :class`:

[[[ code('5eb98bb541') ]]]

Esto tomará el `s`, nuestro alias de entidad DQL, y lo convertirá al SQL correcto con la columna discriminadora.

## Creación de la clase `TypeFunction` 

En `src`, crea una nueva clase PHP... Llámala `TypeFunction` y el espacio de nombres será`App\Doctrine\ORM\Function`. Este parece un buen hogar para nuestras funciones DQL personalizadas.

Durante mi investigación, encontré este [GitHub Gist](https://gist.github.com/nanotronic/f967239f1b4613b3b01110072f0c8a31)... ¿O es gist? Oh tío, este es el argumento jif, gif...

En fin, el autor tuvo la misma idea que nosotros. Y este gist funciona... pero necesita algo de limpieza y modernización. Copia todo el archivo, excepto el espacio de nombres, y pégalo sobre la clase vacía que acabamos de crear.

## Corrección de errores y limpieza

Como puedes ver, esta clase extiende a `FunctionNode`, que es la clase base que se utiliza para las funciones DQL personalizadas.

Hay algunos errores que debemos corregir. En primer lugar, parece que el método `getSql()` probablemente requiere un tipo de retorno. Si miramos en el padre, sí, `string`, así que añádelo. 

Este `ClassMetadataInfo` ha pasado a llamarse simplemente `ClassMetadata`, así que lo actualizaré. Esto no es realmente un error porque está en un comentario, pero ayudará con el autocompletado y la legibilidad.

Creo que el método `parse()` también requiere un tipo de retorno. Mira el padre... sí, `void`, añade eso.

Parece que esta clase `Lexer` ya no tiene estas constantes. Si saltamos a esa clase y nos desplazamos hacia abajo, veo que estas constantes se han trasladado a este enum `TokenType`. Así que, de vuelta en el método `parse()`, sustituye `Lexer` por `TokenType` en los tres lugares.

Ahora que nuestro código PHP es válido, hagamos un poco de limpieza. Elimina estos docblocks redundantes, ya no aportan valor. Esta propiedad `$dqlAlias` no necesita ser pública, conviértela en una `private` anulable `string`que por defecto sea `null`. Arriba, en la parte superior, podemos eliminar la importación `Lexer` puesto que ya no se utiliza.

[[[ code('03759221d4') ]]]

## Anatomía de la clase `TypeFunction` 

Ahora vamos a ver cómo funciona esto. Vamos a registrar esta función con el nombre `TYPE` para que coincida con la función que utilizamos en nuestro DQL. Cuando Doctrine analiza el DQL y encuentra una función, recorre las funciones registradas y llama al método `parse()` en cada una de ellas para encontrar una coincidencia.

Aquí puedes ver que está intentando analizar este `IDENTIFIER`, que será `TYPE`, el nombre con el que lo registramos. A continuación, buscamos una coincidencia con `OPENING_PARANTHESIS`. Luego establecemos la propiedad `dqlAlias` en este `IdentificationVariable()`, básicamente, la cadena que viene después del paréntesis de apertura, que en este caso será `s`. Por último, hacemos coincidir el `CLOSING_PARANTHESIS`.

Si todo eso coincide, entonces Doctrine llama a este método `getSql()` para convertirlo a SQL.

No voy a entrar en detalles aquí. Básicamente, estamos cogiendo este "componente de consulta", que es una matriz. Una de las claves es `metadata`, que es una instancia de `ClassMetadata`. A continuación, cogemos el alias de la tabla SQL. Este alias es esa cosa interna que genera Doctrine. Puedes verlo en el panel del perfilador de consultas. En este caso, la tabla `starship`tiene como alias `s0_`. Eso es lo que estamos cogiendo aquí.

Esta comprobación y excepción es para asegurarnos de que esta función sólo se puede utilizar en entidades que estén en una jerarquía de herencia.

Por último, estamos devolviendo `$tableAlias`... punto... nombre de columna discriminador de los metadatos de la clase.

## Registrar el `TypeFunction` con Symfony

Ahora tenemos que informar a Doctrine de nuestra nueva función registrándola. ¡Hay alguna [documentación] de Symfony (https://symfony.com/doc/current/doctrine/custom_dql_functions.html) que muestra esto! Parece que la registramos en `doctrine.yaml`, en la sección `orm.dql`. Nuestra función es una función de cadena, así que copiaré este trozo... abriré `config/packages/doctrine.yaml`... encontraré la sección `orm`... y la pegaré aquí.

Esta clave bajo `string_functions` es el nombre de la función, así que utiliza `TYPE`. El valor es el nombre de la clase: `App\Doctrine\ORM\Function\TypeFunction`:

[[[ code('00201396af') ]]]

## Prueba el `TypeFunction`

Para asegurarnos de que está registrada y funciona como esperamos, vuelve a `TypeFunction::getSql()`, `dd($this->dqlAlias)`. Esto debería volcar el contenido de nuestra función `TYPE`, `s` en nuestro caso.

De vuelta en el navegador, vamos a la página principal... y obtenemos un error: "Esperaba función conocida, obtuvo TIPO". Hmm, esto significa que nuestra función no se está registrando correctamente... Tal vez tenga un error tipográfico en el espacio de nombres. Lo copiaré y lo pegaré en `doctrine.yaml` para asegurarme de que es correcto. Hmm, era correcto. Oh, duh, ¡tengo el nombre de la función como `TEST`! Cambiaré el nombre a `TYPE` y actualizaré la página principal... ¡genial! ¡se ha volcado `s`!

Elimina el `dd()` y actualiza de nuevo... nuestro viejo amigo, la excepción "el array no puede estar vacío" ha vuelto, pero eso es una buena señal porque significa que nuestra función al menos está registrada y funcionando sin lanzar un error. Comprobemos el panel del perfilador de consultas para ver qué está pasando. Muy bien, la cláusula `WHERE` es ahora `s0_.ship_type`, que es nuestra columna discriminadora. Ahh, el problema es el parámetro pasado. Es el nombre de la clase Carguero, pero esta columna utiliza los alias del mapeo.

En `StarshipRepository::filterShips()`, sustituye el valor de este parámetro por una simple cadena: `freighter`:

[[[ code('3dcc362105') ]]]

y actualiza la página de inicio. ¡Guau! Ha funcionado, y fíjate, ahora sólo tenemos los cargueros, ¡no hay cargueros mineros a la vista!

## Mejorando nuestra función

Puede que sea un fastidio que tengamos que utilizar el alias de cadena. Si cambiáramos el alias en nuestra entidad `Starship`, tendríamos que acordarnos de cambiarlo también aquí. ¡Creo que podemos mejorar esto!

En nuestra entidad `Starship`, tenemos este método `getType()` que invierte el `TYPE_MAP` y coge el alias de la clase actual. Ahora mismo, es un método de instancia, por lo que necesitas un objeto `Starship` instanciado para llamarlo. Pero no hay nada en este método que requiera que sea un método de instancia. Hazlo `static`:

[[[ code('86f8c2979a') ]]]

No te preocupes, PHP es indulgente cuando llama a métodos estáticos como métodos de instancia, así que esto no romperá nada.

Ahora, de vuelta en el método `filterShips()`, establece el valor del parámetro en `Freighter::getType()`:

[[[ code('5391f5c4dc') ]]]

Vuelve a actualizar la página de inicio... ¡perfecto, no ha cambiado nada! ¡La lógica funciona como se esperaba!

Bien, hemos encontrado una función que le faltaba a Doctrine, ¡y la hemos añadido nosotros mismos como una función DQL personalizada! Esta es una función realmente potente de Doctrine, y te permite ampliar las capacidades de DQL para adaptarlas a tus necesidades específicas. Puedes crear todo tipo de funciones personalizadas para diferentes casos de uso. El paquete Composer [beberlei/doctrineextensions](https://packagist.org/packages/beberlei/doctrineextensions) contiene un montón de funciones precreadas. Si alguna vez necesitas una función que no está incorporada, echa un vistazo a ese paquete, ¡puede que encuentres lo que necesitas!

Hasta la próxima, ¡feliz programación!
