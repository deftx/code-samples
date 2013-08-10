<?php

class Person
{
	public $mother;
	public $father;
	public $name;

	public function __construct($name, Person $father = null, Person $mother = null)
	{
		$this->name = $name;
		$this->mother = $mother;
		$this->father = $father;
	}

	public function is_related(Person $person)
	{
		$max = 8;
		$steps = 0;

		$tree = array();
		$this->traverse($this, $tree);

		foreach ($tree as $k=>$v) {
			echo $v."\n";
		}

		if (in_array($person, $tree)) {
			echo "YES!\n";
		} else {
			echo "\nTheir tree:\n";
			$their_tree = array();
			$this->traverse($person, $their_tree);

			foreach ($their_tree as $k=>$v) {
				echo $v."\n";
			}

			echo "\nResult: ";
			if (count(array_intersect($tree, $their_tree))) {
				echo "RELATED!";
			} else {
				echo "NOT RELATED!";
			}

			echo "\n";
		}
	}

	public function traverse(Person $person, &$tree)
	{
		if ($person->mother !== null) {
			$tree[] = $person->mother->name;

			$this->traverse($person->mother, $tree);
		}

		if ($person->father !== null) {
			$tree[] = $person->father->name;

			$this->traverse($person->father, $tree);
		}
	}

}