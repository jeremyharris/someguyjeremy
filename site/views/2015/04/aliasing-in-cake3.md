# Aliasing in CakePHP 3.0

I finally got around to starting a CakePHP 3.0 project. One of the first
things I do when creating a new Cake project is alias the FormHelper, so
I can add default classes, remove HTML5 validation, etc. In the past,
you would alias helpers via the `className` key in your configuration.

## The Olde Timey Way

```php
<?php
namespace App\Controller;

use Cake\Controller\Controller;

class AppController extends Controller
{
	public $helpers = [
		'Form' => [
			'className' => 'MyForm'
		]
	];
}
```

This worked well for a while. (Fun fact, I actually helped contribute
this feature into the core back in the day!) This little bit of extra
configuration allows you to use `$this->Form` in your views to access
your Helper, removing the necessity to update all of your views.

Since Cake 3 uses namespaces now, I thought there might be a way to
achieve this without the configuration. And there is.

## Namespaces

If you're not familiar with namespaces, you should be. They've been
around for a while. They allow you to do some pretty neat things, one of
which we'll do here.

Instead of adding that configuration, why not just create your own
`FormHelper` class? After all, Cake will look for a class in your App
before it loads its default ones. Without namespaces, you can't
accomplish this because you couldn't extend Cake's helper like this: 
`FormHelper extends FormHelper`. Hence the `className` aliasing.

With namespaces, however, this is easily achieved like so:

```
<?php
namespace App\View\Helper;

use Cake\View\Helper\FormHelper as CakeFormHelper;

/**
 * Form Helper
 */
class FormHelper extends CakeFormHelper
{
	/**
	 * Initializes input field
	 *
	 * Removes HTML5 validation
	 *
	 * @param string $field Field name
	 * @param array $options Options
	 * @return array
	 */
	public function _initInputField($field,	$options = array())
	{
		$options = parent::_initInputField($field, $options);
		unset($options['required']);
		return $options;
	}
}
```

Here we just use PHP's aliasing ability to bring in Cake's `FormHelper`
as `CakeFormHelper`, allowing us to extend it without a naming conflict.
Without any additional configuration in your controller, Cake's class
loader will find this `FormHelper` and use it by default.

Pretty neat.
