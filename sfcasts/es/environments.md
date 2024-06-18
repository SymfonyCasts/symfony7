# Entornos Symfony

A veces, nos vendría muy bien un conjunto de configuraciones que nos ayuden a desarrollar en diferentes escenarios. Por suerte, Symfony tiene justo lo que necesitamos: entornos.

En el archivo `.env` (situado en la raíz del directorio de nuestro proyecto), tenemos algunas variables de entorno.

[[[ code('b4056f5cf3') ]]]

Son conjuntos de configuraciones para nuestra aplicación que podemos cambiar en función del escenario -o entorno- en el que estemos desarrollando. Symfony lee este archivo para ver qué variables estamos utilizando y crea ese entorno.

Por el momento, sólo tenemos unas pocas variables de entorno aquí, como esta variable `APP_ENV` establecida en `dev`. Esto le dice a Symfony que nuestra aplicación debe cargarse en modo desarrollo. Después de desplegar nuestra aplicación en producción, querremos cambiar esto a `prod`, que está optimizado para el rendimiento y evita la fuga de datos sensibles. ¿Dónde se utiliza exactamente?

Abre `/public/index.php`. Este es nuestro controlador frontal, que se ejecuta en cada petición y arranca nuestra aplicación. 

[[[ code('928e8cff8a') ]]]

Crea una instancia de `App\Kernel`, y ésta tiene algunos métodos. Mantén pulsado "comando" y haz clic en `Kernel()` para abrirlo. Esta clase está bastante vacía, aparte de esta línea `use MicroKernelTrait;`, y de ese trait procede la mayor parte del código.

[[[ code('e41007c7b3') ]]]

Si abrimos esto... ¡ah! ¡Ya está! Contiene un montón de métodos como, por ejemplo, `configureContainer()`, que importa nuestros archivos de configuración. Dentro de él, abajo, tenemos `$this->environment` que, si escarbas un poco, es el valor de nuestra variable `APP_ENV`. Así que si queremos añadir una configuración específica del entorno, podemos poner esto en `config/packages/`, seguido de tu entorno, como `dev` o `prod`, y luego el nombre del archivo de configuración - `framework.yaml` por ejemplo.

Eso funcionará, pero recientemente, Symfony ha introducido una forma mucho más genial de hacer esto, utilizando la sintaxis `when@`. Puedes ver esto en toda la nueva configuración. Si abrimos `framework.yaml` por ejemplo, aquí abajo al final... ¡aquí está - `when@test`! Este código sólo se cargará para el entorno `test`.

[[[ code('843d8d2006') ]]]

En nuestro archivo `monolog.yaml`, vemos más de esta configuración específica del entorno bajo `when@dev`. Esto le dice a Symfony que sólo cargue esta configuración en el entorno dev. Si nos desplazamos hacia abajo, podemos ver también configuraciones ligeramente diferentes para los entornos `test` y `prod`.

[[[ code('fb9db28629') ]]]

Si volvemos a `MicroKernelTrait`, aquí abajo, podemos ver lo mismo para este método `configureRoutes()`. Y en `config/routes/framework.yaml`, vemos `when@dev`, lo que significa que sólo estamos importando este conjunto de rutas para el entorno `dev`. En `web_profiler.yaml`, tenemos lo mismo. Así que Symfony, por defecto, tiene tres entornos (o "modos") que podemos utilizar en nuestra app: `dev` `prod` y `test`. También puedes crear tu propio entorno personalizado si es necesario, pero normalmente, esos tres son más que suficientes para hacer el trabajo.

Bien, abramos un archivo con el que ya estamos familiarizados: `config/bundles.php`.

[[[ code('c062bf4860') ]]]

Tiene una matriz de bundles habilitados en nuestra aplicación, donde la clave es la clase de bundle y el valor es una matriz de entornos disponibles para ese bundle. Por ejemplo, este `WebProfilerBundle` sólo está disponible para los entornos `dev`y `test`. Mientras tanto, `DebugBundle` y `MakerBundle` sólo están disponibles para el entorno `dev`. Son súper útiles mientras desarrollamos, pero definitivamente no queremos utilizarlos en producción y arriesgarnos a filtrar información sensible.

A continuación: Cambiemos e intentemos cargar nuestra aplicación utilizando el entorno `prod`.
