# Quick Tip: Better requestAction content

A big gripe I have with ajax is that when you initially land on the page, you have to wait for ajax to fetch content to fill containers (a la WordPress dashboard). This is annoying and ugly, and you typically have content bouncing around after it's populated. So, I thought, use `requestAction()` for the initial load (no bouncing), then your ajax pagination within those views will appear seamless.

When using `$this->requestAction()` in your views, however, one of the downsides is that you get two options when it comes to rendering: completely bare with no layout, or the default layout.

If you're like me, your ajax layout outputs the Js buffer so your ajax views can run JavaScript for things like ajax pagination. Problem is, setting bare in `requestAction()` will skip your ajax layout and thus your buffered scripts. Here's a quick solution.


```php
// in your view (where you would have used ajax to populate)
    echo $this->requestAction($url, array(
  'renderAs' => 'ajax',
  'return',
  'bare' => false
));
```

I tell `requestAction()` to return my view with a rendered layout by setting bare to `false`. Then I'm passing an extra parameter, which gets added to our `$this->params` array in the controller. All you need to do from there is tell `RequestHandler` to render the way we want it.


```php
// in AppController
function beforeFilter() {
  if (isset($this->params['renderAs'])) {
	$this->RequestHandler->renderAs($this, $this->params['renderAs']);
  }
}
```


Now we can pass 'renderAs' in `requestAction()` and get what we want, whether it be ajax, json, xml, or whatever.