<?
namespace Bitrix\Kabinet\taskrunner\states\contracts;

interface Icommand
{
    public function execute(array $params);
    public function setObject($Object);
    public function getObject();
}