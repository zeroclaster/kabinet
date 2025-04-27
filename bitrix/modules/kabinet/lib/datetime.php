<?php
namespace Bitrix\Kabinet;


class DateTime extends \Bitrix\Main\Type\DateTime
{
    public function makemodify($date)
    {
        $this->value->modify($date);
    }


    public function modify($date)
    {
        $new = clone $this;

        $new->makemodify($date);

        return $new;
    }

    public function dayStart()
    {
        $s = $this->value->format("d.m.Y")." 00:00:01";
        $this->value = \DateTime::createFromFormat("d.m.Y H:i:s", $s);

        return $this;
    }

    public function dayEnd()
    {
        $s = $this->value->format("d.m.Y")." 23:59:00";
        $this->value = \DateTime::createFromFormat("d.m.Y H:i:s", $s);

        return $this;
    }
}