# Más sobre los servicios

Ya sabemos que los servicios proceden de bundles. Y cada servicio es una combinación de un ID, una clase y un conjunto de argumentos necesarios para instanciarlo. Pero, ¿sabías que también podemos crear nuestros propios servicios para organizar nuestro código y mantenerlo mejor? ¡Sí! Lo creas o no, ya creamos uno en el episodio anterior.

Abre `StarshipRepository.php`. Lo creamos sin configuración y aún podemos utilizarlo en `StarshipApiController.php`. ¿Pero cómo podemos hacerlo? Esto funciona gracias a `config/services.yaml`. Vamos a abrirlo. Aquí abajo, debajo de nuestra clave `services`, vemos esta sección `App\`. Este código registra todo lo que hay en nuestro directorio `src/` como servicio. Pero también excluye algunas cosas, como `DependencyInjection`, `Entity`, y `Kernel.php`.

[[[ code('774ce78c59') ]]]

Este archivo `services.yaml`, incluida esta configuración, viene con el núcleo `symfony/framework-bundle`.

Aquí arriba, tenemos esta clave `_defaults`. 

[[[ code('46b9d47a6d') ]]]

Es la configuración de todos los servicios de este archivo. 
Esta clave `autowire`, establecida en `true`, inyecta automáticamente dependencias en nuestros servicios. 
También tenemos esta clave `autoconfigure`, establecida en `true`, que registra automáticamente nuestros servicios como comandos, suscriptores de eventos, etc. ¡Muy chulo! Hablaremos más sobre `autoconfigure` más adelante.

Para ver una lista de servicios, en tu terminal, ejecuta:

```terminal
bin/console debug:autowiring
```

Pero esta vez, añadamos la opción `--all` al final:

```terminal-silent
bin/console debug:autowiring --all
```

Esto nos mostrará todos nuestros servicios, incluso los que no tienen alias. Técnicamente, los que no son servicios, como nuestra clase `Model`, también se registran como servicios, pero se eliminan más tarde porque no los vamos a utilizar en nuestro código. La cuestión es que, para crear un servicio, todo lo que tenemos que hacer es crear una clase en algún lugar de nuestro directorio `src/` y el autocableado se activa automáticamente para él.

Por cierto, todos estos archivos `.yaml` son idénticos. La clave raíz, como `services` o `framework`, es lo que los hace diferentes. Esto significa que podrías copiar toda la configuración de cada archivo en un único archivo `.yaml` y funcionaría igual. Nosotros los mantenemos separados por razones de mantenimiento y cordura.

Siguiente: Me has oído decir una y otra vez que el contenedor contiene servicios, y es cierto. Pero también contiene otra cosa: una configuración sencilla llamada parámetros.
