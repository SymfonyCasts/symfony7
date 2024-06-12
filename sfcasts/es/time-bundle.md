# KnpTimeBundle: Instala el bundle, obtén su servicio

En nuestro sitio, los clientes disponen de una práctica "Cola de Reparación de Barcos" que enumera todos los barcos que están siendo reparados y su estado. Para este tutorial, hemos añadido un nuevo campo `$arrivedAt`a nuestra clase `Starship` con algunos getters y setters. Queremos imprimir este campo en la página de inicio.

[[[ code('257f2fdabf') ]]]

Si has olvidado qué controlador es responsable de la página de inicio, siempre puedes pasar el ratón por encima de la información de la página en la barra de herramientas de depuración web y... ¡boom! Dice "MainController :: homepage". Abramos eso - `MainController.php` - y busquemos la acción `homepage()`. Aquí abajo, podemos ver que genera una plantilla: `main/homepage.html.twig`. Ábrela, busca la "Ship Repair Queue" y, aquí abajo, después de `{{ ship.name }}`, añade una nueva `<div>` con `Arrived at: {{ ship.arrivedAt }}`.

[[[ code('e242080815') ]]]

Ahora, si volvemos al navegador y actualizamos la página de inicio... ah... un error.

> Se ha lanzado una excepción durante la renderización
> de una plantilla ("Objeto de clase `DateTimeImmutable`
> no se ha podido convertir en cadena").

Eso tiene sentido. PHP no puede imprimir simplemente objetos `DateTime` porque no sabe qué formato queremos. ¿Cómo lo arreglamos? Muy fácil Podemos utilizar un filtro Twig. Aquí detrás, después de `arrivedAt`, digamos `|date`. Si refrescamos de nuevo... ¡genial! Aquí tenemos la fecha y la hora en un formato específico.

[[[ code('7ade76f3a9') ]]]

Podemos pasar un formato `DateTime` opcional como primer argumento a este filtro `|date`, pero si lo omitimos, se utilizará el formato por defecto de la aplicación. ¿Cuál exactamente? ¡Buena pregunta! Comprobemos la configuración. En tu terminal, ejecuta:

```terminal
bin/console config:dump twig
```

Aquí puedes ver la configuración del formato de fecha de tu aplicación. En realidad, he hecho un poco de trampa al ejecutar este comando. El nombre completo del comando es `config:dump-reference`. Con los comandos Symfony, puedes acortar el nombre todo lo que quieras siempre que no sea ambiguo con el nombre de otro comando. Si coinciden varios comandos, la consola te preguntará cuál quieres ejecutar.

Bien, volvamos al navegador. Hemos impreso nuestra fecha, pero sería mucho más guay si pudiéramos decir algo como "hace 2 horas" en lugar de esta fecha tan larga. Por desgracia, aún no tenemos un servicio en nuestra aplicación que pueda hacer eso por nosotros. Y desde luego no quiero escribirlo yo. Tengo cosas más divertidas que hacer, como jugar a juegos de mesa. Pero... ¿Existe un bundle con un servicio que pueda hacerlo? Sí Se llama "KnpTimeBundle". Busquémoslo en GitHub. Aquí lo tienes Desplázate hasta la sección "Instalación" y copia este comando. 
En tu terminal, pega ese comando y ejecútalo:

```terminal
composer require knplabs/knp-time-bundle
```

Esto instala el bundle, las dependencias necesarias y también ejecuta algunas recetas. Si lo ejecutamos:

```terminal
git status
```

¡Compruébalo! Cada vez que instalamos un nuevo bundle, cambia nuestros archivos `composer.json`,`composer.lock`, `symfony.lock`, y `bundles.php`. Abrámoslo.

[[[ code('e796395e81') ]]]

Aquí abajo, podemos ver que se ha añadido `KnpTimeBundle` a esta matriz. Ahí es donde Symfony activa este bundle en nuestra aplicación. Recuerda, los bundles nos dan servicios, y éste no es una excepción. Pero... ¿qué servicios nos da? 
Podríamos leer la documentación para saber más sobre esto, pero yo voy a ser perezoso y voy a ejecutar:

```terminal
bin/console debug:container time
```

Seleccionaré `datetime_formatter`, que es la opción `10`, para obtener más información. ¡Genial!

Para ver si podemos autocablearlo, ejecutemos otro comando:

```terminal
bin/console debug:autowiring time
```

Y... ¡podemos! Si queremos utilizar el formato `ago` para nuestro objeto de fecha, éste es el typehint que tenemos que utilizar para inyectar este servicio en nuestras clases PHP. Pero, como sólo queremos esto en nuestra plantilla Twig, hay una solución mejor. Este bundle también viene con una integración Twig que proporciona algunos bonitos filtros y funciones Twig. 
Podemos verlo si ejecutamos

```terminal
bin/console debug:twig
```

y buscamos `ago`. ¡Aquí está! Si este `date` te resulta familiar, es porque lo es. 
Es el que hemos utilizado antes. Probemos esta vez con el filtro `ago`.

[[[ code('03a06fb3cd') ]]]

Vuelve aquí, sustituye `date` por `ago`... guarda, y abre el navegador. Actualiza la página de inicio y... ¡ahí está! Ahora tenemos este bonito formato "hace". Así, los bundles nos dan servicios, los servicios son herramientas y las herramientas son divertidas.

A continuación, vamos a añadir aún más servicios instalando nuevos componentes Symfony.
