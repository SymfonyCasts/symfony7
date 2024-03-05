# Conociendo nuestro pequeño proyecto

Vuelve a tu centro de comandos (también conocido como terminal). Esta primera pestaña está ejecutando el servidor web. Si necesitas detenerlo, pulsa Ctrl-C... y reinícialo con:

```terminal
symfony serve
```

***TIP
Puedes utilizar `symfony serve -d` para ejecutar el comando en "segundo plano" y poder seguir utilizando esta pestaña del terminal.
***

Lo dejaremos así y dejaremos que haga lo suyo.

## Los 15 archivos de nuestro proyecto

Abre una segunda pestaña de terminal en el mismo directorio. Cuando ejecutamos el comando `symfony new`, descargó un pequeño proyecto e inicializó un repositorio Git con una confirmación inicial. ¡Eso estuvo muy bien! Para ver nuestros archivos, voy a abrir este directorio en mi editor favorito: PhpStorm. Más sobre este editor en unos minutos.

Ahora quiero que te des cuenta de lo pequeño que es nuestro proyecto Para ver la lista completa de archivos confirmados, vuelve a tu terminal y ejecuta:

```terminal
git ls-files
```

Sí, eso es. Sólo hay unos 15 archivos confirmados en git

## ¿Dónde está Symfony?

Entonces... ¿dónde demonios está Symfony? Uno de nuestros 15 archivos es especialmente importante:`composer.json`. 

[[[ code('dd6d61790e') ]]]

Composer es el gestor de paquetes de PHP. Su trabajo es sencillo: leer los nombres de los paquetes bajo esta clave `require` y descargarlos. Cuando ejecutamos el comando`symfony new`, descargó estos 15 archivos y también ejecutó `composer install`. Eso descargó todos estos paquetes en el directorio `vendor/`.

¿Dónde está Symfony? Está en `vendor/symfony/`... ¡y ya estamos utilizando unos 20 de sus paquetes!

## Ejecuta Composer

El directorio `vendor/` no está registrado en git. Se ignora gracias a otro archivo con el que empezamos: `.gitignore`. 

[[[ code('9895cd1cc5') ]]]

Esto significa que si un compañero de equipo clona nuestro proyecto, no tendrá este directorio. 
¡Y no pasa nada! Siempre podemos repoblarlo ejecutando `composer install`.

Observa: Haré clic con el botón derecho y borraré todo el directorio `vendor/`. Y ¡huy!

Si probamos ahora nuestra aplicación, se estropeará. ¡Mal rollo! Para arreglarlo y salvar el día, en tu terminal, ejecuta:

```terminal
composer install
```

Y... ¡listo! El directorio vuelve a .... y por aquí, el sitio vuelve a funcionar.

## Los 2 directorios que te importan

Si volvemos a mirar nuestros archivos, sólo hay dos directorios en los que tengamos que pensar. El primero es `config/`: contiene... ¡configuración! Aprenderemos lo que hacen estos archivos por el camino.

El segundo es `src/`. Aquí es donde vivirá todo tu código PHP.

¡Y eso es todo! el 99% del tiempo estás configurando algo o escribiendo código PHP. Eso ocurre en `config/` y `src/`.

¿Qué pasa con los otros 4 directorios? `bin/` contiene un único archivo ejecutable `console` que probaremos pronto. Pero nunca vamos a mirar o modificar ese archivo. El directorio `public/` se conoce como la raíz de tu documento. Cualquier cosa que pongas aquí -como una imagen- será accesible públicamente. También contiene `index.php`. 

[[[ code('fef370b8cc') ]]]

Esto se conoce como tu "controlador frontal": es el archivo PHP principal que tu servidor web ejecuta al inicio de cada petición. Y aunque es superimportante... nunca editarás ni pensarás en este archivo.

El siguiente es `var/`. Esto también se ignora desde git: es donde Symfony almacena los archivos de registro y los archivos de caché que necesita internamente. Así que muy importante... pero no algo en lo que tengamos que pensar. Y ya hemos hablado de `vendor/`. ¡Eso es todo!

## Preparando PhpStorm

Ahora, antes de ponernos a codificar, he mencionado que yo utilizo PhpStorm. Eres libre de utilizar el editor que quieras. Sin embargo, PhpStorm es increíble. Y una gran razón es el incomparable plugin Symfony. Si vas a PhpStorm -> Configuración y buscas "Symfony", aquí abajo bajo Plugins y luego Marketplace, podrás encontrarlo. Descarga e instala el plugin si aún no lo tienes. Después de la instalación, reinicia PhpStorm. Luego hay un paso más. Vuelve a la configuración y busca Symfony de nuevo. Esta vez tendrás una sección Symfony. Asegúrate de activar el plugin para cada proyecto Symfony en el que trabajes... de lo contrario no verás la misma magia que yo.

¡De acuerdo! Empecemos a codificar y construyamos nuestra primera página en Symfony a continuación.
