# Controladores de alta tecnología: Entidades de Autoinyección

Cuando entramos en una nave desde nuestra página de inicio y vemos su página de presentación, esta URL no es muy bonita ni memorable. Es sólo el ID de la nave. Cambiémosla para utilizar en su lugar nuestro nuevo campo slug. Recuerda que, al igual que el ID, es único, por lo que podemos utilizarlo para encontrar una única nave en la base de datos.

Pero antes quiero mostrarte algo superguay. Abre nuestro controlador`StarshipController::show()`. Estamos inyectando el parámetro `$id` de nuestra ruta y también el servicio `StarshipRepository` para encontrar la nave a partir de este ID. Luego tenemos algo de lógica para lanzar un 404 si no se encuentra la nave.

Sustituye todos los argumentos por sólo `Starship $ship`, luego, elimina toda esta lógica de encontrar y no encontrar. Esto es ahora un controlador delgado - me encanta. Si estás diciendo "pero Starship no es un servicio", tienes razón, pero ten paciencia conmigo.

De vuelta en la aplicación, estamos en la página del programa Starship. Actualiza... y... ¡aún funciona! Intentemos visitar una nave que sabemos que no existe: una con ID 999. Obtenemos un error 404. Obtenemos un error 404. Seguimos teniendo la misma lógica que antes... ¿Cómo?

Las entidades no son servicios... eso sigue siendo, y siempre, cierto. Mira en nuestro controlador`MainController::homepage()`. Estamos inyectando el objeto `Request`. Esto tampoco es un servicio. Si intentaras autoconectar esto en el constructor de un servicio, obtendrías un error.

Los controladores son especiales. Cuando Symfony llama a un método de controlador, primero mira todos los argumentos y los pasa a través de algo llamado "resolvedores de valores de controlador". Hay varios, y ya hemos utilizado unos cuantos, aunque no lo sabíamos. Hay un `RequestValueResolver` para inyectar el objeto `Request`. Incluso los servicios se inyectan a través de un `ServiceValueResolver`.

La integración con Doctrine de Symfony proporciona un `EntityValueResolver`. Así es como podemos inyectar la entidad `Starship`. Funciona porque hemos insinuado el tipo `Starship`, una entidad Doctrine válida, y tenemos un parámetro de ruta `id`. Como toda entidad tiene un `id`, el resolver encuentra e inyecta automáticamente la entidad desde la base de datos. Si no encuentra la entidad, lanza un 404. ¡Esto me encanta!

Como he dicho antes, quiero utilizar la nave `slug` en la URL en lugar del ID. En primer lugar, actualiza nuestro atributo `#[Route]` a `/starship/{slug}`. Ahora, tenemos que actualizar todos los lugares donde generamos la URL para esta ruta.

Empieza por `templates/main/homepage.html.twig`. En esta función `path`, sustituye `id: ship.id` por `slug: ship.slug`. Ahora, abre`templates/main/_shipStatusAside.html.twig`, busca "show", y en esta función `path`, sustituye `id: myShip.id` por `slug: myShip.slug`.

Vuelve a nuestra aplicación y pulsa "Atrás" para ir a la página de inicio. Pasa el ratón por encima del enlace de un barco y observa en la parte inferior izquierda de tu navegador: la URL utiliza ahora el slug. Ahora, haz clic en el enlace.

¡Alerta roja! Tenemos un error. "No se puede autoenlazar el argumento $nave...". El problema aquí es que Symfony ya no detecta esto como un valor de entidad, así que está intentando inyectarlo como un servicio... cosa que no es. El `EntityValueResolver` sólo funciona cuando el parámetro de ruta es `id`. Tenemos que ayudarle un poco.

De vuelta a `StarshipController::show()`, mueve `Starship $ship` a su propia línea para dejarnos algo de espacio. Encima, añade un atributo: `#[MapEntity]`. Dentro, añade`mapping:` y luego un array con una clave de `slug`, que es el nombre del parámetro de ruta. Luego un valor también de `slug`, que es el nombre de la propiedad `Starship` que queremos que consulte`EntityValueResolver`.

Vuelve a la aplicación y actualiza. Vuelve a funcionar, ¡alerta roja cancelada!

Prueba a poner un texto cualquiera para el slug y... ¡404! ¡Perfecto!

Ahora las URL de las naves son bonitas, legibles y aptas para SEO

Volar por el espacio es peligroso. A veces, las naves estelares sufren "desmontajes rápidos no programados"... o, en otras palabras, explotan. Necesitamos una forma de eliminar de nuestra base de datos las naves que ya no... err... existen. A continuación, veremos cómo eliminar entidades con Doctrine.
