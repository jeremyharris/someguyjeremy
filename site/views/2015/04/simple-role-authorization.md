# Simple Role-based Authorization in CakePHP 3.0

When prototyping applications, one of the things I usually do is add
role based authorization. I usually hook up to the database and do the
typical `User -> Group` relationship, however this time I just threw a
role column onto the users table and decided to use constants. This has
its advantages and disadvantages, but that's out of scope for this
article.

## Adding a Role Column

CakePHP's built-in migrations plugin and baking capabilities are super
nice. Assuming we don't have a "role" column yet, creating one is a
simple as:

```
$ bin/cake bake migration AddRoleToUsers role:string
$ bin/cake migrations migrate
```

Couldn't be simpler.

## Creating Some Roles

Since we're using constants to define roles, the best
place to put them would be the `UsersTable`. Using some of PHP's newer
functionality of array constants (PHP 5.6), we can accomplish this without
worrying about typos.

```php
<?php
namespace App\Model\Table;

use Cake\ORM\Table;

class UsersTable extends Table
{
    const ROLE_ADMIN = 'admin';
    const ROLE_CUSTOMER_SERVICE = 'customer_service';
    const ROLES = [
        '' => 'User',
        self::ROLE_ADMIN => 'Administrator',
        self::ROLE_CUSTOMER_SERVICE => 'Customer Service Rep'
    ];
}
```

Pretty clean. The `UsersTable::ROLES` constant can be used in templates
and even to validate using Cake's built-in "inList" validation rule,
using `array_keys(self::ROLES)` as the list to validate against.

## Adding Prefix Routing

Now that we have our roles, adding prefixes dynamically for these roles
is easy. In our routes file, loop over and add some inflection routing:

```php
<?php
use App\Model\Table\UsersTable;

foreach (UsersTable::ROLES as $roleId => $roleName) {
    if (!empty($roleId)) {
        Router::prefix($roleId, function ($routes) {
            $routes->fallbacks('InflectedRoute');
        });
    }
}
```

Skipping the blank "User" role, adding prefixes will allow us to use
them to authorize against the user's role. Any future roles will get a
prefix created automatically.

## Authorizing

Using the `AuthComponent`, we can set up some quick authorization to
check against the current user's role and decide if we should let them
past. In our `AppController`, add some simple checks:

```php
<?php
namespace App\Controller;

use Cake\Controller\Controller;

class AppController extends Controller
{
    public function initialize()
    {
        $this->loadComponent('Auth', [
            'authorize' => [
                'Controller'
            ]
        ];
    }

    public function isAuthorized($user)
    {
        $prefix = $this->request->param('prefix');
        if ($prefix) {
            return $user['role'] === constant('App\Model\Table\UsersTable::ROLE_' . strtoupper($prefix));
        }
        return true;
    }
}
```

The above `isAuthorized` method is called because we told the
`AuthComponent` to authorize against the controller. If the user is
trying to access a prefixed route, we simply check if they have the role
for the prefix they are accessing. If they are trying to access an
unprefixed route, they're a "User" and should be able to see the page.

## Conclusion

While this code doesn't look *much* different than it might in CakePHP
2.x, it does have a cleanliness to it that it wouldn't have in 2.x. We
can also assume that the version of PHP is higher and can therefore take
advantage of some of PHP's newer functionality.

You might notice that it doesn't allow for inherited permissions. It
would not be hard to add a comparison method to the `UsersTable` to
determine if certain roles could access other prefixed routes.

## Bonus

A little bonus, some prefix-based elements allow you to create, say, navigation
for certain roles with fallback to the original.

`$this->element('navigation');` checks `Template/Element/Admin/navigation` first,
(if the logged-in user has the role "admin") falling back to
`Template/Element/navigation` if none is found.

```php
<?php
namespace App\View;

use Cake\View\View;
use Cake\View\Exception\MissingElementException;
use Cake\Utility\Hash;
use Cake\Utility\Inflector;

class AppView extends View
{

    public function element($name, array $data = array(), array $options = array()) {
        $user = $this->get('authUser'); // your user view var
        if ($user && Hash::get($user, 'role')) {
            $roleElement = Inflector::camelize(Hash::get($user, 'role')) . DS . $name;
            try {
                $element = parent::element($roleElement, $data, $options);
            } catch (MissingElementException $e) {
                $element = parent::element($name, $data, $options);
            }
        } else {
            $element = parent::element($name, $data, $options);
        }
        return $element;
    }

}
```