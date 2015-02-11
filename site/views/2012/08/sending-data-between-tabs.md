# Sending Data Between Tabs

I was recently presented with the challenge of implementing a sort of socket, but only between two browser windows. Since the communication was between just the local browser windows and didn't involve a server, I didn't think websockets or involving a server would be necessary.

Having been a pretty solid ActionScript programmer in the past, my immediate thought was to create a small Flash file that would serve as the connector, since it already had a [class](http://help.adobe.com/en_US/FlashPlatform/reference/actionscript/3/flash/net/LocalConnection.html) dedicated to this. But I didn't feel like diving back into ActionScript for such a small task, not to mention having to include another file on my pages, so I decided to figure out how to do it in JavaScript. Turns out the answer was pretty obvious and stated in this [SO](http://stackoverflow.com) post - [use cookies](http://stackoverflow.com/questions/4079280/javascript-communication-between-browser-tabs-windows/4079423#comment16175389_4079423). Cookies are shared locally within a browser, so it seemed like a nice solution. I decided to take the idea and make it work in the way we're all familar with JavaScript, callbacks.

### LocalConnection

LocalConnection allows you to trigger callbacks between two tabs. Very simply, it polls a cookie for a new event. If an event was sent from a different connection, a previously set callback is triggered.

```js
var t = new LocalConnection({
  name: 'mycookiename'
});
// start listening
t.listen();
```

This browser tab is now listening for new events. We can add a callback very simply. In the same tab, add a callback:

```js
t.addCallback('log', function(msg) {
  document.getElementById('log').innerHTML += msg+'<br />';
});
```

Now, from a different tab, you can trigger this event by using the `send()` method. Make sure the connection has the same name.

```js
var t = new LocalConnection({
  name: 'mycookiename'
});
// log some stuff!
t.send('log', 'Come at me bro');
```

The 'log' event is picked up by the listening tab (the first one in the example), and triggers the attached callback. And now you can communicate between tabs.

### How it works

The process is very simple. LocalConnection creates a cookie that it uses to pass data back and forth. The data is just the event that was triggered along with the arguments passed to it. It also includes a unique identifier so connections don't intercept their own callbacks. After the callback is triggered the cookie is reset and gets ready to accept another trigger. It has some limitations, however. For example, because the data is stored in a cookie, all arguments are sent to the callback function as strings.
The use for something like this is probably not highly sought after, however, if you're looking for something very specific like this it works great. Grab it on [github](https://github.com/jeremyharris/LocalConnection.js).