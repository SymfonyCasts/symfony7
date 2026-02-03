# Adoptar las operaciones CRUD de Entity

¡Hablemos de CRUD! No, no las cosas asquerosas, sino las operaciones de creación, lectura, actualización y eliminación que son la columna vertebral de la mayoría de las secciones de administración. En lugar de perder un tiempo precioso escribiendo controladores, formularios y plantillas manualmente, Symfony tiene un ayudante de confianza que se encarga de todo lo tedioso por nosotros. Es hora de recurrir a nuestro fiable aliado, el MakerBundle.

Ve al terminal y ejecuta:

```terminal
symfony console make:crud
```

Una vez que lo hagas, MakerBundle entrará en acción y te hará algunas preguntas. Para la entidad, vamos a usar `Starship`. Para el nombre del controlador, ya tenemos un `StarshipController`, pero como estamos tratando con cosas de administradores, vamos a llamarlo `StarshipAdminController`. En cuanto a las pruebas unitarias de PHP, sáltatelas por ahora.

Pulsa intro y, ¡guau! Esta vez ha creado un montón de archivos. Un controlador, un tipo de formulario y un montón de plantillas para listar, mostrar, crear y editar la entidad `Starship`. Este es el tipo de boilerplate que preferirías no escribir a mano cada vez.

## Arreglar el problema con los Enums en la página de lista

Ahora, echemos un vistazo al interior del recién estrenado controlador. En PhpStorm, navegaré hasta `StarshipAdminController` en nuestro directorio `src/Controller/`. Lo primero que quiero cambiar es la ruta. `Maker` eligió una ruta razonable, pero me gusta la coherencia entre mis rutas de administración, así que cambiaré la ruta a `/admin/starship`:

[[[ code('75dbe470a2') ]]]

¡Perfecto!

Abre esa URL `/admin/starship` en el navegador. Ah, ¡un error! Dice

> No se ha podido convertir un objeto de la clase `StarshipStatusEnum` en una cadena.

Problema clásico. Arreglémoslo abriendo la plantilla responsable de esta ruta: `starship_admin/index.html.twig`. Actualmente, intenta mostrar directamente el estado de la Nave Estelar:

[[[ code('1496ca0df4') ]]]

Pero no es una cadena:

Si abres la entidad `Starship` - verás que la propiedad de estado es un `StarshipStatusEnum`:

[[[ code('14f9f7bda8') ]]]

Tendremos que acceder explícitamente a su valor.

Aunque MakerBundle ha hecho mucho trabajo por nosotros, parece que aún no entiende del todo los enums de PHP. Pero no temas, lo tenemos. Todo lo que tenemos que hacer es sustituir `starship.status` por `starship.status.value` en la plantilla:

[[[ code('4ebf24ab7b') ]]]

Tras actualizar la página, tenemos una bonita lista de todas las naves estelares de nuestra base de datos, con algunas acciones útiles que podemos realizar sobre ellas, como mostrar y editar.

## Arreglar los Enums en la página Mostrar

Haz clic en el enlace Mostrar y aparecerá el mismo error. Pero ahora que somos avezados solucionadores de problemas, busquemos el controlador y la acción responsables, y apliquemos la misma corrección.

La barra de herramientas de depuración web nos dice que `StarshipAdminController::show()` es el culpable. Encuentra ese método... salta a la plantilla `show.html.twig`, y actualiza el campo a `starship.status.value`:

[[[ code('077f9a42eb') ]]]

Ahora actualiza la página. ¡Genial! Ahora podemos ver los detalles individuales de la nave estelar y, lo que es más importante, editarlos o eliminarlos.

Si hago clic en el botón Eliminar - se activa un diálogo de confirmación de JavaScript. Un detalle pequeño pero significativo para evitar eliminaciones accidentales - una característica que aprecio mucho.

Si lo cancelo y hago clic en Editar, nos encontramos con otro error similar, pero esta vez lo lanza el tema de formulario predeterminado de Symfony:

