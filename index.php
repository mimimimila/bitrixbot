<?php

$appsConfig     = array();
$configFileName = '/config_' . trim(str_replace('.', '_', $_REQUEST['auth']['domain'])) . '.php';

if (file_exists(__DIR__ . $configFileName)) {
   include_once __DIR__ . $configFileName;
}

if ($_REQUEST['event'] == 'ONIMBOTMESSAGEADD') {

   if (!isset($appsConfig[$_REQUEST['auth']['application_token']])) {
      return false;
   }

   $arReport = getAnswer($_REQUEST['data']['PARAMS']['MESSAGE'], $_REQUEST['data']['PARAMS']['FROM_USER_ID']);


   if (checkUserInItDepartment($_REQUEST['data']['PARAMS']['FROM_USER_ID']))      
      $arReport['attach'][] = array("MESSAGE" => 'Вы можете спросить у меня про задачи, которые имеют статусы [send=просрочено]"Просрочено"[/send] и [send=в работе]"В работе"[/send]!');

   else $arReport['attach'][]=array(array());
   //$attach='';


   $result = restCommand('imbot.message.add', 
      array(
         "DIALOG_ID" => $_REQUEST['data']['PARAMS']['DIALOG_ID'],
         "MESSAGE"   => $arReport['title'] . "\n" . $arReport['report'] . "\n",
         "ATTACH"    => $arReport['attach'],
      ), 

     $_REQUEST["auth"]);
} 


