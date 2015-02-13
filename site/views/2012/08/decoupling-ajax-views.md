# Decoupling Ajax Views

CORE (our member management app) has always heavily used ajax to update areas of the page without bothering the user with a refresh. This is nice on the user's end, but on the developer's end can often be a pain. These areas included multiple paginated lists on a single page, ajax tab content, modal content, etc. While the builtin CakePHP support for automatically creating ajax pagination is nice, it didn't do everything I wanted and too tightly coupled views. We never used CakePHP's built in ajax pagination because of this, but for simplicity's sake I'll illustrate the problem using CakePHP's built in features.

### Coupled Views

Using ajax-powered paginated lists, a page might look like this:

```php
><div id="content"> <!-- produced in the layout -->
  <div id="lists"> <!-- produced in the view -->
    <div id="list_1"> <!-- a list produced using requestAction or ajax -->
    <?php
    $url = '/some/url';
    echo $this->requestAction($url, array('return'));
    ?>
    </div>
    <div id="list_2"> <!-- another ajax list -->
    <?php
    $url = '/other/url';
    echo $this->requestAction($url, array('return'));
    ?>
    </div>
  </div>
</div>
```

`/some/url` and `/other/url`would have ajax pagination. The Cake way of doing this is to point to a DOM id to update, in this case, `#list_1` and `#list_2`. Theseviews now become coupled to the parent view. The pagination links in the child view would now need to know the DOM id it is going to update.As soon as you move one of those lists somewhere else, the ajax pagination fails unless the exact DOM id is used.
Slowly, I began to recognize that the more the views were unaware of what parent view or layout, the more DRY and easy setting up automatically updating areas became. Not having to explicitly define a container id allowed the content to be loaded into any "parent" (be it a modal, tab, or just a regular container on a page) and auto update.

### A Better Pattern Emerged

With these things in mind, I decided to look at things differently. A new pattern emerged:

> A container holds a certain url's content. That content is either refreshed or a new URL's content is loaded into it.

Discovering this simple pattern led me to the realization that a DOM element should be "attached" to a URL.When that container was told to update, it would run a request using the attached URL and populate itself. If it was told to update *to* a certain URL, it would update its attached URL to the new one so future updates update the same content. The best way I felt to represent this was to use the [new arbitrary HTML `data-` attribute](http://www.whatwg.org/specs/web-apps/current-work/multipage/elements.html#embedding-custom-non-visible-data-with-the-data-*-attributes). Containers now only needed the attribute added with the url it currently held, *outside of the view it was loading*. Following this pattern, the new HTML looks something like this.

```php
<div id="content" data-update-url="/">
  <div id="lists"> <!-- produced in the view -->
    <?php $url = '/some/url'; ?>
    <div id="list_1" data-update-url="<?php echo $url; ?>">
    <?php echo $this->requestAction($url, array('return')); ?>
    </div>
    <?php $url = '/some/url'; ?>
    <div id="list_2" data-update-url="<?php echo $url; ?>">
    <?php echo $this->requestAction($url, array('return'));  ?>
    </div>
  </div>
</div>
```

This makes entirely more sense. The parent view tells the container what content it's populated with. The child view, that is the view produced by `requestAction` or ajax, is no longer coupled to any other view. It doesn't care where it was loaded, or even if it was loaded by ajax. All ajax actions simply update the closest container that has the`data-update-url` attribute. It should then update the url to whatever content was pulled. The code to find what container to update is simple:

```js
// after pagination request - where `this` is the anchor
var container = $(this).closest('[data-update-url']);
container.html(data);
container.attr('data-update-url', this.href);  
```

The good thing about this is that it trickles up the DOM until it finds a container to update. At the very least, the layout's main container will be used. A good use case is something like this:

> A container holds page 1 of a list. User clicks to the next page, which updates the container with content. It then looks for the closest container to update, updates it, then sets the new url. Now that we're on the second page, the user clicks to edit an item. A popup comes up, they finish, and close it. The popup's "afterclose" event looks for the closest container (from where it was launched) and updates it with whatever URL it's set to, in this case, page 2 of our list. The list now reflects the edit and is still on page 2.

This pattern makes much more sense, creates less coupling, and is easier to implement on the developer's side. Creating multiple updating lists becomes a breeze, and the views could now be used anywhere without worries of losing ajax pagination because it relied on a DOM id.