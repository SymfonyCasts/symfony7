# Creación de una Clase de Tipo de Formulario

## Introducción: El poder de los formularios

Los formularios están por todas partes. Casillas de inicio de sesión, campos de búsqueda, herramientas de administración, pasos de pago... si tu aplicación permite que los humanos escriban cosas, felicidades, estás en la Tierra de los Formularios. Y aunque los front-ends de hoy en día pueden estar repletos de Ajax, Turbo y bibliotecas JavaScript de lujo, el trabajo principal sigue siendo el mismo clásico: coger algunos datos, comprobarlos y hacer algo significativo con ellos. Y ahí es exactamente donde el componente Formulario de Symfony se pone la capa. Trata los datos de la petición, aplica la validación, te protege de los villanos del CSRF y, en general, evita que tengas que crear manualmente entradas HTML como si fuera 1995.

Así que vamos a ensuciarnos las manos y a crear nuestro primer formulario

## Cómo empezar con el código del curso

Para seguir adelante, descarga el código del curso de esta página y extráelo. Dentro, encontrarás un directorio `start/` que contiene el proyecto del curso. Abre su archivo `README.md`, y encontrarás instrucciones para poner en marcha el sitio web. Yo ya he hecho esta parte, así que me limitaré a lanzar el servidor web Symfony incorporado en mi terminal con:

```terminal
symfony serve -d
```

A continuación, abre el sitio manteniendo pulsado `Cmd` y haciendo clic en la URL de salida. Bienvenido a Starshop, tu tienda integral para todas las piezas esenciales de tu nave estelar: acopladores de hipervelocidad, bobinas factoriales, estabilizadores cuánticos zurdos, ya sabes, lo de siempre. Vamos a la página de Piezas.

El negocio va viento en popa en nuestro pequeño rincón de la Galaxia, así que ahora tenemos que crear nuevas piezas para naves estelares en el área de administración. Ya he añadido un enlace a "Crear una nueva pieza". El único problema es que el formulario en sí todavía está en construcción. ¡Vamos a arreglarlo!

## Instalar el componente de formulario Symfony

Antes de que podamos crear formularios, necesitamos el componente Symfony Form. Instálalo utilizando:

```terminal
symfony composer require form
```

Ejecutando `git status` nos mostrará que la instalación trajo algunos archivos de configuración nuevos. Perfecto, ahora estamos listos para empezar a construir. Podríamos construir el formulario manualmente, campo a campo, línea a línea, pero esa es una forma segura de agotar nuestra fuerza vital.

## Crear una clase de tipo formulario con Maker

En lugar de eso, dejemos que Maker bundle nos ayude. Ejecuta:

```terminal
symfony console make:form
```

Ejecuta: si aún no has instalado el componente Formulario, Maker te dirá exactamente qué comando tienes que ejecutar. Maker es como ese amable compañero de trabajo que te recuerda amablemente que has olvidado ejecutar`composer install`... otra vez.

Puedes dar al formulario el nombre que quieras, pero es práctica común terminar las clases de formulario con "Tipo". Por ejemplo, como vamos a crear piezas para naves estelares, a ésta la llamaré `StarshipPartType`. A continuación, maker nos preguntará si queremos asignar el formulario a una entidad. En este caso, desde luego que sí, así que elige la entidad`StarshipPart`. Esa es la entidad que crearemos con este formulario. Una vez hecho esto, maker creará un nuevo archivo en el directorio `src/Form/`. Ahora, ábrelo en PhpStorm:

[[[ code('9349fb25c2') ]]]

La clase extiende `AbstractType`, que proviene del componente Formulario que acabamos de instalar. Maker también ha añadido convenientemente un campo de formulario para cada propiedad de la entidad. Sin embargo, en realidad no queremos que los usuarios editen marcas de tiempo (a menos que busquemos paradojas de viajes en el tiempo), así que vamos a deshacernos de esos campos:

[[[ code('213f049372') ]]]

Abajo, en `configureOptions()`, verás la opción `data_class` establecida en `StarshipPart::class`:

[[[ code('cd5c5be5d6') ]]]

Eso vincula este formulario a esa entidad. ¡Muy práctico!

## Crear el objeto formulario con `createForm()`

Ahora es el momento de crear un objeto formulario utilizando nuestro nuevo tipo de formulario. El controlador de esta página es `AdminController` y la acción es `newStarshipPart()`. Vamos a abrirlo. Dentro, crearemos un objeto formulario con`$form = $this->createForm`... 

Symfony proporciona amablemente dos métodos útiles, `createForm()` y`createFormBuilder()`. Este último te permite crear un formulario directamente dentro del controlador, perfecto para prototipos rápidos. Sin embargo, la mejor práctica es crear una clase de tipo formulario dedicada, como la que acabamos de crear.

Así que nos quedaremos con `$this->createForm()`. Pasa nuestro `StarshipPartType::class`, y voilá, ¡tenemos un formulario! Si `dd($form)` a continuación:

[[[ code('9e40f30d2e') ]]]

Y refrescamos la página, verás que es un objeto Formulario: un objeto PHP en toda regla con toda la lógica. Pasémoslo a la plantilla con`'form' => $form` y eliminemos la declaración `dd($form)`:

[[[ code('9a62aa4f34') ]]]

Ahora podemos renderizarlo en la plantilla. 

## `Form` Objeto en PHP frente a `FormView` uno en Twig

¡Pero hay un giro! Si primero lo vuelcas en Twig con `{{ dump(form) }}`:

[[[ code('d9981327eb') ]]]

Y vuelves a actualizar la página, verás que en su lugar es un objeto `FormView`. Eso es que Symfony te está haciendo tranquilamente un favor convirtiendo el objeto interno `Form` en un modelo de vista más sencillo que puede renderizarse en Twig. Pueden parecer similares, pero recuerda que técnicamente son objetos diferentes.

Las versiones antiguas de Symfony requerían que los desarrolladores pasaran explícitamente el objeto `FormView` a la plantilla para su renderización. Por eso es posible que veas llamadas a `$form->createView()` en proyectos antiguos.

Pero con la última versión de Symfony, esto se gestiona automáticamente, por lo que no tienes que preocuparte por ello. De hecho, para que funcione correctamente con Turbo, ahora debes pasar siempre el objeto interno del formulario. 

Bien, a continuación, renderizaremos y enviaremos el formulario de verdad. ¿Estás listo para sumergirte?
