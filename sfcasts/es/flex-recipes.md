# Recetas Flex Mágicas

Tengo un secreto. Cuando se creó nuestro proyecto, no eran 15 archivos. Era... un solo archivo. Si miraras dentro del código del comando `symfony new`, descubrirías que es un atajo para sólo dos cosas. Primero, clona un repositorio llamado `symfony/skeleton`... que es sólo un archivo si ignoras la licencia. Y en segundo lugar, ejecuta `composer install`.

Y ya está Pero espera, si ese es el caso, ¿de dónde han salido todos esos otros archivos? ¿Como las cosas de `bin/`, `config/` y `src/`? La respuesta empieza con un paquete especial dentro de nuestro archivo `composer.json` llamado`symfony/flex`. Flex es un complemento de Composer que añade dos superpoderes a Composer: alias y recetas.

[[[ code('2a52ebb1a0') ]]]

## Alias Flex

Los alias son sencillos. Para añadir un nuevo paquete a tu aplicación -lo que haremos en un minuto- ejecutas `composer require` y luego el nombre del paquete, como `symfony/http-client`. Flex da a los paquetes más importantes del ecosistema Symfony un nombre más corto, llamado alias. Por ejemplo, `symfony/http-client` tiene un alias llamado`http-client`. Sí, podríamos ejecutar `composer require http-client` y Flex lo traduciría al nombre final del paquete. Es sólo un atajo a la hora de añadir paquetes.

Si quieres ver todos los alias disponibles, ve a un repositorio llamado [symfony/recetas](https://github.com/symfony/recipes)... y luego haz clic en el enlace a `RECIPES.md`. A la derecha, ¡ahí están!

## El sistema de recetas

El segundo superpoder que Symfony Flex añade a Composer son las recetas. Son fascinantes. Cuando añades un nuevo paquete, puede tener una receta, que es básicamente un conjunto de archivos que se añadirán a tu proyecto. Y resulta que todos los archivos con los que empezamos -en `bin/`, `config/`, `public/` - proceden de las recetas de los paquetes que se instalaron originalmente.

Por ejemplo, `symfony/framework-bundle` es el paquete "core" del Framework Symfony. Puedes comprobar su receta yendo al repositorio `symfony/recipes` y navegando a `symfony`, `framework-bundle`, y luego a la última versión. Echa un vistazo a `config/packages/`: ¡la mayoría de las cosas con las que empezamos proceden de esta receta!

Otra forma de ver las recetas es en tu línea de comandos. Ejecuta:

```terminal
composer recipes
```

Aparentemente se instalaron las recetas de cuatro paquetes diferentes. Y podíamos obtener información sobre cualquiera de ellos añadiendo su nombre al final del comando.

De todos modos, las recetas son increíbles porque podemos instalar un paquete y obtener al instante cualquier archivo que necesitemos. En lugar de complicarnos con la configuración, nos ponemos manos a la obra.

## Instalar PHP CS Fixer

Vamos a probar esto: añadamos un nuevo paquete llamado PHP-CS-Fixer que nos proporcionará un archivo ejecutable para arreglar el estilo de nuestro código. Por ejemplo, en`src/Controller/MainController.php`, si sigues las normas de codificación de PHP, la llave debe estar en la línea siguiente a una función. Si hiciéramos algo así, nuestro archivo violaría ahora esas normas. Eso no dañaría nada, pero ya sabes, queremos que nuestro código tenga un aspecto limpio. Y PHP-CS-Fixer puede ayudarnos a hacerlo.

Para instalarlo, ejecuta:

```terminal
composer require cs-fixer-shim
```

Y sí, se trata de un alias. Encima, el paquete verdadero es `php-cs-fixer/shim`.

¿Este paquete venía con una receta? ¡Pues sí! El `Configuring php-cs-fixer/shim`nos lo indica. Pero, también podemos verlo ejecutando:

```terminal
git status
```

El hecho de que `composer.json` y `composer.lock` estén modificados es un comportamiento 100% normal de Composer. Puedes ver que `composer.json` tiene la nueva biblioteca bajo la clave `require`. 

[[[ code('3af38c89f5') ]]]

Pero todos los demás archivos modificados o nuevos lo son gracias a la receta del paquete.

## Investigando la receta

¡Vamos a investigar esto! Abre `.gitignore`. ¡Genial! En la parte inferior, ha añadido dos nuevas entradas para dos archivos comunes que querrás ignorar cuando utilices PHP CS fixer.

[[[ code('7a2e45e201') ]]]

La receta también añadió un nuevo archivo `.php-cs-fixer.dist.php`. Este es el archivo de configuración de CS Fixer. ¡Y compruébalo! 

[[[ code('cf79d4391f') ]]]

Está prediseñado para funcionar con nuestra aplicación Symfony. Le dice que arregle todos los archivos del directorio actual, pero que ignore el directorio `var/` porque es donde Symfony almacena sus archivos de caché. También le dice que utilice un conjunto de reglas llamado Symfony. 
Eso significa que queremos que el estilo de nuestro código coincida con el estilo de Symfony. La cuestión es 
en lugar de perder el tiempo buscando esta configuración por defecto... ¡simplemente la cogemos!

El último archivo modificado es `symfony.lock`. Esto mantiene un registro de qué recetas tenemos instaladas y en qué versión. Y sí, vamos a enviar todos estos archivos a nuestro repositorio.

## Utilizar PHP-CS-Fixer

Ahora que hemos instalado el paquete, vamos a utilizarlo. Para ello, ejecuta:

```terminal
./vendor/bin/php-cs-fixer
```

Eso mostrará todos los comandos disponibles. El que queremos se llama fix. Pruébalo:

```terminal-silent
./vendor/bin/php-cs-fixer fix
```

Y... ¡sí! ¡Ha encontrado la infracción en `MainController.php`! Cuando vamos a ese archivo... ¡sí! Movió mi llave rizada desde el final de la línea hasta la línea siguiente. Es fantástico.

A continuación, vamos a conocer e instalar una de mis bibliotecas favoritas de todo PHP: el motor de plantillas Twig.
