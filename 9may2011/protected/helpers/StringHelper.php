<?php
 
class StringHelper {

	static public function plural($n, $c1, $c2, $c3 = false)
	{
		if($c3 === false)
			$c3 = $c2;

		return $n % 10 == 1 && $n % 100 !=11 ? $c1 : ($n % 10 >= 2 && $n % 10 <=4 && ($n % 100 < 10 || $n % 100 >= 20) ? $c2 : $c3);
	}
}