else { if ($_REQUEST['event'] == 'ONIMBOTJOINCHAT') {

      if (!isset($appsConfig[$_REQUEST['auth']['application_token']])) {
        return false;
      }

      $result = restCommand('imbot.message.add', array(
         'DIALOG_ID' => $_REQUEST['data']['PARAMS']['DIALOG_ID'],
         'MESSAGE'   => 'Я робот по приему заявок в отдел ИТ! Вы можете написать мне заявку, например, на замену картриджа и я передам её!',
         "ATTACH"    => array(
           array('MESSAGE' => 'Внимание! Отделение и заявитель будут указаны автоматически исходя из данных пользователя, который написал заявку.'),
         ),
      ), $_REQUEST["auth"]);

}


else { if ($_REQUEST['event'] == 'ONIMBOTDELETE') {

      $result = restCommand('imbot.message.add', Array(
      'BOT_ID' => 989, // Идентификатор чат-бота, от которого идет запрос, можно не указывать, если чат-бот всего один
      'DIALOG_ID' => 11, // Идентификатор диалога, это либо USER_ID пользователя, либо chatXX - где XX идентификатор чата, 
      'MESSAGE' => 'На этом мы с вами прощаемся, всего доброго!', // Тест сообщения
      'ATTACH' => '', // Вложение, необязательное поле
      'KEYBOARD' => '', // Клавиатура, необязательное поле
      'MENU' => '', // Контекстное меню, необязательное поле 
      'SYSTEM' => 'N', // Отображать сообщения в виде системного сообщения, необязательное поле, по умолчанию 'N'
      'URL_PREVIEW' => 'Y' // Преобразовывать ссылки в rich-ссылки, необязательное поле, по умолчанию 'Y'

         ), $_REQUEST["auth"]);


        if (!isset($appsConfig[$_REQUEST['auth']['application_token']])) {
            return false;
        }


      unset($appsConfig[$_REQUEST['auth']['application_token']]);

      saveParams($appsConfig);
}


else { if ($_REQUEST['event'] == 'ONAPPINSTALL') {

         $handlerBackUrl = ($_SERVER['SERVER_PORT'] == 443 ? 'https' : 'http') . '://' . $_SERVER['SERVER_NAME'] . (in_array($_SERVER['SERVER_PORT'],
               array(80, 443)) ? '' : ':' . $_SERVER['SERVER_PORT']) . $_SERVER['SCRIPT_NAME'];

         $result = restCommand('imbot.register', array(
               'CODE'                  => 'ReportBot',
               // строковой идентификатор бота, уникальный в рамках вашего приложения (обяз.)
               'TYPE'                  => 'B',
               // Тип бота, B - бот, ответы  поступают сразу, H - человек, ответы поступаю с задержкой от 2х до 10 секунд
               'EVENT_MESSAGE_ADD'     => $handlerBackUrl,
               // Ссылка на обработчик события отправки сообщения боту (обяз.)
              'EVENT_WELCOME_MESSAGE' => $handlerBackUrl,
              // Ссылка на обработчик события открытия диалога с ботом или приглашения его в групповой чат (обяз.)
              'EVENT_BOT_DELETE'      => $handlerBackUrl,
              // Ссылка на обработчик события удаление бота со стороны клиента (обяз.)
              'PROPERTIES'            => array( // Личные данные чат-бота (обяз.)
               'NAME'              => 'itbot',
                // Имя бота (обязательное одно из полей NAME или LAST_NAME)
                'LAST_NAME'         => '',
                // Фамилия бота (обязательное одно из полей NAME или LAST_NAME)
                'COLOR'             => 'AQUA',
               // Цвет бота для мобильного приложения RED,  GREEN, MINT, LIGHT_BLUE, DARK_BLUE, PURPLE, AQUA, PINK, LIME, BROWN,  AZURE, KHAKI, SAND, MARENGO, GRAY, GRAPHITE
                'EMAIL'             => 'no@mail.com',
                  // Емейл для связи
                 'PERSONAL_BIRTHDAY' => '2021-09-21',
                 // День рождения в формате YYYY-mm-dd
                'WORK_POSITION'     => 'Принимаю заявки в отдел информационных технологий',
               // Занимаемая должность, используется как описание бота
                'PERSONAL_WWW'      => '',
           // Ссылка на сайт
                 'PERSONAL_GENDER'   => 'M',
                 // Пол бота, допустимые значения M -  мужской, F - женский, пусто если не требуется указывать
                'PERSONAL_PHOTO'    => 'avatar.jpg',
                  // Аватар бота - base64
              ),
          ), $_REQUEST["auth"]);
            
            $appsConfig[$_REQUEST['auth']['application_token']] = array(
                  'BOT_ID'      => $result['result'],
                  'LANGUAGE_ID' => $_REQUEST['data']['LANGUAGE_ID'],
            );


            $result = restCommand('imbot.message.add', Array(
               'BOT_ID' => 989, // Идентификатор чат-бота, от которого идет запрос, можно не указывать, если чат-бот всего один
               'DIALOG_ID' => 11, // Идентификатор диалога, это либо USER_ID пользователя, либо chatXX - где XX идентификатор чата, передается в событии ONIMBOTMESSAGEADD и ONIMJOINCHAT
               'MESSAGE' => 'Рожден чтобы помогать', // Тест сообщения
               'ATTACH' => '', // Вложение, необязательное поле
               'KEYBOARD' => '', // Клавиатура, необязательное поле
               'MENU' => '', // Контекстное меню, необязательное поле 
               'SYSTEM' => 'N', // Отображать сообщения в виде системного сообщения, необязательное поле, по умолчанию 'N'
               'URL_PREVIEW' => 'Y' // Преобразовывать ссылки в rich-ссылки, необязательное поле, по умолчанию 'Y'
            ), $_REQUEST["auth"]);

            saveParams($appsConfig);
            writeToLog($result, 'ReportBot register');
         }
      }
   }
}


function saveParams($params) {
   $config = "<?php\n";
   $config .= "\$appsConfig = " . var_export($params, true) . ";\n";
   $config .= "?>";
   $configFileName = '/config_' . trim(str_replace('.', '_', $_REQUEST['auth']['domain'])) . '.php';

   file_put_contents(__DIR__ . $configFileName, $config);

   return true;
}


function restCommand($method, array $params = array(), array $auth = array()) {
   $queryUrl  = 'https://' . $auth['domain'] . '/rest/' . $method;
   $queryData = http_build_query(array_merge($params, array('auth' => $auth['access_token'])));

   writeToLog(array('URL' => $queryUrl, 'PARAMS' => array_merge($params, array("auth" => $auth["access_token"]))), 'ReportBot send data');

   $curl = curl_init();

   curl_setopt_array($curl, array(
      CURLOPT_POST           => 1,
      CURLOPT_HEADER         => 0,
      CURLOPT_RETURNTRANSFER => 1,
      CURLOPT_URL            => $queryUrl,
      CURLOPT_POSTFIELDS     => $queryData,
   ));

   $result = curl_exec($curl);
   curl_close($curl);
   $result = json_decode($result, 1);

  writeToLog($result, 'ReportBot obnova');
   return $result;
}


