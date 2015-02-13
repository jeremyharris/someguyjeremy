# Cache Query Results Transparently

We all know that caching will (usually) speeds up your application. There are several methods for caching information in CakePHP, including caching your views, elements, arbitrary information and queries. I'm not going to go over these methods in this post, but rather introduce a plugin that caches the actual *results*of your queries.
Caching your find call results can dramatically improve performance. Consider an application that organizes Artists, Albums and Songs (you can figure out the relationships). Songs are also tagged. As you can probably figure out, a list of the most recent Artists that includes all Albums, Songs and Tag information would be several queries, joins and internal CakePHP array merges.


```php
$this->Artist->find('all', array(
  'conditions' => array(
    'Artist.created >' => date('Y-m-d', strtotime('-1 day'))
  ),
  'contain' => array(
    'Album' => array(
      'Song' => array(
        'Tag'
      )
    )
  )
));
```

There are some result caching solutions out there. Most of them either involve calling an extra function to actually retrieve the cached data, or overwrite the `Model::find()` method. I wasn't satisfied. I wanted a transparent solution that required no new code, outside of enabling the plugin.

### Cacher, The Transparent Caching Plugin

Cacher accomplishes this feat with one special addition: a datasource. When you think about it, cache is just a datasource, albeit limited. Don't worry, you don't have to worry about changing datasources, Cacher contains a behavior that handles all of this for you, behind the scenes.


```php
var $actsAs = array(
  'Cacher.Cache'
);
```

And you're done. Okay, maybe not quite. By default, Cacher caches every find result. If you don't want this, set the behavior option "auto" to `false`. There are several other options, but I'm not going to go into those right now. Instead I'm going to show you how Cacher accomplishes its goals.

The behavior part of the plugin has two main functions: transparently caching, and developer cache manipulation. The latter part is of course, clearing cache. The former simply sets the model's datasource before the model's datasource is read from (read: `<a href="http://api13.cakephp.org/view_source/model/#line-2069" target="_blank">Model::find()</a>`).Since `Model::beforeFind()` can't actually manipulate the results (because they haven't been read from the datasource yet), it can't really do much other than that.

### What Makes It Happen

The datasource is really the star of the show, as it handles the actual caching.


```php
function read($Model, $queryData = array()) {
  $this->_resetSource($Model);
  $key = $this->_key($Model, $queryData);
  $results = Cache::read($key, $this->cacheConfig);
  if ($results === false) {
    $results = $this->source->read($Model, $queryData);
    Cache::write($key, $results, $this->cacheConfig);
  }
  return $results;
}
```

The first thing it does is reset the model's datasource back to it's original. If anything gets interrupted, your original datasource will be intact. Then it just checks the cache.

There's more going on behind the scenes, obviously, such as creating a unique key based on the query, automatically removing cache after updates and deletes, etc. All configurable, of course.

### One Query Array To Rule Them All

Think back to our example. CakePHP allows us to write one set of conditions that do a whole lot - joins, extra searches for hasMany and HABTM relationships, and more. There would be several queries to join the Albums, then several to join the Songs and Tags. But, since Cacher uses the original set of conditions to create it's cache key, all of those results get wrapped into one cached file. Imagine saving all of those queries and all of those array merges in one fell swoop. That's what Cacher hopes to do.

Try it out here: [http://github.com/jeremyharris/cacher](http://github.com/jeremyharris/cacher)

Other features:

- Easy and quick - no extra code other than adding the behavior
- Fully tested
- Automatically clear cache on save or delete
- Manually clear all of a model's cache, or a single query, from the behavior
- Use custom cache configs
- File path chunking (separate branch)