# Testing a Subset of Methods Within a Test Case

**UPDATE**: @savant and I were chatting in #cakephp one night. Turns out he had something similar, and took some of my ideas and came up with a nice solution: [https://gist.github.com/1208800](https://gist.github.com/1208800)

We all know that testing can be really slow if you have a large app with a lot of fixtures. When dealing with this on a large test case file, it becomes a huge time waste to test the entire file each time when making a lot of little changes to a single method. My all-to-elegant method I've been using to speed this up is to simply comment out the functions I don't want to test at the time. No fixtures loaded, no test data inserted - good to go.

This fantastically professional method of mine was still just too slow for me (not to mention issues with comment blocks within test functions, testing just a couple of methods at a time, etc.). So I decided to write a bit of code that tells the test case manager to skip those methods altogether, effectively creating a "I just want to test these methods" option.


```php
function getTests() {
  $tests = parent::getTests(); 
  $testMethods = array_udiff($tests, $this->methods, 'strcasecmp');
  if (!isset($this->testMethods) || empty($this->testMethods)) {
    $this->testMethods = $testMethods;
  }
  if (!is_array($this->testMethods)) {
    $this->testMethods = array($this->testMethods);
  }
  if (isset($this->skipSetup) && $this->skipSetup) {
    $tests = array_udiff($tests, array('start', 'end'), 'strcasecmp');
  }
  if (empty($this->testMethods)) {
    return $tests;
  }
  $removeMethods = array_udiff($testMethods, $this->testMethods, 'strcasecmp');
  $tests = array_udiff($tests, $removeMethods, 'strcasecmp');
  $skipped = array_udiff($testMethods, $this->testMethods, 'strcasecmp');
  foreach ($skipped as $skip) {
    $this->_reporter->paintSkip(sprintf(__('Skipped entire test method: %s', true), $skip));
  }
  return $tests;
}
```

Place this in your test case class (assuming you have extended `CakeTestCase`). You can now add a `$testMethods` var as a string - the only method you want test - or as an array of methods you want to test. All methods *not* in the `$testMethods` var will be skipped, giving you a quick way to test just the methods you're working on.

If you're not extending Cake's `CakeTestCase` already, I suggest you do. At the very least it's beneficial in creating a centrailized place to add the `$fixtures` array when your tables expand. Who hasn't gotten a "missing table" error or two? (For those who haven't extended it before, remember to [ignore](http://www.simpletest.org/api/SimpleTest/UnitTester/SimpleTest.html#ignore) your child class if you do, otherwise you'll pay in some extra database setup time. It's also worth noting that you should [flush the registry](http://api13.cakephp.org/class/class-registry#method-ClassRegistryflush) right after your ignore statement so you don't accidentally access your default database.)

Oh, also: Please, please, please make sure to remove the `$testMethods` variable from your test case once you're done focusing on a single method. We wouldn't want you to commit a change that skips a bunch of tests, would we?

Oh, yeah, also also: I've folded this little method into my [ExtendedTestCase](/downloads/cakephp-extended-test-case-plugin) plugin, if you're interested.