function writeToLog($data, $title = '') {
   $log = "\n------------------------\n";
   $log .= date("Y.m.d G:i:s") . "\n";
   $log .= (strlen($title) > 0 ? $title : 'DEBUG') . "\n";
   $log .= print_r($data, 1);
   $log .= "\n------------------------\n";

   file_put_contents(__DIR__ . '/imbot.log', $log, FILE_APPEND);

   return true;
}

function getAnswer($command = '', $user) {

   switch (strtolower($command)) {

      case 'просрочено':
           $arResult = getTaskByStatus($user, 'STATUS','-1', 'Просрочено');
           break;

      case 'в работе':
           $arResult = getTaskByStatus($user, 'REAL_STATUS','2', 'В работе');
           break;

      default:
          $arResult = createTask($user,$_REQUEST['data']['PARAMS']['MESSAGE']);
   }

   return $arResult;


}

function searchKeyWordInUserMessage($userMessageText)
{
   $keyWordName='';
   $keyWordsInMessage=['printer'=>['картридж','принтер','катридж','краск',], 
            'computer'=>['компьютер',' пк '], 
            'rmias'=>['рмиас','истор','промед','больничный',],
            'internet'=>['интернет'], 
            'bitrix'=>['битрикс'], 
            '1C'=>['1с']];

   foreach (array_keys($keyWordsInMessage) as $keyName)
    foreach ($keyWordsInMessage[$keyName] as $key=>$keyWord)
   {
     if (mb_strripos($userMessageText, $keyWord)!==false)
      $keyWordName=$keyName;
      
   }

   return $keyWordName;
}

function createTask($userId, $userMessageText)
{

   $taskTemplate['cartridge']=['RESPONSIBLE_ID'=>"197",'DEADLINE'=>date('d.m.Y 15:00:00')];//15:00 потому что битрикс прибавляет 2 часа
   $taskTemplate['printer']=['RESPONSIBLE_ID'=>"197",'DEADLINE'=>date('d.m.Y 15:00:00')];
   $taskTemplate['computer']=['RESPONSIBLE_ID'=>"9",'DEADLINE'=>date('d.m.Y 15:00:00')];
   $taskTemplate['rmias']=['RESPONSIBLE_ID'=>"203",'DEADLINE'=>date('d.m.Y 15:00:00')];
   $taskTemplate['internet']=['RESPONSIBLE_ID'=>"9",'DEADLINE'=>date('d.m.Y 15:00:00')];
   $taskTemplate['bitrix']=['RESPONSIBLE_ID'=>"31",'DEADLINE'=>date('d.m.Y 15:00:00')];
   $taskTemplate['1C']=['RESPONSIBLE_ID'=>"11",'DEADLINE'=>date('d.m.Y 15:00:00')];
   $taskTemplate['deafult']=['RESPONSIBLE_ID'=>"11",'DEADLINE'=>date('d.m.Y 15:00:00')];

   $taskTemplateByKeyWord=searchKeyWordInUserMessage($userMessageText);

   if ($taskTemplateByKeyWord)
   {
      $responsibleId=$taskTemplate[$taskTemplateByKeyWord]['RESPONSIBLE_ID'];
      $deadline=$taskTemplate[$taskTemplateByKeyWord]['DEADLINE'];
   }
   else 
   {
      $responsibleId=$taskTemplate['deafult']['RESPONSIBLE_ID'];
      $deadline=$taskTemplate['deafult']['DEADLINE'];
   }
  
   $userInfo=getUserInfo($userId);

   $task= restCommand('tasks.task.add', 
      [
         'fields'=>[
            'TITLE'=>$userMessageText,
            'GROUP_ID'=>17,
            'CREATED_BY'=>1,
            'AUDITORS'=>getEmployeeIdForItDepartment(),
            'DESCRIPTION'=>$userInfo['userName']."\n".$userInfo['departmentName']."\n".$userInfo['work_position']."\n".$userInfo['phoneNumber'],
            'RESPONSIBLE_ID'=>$responsibleId,
            'DEADLINE'=>$deadline,

         ]
      ]     
      ,
      $_REQUEST["auth"]);

   return  $arResult = array(
            'title' => 'Заявка принята.'.$taskTemplateByKeyWord,
            'report'  => 'Номер задачи-'.$task['result']['task']['id']."\n".$userInfo['messageForAddPhoneNumber'],
        );
}


