<?php

    use codedefective\quakeTurkey;

    require 'inc.php';

	$newdata = new quakeTurkey();
//    $newdata->setUrl('das');

	echo $newdata->limit(10)->createGraphic();





	// All data (max 500)
	//$quakes = new \codedefective\quakeTurkey();

	//Limited data (ex:15)
	//$quakes = new \codedefective\quakeTurkey(15);







	//To group by location;
	//$list = $quakes->groupByLocation()->getList();

	//json response;
	//$list = $quakes->groupByLocation()->toJson()->getList();
    //print_r($list);
	/*
	//To group by date;
	$quakes->groupByDate()->getList();
	//json response;
	$quakes->groupByDate()->toJson()->getList();

	//To sort by date;
	$quakes->sortByDate()->getList();
	//json response;
	$quakes->sortByDate()->toJson()->getList();

	//To sort by size;
	$quakes->sortBySize()->getList();
	//json response;
	$quakes->sortBySize()->toJson()->getList();

	//graph (experimental)
	$quakes->graphData()->create();
*/