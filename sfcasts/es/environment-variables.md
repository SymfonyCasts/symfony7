# Variables de entorno

Las variables de entorno son para valores que difieren según el entorno en el que estemos desarrollando, como localmente frente a producción. El ejemplo más común de esto son los detalles de conexión a la base de datos. Podemos establecer variables de entorno reales en nuestro sistema operativo, y aunque muchas plataformas de alojamiento en la nube hacen que sea súper fácil establecer estas variables, no es lo más fácil de hacer localmente. Symfony también tiene este archivo `.env` que ayuda a hacer la vida más fácil, especialmente durante el desarrollo.

Bien, éste es el plan: Queremos que nuestro valor `iss_location_cache_ttl` sea diferente localmente que en producción. En nuestro entorno `prod`, queremos que nuestra caché dure más que los 5 segundos que tenemos ahora. La forma más sencilla de hacerlo sería crear una variable de entorno personalizada y establecer un valor diferente para cada entorno: `dev` y `prod`.

En nuestro archivo `.env`, aquí abajo, escribe `ISS_LOCATION_CACHE_TTL` en mayúsculas, que es lo habitual para las variables de entorno. Pongámoslo en `5` por defecto. Ahora, en `services.yaml`, vamos a mantener el parámetro `iss_location_cache_ttl`, pero en lugar de `5`, vamos a establecerlo en la variable de entorno que acabamos de crear. Para ello, tenemos que aprovechar una sintaxis especial. Escribe `'%env()'` y selecciona nuestra nueva variable de entorno `ISS_LOCATION_CACHE_TTL`. ¡Qué bien!

Para depurar esto, en `/src/Controller/MainController.php`, busca `homepage()`. Dentro de eso, debajo de `Response`, escribamos `dd($this->getParameter())` y añadamos `iss_location_cache_ttl`. De vuelta al navegador, actualízalo. Ahí está `5`. Es sutil, pero te habrás dado cuenta de que este valor es una cadena en este momento. Todos los valores de las variables de entorno son simples cadenas por defecto, pero Symfony tiene una forma de encasillarlas en un tipo diferente. Se llaman "procesadores de variables de entorno", y uno de ellos puede ayudarnos a convertir esto en un entero.

De vuelta a nuestro código, abre `services.yaml`. Antes de la variable de entorno, añade `int:`. Si actualizamos... ahora tenemos un entero real `5`. Si estuviéramos desplegando este proyecto en producción, probablemente querríamos establecer esta variable `ISS_LOCATION_CACHE_TTL` en algo un poco más largo, como `60`, para que almacene en caché los datos durante 1 minuto en lugar de 5 segundos. El plazo más corto es más práctico mientras probamos las cosas.

Ya que estamos aquí, quiero hablar de otros archivos `.env`. Este archivo `.env` está comprometido en tu repositorio Git, y como puedes ver aquí, cuando hago cambios en él, esos cambios se desestabilizan. Así que si tienes algunos secretos que no quieres confirmar en tu repositorio Git, como tokens sensibles, contraseñas, etc., puedes crear un archivo diferente - `.env.local`. Éste es ignorado por Git, lo que podemos ver en nuestro archivo `.gitignore`. Cualquier información sensible puede almacenarse aquí y no se confirmará en el repositorio. Podríamos, por ejemplo, mover esta variable de entorno `APP_SECRET` a nuestro archivo `.env.local`. Dentro de nuestro archivo `.env`, podemos dejarla vacía o ponerle un valor falso. Por lo general, es una buena práctica mantener las variables de entorno en `.env` para que otros desarrolladores puedan verlas y establecer valores reales para ellas en su archivo `.env.local`. Esto era sólo un ejemplo, así que podemos volver a cambiarlo.

Junto con estos dos archivos, también están los menos utilizados `.env.test` y `.env.prod`. Éstos sólo se cargan en los entornos `test` y `prod` respectivamente. También disponemos de un práctico comando para depurar variables de entorno. En tu terminal, ejecuta:

```terminal
bin/console debug:dotenv
```

Esto puede ayudarnos a comprender en qué orden se cargarán esos archivos y, como extra, enumera todas las variables de entorno que encontró en cada archivo. De momento, sólo tenemos tres y podemos ver sus valores reales y en qué archivos están configurados.

Si te tomas en serio la seguridad de tu información sensible, Symfony tiene una herramienta especial para ello llamada "Secrets Vault". Si buscas en Google "Secretos de Symfony", uno de los primeros resultados es "Cómo mantener en secreto la información sensible", que nos lleva a cierta documentación. Con la Bóveda de Secretos, podemos confirmar con seguridad variables de entorno en nuestro repositorio Git, porque están encriptadas y no se pueden leer sin desencriptarlas. Si necesitas este nivel de protección de datos, te animo a que leas la documentación o veas nuestros vídeos relacionados en SymfonyCasts. Revertiré los cambios que hicimos en nuestra página de inicio y eliminaré este `dd()`. Ya no lo necesitamos.

A continuación: Hablemos más sobre la autoconfiguración.
