<?php
class Rasta_Util_DotNotation
{

	static function Set($source, $dot_object)
	{
		foreach($dot_object as $key=>$value)
		{
			$keys = explode(".", $key);
			$source = self::CreateArrayTree($keys, $source, $value);	
		}
		return $source;
	}
	static function DUnSet($source, $key)
	{
		$keys = explode(".", $key);
		$source = self::UnsetArrayItem($keys, $source);	
		return $source;
	}
	
	static function CreateArrayTree($keys, $parent, $value)
	{
		$count= count($keys);
		$first= array_shift($keys);
		if( is_numeric($first) )	$first	= (integer) $first;
		if(!empty($first))
		{
			if(!isset($parent[$first])) $parent[$first]=array();
			if($count>1)		$parent[$first]	= self::CreateArrayTree($keys, $parent[$first], $value);
			elseif($count==1)	$parent[$first]	= $value;
		}
		else
			$parent[]	= $value;
		return $parent;	
	}
	static function UnsetArrayItem($keys, $source)
	{
		$count	= count($keys);
		$first	= array_shift($keys);
		if( is_numeric($first) )	$first	= (integer) $first;
		if(!empty($first))
		{
			if(isset($source[$first]) and $count>2)			$source[$first]	= self::UnsetArrayItem($keys, $source[$first]) ;
			elseif(is_array($source[$first]) and $count==2)	unset($source[$first][ $keys[0] ]); 
		}
		return $source;
	}

	static function DIsSet($source, $key)
	{
		$keys	= explode('.', $key);
		$temp = $source;
		foreach($keys as $k)
		{
			if(isset($temp[$k]))
				$temp = $temp[$k];
			else
				return false;
		}
		return true;
	}
	static function GetValue($source, $key, $default=false)
	{
		$keys	= explode('.', $key);
		if(count($keys)==0)return $default;
		$temp = $source;
		foreach($keys as $k)
		{
			if(isset($temp[$k]))
				$temp = $temp[$k];
			else
				return $default;
		}
		return $temp;
	}
	
}
?>