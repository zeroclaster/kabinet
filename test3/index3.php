<?
class B {
	public $b = 1;
}

class cacher {
	public function getCache($id){
        if (!$this->CacheArray) {

        	$v = new B;
        	$v->b = 100;

        	$v2 = new B;
        	$v2->b = 200;

            $this->CacheArray = 
            [
            	0=>[
            		'A' => $v
            	],

            	1=>[
            		'A' => $v2
            	],
            ];
        }

        $ret = [];
        foreach ($this->CacheArray as $item) $ret[]=$item;

        return $ret;
    }
}


$c= new cacher;

$f = $c->getCache(555);
$f[0]['A']->b = 500;

$f = $c->getCache(555);

// ?????
// что отобразит код
echo $f[0]['A']->b;