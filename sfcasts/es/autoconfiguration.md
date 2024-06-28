# Autoconfiguración

Esta función de localización de la ISS en tiempo real es genial, pero lo sería aún más si pudiéramos verla en todas las páginas, no sólo en la página de inicio. ¿Cómo podemos hacerlo? Podríamos pasar los datos en cada acción, pero eso no es lo ideal. En su lugar, vamos a crear una función Twig personalizada que obtendrá los datos reales en la plantilla. De esta forma, podremos mostrar los datos de localización de la ISS en nuestro archivo `base.html.twig` sin tener que pasarlos desde cada controlador. ¿Te parece bien? ¡Empecemos!

En primer lugar, tenemos que crear una extensión Twig. En un curso anterior, instalamos Symfony Maker Bundle. Veamos si eso puede ayudarnos a generar algo de código boilerplate. En tu terminal, ejecuta

```terminal
bin/console make:
```

y pulsa intro. Aparece un error, pero esto nos muestra una lista de todos los comandos a nuestra disposición que hacen algo, y... ¡fíjate! Tenemos uno relacionado con Twig: `make:twig-extension`. Eso es lo que estamos buscando! Ejecuta eso:

```terminal
bin/console make:twig-extension
```

Nos pide un nombre de clase de extensión Twig. Podemos quedarnos con el nombre por defecto - `AppExtension`. Y... genial. Podemos ver que ha creado dos archivos: `AppExtension.php` y `AppExtensionRuntime.php`. Abramos el primero - `/src/Twig/Extension/AppExtension.php`. 

[[[ code('f5929bd6b1') ]]]

Ya tiene un par de métodos: `getFilters()` y `getFunctions()`. Ahora mismo, sólo nos interesan las funciones, así que podemos deshacernos por completo del método `getFilters()`. Dentro de `getFunctions()`, sustituyamos la demostración `function_name` por algo más relevante. ¿Qué te parece `get_iss_location_data`. Este es el nombre real de la función Twig que vamos a llamar en las plantillas.

[[[ code('b341193be5') ]]]

Por aquí, podemos ver que se llama a un método en `AppExtensionRuntime::class`. Ahora mismo, sólo se llama `doSomething`. Mantén pulsado "comando" (o "control" en un Mac) y haz clic en este método para abrirlo. Aunque estoy seguro de que este método está, de hecho, haciendo algo, vamos a cambiarle el nombre para que sepamos lo que está haciendo. ¿Qué te parece `getIssLocationData()` para que coincida con nuestra función? También podemos eliminar este argumento, ya que no lo necesitamos.

[[[ code('7d3ed60eff') ]]]

De vuelta en `AppExtension.php`, sustituye `doSomething` por el nombre de nuestro nuevo método - `getIssLocationData`.

[[[ code('ead93d0921') ]]]

Como puedes ver, PhpStorm lo autocompleta por mí. Ahora podemos ir a coger el código responsable de esa obtención de datos de la acción `homepage()`. Aquí lo tienes. Cópialo, bórralo, y también podemos limpiar algo de nuestro código mientras estamos aquí. Ya no necesitamos pasar este `issData` a la plantilla, y también podemos deshacernos de estos dos argumentos.

[[[ code('9db8616201') ]]]

¡Mucho mejor! Ahora podemos volver a `AppExtensionRuntime.php` y, aquí abajo, pegar. No necesitamos una variable para estos datos, así que podemos simplemente `return`.

[[[ code('ed9e745775') ]]]

Tenemos algunas variables indefinidas como `$issLocationPool` y `$client`; Ésas son nuestras dependencias. No podemos inyectarlas directamente en el método, como hacemos con nuestros controladores, porque la inyección de métodos sólo funciona para los controladores. Sin embargo, podemos inyectar dependencias en nuestro constructor, e incluso podemos utilizar una práctica función de PHP 8 llamada "Promoción de propiedades del constructor".

¡Compruébalo! Aquí arriba, escribe `private readonly` -escribiremos nuestro primer argumento- `HttpClientInterface`, y llámalo `$client`. Debajo, una vez más, escribe `private readonly`, pero esta vez escribe `CacheInterface` (el de `Symfony\Contracts\Cache`) y llámalo `$issLocationPool`. También podemos deshacernos de este comentario aquí. Genial.

[[[ code('2981cd9d82') ]]]

Por cierto, si necesitáramos inyectar nuestro `issLocationCacheTtl` aquí, podríamos hacerlo de la misma manera, utilizando el atributo PHP `#[Autowire]`. No necesitamos hacerlo para este ejemplo, pero es bueno tenerlo en cuenta.

Aquí abajo, vamos a actualizar este método. Debería ser `$this->issLocationPool`, `$this->client`, y como podemos llamarlo directamente desde la función anónima, ya no necesitamos este `use ($client)`.

[[[ code('b68b114d9b') ]]]

Vale, en el navegador, actualiza para ver... un error.

