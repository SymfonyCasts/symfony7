# Añadir una búsqueda + el objeto de petición

Es hora de desviarse un poco de las Relaciones Doctrine. Sé que las Relaciones Doctrine molan, ¡pero esto también! Quiero añadir una barra de búsqueda a nuestra página. Confía en mí, esto va a ser bueno.

Abre la plantilla `index.html.twig`. Justo en la parte superior, pegaré una entrada de búsqueda:

[[[ code('f67471f9e7') ]]]

Nada del otro mundo: sólo un `<input type="text" "placeholder="search"`, y luego un puñado de clases y un elegante SVG para que quede bonito.

Para que este chico malo se envíe, envuélvelo en una etiqueta `form`. Para la acción, haz que se envíe directamente a esta página: `{{ path('app_part_index') }}`. Añade también `name="query"` y`method="get"` al formulario:

[[[ code('6b6dc8f1c8') ]]]

De esta forma, cuando enviemos el formulario, añadirá la consulta de búsqueda a la URL como parámetro de consulta.

## Obtener la petición

A continuación, dirígete a `PartController`. ¿Cómo leemos el parámetro de consulta `name`de la URL? Bueno, es información de la petición, igual que las cabeceras de la petición o los datos POST. Symfony empaqueta todo eso en un objeto `Request`. ¿Cómo lo obtenemos? En un controlador, es superfácil. Añade un argumento `Request` al método de tu controlador.

Probablemente recuerdes que puedes autoconectar servicios de este modo. El objeto `Request`no es técnicamente un servicio, pero Symfony es lo suficientemente guay como para permitir autocablearlo de todas formas. Coge el de `Symfony\Component\HttpFoundation\Request`. Puedes llamarlo como quieras, pero para mantener la cordura, vamos a llamarlo `$request`:

[[[ code('4c6cea75bd') ]]]

Establece `$query = $request->query->get('query')`: el primer `query` se refiere a los parámetros de consulta, y el segundo `query` es el nombre del campo de entrada. Para asegurarte de que funciona, `dd($query)`:

[[[ code('a346c70d5d') ]]]

Gira y pruébalo. ¡Fíjate! Es la cadena "holodeck". 

## Mejorar la búsqueda

A continuación, vamos a mejorar el método `findAllOrderedByPrice()` para permitir una búsqueda. Elimina el `dd($query);` y pásalo al método:

[[[ code('0fef7f4784') ]]]

Divídelo en varias líneas y añade una sentencia `if`. También voy a cambiar el retorno por `$qb = $this->createQueryBuilder('sp')` y a deshacerme de `getQuery()`y `getResult()`: por ahora sólo queremos `QueryBuilder`.

Ahora viene la magia. Si tenemos una búsqueda, añade un `andWhere()` que compruebe si el nombre en minúsculas de nuestra parte Starship es como nuestra búsqueda. Ya sé que parece un poco raro, pero es porque PostgreSQL distingue entre mayúsculas y minúsculas.

Por último, devuelve el resultado de la consulta:

[[[ code('9834ce31ff') ]]]

## Conservar el valor de búsqueda

Puede que notes que perdemos nuestro valor de búsqueda después de una búsqueda. Ya no vemos "holodeck" ahí, y eso es una grosería. Para solucionarlo, vuelve a la plantilla y añade un objeto `value="{{ app.request.query.get('query') }}"`. Sí, ese práctico objeto `Request`está disponible en cualquier plantilla como `app.request`:

[[[ code('01432cfda0') ]]]

## Buscar en varios campos

¿No sería genial poder buscar también en las notas de las piezas? Busca "controles". Ahora mismo, nada. Realmente queremos buscar en el nombre y en las notas.

Necesitamos algo de lógica `OR`. De vuelta al repositorio, añade un `OR` a la cláusula `andWhere()`:

[[[ code('ebe7605bcc') ]]]

Podrías tener la tentación de utilizar `orWhere()`, ¡pero eso es una trampa! No puedes garantizar dónde estarán los paréntesis lógicos. Confía en mí, me lo agradecerás más tarde. En lugar de eso, utiliza `andWhere()` y coloca el `OR` justo dentro.

¡Y ya lo tenemos! Ahora podemos buscar en las notas, en el nombre, o en ambos. La conclusión es que cuando quieras utilizar `orWhere()`, no lo hagas: incrusta el `OR`dentro de un `andWhere()`, y tendrás pleno control sobre dónde van los paréntesis lógicos.

Bien, una vez completado este emocionante desvío, volvamos al tema y hablemos del último tipo de relación: de muchos a muchos.
