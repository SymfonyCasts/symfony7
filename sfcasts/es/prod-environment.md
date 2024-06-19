# El entorno Prod

Abre el archivo `.env` en la raíz de nuestro proyecto y cambia esta variable de entorno `dev`por `prod`. 

[[[ code('76ca37e8e8') ]]]

Para ver qué ha cambiado, de vuelta a nuestro navegador, actualiza. Y... ¡eh! ¡Fíjate! 
La barra de herramientas de depuración web ha desaparecido. Ahora, intentemos cambiar algo en una de nuestras plantillas. Abre `templates/main/homepage.html.twig` y, en la parte inferior, cambiemos `Time` por `Updated at` para que sea más descriptivo. 

[[[ code('f1e5e01ade') ]]]

Si volvemos atrás y actualizamos... no ha cambiado nada. ¿Por qué? Por razones de rendimiento, las plantillas se almacenan en caché. Hicimos nuestro cambio después de que la plantilla se almacenara en caché, por lo que nuestro navegador aún no puede verlo. Para solucionarlo, tenemos que borrar manualmente nuestra caché. En tu terminal, ejecuta:

```terminal
bin/console cache:clear
```

Para especificar la caché del entorno que queremos borrar, podemos añadir la opción `--env=` con el nombre del entorno para el que queremos borrar la caché al final de este comando, como `--env=prod`, por ejemplo:

```terminal-silent
bin/console cache:clear --env=prod
```

Esto puede ser útil cuando necesites ejecutar un comando en un entorno específico distinto de aquél en el que estás trabajando actualmente. Como ya estamos desarrollando en el entorno `prod`, esta parte del comando no es necesaria. Si ejecutamos eso... ¡bien! La caché del entorno `prod` se ha borrado correctamente.

Bien, si volvemos y actualizamos la página de nuevo... ¡tachán! Vemos "Actualizado en". Fantástico. Si alguna vez trabajas en el entorno `prod` y no ves reflejados en el navegador los cambios que has hecho en tus plantillas, archivos de configuración, etc., puede que necesites borrar manualmente la caché.

Ahora mismo, estamos utilizando `cache.adapter.array`, que es algo así como una caché falsa. Podemos verlo en el archivo `config/packages/cache.yaml`. Una caché falsa está bien para el desarrollo, pero cuando estamos trabajando en el entorno `prod`, realmente queremos utilizar `cache.adapter.filesystem` en su lugar. Como ya conocemos la sintaxis de `when@`, aprovechémosla. A continuación, digamos `when@`, pero esta vez, tenemos que configurarlo en nuestro entorno `prod` con `when@prod:`. Debajo, repetiremos la misma estructura que vemos arriba - `framework`, `cache`, y `app` - seguidos de `cache.adapter.filesystem`.

[[[ code('1b1840bc0b') ]]]

Bien, para ver esto en acción, necesitamos borrar la caché de nuevo (ya que aún estamos en el entorno `prod` ) con:

```terminal
bin/console cache:clear
```

De vuelta a nuestro navegador, si observas atentamente, verás que nuestros datos se almacenan en caché durante unos cinco segundos, y luego... ¡nuevos datos! Funciona. En nuestro archivo `.env`, cambiemos `APP_ENV=prod` por `dev`. Si volvemos y actualizamos de nuevo... después de cada actualización... vemos una petición HTTP.

A continuación: Aprendamos más sobre los servicios.
