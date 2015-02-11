# Fulltext Searches with CakeDC Search Plugin

I recently dove into the Search plugin that the [CakeDC](http://cakedc.com) created. It's one of **many** plugins that the CakeDC has been releasing lately. Isn't open source lovely? You can grab the [Search plugin here](http://github.com/CakeDC/search).

### MATCH (`description`) AGAINST ('totally +awesome -lame')

One of the requirements of my search was to allow boolean fulltext searches. I wanted it to be as dynamic as possible so I can easily perform a `MATCH (...) AGAINST (...)` on any model. The Searchable behavior doesn't support this by default (and why should it? It's pretty specific functionality), but has such flexibility that adding it wasn't much of a problem. The first thing I did is construct my `$filterArgs` variable on my Profile model.


```php
/**
 * Filter args for the Search.Searchable behavior
 *
 * @var array
 * @see Search.Searchable::parseCriteria()
 */
var $filterArgs = array(
	array(
		'name' => 'simple',
		'type' => 'query',
		'method' => 'makeFulltext',
		'field' => array(
			'Profile.first_name',
			'Profile.last_name',
		)
	)
);
```


As you can see, we chose the `query` type (you'll see later) and tell it to call the method `makeFullText()`. The `field` parameter is an array of fields that we want to match against. Next, the heart of what makes this work.


```php
/**
 * Creates a MATCH (...) AGAINST (...) expression from the query using the fields
 * defined in filterArgs
 *
 * @param array $data The key value pair for the filterArg's name to the query
 * @return string
 */
function makeFulltext($data = array()) {
	$filterName = key($data);
	$filter = Set::extract('/.[name='.$filterName.']', $this->filterArgs);
	if (!isset($filter[0]['field'])) {
		$filter[0]['field'] = $this->alias.'.'.$this->displayField;
	}
	$field = $filter[0]['field'];
	$query = $data[$filterName];
	$ds = ConnectionManager::getDataSource($this->useDbConfig);
	if (!is_array($field)) {
		$field = array($field);
	}
	return array($ds->expression('MATCH ('.implode(',',array_map(array($ds, 'name'), $field)).') AGAINST ('.$ds->value($query).' IN BOOLEAN MODE)'));
}
```

Drop this in your `app_model.php` so it's available to all models. Basically, it finds the name of the filter (which you pass in the controller, later) and looks up the `$filterArg` to get the fields. Once it has the fields, it creates a DB expression based on the query we pass.

### Making it all come together

Your controller code uses the Searchable behavior to generate conditions.


```php
$this->Profile->find('all', array(
	'conditions' => $this->Profile->parseCriteria(array('simple' => 'totally +awesome'))
));
```

As you can see, we call `Searchable::parseCriteria()` and pass the name of the search according to `$filterArgs` as the key, with the value being the boolean search you want to perform. You can name the search whatever you want, just make sure to change it in `$filterArgs`. Or, create multiple searches to take advantage of any indexing you might be doing.

And there you have it, boolean fulltext searches in CakePHP using CakeDC's Searchable plugin.

*Note: To perform fulltext searches you need your MySQL engine to be MyISAM and a FULLTEXT index with the columns you want to search. Also, this does not take relevance into account. Perhaps I'll come back to that at a later date.*