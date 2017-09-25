# Testing For XSS

If you're using PHP as your templating system, chances are you have faced at
least one instance of [cross-site scripting][1], or XSS. Since PHP doesn't
escape output by default, it's quite easy to fall into the trap of including
user-supplied data into your template without first escaping it. If you use
a templating language such as [Twig][2], you are usually protected by default.

The default templating language in CakePHP is &mdash; wait for it &mdash; PHP! While this
makes getting up and running fast and doesn't require one to learn a new templating
language, however it puts the onus of escaping output on the developer. (It's
worth noting that baked templates escape data by default.)

My startup app uses PHP for its templates, and I recently woke up in the middle
of the night with XSS on my mind. I thought, *What if I forgot to escape some
user output somewhere?* That could be bad. At my last job I had fixed several
vulnerable areas of the site that allowed script injection. As a precaution to prevent my
personal app from succumbing to the same fate, I decided to run through the site
to check and fix any spots I may have missed.

## Peace of mind is a test away

It's no secret I love testing. While this might not be the most appropriate
place to check for XSS, it was certainly the easiest. Perhaps one day I will have a
proper front-end testing setup.

The goal is simple: create records with hypothetical XSS, make sure it doesn't
appear in the HTML.

For the purpose of this article, we'll assume we have a very simple app where
users can keep track of beers they enjoy (**User** hasMany **Beer**), along with
an admin area for our administrators to view stats on users.

## Assume the user is evil

As developers, we have to unfortunately assume that users will be evil, however
we don't always create our fixture data with this in mind. To start, let's play
the role of an evil user. This user is trying hard to access our system and gain
access to all that sweet, sweet data. We should assume that every table a user
can write to will be an attempt at infiltration.

**tests/Fixture/UsersFixture.php**
```php
[
    'id' => 99,
    'username' => '<script>XSS</script>',
    'name' => '<script>XSS</script>',
    'favorite_brewery' => '<script>XSS</script>',
    'is_admin' => true,
]
```

**tests/Fixture/BeersFixture.php**
```php
[
    'id' => 99,
    'user_id' => 99,
    'name' => '<script>XSS</script>',
]
```

The `<script>XSS</script>` doesn't actually need to do anything, all we need to
do is test that it doesn't end up in our templates un-escaped. In a real world
application, you would want to include an "evil" fixture for every table.

## Find the XSS

In our hypothetical app, a user can visit `/beers/index` to see a list of their
favorite beers. Let's say the very simple template is the following:

**src/Template/Beers/index.ctp**
```php
<?php
$this->assign('header', $beer->name);
?>

<div class="row">
    <?= $user->name ?> enjoys drinking <?= $beer->name ?>.
</div>
```

This template has obvious XSS opportunities. Usually templates are a bit more
complicated than this, and contain a lot of user-supplied data, making it much
harder to just scan and fix.

Writing a test to find XSS here is easy, with the included benefit of becoming a
permanent member of your testsuite, adding to your never-ending arsenal of code
quality and security checks. As I said before, the test is simple: make
sure the unescaped data doesn't appear in our template.

**tests/TestCase/XssTest.php**
```php
namespace App\Test\TestCase;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestCase;

class XssTest extends IntegrationTestCase
{

    public function testForXss()
    {
        // log in as the evil user
        $this->session([
            'Auth.User' => TableRegistry::get('Users')->get(99)
        ]);

        // go to the page
        $this->get('/beers/index');
        $this->assertResponseOk();

        // assert the unescaped data is not there
        $this->assertResponseNotContains('<script>XSS</script>');
        $this->assertResponseContains('&gt;XSS&lt;');
    }
}
```

Since we haven't escaped our user's data, this test case will fail. Success,
XSS found! Let's fix it in the template. By wrapping **all** user data in
CakePHP's `h()` method, the data will be escaped. The [`h()` method][3] is simply
a global function that wraps PHP's `htmlspecialchars`.

**src/Template/Beers/index.ctp**
```php
<?php
$this->assign('header', h($beer->name));
?>

<div class="row">
    <?= h($user->name) ?> enjoys drinking <?= h($beer->name) ?>.
</div>
```

Horray, the test passes now! Better yet, we've fixed a vulnerability!

You might have noticed we included an assertion that
checks that the template indeed contains the escaped version of `>XSS<`. We do
this out of paranoia that something might have been typed wrong in the fixture,
which would cause the first assertion to pass, giving us a false positive.

## Building out our XSS test case

This test case is a perfect use-case for using PHPUnit's [data provider][4]
functionality. Let's refactor the test case to test other endpoints.
Our ficticious admin areas include user data, so those endpoints will need to be tested as
well.

**tests/TestCase/XssTest.php**
```php
namespace App\Test\TestCase;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestCase;

class XssTest extends IntegrationTestCase
{

    /**
     * @dataProvider pathProvider
     */
    public function testForXss($path)
    {
        // log in as the evil user
        $this->session([
            'Auth.User' => TableRegistry::get('Users')->get(99)
        ]);

        // go to the page
        $this->get($path);
        $this->assertResponseOk();

        // assert the xss is not there
        $this->assertResponseNotContains('<script>XSS</script>');
        $this->assertResponseContains('&gt;XSS&lt;');
    }

    public function pathProvider() : array
    {
        return [
            'Users Edit' => ['/users/edit'],

            'Beers Index' => ['/beers/index'],
            // editing an "evil" record
            'Beers Edit' => ['/beers/edit/99'],

            'Admin Users Index' => ['/admin/users/index'],
            // viewing an "evil" user from admin
            'Admin Users Index' => ['/admin/users/view/99'],
        ];
    }
}
```

Adding to this list as our app grows is pretty easy. Also, if we ever add additional
unescaped data to our tested endpoints, say `$user->favorite_brewery`, it'll cause a
failure before it reaches production.

Remember, user supplied data can come from other areas, such as query arguments,
so make sure to include tests for those as well. If this hypothetical app had a search endpoint
that included the user's search query within the template, adding it to our
data provider would be a good idea.

```php
'Beers Search' => ['/beers/search?name=<script>XSS</script>'],
```

## Not a silver bullet

While this isn't a silver bullet, it automates a lot of what would otherwise be
a laborious manual task. Embarrassingly enough, writing this test for my startup
app revealed a handful of areas that were open to XSS. It's pretty easy to miss,
but I feel much more confident now that this particular area of testing is more
automated than it was before. Aside from the actual security fixes, this exercise
has brought the consideration of moving to a more secure templating language
back to the forefront of my mind.

[1]: https://en.wikipedia.org/wiki/Cross-site_scripting
[2]: https://twig.symfony.com/
[3]: https://api.cakephp.org/3.5/function-h.html
[4]: https://phpunit.de/manual/current/en/writing-tests-for-phpunit.html#writing-tests-for-phpunit.data-providers