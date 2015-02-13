# Croogo Excerpts

As of Croogo 1.3.1 beta, the default behavior for paginated views (promoted, search, etc.) was to show the entire article. I wasn't too fond of this so I edited my templates. For example, under `mytheme/nodes/promoted.ctp` the body text reads something like:


```php
echo $layout->nodeInfo();
echo $layout->nodeBody();
echo $layout->nodeMoreInfo();
```

So I changed it to:


```php
echo $layout->nodeInfo();
echo $layout->nodeExcerpt();
echo $layout->nodeMoreInfo();
```

I had never filled in the excerpt field in any of my pages or blog posts, so naturally they were empty. The intuitive action, in my opinion, would be to default to some of the body text if there's no excerpt. It's a simple change, just change `mytheme/elements/node_excerpt.ctp` to this:


```php
<div class="node-excerpt">
	<?php
	$excerpt = $layout->node('excerpt');
	if (empty($excerpt)) {
	  $body = $layout->node('body');
	  echo $this->Text->truncate($body, 400, array('html' => true));
	} else {
	  echo $excerpt;
	}
	?>
</div>
```

By default I show 400 characters of the body if there's no excerpt defined. This makes it quick and easy to apply to all existing posts. Then just make sure to modify your template to use `Layout::nodeExcerpt()</span> instead of <span class="code">Layout::nodeBody()` where applicable.

In doing this you'll probably want a "Read more" link. I added a function to my CustomHelper.


```php
class CustomHelper extends AppHelper {
	var $helpers = array('Layout');
	/**
	 * A read more link for the node
	 *
	 * @param array $options
	 * @return string
	 */
	  function readMore($options = array()) {
	        $_options = array(
	            'element' => 'node_read_more',
	        );
	        $options = array_merge($_options, $options);

	        $output = $this->Layout->View->element($options['element']);
	        return $output;
	}
}
```

Then add `mytheme/elements/node_read_more.ctp` to your elements.


```php
<div class="read-more">
	<?php
	$slug = $layout->node('url');
	echo $this->Html->link('Read More', $slug);
	?>
</div>
```

And finally, your promoted, etc. views should now look like:


```php
echo $layout->nodeInfo();
echo $layout->nodeExcerpt();
echo $this->Custom->readMore();
echo $layout->nodeMoreInfo();
```

There you have it. Automatic excerpts with a nice customizable "Read more" link!