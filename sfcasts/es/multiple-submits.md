# Varios botones de envío

Muy bien, tenemos nuestro botón "Crear" funcionando a las mil maravillas, pero ¿y si queremos un par de flujos de trabajo diferentes? "Crear y cerrar" para guardar la pieza y volver a la lista, y "Crear y añadir otra" para guardar la pieza pero permanecer en la página del formulario para introducir datos rápidamente.

No te preocupes, Symfony es totalmente capaz de manejar esto. Vamos a sumergirnos en el segundo método para añadir botones de envío a un formulario: utilizando `SubmitType`.

## Añadir un segundo botón de envío dentro del tipo de formulario

Lo primero es lo primero: cambiemos el nombre de nuestro botón actual en la plantilla`new.html.twig` a "Crear y cerrar". Ahora, abre tu clase Tipo de formulario en `src/Form/StarshipPartType.php`. Es hora de añadir un segundo botón debajo de los campos. Añádelo utilizando`->add('createAndAddNew', SubmitType::class)`. Este práctico`SubmitType::class` le dice a Symfony que lo muestre como un `<button type="submit">`. 

Si vas al navegador y actualizas, verás nuestros dos botones. El nuevo no parece un botón, porque hemos restablecido los estilos. Pero técnicamente, en el código, es `<button type="submit">`. Más adelante arreglaremos los estilos.

## Acceso a campos no mapeados en Symfony

Por ahora, en el controlador, ya sabemos que `$form->getData()`nos entrega una entidad mapeada, que en nuestro caso es `StarshipPart`. Esta vez, sin embargo, necesitamos acceder a un campo no mapeado: el botón de envío que acabamos de añadir. Este campo no tiene una propiedad coincidente en la entidad, por eso lo llamamos "no mapeado".

No pasa nada, podemos acceder a los datos brutos del formulario. Justo debajo de `addFlash()`, crea una variable `$createAndAddNewBtn` que sea igual a`$form->get('createAndAddNew')`. Esto debería coincidir con el nombre del botón de tu tipo de formulario. Hagamos primero una prueba rápida. Abajo,`dd($createAndAddNewBtn)`.

De vuelta al navegador, rellenar las notas del formulario es opcional, así que bastará con el nombre y el precio. A continuación, pulsa nuestro botón "Crear y añadir nuevo"... y... ahí está nuestro volcado. Es un Botón de Enviar con algunos campos intrigantes. Míralo más de cerca y descubrirás el mágico `clicked = true`. Lo creas o no, esta pequeña joya es la forma en que podemos saber qué botón se ha pulsado realmente, y podemos aprovecharla para potenciar diferentes lógicas de negocio.

## Aprovechar los clics de los botones para ejecutar la lógica empresarial

Volviendo al código, deshazte del `dd()` y dale una ayudita a PhpStorm. Para solucionar el autocompletado, encima del botón, añade un docblock con`/** @var SubmitButton $createAndAddNewBtn */`. Ahora, dentro de la lógica de envío del formulario, escribe `$createAndAddNewBtn->isClicked()`. Eso es exactamente lo que necesitamos. Envuélvelo en una declaración `if`, y si se ha hecho clic en el botón, devolvamos `$this->redirectToRoute('app_admin_starship_part_new')`.

Ahora, de vuelta en el navegador, actualiza y vuelve a enviar. Hmm, vemos el mensaje flash de éxito dos veces... Eso es porque se añadió durante la petición a `dd()`, y de nuevo, cuando volvimos a enviarla. Pero funcionó: estamos de nuevo en el formulario en blanco, listos para añadir otra parte. ¡Genial!

## Las complejidades del tipo de formulario de Symfony: Adivinar el tipo de campo

Volvamos un momento al tipo de formulario. Estamos utilizando `$builder->add()`para añadir campos a nuestro formulario. A veces especificamos el segundo argumento, pero otras, no. Este segundo es el tipo de campo, y es nulo por defecto. Entonces, ¿por qué no necesitamos especificarlo siempre?

