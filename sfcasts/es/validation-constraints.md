# Restricciones de validación

En el capítulo anterior, instalamos el Componente Validador de Symfony y aplicamos nuestra primera restricción de validación al campo `name` de nuestro formulario. Esta configuración nos ayuda a detectar errores cuando enviamos un formulario vacío que elude la validación HTML5 del lado del cliente.

Sin embargo, nuestra validación está actualmente escondida dentro del tipo de formulario. Esta configuración está bien hasta que creemos otro formulario para la misma entidad `StarshipPart`. Si lo hiciéramos, acabaríamos duplicando todas las restricciones en el nuevo tipo de formulario.

## Añadir validación a las propiedades de la entidad

Un enfoque mejor es adjuntar la validación directamente a la propia entidad. De ese modo, todos los formularios se beneficiarán automáticamente. Además, será útil si decidimos validar el objeto entidad de forma independiente, fuera de un formulario.

Empecemos por comentar la restricción en el tipo de formulario. A continuación, abre la entidad `StarshipPart`. Justo encima de la propiedad `$name`, empieza a añadir un atributo `#[NotBlank()]`. PhpStorm me da varias opciones, y yo elegiré`Assert\NotBlank`. Esto crea un alias común `Assert` para nuestro espacio de nombres de restricción por comodidad. Dentro, podemos especificar un `message:`Agarra que del tipo de formulario:

> ¡Cada parte debe tener un nombre!

## Añadir más restricciones

Ya que estamos aquí, añadamos también una regla para la propiedad `$price`. Porque no podemos tener piezas de naves estelares gratis, son caras. Así que, por encima de`$price`, añade `#[Assert\GreaterThan()]` y establece `value` en `0`. También podemos personalizar esta `message`, ¿qué te parece:

> ¡La Parte de Nave Estelar no puede ser gratis!

Perfecto. 

Ahora, probemos de nuevo nuestro formulario. Hm, seguimos viendo sólo el error `name`. ¿Dónde está nuestro nuevo error de validación para el precio? En realidad, éste es el comportamiento esperado. La mayoría de las restricciones de validación se ignoran cuando el valor es `null` para evitar tropiezos en los campos opcionales. Pero `NotBlank` es diferente: rechaza directamente `nulls`. Así que, encima de nuestra restricción `GreaterThan`, añade `#[Assert\NotBlank()]` con el mensaje:

> ¡Olvidaste poner el precio!

Puedes apilar tantas restricciones como quieras sobre el mismo campo.

Ahora, enviemos de nuevo el formulario vacío. Finalmente, ¡obtenemos errores para ambos campos!

Y si establecemos el `price` a 0 e intentamos de nuevo, veremos un mensaje de error ligeramente diferente procedente de la restricción de validación `GreaterThan`. ¡Perfecto!

## Ventajas de la validación de formularios Symfony

He aquí un detalle genial: Cuando un formulario no es válido, Symfony devuelve automáticamente un código de estado HTTP `422 Unprocessable Content` al renderizar el formulario no válido. Puedes ver el estado en la pestaña de red de tu navegador o en la barra de herramientas de depuración web. Este comportamiento garantiza la compatibilidad con herramientas que dependen de la especificación HTTP, como Symfony UX Turbo.

Esto funciona porque, en nuestro controlador, estamos pasando el objeto `$form` a la plantilla Twig. Si hubiéramos llamado a `->createView()` en él, como se requería en versiones anteriores de Symfony, el código de estado sería por defecto `200 OK`, lo que no es ideal para formularios no válidos, y rompería la integración con cosas como Turbo.

La barra de herramientas de depuración web también es muy útil para los errores de validación. Verás un icono con el número de errores que contiene tu formulario. Ahora tenemos 2. Si haces clic en él para abrir la pestaña Formulario del perfilador, verás qué clase de tipo de formulario es responsable del formulario. Haz clic en el nombre del campo para obtener información útil que podrías necesitar durante la depuración. 

## Depuración de problemas de validación

Si cambias a la pestaña `Validator`, verás datos similares, pero presentados de forma ligeramente diferente, con más contexto sobre las restricciones de validación, como el nombre de su clase.

Esta pestaña es especialmente útil cuando utilizas el Componente Validador fuera de los formularios, ¡lo cual es totalmente posible! Independientemente de tu configuración, Symfony nos proporciona todo lo que necesitamos para depurar problemas de validación. 

## Comprender la protección CSRF

Ahora, hablemos de otro concepto importante: la protección CSRF: Protección CSRF. CSRF son las siglas de Cross-Site Request Forgery (Falsificación de peticiones en sitios cruzados). Se trata de un tipo de ataque en el que un sitio web malicioso engaña a tu navegador para que envíe una petición que tú no pretendías, por ejemplo, enviar un formulario o hacer clic en un botón de otro sitio sin tu conocimiento.

Para evitarlo, los frameworks web utilizan tokens CSRF, valores aleatorios que prueban que una petición procede realmente de tu aplicación y no de otro sitio. En la protección CSRF tradicional, el servidor genera un token y lo incrusta en cada formulario como un campo oculto, al tiempo que almacena el mismo valor en la sesión del usuario. Cuando se envía el formulario, el servidor comprueba que el token del formulario coincide con el de la sesión. Si falta o no es válido, se rechaza la petición y se muestra un error de validación CSRF. Esto impide que los atacantes falsifiquen las peticiones, porque no pueden saber ni incluir el token CSRF correcto.

Este es el enfoque clásico, con estado, y Symfony lo soporta. 

Pero en las nuevas versiones de Symfony, también existe la protección CSRF sin estado, útil para aplicaciones que no quieren depender de las sesiones. En lugar de almacenar tokens en el servidor, Symfony comprueba cosas como el Origen y el Referer de la petición. Si utilizas Stimulus, puedes reforzar esta protección con cookies y tokens de cabecera que el navegador envía junto con el formulario.

### Instalar la protección CSRF

Dirígete al terminal e instala el componente CSRF:

```terminal
symfony composer require csrf
```

No hay un alias de Flex para esto. Así que, en su lugar, instala el paquete real`symfony/security-csrf`:

```terminal-silent
symfony composer require symfony/security-csrf
```

En cuanto instales el paquete, la protección CSRF se activará por defecto en todos los formularios Symfony, lo que hará que tus formularios sean más seguros desde el primer momento. 

### Comprobando la protección CSRF

Puedes comprobarlo por ti mismo. Ve a la página del formulario, actualízala y abre el inspector HTML de tu navegador. Dentro de nuestro formulario, encontrarás un tipo de entrada oculto conectado a un controlador Stimulus de `csrf-protection`. Este controlador se añade como parte de la receta Flex del StimulusBundle y añade el endurecimiento mencionado anteriormente.

La forma más fácil de verlo en acción, es modificando el valor del campo CSRF en el navegador. Sustitúyelo por cualquier cadena aleatoria y envía el formulario. Obtendrás un error global de validación del formulario.

> El token CSRF no es válido. Por favor, intenta reenviar el formulario.

Global significa que no está vinculado a un campo específico, sino al formulario en su conjunto.

Y así de sencillo, Symfony ha bloqueado una petición no válida y ha devuelto un error, impidiendo que se ejecute cualquier acción de escritura.

Y así es como se hace la validación de formularios. Añades restricciones de validación, bien al formulario, bien al objeto que estás validando como atributos. Después, antes de procesar los datos del formulario, llama a `$form->isValid()` para comprobar que todo está correcto.

A continuación veremos cómo mostrar los campos del formulario en un orden específico, haciendo que los campos importantes aparezcan arriba y los opcionales abajo. ¡Permanece atento!