> Se ha lanzado una excepción durante la representación de la plantilla
> ("El objeto de clase `StarshipStatusEnum` no se ha podido convertir a cadena")
> en `form_div_layout.html.twig.

## Arreglar las páginas del formulario

Busca la acción `edit()` en el controlador y abre la plantilla relacionada:`edit.html.twig`. Ahora bien, este archivo no contiene lo que necesitamos, pero sí `include()` otra plantilla: `_form.html.twig`:

[[[ code('d1ca0a2b56') ]]]

Continúa abriéndola.

Aquí es donde se renderiza el formulario. Si abres `starship_admin/new.html.twig`verás que estamos incluyendo el mismo formulario tanto para acciones nuevas como para acciones de edición. La única diferencia es el `button_label` que pasamos como argumento al `include()`. El propósito de esta plantilla es evitar la duplicación de código.

Volviendo a `_form.html.twig`, no estamos renderizando ese campo de estado manualmente... Todo el formulario se está renderizando con esta llamada a `form_widget(form)`:

[[[ code('ffc33983e1') ]]]

Por suerte, esta corrección se realiza en la clase de tipo de formulario. MakerBundle creó `StarshipType`para nosotros - ábrelo en el directorio `src/Form/`. El campo `status` aquí es el culpable, parece que adivinar el tipo de campo de formulario no funciona para los enums:

[[[ code('e868e4e909') ]]]

No te preocupes, podemos especificar explícitamente el tipo. Pasa `EnumType::class` como 2º argumento y ve a actualizar la página:

[[[ code('cd54525834') ]]]

Otro error:

> Falta la opción requerida `class` para este `EnumType`.

Los mensajes de error de Symfony son bastante útiles, así que puede que ya tengas una idea del problema y de cómo solucionarlo. Pero vamos a confirmarlo. En tu terminal, ejecuta un comando ya conocido:

```terminal
symfony console debug:form EnumType
```

Te mostrará que la opción `class` es necesaria para este tipo de formulario, y debe apuntar a la clase concreta Enum. En nuestro caso, sería`StarshipStatusEnum`.

Añade una matriz vacía como 3er argumento, y dentro, establece la opción `class` en`StarshipStatusEnum::class`:

[[[ code('39c2aaa727') ]]]

Vuelve a actualizar la página... y... ¡genial! El formulario se muestra correctamente, podemos editar los detalles y actualizar la entidad. ¡Todo funciona como se esperaba!

En la página de la lista, desplázate hacia abajo y encontrarás un enlace "Crear nuevo". Al hacer clic en él, aparece el mismo formulario, pero sin datos rellenados de antemano: perfecto para crear una nave estelar nueva. Esta es una de las mejores partes de la generación CRUD de Maker: un formulario que se reutiliza tanto para operaciones de creación como de actualización.

## Mejorar el estilo de las páginas CRUD

Vale, seamos sinceros. El código generado ahora funciona muy bien, pero visualmente no está ganando ningún premio de diseño. Voy a mejorar rápidamente algunos estilos, pero no te preocupes, puedes copiar/pegar el mismo código de los bloques de código que aparecen debajo del vídeo.

Primero, en `_form.html.twig`, pegaré algunas clases CSS de Tailwind en el botón de envío:

[[[ code('f033a494ca') ]]]

Esta plantilla `_delete_form.html.twig` es interesante:

[[[ code('d8c2acfd2e') ]]]

Es un parcial Twig para el botón Eliminar. Nunca querrás que las acciones de eliminar sean simples enlaces, que utilicen el método HTTP GET. En su lugar, deben utilizar el método POST.

La única forma de conseguirlo con HTML puro es utilizar un formulario. Así que MakerBundle nos genera este pequeño formulario que contiene el botón Eliminar. Para mayor protección, también incluye un token CSRF. ¡Bastante elegante!

También pegaré aquí algunas clases CSS de Tailwind al botón Eliminar:

[[[ code('3d55037bf0') ]]]

A continuación, pegaré algo de CSS y HTML para mejorar el diseño de la plantilla de edición:

[[[ code('f02a5cf866') ]]]

Plantilla de índice:

[[[ code('85555a9eeb') ]]]

Nueva plantilla:

[[[ code('496aa0ce4c') ]]]

Y por último la plantilla espectáculo:

[[[ code('4edeadb04c') ]]]

Una vez que hayamos terminado, vuelve al navegador y actualiza la página: ¡mucho mejor! Los nuevos estilos aportan un diseño más limpio y botones más intuitivos, incluido un botón "Crear nuevo" en la parte superior para facilitar el acceso.

Lo importante es que sólo hemos hecho retoques de estilo, la funcionalidad principal sigue estando generada al 100% por el MakerBundle. Te da un muy buen comienzo, pero puedes tomar el control sobre esto si quieres también retocando el `StarshipAdminController` generado. Un buen comienzo podría ser añadir mensajes flash. ¡Pero depende de ti!

## Aplicando globalmente el tema del formulario Symfony

El único detalle que queda - el formulario `Starship` claramente no está utilizando la plantilla de formulario CSS de Tailwind. Podemos aplicarla en `_form.html.twig`, igual que hicimos para el formulario `StarshipPart`. Pero, ¡espera! Prefiero no repetir esto para cada formulario de mi aplicación.

En lugar de eso, apliquemos ese tema globalmente para todos los formularios de nuestra app. ¿Cómo? En el terminal, ejecuta:

```terminal
symfony console config:dump twig
```

Y busca la clave `form_themes` en la salida, en algún lugar al principio ¡Aquí está! Está configurado con el tema de formulario predeterminado de Symfony, pero podemos anularlo en la configuración.

Abre `new.html.twig` para la StarshipPart y comenta la etiqueta del tema del formulario:

[[[ code('fe2cf20ae8') ]]]

Ya no la necesitaremos. A continuación, copia el nombre de la plantilla del tema y ve a`config/packages/twig.yaml`.

Debajo de la clave, añade esa opción `form_themes`, y debajo, añade `-`, pega:`tailwind_2_layout.html.twig`:

[[[ code('0502e23a5d') ]]]

¡Eso es todo! Ahora, todos los formularios utilizan automáticamente el tema CSS de Tailwind. Y sí, esta configuración es una lista, así que puedes añadir más temas aquí. Eso es útil para aplicar parches y personalizaciones al tema predeterminado del formulario. Pero por ahora, mantendré las cosas sencillas.

Vuelve al navegador para asegurarte de que el formulario se ha aplicado tanto en la página nueva como en la de edición, y comprueba que nuestro formulario `StarshipPart` también lo sigue utilizando. Sí, tiene un aspecto estupendo, sin regresión.

Y lo mejor de todo es que, aunque hayamos establecido ese tema globalmente, puedes anularlo aplicando otro tema directamente en la plantilla a un formulario específico, como hicimos al principio.

## Para terminar

En un abrir y cerrar de ojos, tenemos un controlador rico en operaciones CRUD y una base sólida que podemos personalizar a nuestro antojo. MakerBundle se encarga de las cosas aburridas, permitiéndonos centrarnos en cosas increíbles.

***SEEALSO
Si quieres un generador de administración aún más potente para tu aplicación Symfony, con operaciones CRUD ya implementadas y otras funciones geniales, echa un vistazo al [curso EasyAdminBundle](https://symfonycasts.com/screencast/easyadminbundle).
***

A continuación, crearemos un nuevo tipo de formulario que no se asigna a ninguna entidad. Pero por ahora, ¡disfruta de tu CRUD recién generado y ve a añadir más naves estelares a tu flota!
