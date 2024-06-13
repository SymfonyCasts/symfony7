# Configuración del bundle: Configurar el Servicio de Caché

Hasta ahora, hemos aprendido a utilizar los servicios Cliente HTTP y Caché, y los hemos inyectado en `homepage()`. Pero no somos responsables de crear sus objetos. Ya sabemos que los bundles nos proporcionan servicios, y cuando autoconectamos un servicio, nuestro bundle nos proporciona todos los detalles que necesitamos para instanciarlo. Pero si es otro el responsable de instanciar esos objetos, ¿cómo podemos controlarlo? La respuesta es la configuración del bundle.

Abre el directorio `/config/packages`. Todos estos archivos de configuración de `.yaml` se cargan automáticamente en nuestra aplicación Symfony, y su función es configurar los servicios que nos proporciona cada bundle. En nuestro método `homepage()`, justo al principio, vamos a `dd($cache)` para que podamos ver el nombre de la clase del objeto que estamos obteniendo. 

[[[ code('53aff077ff') ]]]

Por ejemplo, para el servicio de caché, `FrameworkBundle` le dice al contenedor de servicios:

> Cuando te pida el `CacheInterface`, quiero
> quiero que instancies este `TraceableAdapter`
> objeto con un conjunto específico de argumentos que necesite.

Así que parece que nuestro servicio de caché es sólo este `TraceableAdapter`, pero si miramos más de cerca, podemos ver que en realidad es una envoltura alrededor de un `FilesystemAdapter`, y la caché se almacena dentro del sistema de archivos. Eso está bien, pero ¿y si en lugar de eso queremos almacenar la caché en la memoria? ¿O en algún otro lugar del sistema de archivos? Aquí es donde brilla la configuración del bundle. Abre `framework.yaml` y encuentra esta clave raíz `framework`. Esto significa que estamos pasando configuración a `FrameworkBundle`, y que utilizará esa configuración para cambiar cómo instanciar sus servicios. Por cierto, el nombre del archivo aquí no es importante. Podríamos llamarlo `pizza.yaml` y funcionaría igual.

[[[ code('6b5f3ccc54') ]]]

Bien, dirígete a tu terminal y ejecuta

## Configuración de depuración

```terminal
bin/console debug:config framework
```

Esto nos muestra la configuración actual. Para ver la configuración completa, ejecuta:

```terminal
bin/console config:dump framework
```

Eso es mucha información. Vamos a reducirla. Si queremos ver sólo la configuración responsable del servicio de caché, ejecuta:

```terminal
bin/console config:dump framework cache
```

## Adaptador de matriz de caché

Mucho mejor. En `cache.yaml`, podemos ver que sigue formando parte de la configuración de `framework`, sólo que separada en archivos diferentes para su organización. Debajo de este ejemplo, vamos a poner `app` en `cache.adapter.array`.

[[[ code('bf2652118d') ]]]

Bien, de vuelta al navegador, actualiza. ¡Genial! Esto ha cambiado a `ArrayAdapter`. Dirígete y elimina `dd($cache)` para que podamos ver `cache.array.adapter` en acción. Vuelve a actualizar la página, y... ¡ah! Cada vez que actualizamos la página, estamos ejecutando la petición HTTP, por lo que la caché sólo está activa durante la petición. Cuando iniciamos una nueva petición, la caché se invalida y volvemos a ver esa petición HTTP.

A continuación: Echemos un vistazo más de cerca al autocableado.
