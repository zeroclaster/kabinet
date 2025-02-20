<?
use Bitrix\Main\SystemException;

class KContainer {
	private static $instance;
	private $storage = [];
	private $maked = [];
	
	protected function __construct() { 
	}

    protected function __clone() { }


    public function __wakeup()
    {
        throw new SystemException("Cannot unserialize a singleton.");
    }
	
	public static function getInstance()
    {
        $cls = static::class;
        if (!isset(self::$instance)) {
            self::$instance = new static();
        }

        return self::$instance;
    }


    public function setArgs($arg,string $alias){
        $this->storage[$alias] = $arg;
    }

    public function getArgs(string $alias){
        return $this->storage[$alias];
    }
	
	public function make($arg,$alias){
		$this->storage[$alias] = $arg;

        return $this;
	}

	public function maked($arg,$alias){
		$this->maked[$alias] = $arg;	
	}
	
	public function get(...$args){
		$return = null;

		foreach ($args as $alias){

            if (isset($this->maked[$alias])) {
                if (count($args)==1) return $this->maked[$alias];
                else continue;
            }

            if (isset($this->storage[$alias])) {
                $concrete = $this->storage[$alias];

                if ($concrete instanceof \Closure)
                    $return = $concrete($this);

            }

            if ($return) $this->maked[$alias] = $return;

        }

		if (count($args)==1) return $return;
	}

	// Добавляет скрипты в конец страницы
	public function addJS(string $path){
        $addscript = $this->get('addscript')?? [];
        $addScriptinPage = "<script type=\"text/javascript\" src=\"{$path}\"></script>";
        $addscript[] = $addScriptinPage;
        $this->maked($addscript,'addscript');
    }
}