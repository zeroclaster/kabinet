<?
namespace Bitrix\Kabinet\taskrunner;

class Queuestatisticsservice {
    private $taskManager;

    public function __construct(\Bitrix\Kabinet\task\TaskManager $taskManager) {
        $this->taskManager = $taskManager;
    }

    public function getStatistics(array $task): array {
        $dbArray = $this->taskManager->FulfiCache($task);
        $Queue = [];
        $status = 0;
        $ret = [];
        foreach ($dbArray as $item)  if ($item['UF_STATUS']==$status) $Queue[]=$item;

        $st = ['STATUS'=>$status,'COUNT'=>0];
        if ($Queue) {
            foreach ($Queue as $one) {
                if ($one['UF_ELEMENT_TYPE'] == 'multiple') $st['COUNT'] += $one['UF_NUMBER_STARTS'];
                else $st['COUNT']++;
            }
        }
        $ret[] = $st;

        $status = [1,2,3,4,5,6,61,7,8];
        $Queue = [];
        foreach ($dbArray as $item)  if (in_array($item['UF_STATUS'],$status)) $Queue[]=$item;

        $st = ['STATUS'=>$status,'COUNT'=>0];
        if ($Queue) {
            foreach ($Queue as $one) {
                if ($one['UF_ELEMENT_TYPE'] == 'multiple') $st['COUNT'] += $one['UF_NUMBER_STARTS'];
                else $st['COUNT']++;
            }
        }

        $ret[] = $st;

        $status = 9;
        $Queue = [];
        foreach ($dbArray as $item)  if ($item['UF_STATUS']==$status) $Queue[]=$item;


        $st = ['STATUS'=>$status,'COUNT'=>0];
        if ($Queue) {
            foreach ($Queue as $one) {
                if ($one['UF_ELEMENT_TYPE'] == 'multiple') $st['COUNT'] += $one['UF_NUMBER_STARTS'];
                else $st['COUNT']++;
            }
        }
        $ret[] = $st;

        $status = 10;
        $Queue = [];
        foreach ($dbArray as $item)  if ($item['UF_STATUS']==$status) $Queue[]=$item;

        $st = ['STATUS'=>$status,'COUNT'=>0];
        if ($Queue) {
            foreach ($Queue as $one) {
                if ($one['UF_ELEMENT_TYPE'] == 'multiple') $st['COUNT'] += $one['UF_NUMBER_STARTS'];
                else $st['COUNT']++;
            }
        }
        $ret[] = $st;


        // 5 - На согласовании (у клиента)
        // 8 - Отчет на проверке у клиента
        $status = [5,8];
        $Queue = [];
        foreach ($dbArray as $item)  if (in_array($item['UF_STATUS'],$status)) $Queue[]=$item;

        $st = ['STATUS'=>$status,'COUNT'=>0];
        if ($Queue) {
            foreach ($Queue as $one) {
                if ($one['UF_ELEMENT_TYPE'] == 'multiple') $st['COUNT'] += $one['UF_NUMBER_STARTS'];
                else $st['COUNT']++;
            }
        }

        $ret[] = $st;

        return $ret;
    }
}
