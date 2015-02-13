# Testing Quirks

In my travels across the desert plains that are unit tests, I've discovered a few quirks I thought I'd share here.

### Equal !== Identical

The above is obivous, but because my testing knowledge started with the CakePHP docs and the included tests that came with the software, I wrote most of my tests using `assertEqual` and thought it would be sufficient. I pulled some data, sorted it, and asserted it. Pass!

But wait, I debugged the code and it doesn't match! What gives? Well, it's `assertEqual`. When you assert the following:


```php
$this->assertEqual(true, array(2));
```

You'll find that it passes. Well, in PHP, the following is true:


```php
true == array(2);
```

`assertEqual` uses the equal operator (which makes sense but it didn't occur to me at the time), while `assertIdentical` uses the identical operator (===). Things like this pass when I didn't expect them to.


```php
$arr = array(1, 3, 2);
$result = sort($arr);
$expected = array(1, 2, 3);
$this->assertEqual($result, $expected);
```

This is a stupid programming language mixup on my part. `sort()` returns a boolean instead of the sorted array (like it would in some other languages). Then the equal assertion passes as `true == array(1,2,3)`. So, use `assertIdentical` instead. It'll fail because a boolean doesn't equal the array.


```php
$arr = array(1, 3, 2);
sort($arr);
$result = $arr;
$expected = array(1, 2, 3);
$this->assertIdentical($result, $expected);
```

The above will pass using with the assertion we actually attended.

### setReturnValue Once And Only Once

I've been trying out `setReturnValue` for testing some permissions here and there. I've found that it can only be set once. According to the SimpleTest docs, to set it more than once you'll need `setReturnValueAt`.

What if I don't know the number of times a function will be executed?
**-or-**
What if I just want to change it permanently for the time being?

Tough luck. Hopefully the move to PHP unit will address some of these issues. I suppose I should start reading up on those docs.
### Lesson Learned

Read up on all the docs for SimpleTest and, if you're me, stop mixing up your languages.