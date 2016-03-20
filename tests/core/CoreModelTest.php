<?php

class CoreModelTest extends PHPUnit_Framework_TestCase
{
	function __construct()
	{
		$this->model = new Model;
		$this->model->tableName = "temp_table_test";
	}

	public function testCreateTable()
	{
		$value = $this->model->createTable($this->model->tableName, [
																		"id" => "INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY", 
																		"created" => "DATETIME", 
																		"updated" => "DATETIME", 
																		"name" => "VARCHAR(255)", 
																		"lock_me" => "VARCHAR(255)",
																		"sent" => "INT(11) NOT NULL DEFAULT 0",
																		"UNIQUE KEY unique_id" => "(id)"
																	]);
		$this->assertTrue($value);
	}

	public function testInsert()
	{
		// We are going to insert 2000 rows
    	for ($i=0; $i < 2000; $i++) {
    		$this->model->insert( ["name" => uniqid()], true );
    	}

    	// To check this has worked we need to get the count of the table
    	$count = $this->model->count();
    	$this->assertEquals('2000', $count);
	}

	public function testInsertUpdate()
	{
		$this->model->primaryKey = "test"; // Hack so we can get the update to work with our primaryKey for the test table
		$value = $this->model->insertUpdate( ["id" => 1, "name" => "Rory"] );
		$this->assertTrue($value);
	}

	public function testUpdate()
	{
		$value = $this->model->update( ["name" => "Rory Standley", "id" => 1] );
		$this->assertTrue($value);
		// Now we should check to see if that value now exists
		$this->model->find(1);
		$this->assertEquals('Rory Standley', $this->model->data->name);
	}

	public function testLock()
	{
		// We are locking the first row
		$value = $this->model->lock(1);
		// We should assert this call
		$this->assertTrue($value);
		// Now let's get the row
		$this->model->find(1);
		// Now check to make sure the lock_me column is not null
		$this->assertNotNull($this->model->data->lock_me);
	}

	public function testGet() 
	{
		$value = $this->model->__get('foo');
    	$this->assertObjectNotHasAttribute('foo', $this->model->data);
	}

	public function testSet()
	{
		// Set the value of foo
		$this->model->__set('foo', 'bar');
		$this->assertObjectHasAttribute('foo', $this->model->data);
	}

	public function testIsSet()
	{
		// Set a key
		$this->model->__set('foo', 'bar');
		// Check to see if a value has been set
		$value = $this->model->__isset('foo');
		// Assert whether the value we get back is true : false
		$this->assertTrue($value);
	}

	public function testFind()
	{
		$this->model->find(1);
		// Make sure we have the id back in the object
		$this->assertEquals(1, $this->model->id);
	}

	public function testEverything()
	{
		$everything = $this->model->everything();
		// Assert whether what is returned is an array
		$this->assertInternalType('array', $everything);
		// Assert that the lenght is greater than 0
		$this->assertGreaterThan(0, count($everything));
	}

	public function testGetRandomRows()
	{
		$result = $this->model->getRandomRows(1000);
		$this->assertEquals('1000', count($result));
	}

	public function testToday()
	{
		$result = $this->model->today();
		$this->assertEquals('2000', count($result));
	}

	public function testLatest()
	{
		$this->model->latest();
		$this->assertEquals('2000', $this->model->data->id);
	}

	public function testToArray()
	{
		$this->model->find(1);
		if ( is_object($this->model->data) ) {
			$this->assertObjectHasAttribute('id', $this->model->data);
			$this->assertInternalType('array', $this->model->toArray($this->model->data));
		}
	}

	public function testGetData()
	{
		$one = $this->model->find(1);
		$two = $this->model->getData();
		// Now we need to compare the two; they should both be the same
		$this->assertEquals($one->data, $two);
	}

	public function testTruncate()
	{
		$this->assertTrue($this->model->truncate());
		// Now we should check to see if this is the case
		$result = $this->model->everything();
		$this->assertEquals('0', count($result));
	}

	public function testFetchFields()
	{
		$columns = ["name", "lock_me"];
		$fields = $this->model->fetchFields();
		// Make sure that what we get back in an array
		$this->assertInternalType('array', $fields);
		// Now compare the arrays
		$this->assertEquals($columns, $fields);
	}

	public function testDropTable()
	{
		$value = $this->model->dropTable($this->model->tableName);
		$this->assertTrue($value);
	}

	public function testMapArr()
	{
		$arr1 = [
			0 => "One",
			1 => "Two",
			2 => "Three",
		];

		$arr2 = [
			0 => "One Value",
			1 => "Two Value",
			2 => "Three Value",
		];

		// The array that we should have at the end of this test
		$assumption = [
			"one_value" => "One",
			"two_value" => "Two",
			"three_value" => "Three",
		];

		$result = $this->model->mapArr($arr1, $arr2);
		$this->assertEquals($assumption, $result);
	}

	public function testRemoveColumns()
	{
		$arr = ["Column1" => 1, "Column2" => 2, "Column3" => 3, "column4" => 4];
		// We are going to remove Column2 from the array
		$assumption = [0 => 1, 1 => 3, 2 => 4];

		$result = $this->model->removeColumns($arr, ["Column2"]);
		$this->assertEquals($assumption, $result);
	}
}	