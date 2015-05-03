<?php
require_once 'Parser.php';
require_once 'ChattyParser.php';
require_once 'ThreadParser.php';


error_reporting(E_ALL);

function check_set($array, $key)
{
   if (isset($array[$key]))
      return $array[$key];
   else
      die("Missing parameter $key.");
}

function array_top(&$array)
{
   return $array[count($array) - 1];
}

function collapse_whitespace($str)
{
	$str = str_replace("\n", ' ', $str);
	$str = str_replace("\t", ' ', $str);
	$str = str_replace("\r", ' ', $str);

	while (strpos($str, '  ') !== false)
		$str = str_replace('  ', ' ', $str);

	return $str;
}
