# Configurando nuestra App Symfony

¡Bienvenido al primer tutorial de Symfony 7! Me llamo Ryan - vivo aquí en el mundo de fantasía de Symfonycasts y... Estoy más que emocionado de ser tu guía a través de esta serie sobre Symfony, desarrollo web... chistes malos... animaciones espaciales, y lo más importante, construir cosas reales de las que podamos estar orgullosos. Para mí, es como si fuera la persona afortunada que consigue darte un tour personal por el Enterprise... o por cualquier cosa friki que te emocione más.

Y eso es porque me encantan estas cosas. Crear bases de datos, construir bonitas interfaces de usuario, escribir código de alta calidad... es lo que me levanta de la cama por las mañanas. Y Symfony es la mejor herramienta para hacer todo esto... y convertirme en un mejor desarrollador por el camino.

Y ese es realmente mi objetivo: quiero que disfrutes de todo esto tanto como yo... y que te sientas capacitado para construir todas las cosas increíbles que tienes flotando en tu mente.

## Lo que hace especial a Symfony

Ahora, una de mis cosas favoritas sobre la enseñanza de Symfony es que nuestro proyecto va a empezar diminuto. Eso hace que sea fácil de aprender. Pero luego, escalará automáticamente a medida que necesitemos más herramientas mediante un sistema de recetas único. Symfony es en realidad una colección de más de 200 pequeñas librerías PHP. Así que son un montón de herramientas... pero podemos elegir lo que necesitamos.

Porque, puedes estar construyendo una API pura... o una aplicación web completa, que es en lo que nos centraremos en este tutorial. Aunque, si estás construyendo una API, sigue los primeros tutoriales de esta serie, y luego pasa a nuestros tutoriales sobre la API Platform. API Platform es un sistema alucinantemente divertido y potente para crear APIs, construido sobre Symfony.

Symfony también es rapidísimo, tiene versiones de soporte a largo plazo y se esfuerza mucho en crear una experiencia agradable para el desarrollador, al tiempo que mantiene las mejores prácticas de programación. Esto significa que podemos escribir código de alta calidad y hacer nuestro trabajo rápidamente.

Vale, ya está bien de hablar maravillas de Symfony. ¿Listo para empezar a trabajar? Pues sube a bordo.

## Instalar el binario de Symfony

Dirígete a https://symfony.com/download. Esta página tiene instrucciones sobre cómo descargar un binario independiente llamado `symfony`. Ahora bien, esto no es Symfony propiamente dicho... es sólo una pequeña herramienta que nos ayudará a hacer cosas, como iniciar nuevos proyectos Symfony, ejecutar un servidor web local o incluso desplegar nuestra aplicación en producción.

Una vez que lo hayas descargado e instalado, abre un terminal y entra en cualquier directorio. Comprueba que el binario `symfony` está listo para funcionar ejecutándolo:

```terminal
symfony --help
```

Tiene un montón de comandos, pero sólo necesitaremos unos pocos. Antes de iniciar un proyecto, ejecuta también

```terminal
symfony check:req
```

que significa comprobar requisitos. Esto asegura que tenemos todo lo necesario en nuestro sistema para ejecutar Symfony, como PHP en la versión correcta y algunas extensiones PHP.

Una vez que esto esté contento, ¡podemos empezar un nuevo proyecto! Hazlo con `symfony new` y luego un nombre de directorio. Yo llamaré al mío `starshop`. Más adelante hablaremos de ello.

```terminal-silent
symfony new starshop
```

Esto nos dará un proyecto pequeñito con sólo las cosas base instaladas. Luego, iremos añadiendo más cosas poco a poco por el camino. Pero más adelante, cuando te sientas cómodo con Symfony, si quieres empezar más rápidamente, puedes ejecutar el mismo comando, pero con `--webapp` para obtener un proyecto con muchas más cosas preinstaladas.

De todos modos, entra en el directorio - `cd starshop` - y luego escribiré `ls` para comprobar las cosas. ¡Genial! Conoceremos estos archivos en el próximo capítulo, pero este es nuestro proyecto... ¡y ya está funcionando!

## Iniciando el Servidor Web symfony

Para verlo funcionando en un navegador, necesitamos iniciar un servidor web. Puedes utilizar el servidor web que quieras: Apache, Nginx, Caddy, lo que sea. Pero para el desarrollo local, recomiendo encarecidamente utilizar el binario `symfony` que acabamos de instalar. Ejecuta:

```terminal
symfony serve
```

La primera vez que lo hagas, puede que te pida que ejecutes otro comando para configurar un certificado SSL, lo cual está bien porque así el servidor admite https.

Y... ¡bam! Tenemos un nuevo servidor web para nuestro proyecto ejecutándose en https://127.0.0.1:8000. Copia eso, gira a tu navegador más favorito, pega y... ¡bienvenido a Symfony 7! ¡Eso es lo que iba a decir!

A continuación, sentémonos, pidamos un té Earl Grey y hagámonos amigos de todos los archivos de nuestra nueva aplicación... que no son muchos.
