# Debugging CakePHP Ajax calls with FirePHP

If you're writing a CakePHP application, one of the first things you should be doing when starting out is downloading DebugKit, the invaluable debugging plugin. If you don't have it, go install it here: [https://github.com/cakephp/debug_kit](https://github.com/cakephp/debug_kit)

Most web apps make use of Ajax here and there. And with Cake and jQuery basically doing the work for you, why wouldn't you? Debugging these requests can be a tricky nuisance, though, but that's where the awesomeness of DebugKit comes in. It comes with support for sending debug information to FirePHP, a Firefox extension that works in conjunction with Firebug (if you don't have Firebug, [download it](http://getfirebug.com/))to log Ajax information in real time to your browser.

### Installing FirePHP

To use FirePHP, you'll need to download the library. It's a quick download:

[http://www.firephp.org/HQ/Install.htm](http://www.firephp.org/HQ/Install.htm)

Extract the zip, take the FirePHPCore folder within the zip's lib folder, and drop it in your app's vendors folder.

Lastly, you'll need the [FirePHP](http://www.firephp.org/)extension for Firefox. After downloading it and restarting Firefox, you'll need to enable the Net panel in Firebug if you haven't already. Pop open Firebug, go to the Net tab and enable it.

### That's it

When your app performs an Ajax call, you'll now see all of the lovely information DebugKit has to share in your Firebug console, specific to that request.