Bueno, Symfony tiene esta ingeniosa función llamada "adivinación del tipo de campo". Cuando el tipo es`null`, Symfony inspeccionará la clase de datos subyacente - en nuestro caso, la entidad `StarshipPart`. Buscará una propiedad que coincida con el nombre del campo. Basándose en el tipo de la propiedad encontrada (adivinado a partir de las sugerencias de tipo y los metadatos), Symfony seleccionará automáticamente el tipo de campo de formulario más apropiado.

Por ejemplo, como `price` es un entero, Symfony elegirá `IntegerType`. Como `name` es una cadena, Symfony optará por `TextType`. Si tuviéramos, digamos, un booleano `isActive`, entonces Symfony seleccionaría `CheckboxType`. Muy guay, ¿eh?

La mayoría de las veces, Symfony adivina exactamente lo que quieres. Pero cuando no puede adivinar correctamente, o cuando quieres algo diferente, siempre puedes anular el tipo adivinado por defecto pasando el tipo explícitamente.

Si cambiamos `price` por `IntegerType::class`, cuando actualicemos el formulario, nada cambiará, porque ese era el tipo adivinado.

## Explorando los tipos de campos de formulario incorporados de Symfony

Pero, ¿qué tipos de campo de formulario incorporados tiene Symfony y dónde podemos encontrarlos? Gran pregunta, ¡y me alegro de que lo preguntes! Los documentos de Symfony siempre estarán ahí para ayudarte. Pero Symfony también viene con una herramienta súper fácil de usar para descubrir todo sobre los tipos de campo incorporados.

En tu terminal, ejecuta:

```terminal
symfony console debug:form
```

Esto vuelca todos los tipos de formulario disponibles, incluyendo tus propias clases Form Type, que podemos ver aquí.

Si quieres inspeccionar un tipo específico, sólo tienes que especificarlo como argumento del comando. Por ejemplo, Ejecuta::

```terminal
symfony console debug:form TextType
```

Y verás todas las opciones que admite `TextType`, las opciones que son obligatorias y qué opciones proceden de los tipos padre a los que extiende. Prueba con otro:

```terminal
symfony console debug:form EntityType
```

Lo utilizamos en nuestro `StarshipPartType` para el campo `ship`. Con éste, verás que la opción `class` es obligatoria. Si miramos nuestro tipo de formulario... el MakerBundle ya lo ha rellenado por nosotros. ¡Qué inteligente! Las opciones se establecen como tercer argumento de `$builder->add()`.

Y por cierto, Symfony no sólo adivina los tipos de campo, también adivina las opciones del tipo de campo. Por ejemplo, si una propiedad de entidad Doctrine es `nullable: true` como para este campo `notes`, entonces Symfony la convertirá automáticamente en opcional. Y al revés para los otros campos, el atributo HTML `required` se añadirá si el campo no es anulable, como para `name` y `price`.

Puedes verlo en el inspector HTML... El campo `notes` no tiene el atributo`required`, pero `name` sí.

Por eso, cuando envías un formulario vacío, vemos errores de validación HTML5 para los campos obligatorios.

Y, por supuesto, puedes anular fácilmente este comportamiento en esa tercera matriz de argumentos `$builder->add()`. Para verlo, añade `required => false` a las opciones de nuestro campo `price`. De vuelta en el navegador, actualiza e inspecciona el campo de precio: ¡el atributo `required` ha desaparecido!

Revierte ese cambio: ¡realmente es necesario!

Profundizaremos en los tipos de campos de formulario más adelante en este curso. Por ahora, vamos a cambiar de marcha hacia algo divertido y elegante: hacer que nuestro formulario tenga un aspecto realmente agradable aplicándole un tema de formulario Symfony incorporado. ¡Eso a continuación!
