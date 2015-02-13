# Queue Emails Quick

When you're dealing with a large program, you're usually dealing with a large amount of emails. The problem with sending out, say, 200 emails in one action is two fold. The first issue is server load. The second is the user's experience.

Download it here: [https://github.com/jeremyharris/queue_email](https://github.com/jeremyharris/queue_email)

### Psst! Emails, it's your Queue!

My goal was to make a plugin that allows you to automatically queue emails in the database (if you want to) and send them out late using a CRON job or scheduled task with as little change to your application as possible. These tasks, of course, are completed using CakePHP's awesome shell. You know, bake? It utilizes CakePHP's EmailComponent to do most of the work, so any updates it gets, this plugin gets.

### Flexibility is key

One of my goals when I build plugins is to make them flexible. Here are some of the things you can do with the QueueEmail plugin:

**Easy to enable**

Download the plugin, run schema create, search and replace the old Email component references. No need to change how your controller sends email, sans the name of the component. There are alternatives to replacing the Email component references - another post, perhaps. Sending SMTP? Not a problem.


```
cake schema create QueueEmail.queue
```

```php
var $components = array('QueueEmail.QueueEmail');
```


**To queue or not to queue**

Lots of plugins I've seen force you to queue all of your emails. Set the `queue` var on the component to `false` and BAM! no more queue.


```php
$this->QueueEmail->queue = false;
```

**But I want to see how many emails are waiting to be sent!**

Okay. I added a simple management console of sorts. Okay, so it's a glorified baked template. But it's there! I'm planning on adding the functionality to send queued emails directly from the console.

**Reduce server load**

When you send 1000 users a 10mb attachment, it becomes an issue for your IT manager. Not to mention your server. Slowing the server slows your app, and we don't want that, right? The QueueEmail plugin allows you to specify how many emails you want to send out at a time. Let's say your server can handle 50 every 5 minutes. Using the shell you can specify a CRON job to handle this easily, and the best part is that it's separate from your app!


```
/path/to/site/cake/console/cake -app "/path/to/site/app" queue_sender send -batchSize 100;
```

**Improve user experience**

In large applications, it's often important to send large amounts of emails due to a user's actions. This can cause a delay before the user sees the next page. In my opinion, that's an issue. Queuing emails fixes this. Afterall, they're now just writes to a database!

### Future

If you follow any of my plugins, one thing you'll realize is that I always have plans for them. I already mentioned one for this plugin. Another idea I've had is to create some sort of email job queue. For example, I want to email all of my users if their account is going to expire. Implementing this sort of thing shouldn't be hard. I've yet to decide if this is in the scope of the plugin.


Find out more information (and usage) by visiting the plugin's download site. Enjoy!