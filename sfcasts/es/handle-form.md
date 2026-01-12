# Procesamiento del formulario enviado

Muy bien, hemos construido, creado, renderizado y estilizado nuestro formulario. Lo he dado todo y ahora nuestro formulario está listo para ser enviado. Ahora, como te dirá cualquier desarrollador experimentado, la verdadera diversión comienza cuando empezamos a tratar los datos enviados. Volvamos a nuestro controlador y hagamos que este formulario sea funcional. 

## Actualizar el controlador para gestionar los datos del formulario

Abre `src/Controller/AdminController.php`, y en el método `newStarshipPart()`, justo debajo del objeto formulario, añade `$form->handleRequest()`. 

Para ello, tenemos que pasar el objeto de petición actual a este método. Esto ya te resultará familiar. Inyecta `Request` de la Fundación HTTP como argumento del método `$request` y pásalo a`handleRequest()`. Puede que te estés preguntando de qué va esto de `handleRequest()`. Simplemente coge los datos enviados de la petición, aplica esos datos a tu formulario, y ahora tu formulario contiene los valores enviados por el usuario. 

## Comprobación del envío del formulario

A continuación, queremos saber si el formulario se ha enviado realmente o si sólo hemos cargado la página del formulario. Eso es pan comido: escribe `if ($form->isSubmitted())`. Luego, dentro de ese `if`, podemos recuperar los datos enviados con `$form->getData()`.

Como nuestro tipo de formulario tiene una opción `data_class` establecida en `StarshipPart::class`, los datos que recuperas aquí no son un array PHP directo. En lugar de eso, es una instancia de entidad`StarshipPart` con los campos ya rellenados por nosotros. ¿Eres escéptico? Adelante, compruébalo tú mismo. Asignémoslo a una variable `$part`y a continuación `dd($part)`.

## Probando nuestro formulario

De vuelta en el navegador, rellenaré rápidamente el formulario. Pulsa crear para enviarlo... y voilá, un nuevo y reluciente objeto `StarshipPart` con los datos que enviamos. Fíjate en que no tiene ID porque Doctrine aún no lo ha guardado en la base de datos. Añadiré rápidamente un PHPDoc encima de la variable para que el autocompletado de PhpStorm sea más feliz, y borraré la sentencia `dd()`. 

## Guardar datos con el Gestor de Entidades de Doctrine

Para guardar la nueva parte, necesitamos el `EntityManager` de Doctrine. Inyéctalo con`EntityManagerInterface $entityManager` en la firma del método. Luego, de vuelta en el `if`, añade `$entityManager->persist()`, pasando el objeto `$part`y a continuación `$entityManager->flush()`.

De vuelta al navegador, estableceré el nombre a: "Legacy Hyperdrive". Ponle un precio justo, y no te olvides de una nota importante:

> ¡Ten cuidado con las altas revoluciones!

Ahora, pulsa de nuevo el botón de enviar. ¿Finalmente ha funcionado? Bueno, al menos no hay errores. Dirígete a la página `$part` y busca "hipermotor heredado". Ahí está, tu nueva y reluciente pieza para la nave estelar, lista para la venta. 

## Celebrando el éxito con mensajes Flash

Celebremos este momento como es debido. Voy a añadir un mensaje flash de éxito. Los mensajes flash son mensajes temporales que se almacenan en la sesión y se muestran exactamente una vez. Son perfectos para cosas como

> ¡Tu pieza se ha creado correctamente!

Si echas un vistazo a `templates/base.html.twig`, verás que ya tenemos código que hace un bucle sobre los mensajes flash y los muestra con un bonito estilo dependiendo del tipo: éxito, advertencia, error, predeterminado. De vuelta en nuestro controlador, después de guardar la entidad parte en la base de datos, escribe: `$this->addFlash()`

Primer argumento: el "tipo" de mensaje - ayuda a controlar el estilo. Escribe aquí`success`. Segundo argumento: el contenido del mensaje. Qué te parece`sprintf('The part "%s" was successfully created.', $part->getName())`.

## Evitar la duplicación y redirigir al usuario

Para evitar el envío duplicado de formularios cuando el usuario actualice la página -lo que podría dar lugar a partes no deseadas flotando en el espacio-, terminemos el proceso con una redirección. Esta es una buena práctica clásica para los formularios POST. Vamos a redirigir a `$this->redirectToRoute()`.

Podemos redirigir a cualquier sitio, pero yo devolveré a los usuarios a la lista de piezas por comodidad. El nombre de esta ruta es `app_part_index`. 

## Probar el flujo general

Muy bien, volvamos a crear una nueva pieza. ¿Qué te parece un reactor cuántico como nombre, fijar un precio y, como notas, diré

> No superar el 120% de flujo del núcleo

Vale, vuelve a enviar el formulario y ya está. Nuestro mensaje flash, anunciando que la pieza reactor cuántico se ha creado correctamente. Y si intento actualizar la página, el mensaje desaparece, así que sólo se mostró una vez, y Chrome no me pregunta si quiero volver a enviar el formulario, así que también se redirigió correctamente. ¡Genial!

## Añadir un segundo botón de envío

Ahora mismo sólo tenemos un botón de envío: Crear. Pero imagina esto, si te sientes productivo, cafeinado, en racha, y quieres crear varias partes rápidamente, una tras otra, podrías hacerlo con unos pocos clics extra cada vez, haciendo clic en el enlace para volver al formulario. Pero, ¿no sería mucho más rápido tener un segundo botón de envío que, en lugar de crear y volver a la lista, cree y permanezca en esta página con un formulario vacío abierto, para que puedas crear inmediatamente otro parte?

Bueno, puede que no sea mucho más rápido, pero aún así podría ahorrarle a alguien unas cuantas horas de su vida a lo largo de muchos años de añadir esas piezas. Te estarás preguntando, ¿es eso siquiera posible? Por supuesto que sí Y en el próximo capítulo veremos cómo.
