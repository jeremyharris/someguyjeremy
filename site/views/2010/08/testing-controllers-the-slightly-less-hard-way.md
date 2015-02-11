# Testing controllers the (slightly less) hard way

**UPDATE**: This post uses an old version of the class to demonstrate. The principles are the same, though. I've hosted the project on Codaset for all to fork or whatever. It's also wrapped in a nice little plugin. I removed some of the more proprietary things (mocking ACL, configuring Auth, etc.) Get to it here:[](http://www.42pixels.com/downloads/cakephp-extended-test-case-plugin) [https://github.com/jeremyharris/extended_test_case](https://github.com/jeremyharris/extended_test_case)


I've just recently entered the realm of writing tests for my CakePHP applications. Better late than never, I suppose.

A while ago,[Mark Story](http://mark-story.com/) wrote very helpful posts on [testing controllers the hard way](http://mark-story.com/posts/view/testing-cakephp-controllers-the-hard-way) and [utilizing mocks](http://mark-story.com/posts/view/testing-cakephp-controllers-mock-objects-edition) in testing Cake.

I took his advice and turned around and wrote an extension to `CakeTestCase::testAction()` that makes it easier and even backwards compatible to your old tests. Of course, if you've read anything about `CakeTestCase::testAction()` you know it's a terrible and possibly doomed method.

Here's the class:


```php
App::import('Component', 'Acl');
require_once APP.'config'.DS.'routes.php';

Mock::generatePartial('AclComponent', 'MockAclComponent', array('check'));

class ExtendedTestCase extends CakeTestCase {

	var $testController = null;

	function testAction($url = '', $options = array()) {
		if (is_null($this->testController)) {
			return parent::testAction($url, $options);
		}

		$Controller = $this->testController;

		// reset parameters
		ClassRegistry::flush();
		$Controller->passedArgs = array();
		$Controller->params = array();
		$Controller->url = null;
		$Controller->action = null;
		$Controller->viewVars = array();
		$Controller->{$Controller->modelClass}->create();
		$Controller->Session->delete('Message');
		$Controller->activeUser = null;

		$default = array(
			'data' => array(),
			'method' => 'post'
		);
		$options = array_merge($default, $options);

		// set up the controller based on the url
		$urlParams = Router::parse($url);
		if (strtolower($options['method']) == 'get') {
			$urlParams['url'] = array_merge($options['data'], $urlParams['url']);
		} else {
			$Controller->data = $options['data'];
		}
		$Controller->passedArgs = $urlParams['named'];
		$Controller->params = $urlParams;
		$Controller->url = $urlParams;
		$Controller->action = $urlParams['plugin'].'/'.$urlParams['controller'].'/'.$urlParams['action'];

		// only initialize the components once
		if (empty($Controller->Component->_loaded)) {
			$Controller->Component->initialize($Controller);
		}

		// configure auth
		if (isset($Controller->Auth)) {
			$Controller->Auth->initialize($Controller);
			if (!$Controller->Session->check('Auth.User') && !$Controller->Session->check('User')) {
				$Controller->Session->write('Auth.User', array('id' => 1, 'username' => 'testadmin'));
				$Controller->Session->write('User', array('Group' => array('id' => 1, 'lft' => 1)));
			}
		}
		// configure acl
		if (isset($Controller->Acl)) {
			$Controller->Acl = new MockAclComponent();
			$Controller->Acl->enabled = true;
			$Controller->Acl->setReturnValue('check', true);
		}

		$Controller->beforeFilter();
		$Controller->Component->startup($Controller);

		call_user_func_array(array(&$Controller, $urlParams['action']), $urlParams['pass']);

		$Controller->beforeRender();
		$Controller->Component->triggerCallback('beforeRender', $Controller);

		return $Controller->viewVars;
	}
}
```

### First thing's first

I import the Acl component for my own needs. I mock the `Acl::check()`, because when it comes down to it I'm not testing permissions here. I also bring in the routes that the app has set. This is to make things easier and to include any extensions we're parsing. If you have nothing special in your routes file, no need to import them.

### Into the class

Here's the good stuff. It takes Mr. Story's ideas and suggestions a small step further. I didn't feel like rewriting `Controller::beforeFilter()` this and `$Controller->Component->startup()` that. The new `ExtendedTestCase::testAction()` function uses what you would normally pass to `CakeTestCase::testAction()`, but allows you to use your mocks.

The first thing you'll notice is that if you don't set the variable `testController` in your test case, the class will revert to the classic `CakeTestCase::testAction()` method.

Next, we zero everything out. This allows us to use `ExtendedTestCase::testAction()` more than once within a single test case function. In my app controller, I have a variable called `activeUser` that stores user data that I null out as well.

Continuing down, we configure the parameters based on any data and what kind of request you've sent. These are the same variables you send with the old function, so no need to rewrite anything.I chose to only initialize components once because of some redirect errors I was having. If you need brand new components for each test just separate your test case functions.

The next thing I do is take care of some Auth and Acl configurations. Auth automatically sets itself to an admin (based on my group settings) if they haven't been set in the test case. More about that later. Acl will always pass.

### And then the good stuff

The function is called pretty much exactly like in Mark's post, except we keep the parameters based on a url string and pass any passing vars as well. Currently the function only returns the vars. I've found that when testing controller actions, that's really all you care about anyway. If you need the rendered views, this may not be for you.

### The beauty is in the ease of use

To utilize this version of testAction, you just need to extend your current test case by `ExtendedTestCase` instead. In addition, you'll need to create a controller object at the beginning of the test much like Mark does. Mock at your own discretion!

Here's a quick example where I mock some controller actions to prevent redirects and whatnot, but gain the advantage of being able to directly access the controller and even test against the mocks.


```php
App::import('Lib', 'ExtendedTestCase');
App::import('Controller', 'Posts');

// mock instead of needing to create a new controller for every test
Mock::generatePartial('PostsController', 'MockPostsController', array('isAuthorized', 'render', 'redirect', '_stop', 'header'));

class PostsTestCase extends ExtendedTestCase {
	var $fixtures = array('app.post');

	function startTest() {
		$this->Posts =& new MockPostsController();
		$this->Posts->constructClasses();
		$this->testController = $this->Posts;
	}

	function endTest() {
		unset($this->Posts);
		ClassRegistry::flush();
	}

	function testEdit() {
		$data = array(
			'id' => 1,
			'title' => 'Updated post title'
		);
		// run testAction like you normally would. even keep return => vars in
		// there if you'd like to
		$vars = $this->testAction('/posts/edit/1', array(
			'return' => 'vars',
			'data' => $data
		));
		$post = $this->Posts->Post->read(null, 1);
		$this->assertEqual($post['Post']['value'], 'Updated post title');

		// if our posts edit action set a parents variable, we can test it
		$result = $var['parents'];
		$expected = array(
			1 => 'My First post',
			2 => 'My Second post'
		);
		$this->assertEqual($result, $expected);
	}

}
```

### Please, mock me

Being able to mock the `RequestHandler` component and the `Email` component can be incredibly useful. This method allows us to use those mocks. Just add the following mocks:

```php
Mock::generate('EmailComponent');
Mock::generatePartial('RequestHandlerComponent', 'MockRequestHandlerComponent', array('header'));
```


And overwrite the components in your controller:


```php
function startTest() {
	$this->Posts =& new MockPostsController();
	$this->Posts->constructClasses();
	$this->Posts->Email =& new MockEmailComponent();
	// no more emails from our tests!
	$this->Posts->Email->setReturnValue('send', true);
	// no more headers outputting csv files during our tests!
	$this->Posts->RequestHandler =& new RequestHandlerComponent();
	$this->testController = $this->Posts;
}
```


Being able to test your controllers but still utilize mocks to avert the annoying header changes and rendering problems can be very valuable. Testing is important, and if there's one thing going through the process of trying to test controllers has solidified, it's that **fat models** are always in style.