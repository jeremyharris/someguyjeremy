# Security Component Timeout

**Update**: As of CakePHP 2.x, this hack is no longer necessary due to the addition of `SecurityComponent::$csrfExpires`. You can now easily set the CSRF expiration time using that setting on the component, either by adding `$this->SecurityComponent->csrfExpires = '+1 hour';` on the controller or setting the 'csrfExpires' key in the array settings.

I recently had a client come to me and tell me that he was consistently logged out of a CakePHP app after a few minutes. I immediately thought there was a problem with the session and began my debugging process. I tested it on several machines and lo, was never timed out early.

So, the session was okay. Apparently my client's definition of "logged out" was different than mine, in that he wasn't actually being logged out... After ruling out bad sessions, what's left? The good old Security component. If you dig around, you'll notice the security form expiration time is dependent on a method on the Security class.


```php
function inactiveMins() {
	switch (Configure::read('Security.level')) {
		case 'high':
			return 10;
		break;
		case 'medium':
			return 100;
		break;
		case 'low':
		default:
			return 300;
			break;
	}
}
```

Even with a security level of "low," the form will blackhole after 5 minutes. In many cases, this isn't exactly enough time.
### Never modify the core

We all know (all of us, *right*?) that modifying core libraries, especially a full-stack framework like CakePHP, is a terrible idea. One thing I continually tell people is that Cake is just PHP, so if you treat it as such it becomes less mysterious. If you've dug into how the SecurityComponent works with the FormHelper to create secure forms, the answer becomes pretty obvious: just modify the `_Token` session key. After all, this is where the FormHelper gets the expiration date that it passes back to the SecurityComponent on the next request.

All that's needed is a simple addition to your AppController's `beforeRender`:


```php
public function  beforeRender() {
	// increase security form timeout
	if ($this->Session->read('_Token')) {
		$token = unserialize($this->Session->read('_Token'));
		// I'll see your 5 minutes, and raise you 25 more
		$token['expires'] = strtotime('+30 minutes');
		$this->Session->write('_Token', serialize($token));
	}
}
```

Easy. No core files modified, it's easily maintainable and now customizable.