> La variable "issData" no existe.

Esto se debe a que ya no estamos inyectando esta variable desde nuestro controlador, pero nuestra plantilla sigue haciendo referencia a ella. Abre `/templates/main/homepage.html.twig` y, a continuación, vamos a utilizar nuestra función Twig personalizada. Escribe `{% set issData = get_iss_location_data() %}`. 

[[[ code('42e61a170e') ]]]

Si volvemos a actualizar la página... nuestra función personalizada funciona. Pero espera... ¿cómo sabe Twig que debe utilizar esta clase? No hemos añadido ninguna configuración para la extensión Twig. ¿Busca en el directorio `/src/Twig/`? No exactamente. Podríamos cambiar el nombre de este directorio y seguiría funcionando.

La razón por la que funciona es gracias a la opción `autoconfigure: true` de `/config/services.yaml`. Symfony configura automáticamente todos nuestros servicios, como esta extensión Twig o incluso el `ShipReportCommand` que hemos creado antes. Cuando esa opción está activada, básicamente le dice a Symfony:

> ¡Oye! Por favor, mira la clase base o interfaz
> de cada servicio. Si es un comando, extensión Twig
> o cualquier otra clase que deba engancharse a una parte de
> Symfony, por favor sigue adelante e integra ese
> servicio en el sistema por nosotros.

Sí Symfony ve que nuestra clase extiende una clase de comando base y sabe que es un comando que debe integrarse en el sistema. En el caso de nuestra extensión, extiende `AbstractExtension`, por lo que Symfony sabe que debe integrarse en el sistema Twig. ¡Esto me encanta! Significa que de lo único que tenemos que preocuparnos es de crear una clase PHP que extienda una determinada clase o implemente una interfaz específica. La documentación te ayudará a navegar por esto, y la autoconfiguración hará el resto.

Internamente, la autoconfiguración sólo añade una etiqueta especial para nuestros servicios, como `console.command`, que ayuda al sistema a notarlo. Pero otras veces, funciona mediante un atributo, como el comando. En ambos casos, creamos una clase, extendemos una clase base, implementamos una interfaz, o añadimos un atributo especial, y bam - Symfony entiende lo que estás haciendo y lo integra.

Por cierto, si tienes curiosidad por saber para qué sirve este `AppExtensionRuntime` independiente, ¡ojo! Los tiempos de ejecución de las extensiones siempre han estado en Twig, pero sólo recientemente se han promocionado, sobre todo gracias al maker bundle. Podríamos inyectar los servicios directamente en nuestra extensión Twig y alojar allí toda la lógica, pero esto tiene un inconveniente: como las extensiones Twig se cargan siempre que se utiliza Twig, también se cargan la extensión y todas sus dependencias. Incluso cuando no se utiliza una determinada función o filtro de la extensión. Los tiempos de ejecución de las extensiones Twig son una forma de hacer que la lógica de la extensión sea perezosa. El servicio de tiempo de ejecución sólo se instanciará cuando y si es necesario. En nuestro ejemplo, en realidad no nos ayuda, ya que estamos mostrando los datos de localización de ISS en todas las páginas, pero puedes imaginar una función o filtro que sólo se utilice en unas pocas páginas de tu aplicación. Es una buena práctica mantener tus extensiones Twig tan ligeras como sea posible, sin dependencias o con muy pocas, y dejar todo el trabajo pesado a los tiempos de ejecución de las extensiones.

Muy bien, en `homepage.html.twig`, copia este código HTML, bórralo y abre `base.html.twig`. Aquí abajo, debajo de nuestro logotipo, pégalo. Bien, vamos a simplificar esto un poco para hacerlo más compacto. Crea un nuevo `<div>` encima de éste y, dentro, escribe `ISS Location`. Después, entre paréntesis, escribe `{{ issData.visibility }}`. También daremos a nuestro `<div>` un `title` y lo pondremos en esta línea `Updated At` de abajo. Ahora podemos limpiar nuestro código. No necesitamos este `<h2>`, hemos movido `Updated At` por lo que ya no es necesario aquí, y también podemos deshacernos de `Visibility`. 

[[[ code('0230da75f7') ]]]

¡Mucho mejor! En nuestro navegador, actualizamos y... la información de ISS está en una nueva posición en la cabecera. Y si abrimos una página diferente, como una de nuestras páginas de naves, la información sobre la ubicación de la ISS también está allí.

Y ya está Hemos cubierto los fundamentos de los servicios, la configuración y los entornos de Symfony. ¡Somos poderosos! No, imparables.

En el próximo tutorial, presentaremos Doctrine, la forma estándar del sector de trabajar con bases de datos en PHP. Hasta entonces, practica. Construye algo, lo que sea, y cuéntanoslo. Y si tienes alguna pregunta, idea o simplemente quieres decir "hola", estamos a tu disposición en los comentarios. Muy bien, amigos. ¡Hasta la próxima!
