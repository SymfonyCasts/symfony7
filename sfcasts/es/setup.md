# Configuración, servicios y el contenedor de servicios

¡Hola amigos! Bienvenidos de nuevo al Episodio 2 de nuestro tutorial de Symfony 7. Seré tu valiente - o quizás tonta - guía a través de temas que me encantan... No importa lo que hagas con Symfony, lo más importante que utilizarás son los servicios - pequeños esbirros amarillos que hacen el trabajo en tu aplicación. Hablaremos de la configuración de esos servicios, así como de los entornos.

¿Qué es exactamente un servicio? Muy fácil Es una simple clase PHP que hace un trabajo. Por ejemplo, un `Logger` que te ayuda a registrar mensajes es un servicio. O un mailer que envía correos electrónicos a tus clientes. O un objeto de conexión a la base de datos que utilizas para ejecutar consultas a la base de datos. Todos ellos son servicios. Incluso el controlador que gestiona las peticiones es un servicio, pero tiene superpoderes. Hablaremos de ello más adelante.

Este curso se titula "Fundamentos" porque es la base. Todo lo que viene después de este tutorial no es más que una variación de estos temas. Así que, para codificar conmigo, descarga el código del curso en esta página, descomprímelo y, dentro, encontrarás un directorio `start/` con el mismo código que ves aquí. El archivo `README.md` tiene todo lo que necesitas para poner en marcha esta aplicación. Ya he completado la mayoría de estos pasos, así que voy a pasar al último paso y ejecutar el servidor web Symfony incorporado. Para ello, abre tu terminal y ejecuta:

```terminal
symfony serve -d
```

El `-d` le dice a Symfony que lo inicie en segundo plano. Puedes encontrarlo en `https://localhost:8000`. Podría copiar y pegar esto en mi navegador, pero voy a hacer trampas. Mantén pulsada la tecla "cmd" o "control" en un Mac, haz clic en este enlace, y... bienvenido de nuevo a Starshop, el sitio que creamos en el Episodio 1.

Así pues, los servicios son objetos que funcionan: `Logger` el servidor de correo, nuestra conexión a la base de datos, e incluso nuestros controladores. ¿Cada objeto de nuestra aplicación es un servicio? En realidad, ¡no! También tenemos objetos que contienen datos. Por ejemplo, nuestra clase `Starship` no es un servicio. Es un simple objeto de datos. Cuando necesitamos estos objetos, los instanciamos de la forma aburrida habitual.

Pero los servicios -objetos que hacen un trabajo- son diferentes. Podríamos instanciarlos manualmente, pero en la práctica, de eso se encarga otra cosa: el contenedor de servicios. Es un gran admirador de nuestros servicios. Lo sabe todo sobre ellos, desde el nombre de la clase hasta cada argumento del constructor. Si le pides un servicio, lo instanciará por ti y te devolverá un objeto PHP listo para usar. Y es inteligente si le pides un servicio varias veces, sólo lo crea una vez. Por ejemplo, nuestra aplicación sólo necesita un registrador. Si pides el logger 5 veces, se crea sólo una vez y ¡se devuelve el mismo objeto cada vez!

Vale, entonces... ¿cómo sabemos qué servicios tenemos? Para ver la lista de todos los servicios que tenemos disponibles, vamos a utilizar un comando especial. En tu terminal, ejecuta:

```terminal
bin/console debug:container
```

Aquí puedes ver una larga lista de servicios que podemos utilizar en nuestra aplicación. Pero, ¿de dónde vienen? ¿Quién le dice al contenedor que debe haber un servicio `logger` cuya clase es `Logger` y que debe instanciarse con estos argumentos? Algunos servicios proceden de nuestro código y hablaremos de cómo se registran dentro de un rato. Pero la gran mayoría proceden de bundles. Los bundles son plugins que puedes añadir a las aplicaciones Symfony. Proporcionan algunas cosas, pero la más importante son los servicios. Cada bundle tiene un archivo de configuración que dice:

> Quiero tener un servicio llamado `logger` que debería ser una instancia de "Logger", y que
> debe instanciarse con estos argumentos.

Así que los servicios son herramientas y los bundles nos dan herramientas. En nuestro código, abre `config/bundles.php`. Este es el archivo responsable de registrar los bundles en nuestra aplicación. ¡Y fíjate! ¡Ya tenemos diez! Algunos de ellos, como `WebProfilerBundle`, sólo están disponibles para un entorno específico. `MonologBundle` es el que proporciona el servicio `Logger` que estamos utilizando en `StartshipRepository` cuando registramos un mensaje. O, si eliminamos completamente esta línea `TwigBundle`, el método `render()` que estamos llamando en nuestros controladores dejará de funcionar. Esto se debe a que, entre bastidores, se está utilizando el servicio `twig` para generar plantillas. Más adelante hablaremos de ello.

A continuación, vamos a hablar de cómo instalar nuevos bundles en tu aplicación para obtener nuevos servicios.
