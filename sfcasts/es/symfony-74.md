# Actualización a Symfony 7.4

Muy bien, vamos a meternos de lleno en la actualización a Symfony 7.4. Abre tu archivo`composer.json`. Observa que los paquetes Symfony utilizan el formato `7.3.*`. Esto es ligeramente diferente del resto de nuestros paquetes, que suelen utilizar el formato de prefijo `^`. Esto hace que sea muy fácil encontrar y actualizar los paquetes `symfony`.

Si utilizas PhpStorm, ve a editar... buscar... reemplazar. Busca`7.3.*` y sustitúyelo por `7.4.*`. Podemos ver que tenemos 19 ocurrencias que reemplazar. Pulsa reemplazar todo... y... ¡boom!

[[[ code('d2578f21c8') ]]]

Vamos a hacer una doble comprobación para asegurarnos de que no se nos ha pasado ninguna. En `require`... sí, parece que está bien. Ahora en `require-dev`... 

[[[ code('3f1507918a') ]]]

Sí, ahí también se ve bien. `maker-bundle` tiene una estrategia de versionado diferente a la de los componentes principales y bundles de Symfony. Por eso tiene un aspecto diferente.

Observa que bajo la sección `extra`, tenemos esta configuración `symfony` `require` .

[[[ code('fc6afcd1be') ]]]

Esto le dice a Symfony Flex qué versión de Symfony debe utilizar al instalar los componentes Symfony. Algunos de nuestros componentes Symfony necesarios requieren otros componentes Symfony como dependencias. Estas se llaman dependencias transitivas (¡palabra elegante!), y pueden permitir una amplia gama de versiones de Symfony. Como Symfony 6, 7 u 8. Esta configuración garantiza que sólo se instalen las versiones `7.4`. Así que cuando actualices Symfony, es importante que también actualices esta configuración.

¡Genial! Ahora que hemos actualizado nuestro `composer.json`, vamos a ejecutar nuestra actualización de Composer:

```terminal
symfony composer update
```

## Verificando la actualización

Perfecto, ¡parece que ha funcionado! Vayamos a nuestra aplicación y actualicémosla. Sí, ahora estamos en 7.4.8, la última versión 7.4. 

De vuelta a nuestro terminal, ejecuta:

```terminal
git status
```

Nuestros archivos `composer.json` y `composer.lock` están modificados, es de esperar. Pero también tenemos este nuevo `config/reference.php`.

Ábrelo en tu editor. Se trata de un archivo autogenerado que Symfony crea al construir el contenedor. Symfony tiene ahora un formato de configuración basado en arrays PHP, una alternativa a YAML. Este archivo se genera para proporcionar un mejor autocompletado cuando se utiliza ese formato. YAML sigue siendo el formato recomendado, y lo que estamos utilizando en esta aplicación, por lo que este archivo no es importante para nosotros en este momento. Si Symfony cambia su recomendación en el futuro, ¡estaremos preparados! Echa un vistazo a [esta entrada del blog](https://symfony.com/blog/new-in-symfony-7-4-better-php-configuration) para obtener más información al respecto.

Puedes añadir este archivo a tu `.gitignore` o confirmarlo. La mejor práctica ahora mismo es confirmarlo, así que vamos a hacerlo. Ejecuta en tu terminal:

```terminal
git add config/reference.php
```

Después de ejecutar `git status` para confirmar que estamos bien, podemos confirmar con:

```terminal
git commit -a -m "update composer"
```

(Sí, realmente deberíamos usar un mensaje de confirmación mejor aquí)

## Actualizar recetas

¡Veamos si hay alguna actualización de recetas para la 7.4! Ejecuta:

```terminal
symfony composer recipe:update
```

Sólo dos, empieza por `framework-bundle`. Ejecuta `git status` para ver los cambios.`.env` y `config/services.yaml` han sido modificadas.

Abre primero `.env`. Se ha añadido una nueva variable de entorno: `APP_SHARE_DIR`. Cuando se ejecuta Symfony en una arquitectura multiservidor, éste es un directorio que debe compartirse entre los servidores. Antes, tenías que compartir todo el directorio de caché, lo que no es idea. Esta nueva configuración te permite tener un control más preciso sobre lo que se comparte entre servidores. Si te interesa saber más sobre esto, consulta [esta entrada del blog](https://symfony.com/blog/new-in-symfony-7-4-share-directory).

Abre nuestro segundo archivo modificado, `config/services.yaml`. Sólo se han modificado los comentarios de la parte superior. ¡Pero proporciona una nueva función genial! ¿Ves esto de `yaml-language-server $schema`? Esto configura un esquema JSON para este archivo. Espera, ¿JSON? Esto es YAML. Como YAML es compatible con JSON, podemos utilizar esquemas JSON para validar nuestros archivos YAML. Esto es genial, pero lo mejor es que nos proporciona autocompletado en nuestros IDEs si lo soportan. ¡Y PhpStorm lo soporta! ¡Aquí tienes [una entrada de blog](https://symfony.com/blog/new-in-symfony-7-4-deprecated-xml-configuration#better-yaml-autocompletion) si quieres saber más sobre ello!

Bien, vamos a confirmar estos cambios en nuestro terminal con:

```terminal
git commit -a -m "update recipes"
```

Ejecuta de nuevo el comando recipe update y actualiza el paquete `routing`. Ejecuta `git status` para ver los cambios. `config/routes.yaml`: ábrelo. En la parte superior, se ha añadido una configuración de esquema YAML JSON.

Abajo, mira la nueva configuración simplificada. Con la configuración anterior, sólo se cargaban los controladores en `src/Controller`. Con esta nueva configuración, cualquier clase que contenga atributos `#[Route]` se cargará como controlador. Sigue siendo la mejor práctica poner todos tus controladores en `src/Controller`. Pero si tienes una aplicación más compleja con varios dominios y sus propios controladores, éstos se cargarán independientemente de dónde se encuentren.

Vamos a confirmar estos cambios con:

```terminal
git commit -a --amend
```

## Probando la actualización

¡Perfecto! Entra en nuestra aplicación y actualiza la página de inicio para asegurarte de que todo sigue funcionando correctamente. Bien, ¡la actualización a la 7.4 ha sido un éxito!

A continuación, vamos a actualizar Doctrine y explorar una interesante mejora de la última versión del ORM. ¡Permanece atento!
