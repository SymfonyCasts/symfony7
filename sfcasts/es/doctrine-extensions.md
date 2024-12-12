# Auto Slug y Timestamps con extensiones Doctrine

En el último capítulo, añadimos tres nuevos campos a nuestra entidad `Starship`: `slug`,`updatedAt`, y `createdAt` pero ya no podemos cargar nuestras fijaciones. Esto se debe a que nuestro `StarshipFactory` no establece estos nuevos campos. Podríamos añadirlos pero... Estos campos requieren cierta lógica: `slug` debe generarse a partir de `name`. `updatedAt`: se establece en la hora actual cuando cambia la entidad y `createdAt`: se establece en la hora actual cuando se crea la entidad.

¡Existe un paquete que puede manejar esta lógica: `DoctrineExtensions`! En tu terminal, instala el siguiente bundle:

```terminal
composer require stof/doctrine-extensions-bundle
```

Este bundle tiene una receta... pero no se considera oficial. Es una receta de terceros, o contrib. Como medida extra de seguridad, Symfony te pedirá que confirmes la ejecución de la receta. Sé que esta receta es segura, así que pulsaré `yes` para permitirla.

Desplázate hacia arriba para ver lo que tenemos. Los paquetes más importantes son `gedmo/doctrine-extensions`, que contiene las extensiones reales, y `stof/doctrine-extensions-bundle`, que las integra con Symfony. Los demás son sólo dependencias de las que no tenemos que preocuparnos.

Ejecuta:

```terminal
git status
```

Para ver lo que ha añadido la receta. Configuró el bundle y añadió un nuevo archivo de configuración. Para este bundle, necesitamos editar esta configuración para habilitar las extensiones que queremos. En tu IDE, abre `config/packages/stof_doctrine_extensions.yaml`. Debajo de `default_local`añade una nueva clave: `orm:`. Dentro, añade `default:` y, dentro de ella, habilita las siguientes extensiones: `timestampable: true` y `sluggable: true`. Esto le dice al bundle que habilite estas dos extensiones para el gestor de entidades ORM por defecto. Una configuración avanzada de Doctrine podría tener varios gestores de entidades, pero nuestra configuración estándar sólo tiene uno, llamado `default`.

Estas extensiones ya están activadas, pero tenemos que configurar las propiedades de nuestra entidad `Starship` para utilizarlas. Abre `src/Entity/Starship.php`.

Sobre la propiedad `$slug`, añade un nuevo atributo: `#[Slug]`, importando la clase de`Gedmo\Mapping\Annotation`. Dentro, añade un argumento `fields:` y configúralo como una matriz que contenga un único elemento: `name`. Esto indica a la extensión que genere el slug a partir de nuestra propiedad `$name`cuando se persista la entidad por primera vez.

Encima de `$updatedAt`, añade el atributo `#[Timestampable]`. Dentro, añade `on: 'update'`, lo que significa, establecer automáticamente este campo a la hora actual en la actualización de la entidad. Lo mismo para`$createdAt`, pero con `on: 'create'`, lo que significa que se ajusta a la hora actual en la creación de la entidad.

¡Ahora vamos a recargar nuestros accesorios! En tu terminal, ejecuta:

```terminal
symfony console doctrine:fixtures:load
```

Y... ¡funcionó! Ejecuta nuestra consulta SQL para ver los valores:

```terminal
symfony console doctrine:query:sql 'SELECT name, slug, updated_at, created_at FROM starship'
```

¡Sí! Nuestro `slug` se genera a partir del `name`, y `updatedAt` y `createdAt` se establecen con el mismo valor, la marca de tiempo de creación de la entidad. Nota: la creación de una entidad se considera una actualización, por eso `updatedAt` y `createdAt` tienen el mismo valor.

Desplázate un poco hacia abajo. Fíjate en estas babosas que tienen como sufijo `-1`? Esto se debe a que nuestro campo `slug` es único, pero nuestro `name` no lo es. Tenemos algunos campos estelares, como `Lunar Marauder` aquí, que tienen el mismo nombre. La extensión slug es lo suficientemente inteligente como para detectar esto y añadir automáticamente un sufijo numérico (`-1`, `-2`, etc.) para garantizar que el slug sea único.

Ahora que tenemos un slug único y legible por humanos para nuestras naves estelares, vamos a utilizarlo en lugar de este feo `id` en nuestras URL. También utilizaremos algo llamado Resolvedores de valores de controlador para que nuestros controladores sean de alta tecnología ¡Eso a continuación! 
