# Queued Events in CakePHP

One of my favorite aspects of Cake is that it doesn't get in your way. This is
evident in the vast array of plugins available that provide extra functionality
in their own opinionated way. Find one you like, and go with it!

In this post, I'll discuss a common practice in modern web apps: a queue system.

A queue system defers execution of code, allowing a "worker" to later pick up
the job and run it, usually on a separate system. This allows your web application
to skip laborious tasks that would otherwise slow the user experience.

Since Cake doesn't tell us how to make jobs, or how to queue, we're allowed the
freedom to pick from the [Awesome List][1] and create our own wrappers. For
this article, I'll pick one I've had success with, @savant's queuesadilla plugin,
`josegonzalez/cakephp-queuesadilla`.

## Step 0

Install the plugin with composer and load it into your application's bootstrap:

    $ composer install josegonzalez/cakephp-queuesadilla
    $ bin/cake plugin load Josegonzalez/CakeQueuesadilla

## Step 1

Now that we've got the plugin, we need to set up some quick configuration. If
you look at the [php-queuesadilla][2] docs<sup>1</sup> you'll find a whole host of options
for configuration engines. I like Redis and since my app is already using
it for caching, setting it up for queues was a breeze.

**config/queuesadilla.php**

```php
use josegonzalez\Queuesadilla\Engine\RedisEngine;

return [
    'Queuesadilla' => [
        'default' => [
            'engine' => RedisEngine::class,
            'host' => env('CACHE1_HOST'),
            'port' => env('CACHE1_PORT'),
        ]
    ]
];
```

And load/consume the configuration in bootstrap:

**config/bootstrap.php**

```php
use Josegonzalez\CakeQueuesadilla\Queue\Queue;
Configure::load('queuesadilla', 'default', false);
Queue::config(Configure::consume('Queuesadilla'));
```

## Step 2

Once you start the Redis server, we're ready to queue! That was fast! All we
need to do is start the worker:

    $ bin/cake queuesadilla

Remember, a worker is what picks up queued jobs and executes them.

*Note: The plugin [suggests that you manually disconnect][4] from the database to
prevent timeouts. My personal worker shell does this as well.*

## Step 3

If you're like me, you are already using the [CakePHP Event system][3]. A
common use-case for events is dispatching a `User.registered` event when a new
user is created.

**src/Model/Table/Users.php**

```php
public function afterSave($event, $entity, $options)
{
    if ($entity->isNew()) {
        $event = new Event('User.registered', null, ['id' => $entity->id]);
        EventManager::instance()->dispatch($event);
    }
}
```

If we have a listener that sends a welcome email, and another that records statistics
somewhere, and a third that posts to Twitter that we have a new user registered
on our awesome app, user registration becomes a very heavy process. Even with
events, this still happens on the same request, slowing the user's experience.

But what if we **queued this event** into our queue system? All of these processes
would be offloaded to the worker and the user would see that their registration
happened instantaneously. Assuming the worker wasn't busy, the jobs would be
picked up immediately and the user would receive their welcome email, our stats
would send, and the Twitter-verse would be made known that we're one user
closer to our billion-dollar IPO.

While we could rewrite the listeners to queue their work instead, that requires
a lot of refactoring. I've got a different idea.

## Step 4

Let's queue our events!

In my personal app, I have a `QueueManager` class. This class takes an event
and queues it for later dispatching. The listeners don't need to be modified,
because they receive the same event<sup>2</sup> as if it were dispatched!

**src/Queue/QueueManager.php**

```php
<?php
namespace App\Queue;

use Cake\Event\Event;
use Cake\Event\EventManager;
use josegonzalez\Queuesadilla\Job;
use Josegonzalez\CakeQueuesadilla\Queue\Queue;

/**
 * QueueManager
 *
 * Like EventManager, but for Queues. Use QueueManager to queue regular
 * Events into a proper job queue that are fired when the worker runs
 */
class QueueManager
{

    /**
     * Places an event in the job queue
     *
     * @param Event $event
     * @param array $options
     * @return void
     */
    public static function queue(Event $event, array $options = [])
    {
        Queue::push(
            '\App\Queue\QueueManager::dispatchEvent',
            [get_class($event), $event->getName(), $event->getData()],
            $options
        );
    }

    /**
     * Constructs and dispatches the event from a job
     *
     * ### Data array
     * - 0: event FQCN
     * - 1: event name
     * - 2: event data array
     *
     * @param Job\Base $job Job
     * @return void
     */
    public static function dispatchEvent($job)
    {
        $eventClass = $job->data(0);
        $eventName = $job->data(1);
        $data = $job->data(2, []);

        $event = new $eventClass($eventName, null, $data);
        EventManager::instance()->dispatch($event);
    }
}
```

Typically, `Queue::push()` takes a callable as the first argument. If we were to
refactor our listeners like I talked about above, each one would need a callable
for it's work. Instead, using this class, there's only ever one callable:
`dispatchEvent`. It takes the job data that was queued to re-create the event
and dispatch it to the `EventManager`.

Now, we only have to modify areas where we want to *queue* events instead of
*dispatch* them immediately:

**src/Model/Table/Users.php**

```php
// group this with the other "use" statements
use App\Queue\QueueManager;

public function afterSave($event, $entity, $options)
{
    if ($entity->isNew()) {
        // event setup did not change!
        $event = new Event('User.registered', null, ['id' => $entity->id]);
        QueueManager::queue($event);
    }
}
```

It's worth noting this `QueueManager` dispatches everything to the global event
manager and without a subject. Subjects usually have state, and when your worker
picks it up the state could change. This is the same reason I pass an ID in my
event data rather than the entity itself, as user data could change before the
job is fired. As for the global event manager, using it makes it easy
for me to understand what listeners will pick up the event as they are all set
up in bootstrap.

Since the `QueueManager` passes options along, we can do anything that the
queuesadilla plugin can do, like delay jobs:

```php
$event = new Event('User.tomorrow', null, ['id' => $entity->id]);
QueueManager::queue($event, [
    'delay' => 60 * 60 * 24 // dispatches this event "tomorrow"
]);
```

----

I have found that I really like this solution, and it works very well for my
application, [FollowFox][5]. I can queue events quite easily, delay them, and
my `QueueManager` also has additional methods to help me test that events were
queued.

What's better, changing queue engines is easy as the plugin offers an easy way
to do so. Or, if you want a different queue plugin entirely, you only have to
adapt the `QueueManager` to work with how it pushes jobs into the queue.

<sup>1:</sup> <small>`josegonzalez/php-queuesadilla` is the framework-agnostic library that `josegonzalez/cakephp-queuesadilla` uses</small>

<sup>2:</sup> <small>Almost the same event. The `QueueManager` doesn't use a subject.</small>

[1]: https://github.com/FriendsOfCake/awesome-cakephp#queue
[2]: http://josegonzalez.viewdocs.io/php-queuesadilla/
[3]: https://book.cakephp.org/3.0/en/core-libraries/events.html
[4]: https://cakephp-queuesadilla.readthedocs.io/en/latest/configuration.html
[5]: https://followfox.com