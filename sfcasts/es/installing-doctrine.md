# Instalación de Doctrine

¡Hola amigos! Es hora del episodio 3 de nuestra serie Symfony 7. Y éste es emocionante porque vamos a dar vida a nuestra aplicación con una base de datos. No necesitamos ninguna librería nueva para hacerlo, pero como es impresionante y huele a galletas, usaremos una librería llamada Doctrine. Y aunque Doctrine y Symfony son proyectos separados, encajan como partículas cuánticas entrelazadas. ¡Acción fantasmal a distancia, nene!

Soy Kevin, y seré el capitán de tu nave estelar en este viaje. ¡Comprométete! Siempre he querido decir eso.

Para aventurarte conmigo por el espacio de la base de datos, descarga el código del curso y sigue la guía de configuración en`README.md`. El último paso, que yo ya he hecho, es ejecutar

```terminal
symfony serve -d
```

para iniciar un servidor web local en https://127.0.0.1. Saluda a la Tienda Estelar de nuestros episodios anteriores. Tenemos una "Cola de Reparación de Naves" donde se enumeran las naves estelares actualmente atracadas para ser reparadas. Puede parecer que los datos proceden de algún tipo de base de datos, pero en realidad están codificados. ¡Lamentable!

¡Es hora de llevar esta aplicación al mundo de las bases de datos!

## Requerir Doctrine

Lo primero es lo primero: necesitamos instalar Doctrine. Ve al terminal y ejecuta:

```terminal
composer require doctrine
```

¡Vaya, esto ha instalado un montón de cosas! Podemos ver que también configuró algunas recetas Flex. Se nos pregunta si queremos incluir la configuración Docker de las recetas. Elige `p`para habilitarlo permanentemente. Hablaremos de Docker en el próximo capítulo, pero no te preocupes, Docker no es necesario para este tutorial.

Desplázate un poco hacia arriba para ver lo que ha ocurrido. El paquete `doctrine` que hemos instalado es en realidad un alias de Flex para un paquete de Flex llamado `symfony/orm-pack`. Recuerda que los paquetes de Flex no son más que una colección de bibliotecas que funcionan bien juntas. El resultado final es una configuración de Doctrine súper robusta.

El primer paquete interesante es `doctrine/dbal`. DBAL son las siglas de Database Abstraction Layer (capa de abstracción de bases de datos), que es una forma elegante de decir que proporciona una manera coherente de trabajar con distintas plataformas de bases de datos. MySQL, PostgreSQL, SQLite, etc. Es superimportante, aunque la mayoría de las veces se esconde entre bastidores.

La segunda es `doctrine/orm`. ORM son las siglas en inglés de Object Relational Mapper (mapeador relacional de objetos). Son palabras elegantes para una biblioteca que nos ayuda a mapear objetos PHP a tablas de bases de datos. Nos meteremos de lleno en esto.

Luego hay otras que vinculan Doctrine a Symfony y una biblioteca de migraciones que utilizaremos para añadir nuevas tablas y cosas por el estilo.

El resto son paquetes de apoyo a Doctrine que puedes ignorar.

## Recetas Doctrine Flex

Pero lo realmente interesante es lo que hacían las recetas Flex de estos paquetes. Ejecuta:

```terminal
git status
```

Los archivos modificados son cosas estándar de las recetas Flex. `.env` se modificó con algunas variables de entorno específicas de Doctrine -las veremos pronto- y `config/bundles.php` se actualizó para activar los dos bundles que instalamos.

Estos archivos sin seguimiento son archivos nuevos añadidos por las recetas Flex. Estos archivos de `compose*.yaml`nos ayudarán a iniciar un contenedor de base de datos en el próximo capítulo.

En `config/packages/`, tenemos 2 archivos nuevos: `doctrine.yaml` y `doctrine_migrations.yaml`. Éstos tienen buenos valores predeterminados, así que nos limitaremos a comprobarlos cuando sea necesario.

Las recetas añaden un directorio `migrations/` vacío, un directorio `src/Entity/` vacío y un directorio `src/Repository/` vacío. Nos sumergiremos en todos ellos uno a uno.

De acuerdo Tenemos Doctrine instalado, así que podemos hablar con bases de datos... excepto que... en realidad aún no tenemos un servidor de bases de datos en marcha. Pongamos uno en marcha