function getTaskByStatus ($user, $statusField, $statusNumb, $statusName) {

   $tasks = restCommand('task.item.list', 
      array(
         'ORDER' => array('DEADLINE' => 'desc'),
         'FILTER' => array('CREATED_BY' =>1, $statusField=> $statusNumb),
         'PARAMS' => array(),
         'SELECT' => array()

     ), 
      $_REQUEST["auth"]);

   if (count($tasks['result']) > 0) {

      $arTasks = array();

      foreach ($tasks['result'] as $id => $arTask) {
         
         $arTasks[] = array(
               'LINK' => array(
                  'NAME' => $arTask['TITLE'],
                  'LINK' => 'https://'.$_REQUEST['auth']['domain'].'/company/personal/user/'.$arTask['RESPONSIBLE_ID'].'/tasks/task/view/'.$arTask['ID'].'/'
            )
         );


         $arTasks[] = array(
            'DELIMITER' => array(
               'SIZE' => 400,
               'COLOR' => '#c6c6c6'
            )
         );

      }

     $arReport = array(
        'title' => 'Задачи со статусом: '.$statusName,
        'report'  => '',
        'attach' => $arTasks
      );
   }


   else {
      $arReport = array(
         'title' => 'Задачи со статусом: '.$statusName,
         'report'  => 'Отсутствуют',
      );
   }

   return $arReport;
}



function checkUserInItDepartment($userId)
{
   $userInfo=getUserInfo($userId);
   $ItDepartmentId=3;

   if (in_array($ItDepartmentId, $userInfo['departmentId']))
      return true;
   else        
      return false;
}



function getEmployeeIdForItDepartment()
{
   $employeeListForItDepartment = restCommand('im.department.employees.get', Array('ID'=>[3],'USER_DATA' => 'Y'), $_REQUEST["auth"]);

   $i=0;
         
   foreach($employeeListForItDepartment['result'][3] as $user)
   {
      $usersIds[$i]=$user['id'];
      $i++;
           
   }
   
   return $usersIds;
}


function getUserInfo($userId) {

$user = restCommand('im.user.get', array( 'id'=>$userId,), $_REQUEST["auth"]);


   if (count($user['result']) > 0) {

      $userInfo = array(
               'userName' =>'Сотрудник: '.$user['result']['name'],
               'work_position'  =>'Должность: '.$user['result']['work_position'],
               'departmentId'=>$user['result']['departments'], 
            );

      if ($user['result']['phones']['work_phone'])
      {
         $userPhoneNumber[0]=$user['result']['phones']['work_phone'];
      }

      if ($user['result']['phones']['personal_mobile'])
      {
         $userPhoneNumber[1]=$user['result']['phones']['personal_mobile'];
      }

      if ($user['result']['phones']['personal_phone'])
      {
         $userPhoneNumber[2]=$user['result']['phones']['personal_phone'];
      }

      if ($user['result']['phones']['inner_phone'])
      {
         $userPhoneNumber[2]=$user['result']['phones']['inner_phone'];
      }


      if ($userPhoneNumber)

         $userPhoneNumberForTaskDescription='Тел.:'.implode(",",$userPhoneNumber);

      else 
      {
         $userPhoneNumberForTaskDescription.='Пользователь не указал свой номер телефона в карточке Битрикс24';
         $userInfo['messageForAddPhoneNumber']='Вы не указали ни один из номеров телефона. Это может усложнить выполнение заявки. Пожалуйста, укажите номер телефона в вашей карточке в Битрикс24 для будущих заявок.';
      }

      $userInfo['phoneNumber']= $userPhoneNumberForTaskDescription;
       
      $userDepartment= restCommand('im.department.get', array(
               'id'=>[$user['result']['departments']['0']],
                  ), $_REQUEST["auth"]);
   
      if (count($userDepartment['result']) > 0) 

         $userInfo['departmentName']='Отделение: '.$userDepartment['result'][0]['name']; 

  }

   else 

      $userInfo = array(
         'userName' => 'Информация о пользователе не найдена.',
      );

  return $userInfo;

}




