# Slugs: Ugly Bugs, Pretty URLs

I've always disliked how slugs and permalinks were created, managed and implemented. I've never thought of slugs as permalinks, and that's how I treat them. To me, a permalink is *permanent* and slugs are not.

The reason I dislike them is because they are usually implemented by adding a field to the database where the slug is stored. To me, this is duplicate information. I *hate*duplicate data. Almost as much as I hate WordPress (I'll save that for another day).

But we like slugs. They make our url's pretty and are end-users happy. No one wants to see `/posts/234523`. They want to see `/posts/slugs-ugly-bugs-pretty-urls`. Readable, consistent, relevant slugs.

### Step 1: Delete that stupid extra field

Go ahead, do it. Don't worry, you can still have slugs. After all, CakePHP is about having your cake and eating it too. I created a nifty little plugin that automatically turns your messy, albeit practical, named parameters into slugs. This means things like


```php
Router::url(
	'controller' => 'posts',
	'action' => 'view',
	'Post' => 234523
);
```

automatically become `/posts/view/slugs-ugly-bugs-pretty-urls`. Simply by adding a route. Easy, no? And the best part is that it creates the slug on-the-fly based on that specific post's `$displayField`. Or a different field, if you want.

The biggest change in your application will probably be changing your pass params to named params. I tend to like this method better anyway, because there's more you can do with named params. Sure, they're not as friendly, but in my experience and in many cases friendly != better.

### Step 2: Connect that route

```php
App::import('Lib', array('Slugger.routes/SluggableRoute'));

Router::connect(/posts/:action/*,
	array(),
	array(
		'routeClass' => 'SluggableRoute',
		'models' => array('Post')
	)
);
```

And done. Now url's that contain a Post named parameter will be turned into a slug for the user to see, then back to a named parameter for your application to handle.

Download it here: [https://github.com/jeremyharris/slugger](https://github.com/jeremyharris/slugger)

And don't forget - these aren't permalinks! They're slugs!

Quick answers:

1. Yes, it caches
2. Yes, it can handle duplicate slug names
3. Yes, you can slug more than one model at a time
4. Yes, it will give you +5 cool points