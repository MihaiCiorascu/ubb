<?php
    //$foods = array("apple", "orange", "banana", "coconut");
    //array_push($foods,"pineapple");
    //array_pop($foods);
    //array_map('strtoupper', ['a', 'b', 'c']); //['A', 'B', 'C'] // Aplica functia(primul arg) in array(al doilea arg)

    //array_shift($foods);
    //$foods = array_reverse($foods);
//    foreach ($foods as $food) {
//        echo $food . "<br>";
//    }

    $capitals = array(
        "USA" => "Washington, D.C.",
        "Canada" => "Ottawa",
        "Mexico" => "Mexico City",
        "UK" => "London",
        "France" => "Paris"
    );

    echo $capitals["USA"]; // prints Washington, D.C.

    //$keys = array_keys($capitals);
    //$values = array_values($capitals);
    //$capitals = array_flip($capitals); // switch values with keys
    //$capitals = array_reverse($capitals);
    echo count($capitals);

    foreach ($capitals as $country => $capital) {
        echo "The capital of " . $country . " is " . $capital . "<br>";
    }
?>