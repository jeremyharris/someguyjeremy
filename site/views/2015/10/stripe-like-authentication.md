# Stripe-like Authentication in CakePHP

[Stripe][1] has a very simple approach to authentication: a single API key sent
over [HTTP Basic Authentication][2]. Replicating this authentication using
CakePHP is pretty simple, as I'll outline below.

## Create and store a key

The first thing to do is add an `api_key` field to your users table (or whatever
table you'll be authenticating against).

```
$ bin/cake bake migration AddApiKeyToUsers api_key:string
$ bin/cake migrations migrate
```

Now that we've got a field to store the key, we can add a couple of simple key
generation methods to the `src/Model/Table/UsersTable.php`.

```php
<?php
namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\ORM\Entity;
use Cake\Event\Event;
use Cake\Utility\Security;
use Cake\Utility\Text;

class UsersTable extends Table
{

    public function beforeSave(Event $event, Entity $entity, $options)
    {
        if ($entity->isNew()) {
            $entity->api_key = $this->generateApiKey();
        }
        return true;
    }

    public function generateApiKey()
    {
        return Security::hash(Text::uuid());
    }

}
```

This is some very boilerplate key generation code, and adds a new API key to any
new users.

## Let users rotate their key

We can give the user the ability to rotate their key by adding a very simple
controller action. I usually throw a `Form::postLink` or an AJAX link for the
user, so they can rotate their key from their dashboard.

```php
public function rotateApiKey($id = null)
{
	$user = $this->Users->get($id, [
		'contain' => []
	]);
	if ($this->request->is(['patch', 'post', 'put'])) {
		$user = $this->Users->patchEntity($user, [
			'api_key' => $this->Users->generateApiKey()
		]);
		if ($this->Users->save($user)) {
			$this->Flash->success(__('A new key has been generated.'));
		} else {
			$this->Flash->error(__('The user could not be saved. Please, try again.'));
		}
	}
	return $this->redirect($this->referer());
}
```

## Authenticate using our key

Now that we can store an API key, automatically generate one when a new user is
created, and give them the ability to rotate it, let's get to the authentication.
Cake's authentication system is quite extensible, and this is a great example
of how we can plug in our simple API key authentication.

Create a new Authentication adapter in `src/Auth/ApiKeyAuthenticate.php`. We'll
extends Cake's BasicAuthenticate adapter and just re-purpose how it gets users.

```php
<?php
namespace App\Auth;

use Cake\Auth\BasicAuthenticate;
use Cake\Network\Request;

class ApiKeyAuthenticate extends BasicAuthenticate
{

    public function getUser(Request $request)
    {
        $username = $request->env('PHP_AUTH_USER');

        if (!is_string($username) || $username === '') {
            return false;
        }
        return $this->_findUser($username);
    }

}
```

Cake's [BasicAuthenticate::getUser][3] method usually checks for the basic
authentication header to send both the user and password, but like Stripe, we'll
just be using the username (API key) to authenticate.

Now, all we need to do is use our new Authentication adapter to authenticate our
users with. This is pretty standard basic auth stuff as well, except we're using
our API key as the username. When we configure our Auth component:

```php
$this->loadComponent('Auth', [
	'storage' => 'Memory',
	'unauthorizedRedirect' => false,
	'loginAction' => '/',
	'authorize' => false,
	'authenticate' => [
		'ApiKey' => [
			'fields' => [
				'username' => 'api_key',
			]
		]
	]
]);
```

## API key authentication

That's it! We can now use basic authentication and authenticate users using their
API keys. As with all basic authentication, you should always use HTTPS otherwise
users will be transferring their keys plaintext over the network.

```
$ curl https://api.example.com/resource \
   -u 748173c1c9ad2643ca45db66ebd715a2e11646b4:
```

[1]: https://stripe.com
[2]: https://en.wikipedia.org/wiki/Basic_access_authentication
[3]: http://api.cakephp.org/3.1/source-class-Cake.Auth.BasicAuthenticate.html#68-83