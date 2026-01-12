# Procesar el formulario enviado

Muy bien, hemos construido, creado, renderizado y estilizado nuestro formulario. Lo he dado todo y ahora nuestro formulario está listo para ser enviado. Ahora, como te dirá cualquier desarrollador experimentado, la verdadera diversión comienza cuando empezamos a tratar los datos enviados. Volvamos a nuestro controlador y hagamos que este formulario sea funcional. 

## Actualizar el controlador para gestionar los datos del formulario

Abre `src/Controller/AdminController.php` y, en el método `newStarshipPart()`, justo debajo del objeto `$form`, añade `$form->handleRequest()`. Para ello, tenemos que pasar el objeto de petición actual a este método. Esto ya te resultará familiar. Inyecta `Request` desde`HttpFoundation` como argumento del método `$request` y pásalo a`handleRequest()`:

[[[ code('72c538d3de') ]]]

Puede que te estés preguntando de qué va este `handleRequest()`. Simplemente coge los datos enviados de la petición, aplica esos datos a tu formulario, y ahora tu formulario contiene los valores enviados por el usuario. 

## Comprobación del envío del formulario

A continuación, queremos saber si el formulario se ha enviado realmente o si sólo hemos cargado la página del formulario. Eso es muy fácil: escribe`if ($form->isSubmitted())`. Luego, dentro de ese `if`, podemos recuperar los datos enviados con `$form->getData()`:

[[[ code('38f5a44453') ]]]

Como nuestro tipo de formulario tiene una opción `data_class` establecida en `StarshipPart::class`:

[[[ code('0c0b7b2ab4') ]]]

los datos que recuperas aquí no son un simple array PHP. En su lugar, es una instancia de entidad `StarshipPart` con los campos ya rellenados por nosotros. ¿Eres escéptico? Adelante, compruébalo tú mismo. Asignémoslo a una variable `$part` y a continuación `dd($part)`:

[[[ code('2f8c29a1de') ]]]

## Probando nuestro formulario

De vuelta en el navegador, rellenaré rápidamente el formulario. Pulsa Crear para enviarlo... y voilá, un nuevo y reluciente objeto `StarshipPart` con los datos que enviamos. Fíjate en que no tiene ID porque Doctrine aún no lo ha guardado en la base de datos. Añadiré rápidamente un PHPDoc encima de la variable para que el autocompletado de PhpStorm sea más feliz, y borraré la declaración `dd()`:

[[[ code('a67056bf9e') ]]]

## Guardar datos con el Gestor de Entidades de Doctrine

Para guardar la nueva parte, necesitamos el `EntityManager` de Doctrine. Inyéctalo con`EntityManagerInterface $entityManager` en la firma del método. Luego, de nuevo en el `if`, añade `$entityManager->persist()`, pasando el objeto `$part`y a continuación `$entityManager->flush()`:

[[[ code('6f4bd866c9') ]]]

De vuelta al navegador, estableceré el nombre a: "Legacy Hyperdrive". Ponle un precio justo, y no te olvides de una nota importante:

> ¡Ten cuidado con las altas revoluciones!

Ahora, pulsa de nuevo el botón de enviar. ¿Finalmente ha funcionado? Bueno, al menos no hay errores. Dirígete a la página `$part` y busca "Hipermotor Legado". Ahí está, tu nueva y reluciente pieza para la nave estelar, lista para la venta. 

## Celebrando el éxito con mensajes flash

Celebremos este momento como es debido. Voy a añadir un mensaje flash de éxito. Los mensajes flash son mensajes temporales que se almacenan en la sesión y se muestran exactamente una vez. Son perfectos para cosas como

> ¡Tu pieza se ha creado correctamente!

Si echas un vistazo a `templates/base.html.twig`, verás que ya tenemos código que hace un bucle sobre los mensajes flash y los muestra con un bonito estilo dependiendo del tipo: éxito, advertencia, error, predeterminado:

[[[ code('c0476b41c6') ]]]

De vuelta en nuestro controlador, después de guardar la entidad de la pieza en la base de datos, escribe: `$this->addFlash()`. Primer argumento: el "tipo" de mensaje - ayuda a controlar el estilo. Escribe aquí `success`. Segundo argumento: el contenido del mensaje. ¿Qué te parece:`sprintf('The part "%s" was successfully created!', $part->getName())`:

[[[ code('7ac72f6090') ]]]

## Evitar la duplicación y redirigir al usuario

Para evitar que se dupliquen los envíos del formulario cuando el usuario actualice la página -lo que podría dar lugar a partes no deseadas flotando en el espacio-, terminemos el proceso con una redirección. Esta es una buena práctica clásica para los formularios POST. Vamos a redirigir a `$this->redirectToRoute()`.

Podemos redirigir a cualquier sitio, pero yo devolveré a los usuarios a la lista de piezas por comodidad. El nombre de esta ruta es `app_part_index`.:

[[[ code('7491c3e1b1') ]]]

## Probar el flujo general

Muy bien, creemos de nuevo una nueva pieza. ¿Qué tal un "Reactor Cuántico" como nombre, fijamos un precio y, como notas, diré

> No superar el 120% de flujo del núcleo

Vale, vuelve a enviar el formulario, y ya está. Nuestro mensaje flash, anunciando que:

> ¡La pieza "Reactor cuántico" se ha creado correctamente!

Y si intento actualizar la página, el mensaje desaparece, así que sólo se mostró una vez, y Chrome no me pregunta si quiero volver a enviar el formulario, así que también se redirigió correctamente. ¡Genial!

## Añadir un segundo botón de envío

Ahora mismo sólo tenemos un botón de envío: Crear. Pero imagina esto, si te sientes productivo, cafeinado, en racha, y quieres crear varias partes rápidamente, una tras otra, podrías hacerlo con unos pocos clics extra cada vez, haciendo clic en el enlace para volver al formulario. Pero, ¿no sería mucho más rápido tener un segundo botón de envío que, en lugar de crear y volver a la lista, creara y permaneciera en esta página con un formulario vacío abierto, para que pudieras crear inmediatamente otro parte?

Bueno, puede que no sea mucho más rápido, pero aún así podría ahorrarle a alguien unas cuantas horas de su vida a lo largo de muchos años de añadir esas piezas.

Tal vez te preguntes si eso es posible Por supuesto que sí Y en el próximo capítulo veremos cómo.
