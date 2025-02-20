<?
namespace Bitrix\Kabinet;

use \Bitrix\Main\SystemException;

class Archive{
	
	function __construct() {
      
    }
	
	public function add($object,array $fields){


		if ($object instanceof \Bitrix\Kabinet\taskrunner\Runnermanager){
			$ClassHL = (\KContainer::getInstance())->get(ARCHIVEFULFI_HL);

			foreach($fields['UF_PIC_REVIEW'] as $key => $file_id){
				$fileInfo = \CFile::GetFileArray($file_id);
                if ($fileInfo) $fields['UF_PIC_REVIEW'][$key] = \CFile::MakeFileArray($fileInfo['SRC']);                       
			}
			
			$QUEUE_ID = $fields['ID'];
			unset($fields['ID']);
			
	
			$obResult = $ClassHL::add($fields);
			if (!$obResult->isSuccess()){
				$err = $obResult->getErrors();
				$mess = $err[0]->getMessage();
				throw new SystemException($mess);
			}
			
			$ID = $obResult->getID();
            $ret = $ID;
		
			// ищим все сообщения
			$HLMess = (\KContainer::getInstance())->get(LMESSANGER_HL);
			$HLArchiveMess = (\KContainer::getInstance())->get(ARCHIVELMESS_HL);
			$data_mess = $HLMess::getlist([
				'select'=>['*'],
				'filter' => ['UF_QUEUE_ID'=>$QUEUE_ID],
			])->fetchAll();
			
			
			// переносим все найденные сообщения в таблицу архив
			foreach($data_mess as $item){										
				foreach($item['UF_UPLOADFILE'] as $key => $file_id){
					$fileInfo = \CFile::GetFileArray($file_id);
					if ($fileInfo) $item['UF_UPLOADFILE'][$key] = \CFile::MakeFileArray($fileInfo['SRC']);                       
				}
				
					//Новое
					if ($item['UF_STATUS'] == 5) $item['UF_STATUS'] = 17;
					//прочитанное
					if ($item['UF_STATUS'] == 6) $item['UF_STATUS'] = 18;
					//исправленное
					if ($item['UF_STATUS'] == 7) $item['UF_STATUS'] = 19;
					//удаленное
					if ($item['UF_STATUS'] == 8) $item['UF_STATUS'] = 20;


					//пользователь
					if ($item['UF_TYPE'] == 3) $item['UF_TYPE'] = 21;
					//системное
					if ($item['UF_TYPE'] == 4) $item['UF_TYPE'] = 22;		


					$item['UF_QUEUE_ID'] = $ID;
					unset($item['ID']);
					
					//
					
					$obResult = $HLArchiveMess::add($item);
					if (!$obResult->isSuccess()){
						$err = $obResult->getErrors();
						$mess = $err[0]->getMessage();
						throw new SystemException($mess);
					}
			}

			return $ret;
		}


		if ($object instanceof \Bitrix\Kabinet\task\Taskmanager){
			$HLArchiveTask = (\KContainer::getInstance())->get(ARCHIVETASK_HL);
	
			foreach($fields['UF_PHOTO'] as $key => $file_id){
				$fileInfo = \CFile::GetFileArray($file_id);
                if ($fileInfo) $fields['UF_PHOTO'][$key] = \CFile::MakeFileArray($fileInfo['SRC']);                       
			}	
			
			
			//Однократное выполнение
			if ($fields['UF_CYCLICALITY'] == 1) $fields['UF_CYCLICALITY'] = 31;
			//Повторяется ежемесячно
			if ($fields['UF_CYCLICALITY'] == 2) $fields['UF_CYCLICALITY'] =32;


			//Однократное выполнение
			if ($fields['UF_REPORTING'] == 9) $fields['UF_REPORTING'] = 29;
			//Повторяется ежемесячно
			if ($fields['UF_REPORTING'] == 10) $fields['UF_REPORTING'] =30;


			//нет, материал предоставляет клиент
			if ($fields['UF_COORDINATION'] == 11) $fields['UF_COORDINATION'] = 26;
			//нет, материал готовим сервис по брифу и публикует без согласования
			if ($fields['UF_COORDINATION'] == 12) $fields['UF_COORDINATION'] = 27;	
			//согласование есть
			if ($fields['UF_COORDINATION'] == 13) $fields['UF_COORDINATION'] = 28;		

			//Остановлена
			if ($fields['UF_STATUS'] == 14) $fields['UF_STATUS'] = 23;
			//Выполняется
			if ($fields['UF_STATUS'] == 15) $fields['UF_STATUS'] = 24;	
			//Пауза
			if ($fields['UF_STATUS'] == 16) $fields['UF_STATUS'] = 25;		

			
			$TASK_ID = $fields['ID'];
			unset($fields['ID']);
			
			$obResult = $HLArchiveTask::add($fields);
			if (!$obResult->isSuccess()){
				$err = $obResult->getErrors();
				$mess = $err[0]->getMessage();
				throw new SystemException($mess);
			}		
			
			return $obResult->getID();
		}
	
		//throw new SystemException("STOP TEST 1");
		
		
		return true;
	}
}