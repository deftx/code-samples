<?php

include __DIR__."./Person.php";

$top = array(
	'Ray' => new Person('Ray'),
	'Arlene' => new Person('Arlene'),

	'Mark' => new Person('Mark'),
	'Betty' => new Person('Betty'),

	//

	'Larry' => new Person('Larry'),
	'Jo'    => new Person('Jo'),

	//
	'Tim'   => new Person('Tim'),
	'Sally' => new Person('Sally')
);

$persons = array();

$persons['Bob'] = new Person('Bob', $top['Ray'], $top['Arlene']);
$persons['Danette'] = new Person('Danette', $top['Mark'], $top['Betty']);
	$persons['Rob'] = new Person('Rob', $persons['Bob'], $persons['Danette']);

$persons['Larry Jr'] = new Person('Larry Jr', $top['Larry'], $top['Jo']);
$persons['Sarah']    = new Person('Sarah', $top['Tim'], $top['Sally']);
	$persons['Lauren'] = new Person('Lauren', $persons['Larry Jr'], $persons['Sarah']);

$persons['Kevin'] = new Person('Kevin', $top['Mark'], $top['Jo']);

// Not related
$persons['Rob']->is_related($persons['Lauren']);

echo "\n";

// Related
$persons['Rob']->is_related($persons['Kevin']);
