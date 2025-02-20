<?
namespace Bitrix\Kabinet\taskrunner\states;


class Xmlload{
    private $xml;
    public $type = [];
    private $xmlFile = '';
    private $cache = [];
    private $makersCache = [];

    function __construct($xmlFile){
        $this->xmlFile = $_SERVER["DOCUMENT_ROOT"].'/'.$xmlFile;

        if (!file_exists($this->xmlFile)) throw new \Bitrix\Main\SystemException('Не удалось открыть файл '.$this->xmlFile,100);

        $sxi = new \SimpleXmlIterator($this->xmlFile, null, true);
        $catArray = $this->sxiToArray($sxi);
        // for debug!!
        //echo "<pre>";
        //print_r($catArray);
        //echo "</pre>";

        $this->xml = simplexml_load_file($this->xmlFile);
    }

    function getStates(){
        $xml = $this->xml;

        if (empty($this->cache)){

            $result = $xml->xpath("//*[@type]");
            foreach ($result as $node) {
                $attributes1 = $node->attributes();
                $states = $node->xpath(".//state");
                $ret = [];
                foreach ($states as $state) {
                    $attributes2 = $state->attributes();
                    $ret[$attributes2->{'name'}->__toString()] = $state;
                }
                $this->cache[$attributes1->{'type'}->__toString()] = $ret;
            }

            return $this->cache;
        }else{
            return $this->cache;
        }
    }

    public function getQueuStatus($type){
        $states = $this->getStates();
        $ret = [];
        foreach ($states[$type] as $state){
            $attributes = $state->attributes();
            $id = $attributes->{'id'}->__toString();
            $name = $attributes->{'name'}->__toString();
            $ret[] = [
                'ID' => $id,
                'TITLE' => $state->title->__toString(),
                'NAME' => $name,
            ];
        }

        return $ret;
    }

    public function __call($name, $arguments)
    {
        $states = $this->getStates();

        if (empty($this->makersCache[$name])) {
            if (empty($states[$name])) $m = new Maker([]);
            else
                $m = new Maker($states[$name],$name);
            $this->makersCache[$name] = $m;
        }

        return $this->makersCache[$name];
    }

    function setAttribute(\SimpleXMLElement $node, $attributeName, $attributeValue)
    {
        $attributes = $node->attributes();
        if (isset($attributes->$attributeName)) {
            $attributes->$attributeName = $attributeValue;
        } else {
            $attributes->addAttribute($attributeName, $attributeValue);
        }
    }

    public function updateBlock($cellId, $element_ID){
        $xml = $this->xml;

        $content = $xml->asXML();
        $res = file_put_contents($this->xmlFile,$content);
    }

    function sxiToArray($sxi){
        $a = array();
        for( $sxi->rewind(); $sxi->valid(); $sxi->next() ) {

            if ($sxi->current()->attributes()["type"])
                        $this->type[] = $sxi->current()->attributes()["type"];

            if(!array_key_exists($sxi->key(), $a)){
                $a[$sxi->key()] = array();
            }
            if($sxi->hasChildren()){
                $a[$sxi->key()][] = $this->sxiToArray($sxi->current());
            }
            else{
                $a[$sxi->key()][] = strval($sxi->current());
            }
        }
        return $a;
    }
}
