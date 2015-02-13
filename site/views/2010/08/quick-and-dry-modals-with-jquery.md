# Quick and DRY Modals with jQuery

Modals can be a nice way to present data with minimal page refreshes and in a way that sits well with your UI. In my case, I've always used them to load data via Ajax using jQuery. Something that's always bothered me with previous methods was that you would have to change something in your view, or worse, controller, depending on whether or not it was loaded using Ajax. The biggest thing that comes to mind is pagination. If you're using CakePHP's super simple Ajax pagination in your view


```php
$this->Paginator->options(array(
  'update' => '#content', 
  'evalScripts' => true
));
```

You'll run into problems as soon as you load your content into an Ajax window that's outside of the content div. Some solutions I've seen create a special view for the ajax content (not DRY), or worse, just *assume* that the content will be loaded using JavaScript with no fallback.
So how do you keep your application DRY and continue to use CakePHP's magic? Well, with some jQuery magic of course. (Let's assume you're using jQuery UI's dialog, okay?) First thing's first, we've got to wrap a modal function for use throughout our application. This function will take a regular dom element and turn it into an autoloading ajax content driven updating machine.

```js
/**
 * Makes a link open a jQuery modal dialog instead of redirect to a page 
 *
 * @param string id The element id
 * @param hash options jQuery dialog options
 */ 
42Pixels.modal = function(id, options) {
	// create a container if it doesn't exist
	if ($('#modal').length == 0) {
	  $('#wrapper').append('<div id="modal"></div>');
	}
	 
	// default jQuery.dialog() options
	var _defaultOptions = {
	  modal: true,
	  width: 700,
	  autoOpen: false,
	  height: 'auto'
	}
	 
	// use user defined options if defined
	var useOptions;
	if (options != undefined) {
	  useOptions = $.extend(_defaultOptions, options);
	} else {
	  useOptions = _defaultOptions;
	}
	 
	// we use the open event to automatically change the modal's div container's id to "content"
	useOptions.open = function(event, ui) {
	  // save the old content container for later
	  $('#content').attr('id', 'content-reserved');
	  // rename content so ajax updates will update content in modal
	  $('#modal').attr('id', 'content');
	}
	 
	// we need to use the close event to swap the id's back
	useOptions.close = function(event, ui) {
	  // rewrite the id's
	  $('#content').attr('id', 'modal');
	  $('#content-reserved').attr('id', 'content');
	};
	 
	// now let's handle the link that we want to turn into a modal
	$('#'+id).click(function(event) {
	  // remove old settings (other modals, etc)
	  $('#modal').dialog('option', 'buttons', {});
	  $('#modal').dialog('option', 'width', 700);
	  $('#modal').dialog('option', 'height', 'auto');
	 
	  // set options
	  modalOptions = $(this).data('modalOptions');
	  for (var o in modalOptions) {
	    $('#modal').dialog('option', modalOptions, modalOptions[o]);
	  }
	 
	  // stop link
	  event.preventDefault();
	 
	  // load the link into the modal
	  $('#modal').load(this.href, function() {
	    $('#modal').dialog('open');
	  });
	  return false;
	});
	 
	$('#'+id).data('modalOptions', useOptions);
	// attach jQuery modal behavior
	$('#modal').dialog({autoOpen:false});
}
```
 
Let me break this down. First thing we do is create a div container. I put one under my "wrapper" div. The next thing we do is set some default options and overwrite them with any options sent to the function. Pretty standard practice. This allows us to change the modal depending on the link, if we want to. Then, the cool stuff. We define the jQuery "open" and "close" events to simply swap our old "content" id with the new modal. Now any pagination or `JsHelper` generated links that used "content" as the updateable div will update in the modal. When the modal is closed, it puts everything back to where it was. Finally, we'll override the link's click event. This allows us to load Ajax automatically based on the link and open the modal after it's loaded.

Now generate this link in CakePHP:

```php
echo $this->Html->link('Modal content, if Js is enabled', '/', array('id' => 'modal-content'));
$this->Js->buffer('42pixels.modal("modal-content")');
```

Done. The HTML link will open in a modal and falls back to a regular link if it needs to.

To make this even more DRY, use the rel attribute on the HTML link instead

```php
echo $this->Html->link('Modal content, if Js is enabled', '/', array('rel' => 'modal'));
echo $this->Html->link('Another modal link', '/', array('rel' => 'modal'));
```

And attach the modal behavior to all those links using jQuery:


```js
// attach modal behavior to all links with rel=modal
$("[rel=modal]").each(function() {
  if ($(this).data('hasModal') == undefined) {
    if ($(this).attr('id') == '') {
      $(this).attr('id', 'link-'+new Date().getTime());
    }
    42pixels.modal($(this).attr('id'));
    $(this).data('hasModal', true);
  }
});
```

Quick, easy. CakePHP and jQuery pair well together.