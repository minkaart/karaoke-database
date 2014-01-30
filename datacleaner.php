<?php
//GLOBAL | INPUT VARIABLES
$filename = "/Users/minka/Sites/test/kajuns.csv"; //file to clean and overwrite with csv 

$filenamejson = "/Users/minka/Sites/test/kajuns.json"; //file to put JSON data

$newcsvarray = array(); 
//$newjsonarray = array(); (holds array of JSON key:value pairs not used)
$row = 0; //holds row position in full csv array

echo "starting process\n";

//arrays which hold file names
$files = array('other', 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z');
$artistfiles = array('artistother', 'artista', 'artistb', 'artistc', 'artistd', 'artiste', 'artistf', 'artistg', 'artisth', 'artisti', 'artistj', 'artistk', 'artistl', 'artistm', 'artistn', 'artisto', 'artistp', 'artistq', 'artistr', 'artists', 'artistt', 'artistu', 'artistv', 'artistw', 'artistx', 'artisty', 'artistz');

//arrays to hold alphabetized data chunks
$chunks = array();
$chunks = array_pad($chunks, 27, array());
$artistchunks = array();
$artistchunks = array_pad($artistchunks, 27, array());

if (($handle = fopen($filename, "r")) != FALSE)
{
	while (($data = fgetcsv($handle,",")) !== FALSE)
	{
	//functions to clean data and write csv to new csv files
		csvgenerator(datacleaner($data)); //cleans null data and populates $newcsvarray
	}
	$newcsvarray = duplicateremover($newcsvarray); //removes duplicates from array
	
}
else {
echo "Error Opening $filename";
}
fclose($handle);
unset($handle);
echo 'newcsvarray (0) is:'; 
echo $newcsvarray[0][1];
echo "\n got csv";
//all good
chunker(); //breaks single csv file (kajuns,csv) into smaller alphabetized files
echo "chunked \n";

filewriterJSON($newcsvarray, $filenamejson);
echo "JSON written \n";

filewriter($newcsvarray, $filename);
echo "csv written \n";

chunkwriter($artistchunks, $artistfiles);
echo "chunk artists written \n";

chunkwriter($chunks, $files); 
echo "chunk titles written \n";

echo "Finished!!";

//FUNCTIONS

function chunkwriter($data, $keys)
{
	$length = count($data); 
	for($i=0; $i<$length; $i++)
	{
		$location = ("/Volumes/DEVOID42/Professional/Kajuns/KaraokeScript/karaoke-database/csv/".$keys[$i].'.csv');
		$JSONlocation = ("/Volumes/DEVOID42/Professional/Kajuns/KaraokeScript/karaoke-database/json/".$keys[$i].'.json');
		$chunk = array();
		$chunk = $data[$i];
		filewriter($chunk, $location);
		filewriterJSON($chunk, $JSONlocation);
	}
}

//writes individual elements to associated files (uses chunks and files)
function filewriter($data, $location)
{
	//following creates/writes $newcsvarray (cleaned data) to kajuns.csv
	if (($handle = fopen($location, "w")) != FALSE)
	{
		foreach($data as $i)
		{
			fputcsv($handle, $i);
		}
	}
	else 
	{
		echo "Error Opening $location for editing";
	}
	fclose($handle);
	unset($handle);
}

function filewriterJSON($data, $location)
{
	//following creates/writes $newcsvarray (cleaned data) to kajuns,json
	if (($handle = fopen($location, "w")) != FALSE)
	{
	fwrite($handle, 'jsonCallback([');
	$dataCount = count($data);
		foreach($data as $index => $i)
		{
			$jsonindarray = JSONmaker($i);
			fwrite($handle, json_encode($jsonindarray));
			if($index < $dataCount-1) {
			fwrite($handle, ',');
			}
			//array_push($newjsonarray, $jsonindarray); builds $newjsonarray (not used)
		}
	fwrite($handle, ']);');
	}
	else
	{
		echo "Error Opening $location for editing";
	}
	fclose($handle);
	unset($handle);
}

//iterates through $newcsvarray to create and populate chunk arrays based on ascii values
function chunker() 
{
	global $newcsvarray;
	global $chunks;
	global $artistchunks;
	$temp = array();
	foreach($newcsvarray as $data) {
		echo $data[1];
		$artistvalue = ord($data[0]); //holds ascii value for artist name 
		$value = ord($data[1]); //holds ascii value title name 
		
		if(($value < 65) or (($value > 90) && ($value < 97)) or ($value > 122)){
			array_push($chunks[0], $data);
		}
		else {
		 $a = 65;
		 $b = 97;
		 for($c=1; $b<123; $c++) {
		 	if(($value == $a) or ($value == $b)){
		 		array_push($chunks[$c], $data);
		 	}
		 	$a++;
		 	$b++;
		 }
		}
		if(($artistvalue < 65) or (($artistvalue > 90) && ($artistvalue < 97)) or ($artistvalue > 122)) {
			array_push($artistchunks[0], $data);
			}
		else {
			$a = 65;
			$b = 97;
			for($c=1; $b<123; $c++) {
				if(($artistvalue == $a) or ($artistvalue == $b))
				{
		 			array_push($artistchunks[$c], $data);
		 		}
		 		$a++;
		 		$b++;
			}
		}
	}
}

//creates key:value pairs for JSON encoding of individual entries
function JSONmaker($data)
{
	$keyarray = array('Title', 'Artist');
	$datapart = array($data[1], $data[0]);	
	$data = array_combine($keyarray, $datapart);
	return $data;
}

//removes duplicate entries based on position [0] and [1] in an array
function duplicateremover($data)
{
	$length = count($data); 
	for($i=0; $i<$length; $i++)
	{
		$j = ($i+1);
		if (strlen($data[$i][0]) == strlen($data[$j][0]))
		{
			if (strlen($data[$i][1]) == strlen($data[$j][1]))
			{
				if ($data[$i][0] === $data[$j][0])
				{
					if ((strpos($data[$i][1], $data[$j][1]) !== false) || (strpos($data[$j][1], $data[$i][1]) !== false))
					{
						$c = count($data);
						array_splice($data, $i, ($i-$c)+1);
						$length = count($data);
						echo "duplicate found \n";
						$i--;
					}
				}
			}
		}
	}
	return $data;
}

//Data Cleaner $data = one row from csv file
function datacleaner($data)
{
	$length = count($data);
	for($i=0; $i<$length; $i++)
	{
		if ($data[$i] == null)
		{
			$c = count($data);
			array_splice($data, $i, ($i-$c)+1);
			$length = count($data);
		}
	}
	return $data; 
}

//populates $newcsvarray from original csv file
function csvgenerator($data)
{	
		global $newcsvarray;
		global $row;
		$c = count($data);
		for ($col=0; $col<$c; $col++)
		{
			$newcsvarray[$row][$col] = $data[$col];
		}
		$row++;
}

?>