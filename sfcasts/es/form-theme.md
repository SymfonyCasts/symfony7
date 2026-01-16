# Temas de formularios Symfony incorporados

Bien... por fin ha llegado el momento. El momento tan esperado en el que... añadimos estilo a nuestro formulario. ¡Sí! De aquí en adelante, sólo serán 17 capítulos en los que ajustaremos cuidadosamente el relleno, los márgenes y elaboraremos CSS artesanalmente. Puedes ir a tomar un café... Yo seguiré aquí masajeando los valores de border-radius.

¡Ja! ¡Es broma! Te prometo que no te haré eso. Gracias a Symfony, tenemos temas incorporados para varios frameworks CSS, lo que significa que podemos evitar la mayor parte del trabajo CSS y saltar directamente a la parte divertida.

## Temas de formulario incorporados de Symfony

Haz una búsqueda rápida en Google de "temas de formulario Symfony". Ahí lo tienes! Symfony nos proporciona una plétora de temas: diseños de tabla clásicos, temas bootstrap (tanto predeterminados como horizontales) para varias versiones, temas foundation... es como un catálogo de moda completo. ¿Y la guinda del pastel? Si utilizas Tailwind CSS, ¡estás de suerte! Symfony también tiene un tema de formulario Tailwind CSS.

## Aplicación de temas en Symfony

Entonces, ¿cómo utilizamos un tema de formulario específico? ¡Twig al rescate! Hay una etiqueta Twig especial para los temas de formulario. Primero, abre PhpStorm y localiza la plantilla `new.html.twig` con nuestro formulario. En la parte superior, justo debajo de la etiqueta `extends`, añade una nueva etiqueta con:`{% form_theme form 'tailwind_2_layout.html.twig' %}`:

[[[ code('b4286e639d') ]]]

Recuerda pasar la variable específica del formulario - `form` en este caso - para que el tema sepa qué formulario aplicar.

Pulsa actualizar en tu navegador... ¡y voilá! Nuestro formulario tiene un aspecto mucho más refinado, con un espaciado y una alineación ordenados.

## Un tipo de formulario `EntityType` 

Hay algo que todavía me molesta... Abre`src/Form/StarshipPartType.php`... El campo `starship` es un `EntityType`:

[[[ code('64a5fb7b34') ]]]

Si recuerdas el capítulo anterior, este campo sólo requiere la opción `class`. Es la relación que conecta nuestro `StarshipPart`con un `Starship`. Muestra todos los `Starship`'s en un elemento de selección.

Pero fíjate en este `choice_label` - establecido en `id`. Esto establece la propiedad de `Starship` que se mostrará en las opciones del desplegable. MakerBundle la establece en `id` por defecto.

¿Cómo diablos sabrán nuestros usuarios qué nave estelar elegir basándose en un ID?

Dentro de la entidad `Starship`, tenemos una propiedad `name`:

[[[ code('fa6f8bd5f9') ]]]

¡Usémosla! Sustituye `id` por `name`:

[[[ code('42cb082447') ]]]

Pulsa actualizar en tu navegador y... ¡Boom! ¡Mucho mejor! Sin embargo, la lista es bastante larga y parece bastante aleatoria. ¿Y si pudiéramos ordenar estos`Starship`s por su nombre? Suena bien, ¿verdad? ¡Hagámoslo!

## Utilizar una consulta personalizada a la base de datos para `EntityType`

Dentro de la configuración del campo, añade una nueva opción llamada`query_builder`. Ponla en una función anónima que acepte`EntityRepository $repo`. Dentro de esta función,`return $repo->createQueryBuilder('starship')`. A continuación, `->orderBy()`, primer argumento: `starship.name`, segundo: `ASC` para el orden ascendente.

Totalmente innecesario, pero vamos a ponernos un poco elegantes y utilizar un enum `Order` para el segundo argumento. Así que en lugar de `ASC`, escribe `Order`, importando el enum de `Doctrine\Common\Collections`, y luego `::Ascending->value`:

[[[ code('27d5823592') ]]]

Super friki... Pero al menos sabemos que no hemos cometido un error tipográfico en esas, ummm... 3 letras...

Como tenemos acceso al constructor de consultas completo, podemos añadir fácilmente filtros personalizados, JOINs o incluso cláusulas WHERE complejas para filtrar los resultados a un sistema solar o galaxia concretos.

Veamos si ha funcionado

Vuelve al navegador, actualiza la página... ¡Genial! Ahora las naves estelares están ordenadas alfabéticamente por nombre. Sólo hay un pequeño problema: hay nombres duplicados. Es posible que nuestras `Starship`procedan de galaxias distintas, por lo que podrían tener el mismo nombre. Para aclarar las cosas, mejoremos la etiqueta.

## Utilizar una devolución de llamada personalizada para la etiqueta de elección

Podríamos mostrar el ID de la entidad junto al nombre, pero tengo una idea mejor: mostremos el nombre del capitán junto al nombre de la nave.

Podríamos crear un método como `getNameWithCaptain()` en la entidad `Starship`, que devolviera el nombre del `Starship` y el nombre de su capitán. Sería una buena solución... sobre todo si necesitas este valor en otra parte de tu aplicación.

Sin embargo, vamos a simplificarlo por ahora. Para la opción `choice_label`de `starship`, en lugar de `name`, pon una función anónima que acepte`Starship $starship`. Dentro, `return sprintf('%s (by %s)')`, pasando`$starship->getName()` y `$starship->getCaptain()` como valores del marcador de posición:

[[[ code('609c45d270') ]]]

Pulsa actualizar... ¡y ya estamos cocinando! Es mucho más fácil identificar cada barco.

## Establecer atributos de campo de formulario en el tipo de formulario

¡Vamos a ocuparnos del botón feo de la habitación! El botón de envío "Crear y añadir nuevo" tiene un aspecto asqueroso.

Hay un par de formas de añadir clases CSS para darle un estilo agradable. La más sencilla es utilizar la opción `attr` en la configuración del campo del formulario.

De vuelta a nuestro tipo de formulario, añade una matriz de opciones al campo `createAndAddNew` con`'attr' => []`. Se trata de una matriz de atributos HTML que queremos añadir al botón. Dentro, añade `class => ''`, vuelve a nuestra plantilla `new.html.twig`, copia las clases que utilizamos para el primer botón y pégalas aquí. Sustituye `bg-green-700`por `bg-blue-700` para darle una distinción visual:

[[[ code('18fd8d6154') ]]]

De vuelta al navegador, pulsa actualizar y ¡ya lo tenemos! Nuestro botón tiene un aspecto fantástico y está listo para la acción. Se puede añadir cualquier atributo HTML de esta manera. Por ejemplo, puedes poner `id`, `placeholder`, ¡o incluso un atributo `data` para hacer que los campos sean interactivos con Stimulus! También hay una opción hermana llamada `label_attr` si necesitas dar estilo a la etiqueta `<label>` del campo

Hemos hecho bastantes ajustes y ahora nuestro formulario tiene un aspecto impecable y se comporta de maravilla. Lo siguiente es algo realmente emocionante y superimportante: la validación. ¡Permanece atento!
