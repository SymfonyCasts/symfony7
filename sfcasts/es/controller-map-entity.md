# Controladores de alta tecnología: Entidades de Autoinyección

Cuando entramos en una nave desde nuestra página de inicio y vemos su página de presentación, esta URL no es muy bonita ni memorable. Es sólo el ID de la nave. Imagínate que Jean-Luc Picard anunciara que era el capitán del USS 43 en lugar del Enterprise. ¡Penoso!

Cambiemos esto para utilizar en su lugar nuestro nuevo campo `slug`. Al igual que `id`, es único, por lo que podemos utilizarlo para encontrar un único barco en la base de datos.

Pero antes quiero enseñarte algo superguay. Abre`StarshipController::show()`. Estamos inyectando el parámetro `$id` de nuestra ruta y el servicio `StarshipRepository` para encontrar el barco a partir de este ID. Luego tenemos la lógica para lanzar un 404 si no se encuentra el barco.

Sustituye todos los argumentos por sólo `Starship $ship`, luego, elimina toda esta lógica de encontrar y no encontrar. Esto es ahora un controlador delgado - me encanta. Si estás diciendo "pero Starship no es un servicio", tienes razón, pero ten paciencia conmigo.

De vuelta en la aplicación, estamos en la página del programa Starship. Actualiza... y... ¡aún funciona! Intentemos visitar una nave que sabemos que no existe: una con ID 999. Obtenemos un error 404. Obtenemos un error 404. Seguimos teniendo la misma lógica que antes... ¿Cómo?

Las entidades no son servicios... eso sigue siendo, y siempre, cierto. Mira en nuestro controlador`MainController::homepage()`. Estamos inyectando el objeto `Request`. Esto tampoco es un servicio. Si intentaras autoconectar esto en el constructor de un servicio, obtendrías un error.

Los controladores son especiales. Cuando Symfony llama a un método de controlador, primero mira todos los argumentos y los pasa a través de algo llamado "resolvedores de valores de controlador". Hay varios, y ya hemos utilizado unos cuantos, aunque no lo sabíamos. Hay un `RequestValueResolver` para inyectar el objeto `Request` y un`ServiceValueResolver` si un argumento tiene un tipo de servicio.

La integración con Doctrine de Symfony proporciona un `EntityValueResolver`. Así es como podemos inyectar la entidad `Starship`. Funciona porque hemos indicado `Starship`, una entidad Doctrine válida, y tenemos un parámetro de ruta `id`. Como toda entidad tiene un `id`, el resolver busca automáticamente la entidad y nos la pasa. Si no encuentra la entidad, lanza un 404. ¡Me encanta!

Volvamos a nuestra misión: utilizar la nave `slug` en la URL en lugar del ID. En primer lugar, actualiza el atributo `#[Route]` a `/starship/{slug}`. A continuación, tenemos que actualizar todos los lugares donde generamos la URL para esta ruta. No te preocupes, sólo son 2.

Empieza por `templates/main/homepage.html.twig`. En la función `path`, sustituye `id: ship.id` por `slug: ship.slug`. Ahora, abre`templates/main/_shipStatusAside.html.twig`, busca "show", y en este `path`sustituye `id: myShip.id` por `slug: myShip.slug`.

Vuelve a nuestra aplicación y pulsa "Atrás" para ir a la página de inicio. Pasa el ratón por encima del enlace de un barco y mira la URL. ¡Es mucho más bonita! Haz clic en el enlace.

¡Alerta roja! 

> No se puede autocablear el argumento $nave...".

El problema es que cuando no hay un comodín de ruta llamado `id`, vuelve a intentar autoconectar `Starship` como si fuera un servicio. Cuando el comodín de ruta no se llama `id`, tenemos que ayudarle un poco.

De vuelta a `StarshipController::show()`, mueve `Starship $ship` a su propia línea para dejarnos algo de espacio. Sobre él, añade un atributo: `#[MapEntity]` con un array con una clave de `slug` -este es el nombre del parámetro de ruta- y un valor también de `slug` -este es el nombre de la propiedad sobre la que debe consultar-.

Vuelve a la aplicación y actualiza. Vuelve a funcionar, ¡alerta roja cancelada!

Prueba a poner texto aleatorio para el slug y... ¡404! ¡Perfecto!

Ahora las URL de nuestras naves son bonitas, legibles y aptas para el SEO

Volar por el espacio es peligroso. A veces, las naves estelares sufren "desmontajes rápidos no programados"... o, en otras palabras, explotan. Necesitamos una forma de eliminar de nuestra base de datos las naves que ya no... err... existen. A continuación, veremos cómo eliminar entidades con Doctrine.
