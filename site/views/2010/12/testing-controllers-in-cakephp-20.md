# Testing Controllers in CakePHP 2.0

In the past, testing controllers in CakePHP always had its hiccups. In 2.0, `testAction` was written from the ground up to allow for mocking and an all-inclusive set of results.

To gain this new functionality, you'll need to do is extend your test case by `ControllerTestCase` instead of the typical `CakeTestCase`. `ControllerTestCase` gives you access to two new methods, `testAction` and `generate` and several convenient variables:

**ControllerTestCase**

- `generate()` - Generates a mock controller, along with model and component classes
- `testAction()` - Called the same way as the old testAction, but with 20% more awesomeness
- `controller` - The generated controller. You can overwrite this with you own fancy mocked controller if you have one
- `autoMock` - Automatically mock controllers. Set to `true` and skip `generate()` (default: false)
- `loadRoutes` - Load your custom routes to use in parsing the urls (default: true)
- `vars` - The view vars set by your action
- `view` - The rendered view
- `contents` - The entire rendered contents (including the layout)
- `return` - The returned value, if any. This is useful for `requestAction` tests

**Backwards Compatibility**

The only backwards compatibility lost is the use of the 'fixturize' option. Everything else should work as normal as long as you extend any test cases using `testAction()` by `ControllerTestCase` instead of `CakeTestCase`.

### Generating Mocked Controllers

`generate()` takes the hard work out of generating mocks on your controller. If you decide to generate a controller to be used in testing, you can generate mocked versions of its models and components along with it.

```php
$Posts = $this->generate('Posts', array(
  'methods' => array(
	'isAuthorized'
  ),
  'models' => array(
	'Post' => array('save')
  ),
  'components' => array(
	'RequestHandler' => array('isPut'),
	'Email' => array('send'),
	'Session'
)
));
```


The above would create a mocked `PostsController`, stubbing out the `isAuthorized` method. The attached `Post` model will have `save()` stubbed, and the attached components would have their respective methods stubbed. You can choose to stub an entire class by not passing methods to it, like `Session` in the example. Generated controllers are automatically used as the testing controller to test. To enable automatic generation, set the `autoMock` variable on the test case to `true`. If `autoMock` is false, your original controller will be used in the test. Sending headers is stubbed so the test still runs. You can access the controller in the `controller` variable on the test case.

### Oh, the mockabilities!

Since `generate()` uses the class registry to register any mocked models, any models you generate using `ClassRegistry::init()` will make use of your mocked model. Also, due to the fantastic use of collections in 2.0, any components generated on other components (or loaded within an action) will also utilize your mocked versions.

### Testing Controller Actions

The `testAction` has been reworked to give you all of the information you want in one run. The arguments are the same as before, so you can pass data, choose the method and select your return results. Due to its inconsistent nature, `fixturize` was removed. You can still easily use fixtures in the test case as before.

```php
$this->testAction('/posts/index');
$this->assertIsA($this->vars['posts'], 'array');
```


In its simplest form, `testAction` will run `PostsController::index()` on your testing controller (or an automatically generated one), including all of the mocked models and components. The results of the test are stored in the `vars`, `contents`, `view`, and `return` variables. Also available is a `headers` variable which gives you access to the headers that would have been sent, allowing you to check for redirects.

```php
function testAdd() {
  $Posts = $this->generate('Posts', array(
	'components' => array(
	  'Session',
	  'Email' => array('send')
	)
  ));
  $Posts->Session->expects($this->once())->method('setFlash');
  $Posts->Email->expects($this->once())->method('send')
	->will($this->returnValue(true));
  $this->testAction('/posts/add', array(
	'data' => array(
	  'Post' => array('name' => 'New Post')
	)
  ));
  $this->assertEquals($this->headers['Location'], '/posts/index');
  $this->assertEquals($this->vars['post']['Post']['name'], 'New Post');
  $this->assertPattern('/<html/', $this->contents);
  $this->assertPattern('/<form/', $this->view);
}
```


This example shows a simple use of the new `testAction` and `generate()` methods. First, we generate a testing controller and mock the `SessionComponent`. Now that the `SessionComponent` is mocked, we have the ability to run testing methods on it. Assuming `PostsController::add()` redirects us to index, sends an email and sets a flash message, the test will pass. For the sake of example, we also check to see if the layout was loaded by checking the entire rendered contents, and checks the view for a form tag. As you can see, your freedom to test controllers and easily mock its classes is greatly expanded with these changes.

### Custom Routes

The last feature of `ControllerTestCase` is the option to load your routes or not. By default, custom routes are loaded and used when parsing the url. If you wish to disable this, simply set `loadRoutes` to false on the case. This allows you to do things such as test for extension parsing.

```php
$vars = $this->testAction('/controller/action.json', array('return' => 'contents'));
// assert that you returned some sort of json response!
```

The new `ControllerTestCase` also takes care of the new redirect routes included in 2.0. If your action parses and matches a redirect route, the headers will automatically be stored in `$this->headers` for you.

### `$this->assertTrue($testing == 'friggin\' sweet');`

When you discover the power and benefit of testing, you'll never want to return. Yes, it can be inconvenient at times. Yes, it can be annoying. But the benefits **far** outweigh the inconveniences. I strongly suggest you pick it up if you haven't. If you have, you'll thoroughly enjoy the changes made in 2.0 in regards to testing.