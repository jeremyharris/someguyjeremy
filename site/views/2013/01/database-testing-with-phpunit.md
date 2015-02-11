# Database Testing with PHPUnit

Testing was first introduced to me through the [CakePHP](http://cakephp.org) framework. Like learning anything from a framework, I always like to dig a little deeper into the internals and learn as much about the process behind the magic as possible. When it comes to database testing, Cake makes it incredibly easy with automatically handling fixtures for you. Theirs is a custom system which works well for testing within the Cake environment.

I've recently been able to work on some smaller projects that don't justify a full stack framework. I love getting back to writing clean, framework-less code. When it comes to testing them, I found the territory a bit uncomfortable. While writing a simple test isn't bad at all, writing clean database tests in PHPUnit was a rough road to navigate.

*Full code for this post can be found here: [https://gist.github.com/4484784](https://gist.github.com/4484784)*

### A plan so simple even an idiot could devise it

I came up with a plan. Write code, read on writing DbUnit tests, write some DB tests. I decided ahead of time that it wouldn't be that hard.

The docs on PHPUnit are pretty good, in my opinion. The problem is, they are incomplete. I never found official documentation on things like `$this->at()`, and therefore its behavior. Was it a count based on the number of invocations of the method, or a count based on all invocations of all mocked methods? I suppose that's the problem of any good, large framework. After all, if you documented everything in the same book you're using to teach new users, you're bound to cut some features for brevity's sake.

After reading through official docs, I Googled a bit. I also dug intensely into the source code (for two reasons, the second being education) to try and learn what was going on. (PHPUnit is extensible, large, and well organized. With that comes 17 layers of abstraction you have to dig through to figure out what's actually happening.)

I didn't have much luck finding tutorials, so I just started writing. This tutorial assumes you have [PHPUnit](http://www.phpunit.de/manual/current/en/installation.html) running and have the [DbUnit](http://www.phpunit.de/manual/current/en/installation.html#installation.optional-packages) extension installed.

### FixtureTestCase

We'll start with a simple class that we'll extend for any test cases that will use fixtures. On the class we'll include a list of fixtures to load. We'll also store all of our fixtures under a `/fixtures` folder. I'm using XML fixtures because it's easy to dump test data from a working MySQL database to get you going.

```php
// we're loading the Database TestCase here
require 'PHPUnit' . DIRECTORY_SEPARATOR . 'Extensions' .
DIRECTORY_SEPARATOR . 'Database'  . DIRECTORY_SEPARATOR .
'TestCase.php';

class FixtureTestCase extends PHPUnit_Extensions_Database_TestCase {

	public $fixtures = array(
		'posts',
		'postmeta',
		'options'
	);

}
```

### Connecting to the database

This part is covered in the PHPUnit documentation, so I'll cover it briefly here. We basically add a necessary `getConnection` function which PHPUnit uses to get a connection to the test database.

```php
private $conn = null;

public function getConnection() {
	if ($this->conn === null) {
		try {
			$pdo = new PDO('mysql:host=localhost;dbname=test', 'root', '');
			$this->conn = $this->createDefaultDBConnection($pdo, 'test');
		} catch (PDOException $e) {
			echo $e->getMessage();
		}
	}
	return $this->conn;
}
```


I'm just connecting to an existing MySQL database called 'test'. You can connect to an in-memory database if you wanted, as described in the PHPUnit documentation.

### Loading fixtures

PHPUnit's database test cases also requires a `getDataSet()` function that is used to get the data to load. I like splitting my fixture xml files into separate files for each table, so I create a composite datasource that combines whatever fixtures we want to load. By defaut, we'll load everything in the `$fixtures` array.

```php
public function getDataSet($fixtures = array()) {
	if (empty($fixtures)) {
		$fixtures = $this->fixtures;
	}
	$compositeDs = new
	PHPUnit_Extensions_Database_DataSet_CompositeDataSet(array());
	$fixturePath = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'fixtures';

	foreach ($fixtures as $fixture) {
		$path =  $fixturePath . DIRECTORY_SEPARATOR . "$fixture.xml";
		$ds = $this->createMySQLXMLDataSet($path);
		$compositeDs->addDataSet($ds);
	}
	return $compositeDs;
}
```

This function iterates through our fixture array and loads the appropriate XML files, combining them into a single DataSet that PHPUnit can use to insert records.

There's also an argument there that we'll use later on to load specific fixtures. This is helpful for reloading test data within a test method. To do this, we'll add a simple helper function:

```php
public function loadDataSet($dataSet) {
	// set the new dataset
	$this->getDatabaseTester()->setDataSet($dataSet);
	// call setUp which adds the rows
	$this->getDatabaseTester()->onSetUp();
}
```

Within a test method, we can now create a new dataset and load its records on the fly (you'll see this used in an example below):

```php
// create a new dataset from fixtures
$ds = $this->getDataSet(array(
  'posts'
));
// loads the dataset and inserts records
$this->loadDataSet($ds);
```


### Cleaning up the database

PHPUnit does not handle creating tables for you, so we need to create and drop tables as the test progresses. For simplicity, I create and drop the tables on each test method. This could be improved upon by keeping a list of loaded fixtures and just `CREATE`/`DROP` tables based on that.

```php
public function setUp() {
	$conn = $this->getConnection();
	$pdo = $conn->getConnection();

	// set up tables
	$fixtureDataSet = $this->getDataSet($this->fixtures);
	foreach ($fixtureDataSet->getTableNames() as $table) {
		// drop table
		$pdo->exec("DROP TABLE IF EXISTS `$table`;");
		// recreate table
		$meta = $fixtureDataSet->getTableMetaData($table);
		$create = "CREATE TABLE IF NOT EXISTS `$table` ";
		$cols = array();
		foreach ($meta->getColumns() as $col) {
			$cols[] = "`$col` VARCHAR(200)";
		}
		$create .= '('.implode(',', $cols).');';
		$pdo->exec($create);
	}

	parent::setUp();
}

public function tearDown() {
	$allTables =
	$this->getDataSet($this->fixtures)->getTableNames();
	foreach ($allTables as $table) {
		// drop table
		$conn = $this->getConnection();
		$pdo = $conn->getConnection();
		$pdo->exec("DROP TABLE IF EXISTS `$table`;");
	}

	parent::tearDown();
}
```


You'll notice that I make every column a `VARCHAR` type when creating the tables. The fixures I'm using don't support types, but I'm sure it could be written in fairly easily with some `is_*` checks.

### Database testing

Awesome, now that we've got everything automated we can write a test case. Here, I'll test a theoretical WordPress install that has fixtures for common WordPress tables.

```php
<?php
require 'FixtureTestCase.php';

class MyTestCase extends FixtureTestCase {

	public $fixtures = array(
		'posts',
		'postmeta',
		'options'
	);

	function testReadDatabase() {
		$conn = $this->getConnection()->getConnection();

		// fixtures auto loaded, let's read some data
		$query = $conn->query('SELECT * FROM posts');
		$results = $query->fetchAll(PDO::FETCH_COLUMN);
		$this->assertEquals(2, count($results));

		// now delete them
		$conn->query('TRUNCATE posts');

		$query = $conn->query('SELECT * FROM posts');
		$results = $query->fetchAll(PDO::FETCH_COLUMN);
		$this->assertEquals(0, count($results));

		// now reload them
		$ds = $this->getDataSet(array('posts'));
		$this->loadDataSet($ds);

		$query = $conn->query('SELECT * FROM posts');
		$results = $query->fetchAll(PDO::FETCH_COLUMN);
		$this->assertEquals(2, count($results));
	}

}
```

Mocking your connection becomes easy as well, since we have a method `getConnection()` that returns a PDO connection to the test database.

Now that we've got a mockable connection and some automated fixtures working, we're free to test against the database. Go forth and test.

*Full code for this post can be found here: [https://gist.github.com/4484784](https://gist.github.com/4484784)*