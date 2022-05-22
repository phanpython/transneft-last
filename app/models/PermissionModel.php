<?php

namespace models;

use core\DB;
use widgets\date\Date;
use widgets\pagination\Pagination;
use widgets\permission\Permission;
use widgets\employee\Employee;
use widgets\role\Role;
use widgets\status\Status;
use widgets\statuslog\StatusLog;
use widgets\typicalwork\TypicalWork;
use widgets\user\User;
use widgets\protection\Protection;

class PermissionModel extends AppModel
{
    protected $typicalWork;
    protected $permission;
    protected $date;
    protected $user;
    protected $role;
    protected $employee;
    protected $statusLog;
    protected $status;
    protected $pagination;
    protected $db;

    public function __construct() {
        dumper($_POST);

        if(isset($_SESSION['archive-permissions']) && strpos($_SERVER['REDIRECT_QUERY_STRING'], 'add') === false) {
            $this->db = DB::getArchiveDB();
        } else {
            $this->db = DB::getMainDB();
        }

        $this->employee = new Employee($this->db);
        $this->permission = new Permission($this->db);
        $this->typicalWork = new TypicalWork($this->db);
        $this->date = new Date($this->db);
        $this->user = new User($this->db);
        $this->role = new Role($this->db);
        $this->statusLog = new StatusLog($this->db);
        $this->status = new Status($this->db);
        $this->pagination = new Pagination($this->db);
        $this->protection = new Protection($this->db);

        if (isset($_POST["del-masks"])){
            for($i = 1; $i < 1000; $i++) {
                if (isset($_POST["protection_id-$i"])){
                    $this->permission->delMasksByPermissionId($_POST['protection_id-' . $i], $_POST['entrance_exit-' . $i], $_POST['type_location-' . $i], $_POST['location-' . $i], '', $_SESSION['idCurrentPermission']);
                }
            }
        }
        if (isset($_POST['masking-submit'])){
            for($i = 1; $i < 1000; $i++) {
                if (isset($_POST["masking-$i"])){
                    
                    $this->protection->addMaskingStatuses($_POST['id'], $_POST['protection-' . $i], $_POST["masking-$i"]);
                }
                if (isset($_POST["unmasking-$i"])){
                    $this->protection->addMaskingStatuses($_POST['id'], $_POST['protection-' . $i],  '', $_POST["unmasking-$i"]);
                }
                if (isset($_POST["check_masking-$i"])){
                    $this->protection->addMaskingStatuses($_POST['id'], $_POST['protection-' . $i],  '', '', $_POST["check_masking-$i"]);
                }
                if (isset($_POST["check_unmasking-$i"])){
                    $this->protection->addMaskingStatuses($_POST['id'], $_POST['protection-' . $i],  '', '', '', $_POST["check_unmasking-$i"]);
                }
            }
        }


    }

    public function addStatusOfPermission($permissionId = 0, $idStatus = 0, $comment = '', $date = '', $time = '') {
        $comment = htmlspecialchars(trim($comment));

        if($date !== '') {
            $date = $date . ' ' .$time;
        } else {
            $date = date('d.m.Y H:i:s');
        }

        $this->statusLog->addStatusManagementLog($permissionId, $idStatus, $_COOKIE['user'], $comment, $date);

        $this->redirect('permission', '');
    }

    public function updatePermission($description, $addition) {
        $permission = $this->permission->getPermission($_SESSION['idCurrentPermission'])[0];
        $this->permission->updatePermission($permission['id'], $description, $addition,
                                            $permission['number'], $permission['subdivision_id'], $permission['untypical_work']);

        $this->redirect('permission', 'add');
    }

    public function updateNumber($firstNumber, $secondNumber) {
        $number = $firstNumber . '/' . $secondNumber;
        $permission = $this->permission->getPermission($_SESSION['idCurrentPermission'])[0];
        $this->permission->updatePermission($permission['id'], $permission['description'], $permission['addition'],
            $number, $permission['subdivision_id'], $permission['untypical_work']);

        $this->redirect('permission', 'add');
    }

    public function createPermission() {
        $subdivision = $this->user->getUsers($_COOKIE['user'], '', 2, 0)[0]['subdivision_id'];
        $this->permission->setPermission(0, '', '', '', $subdivision);
        $this->permission->connectUserAndPermission($_COOKIE['user'], $_SESSION['idCurrentPermission']);
        $this->statusLog->addStatusManagementLog($_SESSION['idCurrentPermission'], 1, $_COOKIE['user'], '');
        $supervisor = $this->user->getUsers(0, '', 2, $subdivision, '', 1);

        if(isset($supervisor[0])) {
            $supervisorId = $supervisor[0]['user_id'];
            $this->employee->addEmployee($supervisorId, $_SESSION['idCurrentPermission'], 5);
        }

        $this->redirect('permission', 'add');
    }

    public function delPermission($permissionId) {
        $this->permission->delPermission($permissionId);
        $this->redirect('permission', '');
    }

    public function editPermission($permissionId) {
        $_SESSION['idCurrentPermission'] = $permissionId;
        $this->redirect('permission', 'add');
    }

    public function createPermissionById($permissionId) {
        $db = DB::getMainDB();
        $objectStatusLog = new StatusLog($db);
        $objectEmployee = new Employee($db);
        $objectDate = new Date($db);
        $objectTypicalWork = new TypicalWork($db);
        $objectPermission = new Permission($db);

        $permission = $this->permission->getPermission($permissionId)[0];
        $objectPermission->setPermission(0, '', $permission['description'], $permission['addition'], $permission['subdivision_id'], $permission['untypical_work']);
        $dates = $this->date->getDates($permissionId);
        $objectStatusLog->addStatusManagementLog($_SESSION['idCurrentPermission'], 1, $_COOKIE['user'], '');
        $employees = $this->employee->getEmployee(0, $permissionId);
        $typicalWorks = $this->typicalWork->getTypicalWork($permissionId, $_COOKIE['user']);

        foreach ($typicalWorks as $typicalWork) {
            $objectTypicalWork->setTypicalWorks($_SESSION['idCurrentPermission'], $typicalWork['typical_work_id'], $typicalWork['description']);
        }

        foreach ($dates as $date) {
            $objectDate->setDate($date['date'], $date['from_time'], $date['to_time'], $_SESSION['idCurrentPermission']);
        }

        foreach ($employees as $employee) {
            $objectEmployee->addEmployee($employee['user_id'], $_SESSION['idCurrentPermission'], $employee['type_person_id']);
        }

        $this->redirect('permission', 'add');
    }

    public function getDispatcherStatuses():array {
        $statuses = $this->status->getStatuses();
        $result = [];

        foreach ($statuses as $status) {
            if(($status['id'] > 1)  && $status['id'] < 6) {
                $result[] =  $status;
            }
        }

        return $result;
    }

    protected function getAuthorStatuses():array {
       $result = [];
       $statuses = $this->status->getStatuses();

        foreach ($statuses as $status) {
            if($status['id'] !== 6) {
                $result[] =  $status;
            }
        }

       return $result;
    }

    public function getReplacementEngineerStatuses():array {
        $statuses = $this->status->getStatuses();
        $result = [];

        foreach ($statuses as $status) {
            if($status['id'] > 6) {
                $result[] =  $status;
            }
        }

        return $result;
    }

    public function getInspectingEngineerStatuses():array {
        $statuses = $this->status->getStatuses();
        $result = [];

        foreach ($statuses as $status) {
            if($status['id'] > 6) {
                $result[] =  $status;
            }
        }

        return $result;
    }



    public function setSessionsForFilter() {
        if(isset($_POST['date_start'])) {
            $_SESSION['date_start'] = $_POST['date_start'];
            $_SESSION['date_end'] = $_POST['date_end'];
        }

        if(isset($_POST['statuses'])) {
            $_SESSION['statuses'] = $_POST['statuses'];
        }

        $_SESSION['filter'] = $_POST['filter'];
        $this->redirect('permission', '');
    }

    protected function filterPermissionByDates($roles):array {
        if($roles['isAuthor']) {
            $permissions =  $this->permission->getPermission(0, '', $_COOKIE['user'], '', $_SESSION['date_start'], $_SESSION['date_end']);
        } else {
            $permissions =  $this->permission->getPermission(0, '', 0, '', $_SESSION['date_start'], $_SESSION['date_end']);
        }

        unset($_SESSION['date_start']);
        unset($_SESSION['date_end']);

        return $permissions;
    }

    protected function sortArrByDate($arr = []):array {
        usort($arr, function($a, $b){
            return (strtotime($b['date']) - strtotime($a['date']));
        });

        return $arr;
    }

    protected function filterPermissionByStatuses($role = []):array {
        $result = [];
        $permissions = [];
        $statuses = explode(' ', $_SESSION['statuses']);
        foreach ($statuses as $statusId) {
            if($role['isAuthor']) {
                $permissions =  $this->permission->getPermission(0, '', $_COOKIE['user'], '', '', '', $statusId);
            } elseif($role['isDispatcher']) {
                $date = date('Y.m.d');
                $permissions =  $this->permission->getPermission(0, '', 0, '', $date, $date, $statusId);
            } elseif($role['isReplacementEngineer']) {
                $permissions =  $this->permission->getPermission(0, '', 0, '', '', '');
            } else {
                $permissions = $this->permission->getPermission(0, '', $_COOKIE['user']);
            }

            foreach ($permissions as $permission) {
                $result[] = $permission;
            }
        }

        return $this->sortArrByDate($result);
    }

    protected function filterPermission($roles = []) {
        $permissionsFirst = [];
        $permissionsSecond = [];
        $permissions = [];

        if((isset($_SESSION['date_start']) && $_SESSION['date_start'] !== '') || (isset($_SESSION['date_end']) && $_SESSION['date_end'] !== '')) {
            $permissionsFirst = $this->filterPermissionByDates($roles);
        }
        if(isset($_SESSION['statuses']) && $_SESSION['statuses'] !== '') {
            $permissionsSecond = $this->filterPermissionByStatuses($roles);
        }

        if(count($permissionsFirst) > count($permissionsSecond) && count($permissionsSecond) > 0) {
            foreach ($permissionsFirst as $item1) {
                foreach ($permissionsSecond as $item2) {
                    if($item1['id'] === $item2['id']) {
                        $permissions[] = $item1;
                    }
                }
            }
        } elseif(count($permissionsFirst) < count($permissionsSecond) && count($permissionsFirst) > 0) {
            foreach ($permissionsSecond as $item1) {
                foreach ($permissionsFirst as $item2) {
                    if ($item1['id'] === $item2['id']) {
                        $permissions[] = $item1;
                    }
                }
            }
        } elseif(count($permissionsFirst) > 0) {
            $permissions = $permissionsFirst;
        } else {
            $permissions = $permissionsSecond;
        }

        unset($_SESSION['filter']);

        return $permissions;
    }

    protected function filterPermissionForDispatcher($permissions = []):array {
        $result = [];

        foreach ($permissions as $permission) {
            if($permission['status_id'] !== 1) {
                $result[] = $permission;
            }
        }

        return $result;
    }

    protected function filterPermissionForEngineer($permissions = []):array {
        $result = [];

        foreach ($permissions as $permission) {
            if(!(in_array($permission['status_id'], array(1,2,3)) )) {
                $result[] = $permission;
            }
        }

        return $result;
    }

    public function setNumPageToSession($numPage = 1) {
        $_SESSION['num_page'] = intval($numPage);
        $this->redirect('permission');
    }

    protected function getPermissions($roles = []):array {
        if(isset($_SESSION['filter'])) {
            $permissions = $this->filterPermission($roles);
        } elseif(isset($_SESSION['permission_search'])) {
            $permissions = $_SESSION['permission_search'];
            unset($_SESSION['permission_search']);
        } elseif(isset($_SESSION['archive-permissions'])) {
            if($roles['isDispatcher']) {
                $permissions = $this->pagination->getEntriesOfPage();
            } else {
                $permissions = $this->pagination->getEntriesOfPage($_COOKIE['user']);
            }
        } else {
            if($roles['isDispatcher']) {
                $date = date('Y.m.d');
                $permissions = $this->permission->getPermission(0, '', 0, '');
                $permissions = $this->filterPermissionForDispatcher($permissions);
            } elseif($roles['isReplacementEngineer']) {
                $date = date('Y.m.d');
                $permissions = $this->permission->getPermission();
                $permissions = $this->filterPermissionForEngineer($permissions);
                
            } elseif($roles['isInspectingEngineer']) {
                    $date = date('Y.m.d');
                    $permissions = $this->permission->getPermission();
                    $permissions = $this->filterPermissionForEngineer($permissions);
                    
                    }
            else {
                $permissions = $this->permission->getPermission(0, '', $_COOKIE['user']);
            }
        }

        if(!isset($_SESSION['archive-permissions']) && $this->isClosePermission($permissions)) {
            $this->stutteringPermissionsToArchive($permissions);
            $permissions = $this->getOperativePermissions($permissions);
        }

        if(isset($_SESSION['archive-permissions']) && $this->isRecoveryPermission($permissions)) {
            $this->stutteringPermissionsToArchive($permissions, 'trans');
//            $permissions = $this->getArchivePermissions($permissions);
        }

        return $this->setColorsToPermissions($permissions);
    }

    protected function isClosePermission($permissions = []):bool {
        foreach ($permissions as $permission) {
            if($permission['status_id'] === 13) {
                return true;
            }
        }

        return false;
    }

    protected function isRecoveryPermission($permissions = []):bool {
        foreach ($permissions as $permission) {
            if($permission['status_id'] <> 6) {
                return true;
            }
        }

        return false;
    }


    protected function getOperativePermissions($permissions = []):array {
        $result = [];

        foreach ($permissions as $permission) {
            if($permission['status_id'] !== 13) {
                $result[] = $permission;
            }
        }

        return $result;
    }

    protected function getArchivePermissions($permissions):array {
        $result = [];

        foreach ($permissions as $permission) {
            if($permission['status_id'] === 13) {
                $result[] = $permission;
            }
        }

        return $result;
    }

    protected function stutteringPermissionsToArchive($permissions, $nameDb = 'trans_archive') {
        if($nameDb === 'trans_archive') {
            $db = DB::getArchiveDB();
            $permissions = $this->getArchivePermissions($permissions);
        } else {
            $db = DB::getMainDB();
            $permissions = $this->getOperativePermissions($permissions);
        }

        $objectPermission = new Permission($db);
        $objectStatusLog = new StatusLog($db);
        $objectEmployee = new Employee($db);
        $objectDate = new Date($db);
        $objectTypicalWork = new TypicalWork($db);

        foreach ($permissions as $permission) {
            if($nameDb === 'trans_archive') {
                $objectPermission->setPermission($permission['id'], $permission['number'], $permission['description'],
                    $permission['addition'], $permission['subdivision_id'], $permission['untypical_work']);
            } else {
                $objectPermission->recoveryPermission($permission['id'], $permission['number'], $permission['description'],
                    $permission['addition'], $permission['subdivision_id'], $permission['untypical_work']);
            }

            $statusesLog = $this->statusLog->getStatusManagementLogs($permission['id']);
            foreach ($statusesLog as $statusLog) {
                $objectStatusLog->addStatusManagementLog($statusLog['permission_id'], $statusLog['status_id'], $statusLog['user_id'],
                    $statusLog['comment'], $statusLog['date_change_status'], $statusLog['date']);
            }

            $employees = $this->employee->getEmployee(0, $permission['id']);
            foreach ($employees as $employee) {
                $objectEmployee->addEmployee($employee['user_id'], $employee['permission_id'], $employee['type_person_id']);
            }

            $dates = $this->date->getDates($permission['id']);
            foreach ($dates as $date) {
                $objectDate->setDate($date['date'], $date['from_time'], $date['to_time'], $date['permissionid']);
            }

            $typicalWorks = $this->typicalWork->getTypicalWork($permission['id']);
            foreach ($typicalWorks as $typicalWork) {
                $objectTypicalWork->setTypicalWorks($typicalWork['permission_id'], $typicalWork['typical_work_id'], $typicalWork['description']);
            }

            $this->permission->delPermission($permission['id']);
        }

    }

    public function searchPermissions() {
        $roles = $this->role->getRoles($_COOKIE['user']);
        $_SESSION['search_info'] =  trim($_POST['search_info']);
        $search = '%' . trim($_POST['search_info']) . '%';

        if($roles['isAuthor']) {
            $permissions = $this->permission->getPermission(0, '', $_COOKIE['user'], $search);
        } else {

            if(isset($_SESSION['archive-permissions'])) {
                $permissions = $this->permission->getPermission(0, '', 0, $search);
                $permissions = $this->filterPermissionForDispatcher($permissions);
            } else {
                $date = date('Y.m.d');
                $permissions = $this->permission->getPermission(0, '', 0, $search, $date, $date);
                $permissions = $this->filterPermissionForDispatcher($permissions);
            }
        }

        $_SESSION['permission_search'] = $permissions;
        $this->redirect('permission', '');
    }

    protected function getSearch():string {
        $search = '';

        if(isset($_SESSION['search_info'])) {
            $search = $_SESSION['search_info'];
            unset($_SESSION['search_info']);
        }

        return $search;
    }

    protected function getStatuses($roles = []):array {
        $result = [];

        if($roles['isDispatcher']) {
            $result = $this->getDispatcherStatuses();
        } elseif($roles['isAuthor']) {
            $result = $this->getAuthorStatuses();
        } elseif($roles['isReplacementEngineer']) {
            $result = $this->getReplacementEngineerStatuses();
        } elseif($roles['isInspectingEngineer']) {
            $result = $this->getInspectingEngineerStatuses();
        }

        if(isset($_SESSION['statuses'])) {
            $arr = explode(' ', $_SESSION['statuses']);

            foreach ($result as &$item) {
                if(in_array($item['id'], $arr)) {
                    $item['active'] = true;
                }
            }

            unset($_SESSION['statuses']);
        } else {
            foreach ($result as &$item) {
                $item['active'] = true;
            }
        }

        return $result;
    }

    protected function getDate($nameDate = ''):string {
        $result = '';

        if(isset($_SESSION[$nameDate])) {
            $result = $_SESSION[$nameDate];
        }

        return $result;
    }

    protected function getDates($roles = []) {
        $result = [];

        if($roles['isAuthor']) {
            $result = $this->date->getDates(0, $_COOKIE['user']);
        } elseif($roles['isDispatcher']) {
            $result= $this->date->getDates();
        }

        return $result;
    }

    protected function getEmployee($typePersonID = 0, $roles = []) {
        $result = [];

        if($roles['isAuthor']) {
            $result = $this->employee->getEmployee($typePersonID, 0, $_COOKIE['user']);
        } elseif($roles['isDispatcher']) {
            $result = $this->employee->getEmployee($typePersonID);
        }
;
        return $result;
    }

    protected function getTypicalWorks($roles = []) {
        $result = [];

        if($roles['isAuthor']) {
            $result = $this->typicalWork->getTypicalWork(0, $_COOKIE['user']);
        } elseif($roles['isDispatcher']) {
            $result = $this->typicalWork->getTypicalWork(0, 0, 1);
        }

        return $result;
    }

    public function getIndexVarsToTwig() {
        $roles = $this->role->getRoles($_COOKIE['user']);
        $currentUser = $this->user->getUsers($_COOKIE['user'])[0];

        return ['permissions' => $this->getPermissions($roles),
            'protections' => $this->protection->getProtectionsOfPermissionThisStatuses($_COOKIE['user']),
            'author' => $currentUser,
            'dates' => $this->getDates($roles),
            'responsiblesForPreparation' =>  $this->getEmployee(2, $roles),
            'responsiblesForExecute' => $this->getEmployee(3, $roles),
            'responsiblesForControl' =>  $this->getEmployee(4, $roles),
            'typical_works' => $this->getTypicalWorks($roles),
            'message' => 'Совпадений не найдено',
            'search_info' => $this->getSearch(),
            'roles' => $roles,
            'statuses' => $this->getStatuses($roles),
            'date_start' => $this->getDate('date_start'),
            'date_end' => $this->getDate('date_end'),
            'user_fio' => $this->getUserFio($this->db),
            'is_archive' => $this->isArchive(),
            'nums_pages' => $this->pagination->getArrNumPages()];
    }

    public function setSessionArchive() {
        $_SESSION['archive-permissions'] = true;
        $this->redirect('permission');
    }

    public function unsetSessionArchive() {
        unset($_SESSION['archive-permissions']);
        unset($_SESSION['num_page']);
        $this->redirect('permission');
    }

    protected function isArchive():bool {
        if(isset($_SESSION['archive-permissions'])) {
            return true;
        } else {
            return false;
        }
    }

    protected function setColorsToPermissions($permissions = []):array {
        foreach ($permissions as &$permission) {
            if($permission['status_id'] === 1) {
                $permission['color'] = 'violet';
            } elseif($permission['status_id'] === 2) {
                $permission['color'] = 'beige';
            } elseif($permission['status_id'] === 3) {
                $permission['color'] = 'blue';
            } elseif($permission['status_id'] === 4) {
                $permission['color'] = 'green';
            } elseif($permission['status_id'] === 5) {
                $permission['color'] = 'yellow';
            } elseif($permission['status_id'] === 6) {
                $permission['color'] = 'gray';
            } elseif($permission['status_id'] === 7) {  /* Требуется маскирование */
                $permission['color'] = 'red';
            } elseif($permission['status_id'] === 8) {  /* Маскирование проведено */
                $permission['color'] = 'brown';
            } elseif($permission['status_id'] === 9) {  /* Проверка маскирования проведена */
                $permission['color'] = 'purple';
            } elseif($permission['status_id'] === 10) {  /* Требуется демаскирование */
                $permission['color'] = 'orange';
            } elseif($permission['status_id'] === 11) {  /* Демаскирование проведено */
                $permission['color'] = 'lime';
            } elseif($permission['status_id'] === 12) {  /* Проверка демаскирования проведена */
                $permission['color'] = 'darkgreen';
            } elseif($permission['status_id'] === 13) {  /* Завершить */
                $permission['color'] = 'white';
            }
        }

        return $permissions;
    }

    public function delTypicalWork($id) {
        $this->typicalWork->delTypicalWork(0, $id);
    }

    public function delResponsible($idEmployee, $idTypePerson) {
        $this->employee->delEmployee($idEmployee, $_SESSION['idCurrentPermission'], $idTypePerson);
    }

    protected function getSupervisor() {
        $supervisor = $this->employee->getEmployee(5, $_SESSION['idCurrentPermission']);

        if(isset($supervisor[0])) {
            $supervisor = $supervisor[0];
        }

        return $supervisor;
    }

    public function delEmployee($employeeId = 0, $permissionId = 0, $typeResponsibleId = 0) {
        $this->employee->delEmployee($employeeId, $permissionId, $typeResponsibleId);
    }

    public function getAddVarsToTwig():array {
       if (isset($_REQUEST["id_responsible_for_preparation"])) {
            return ['ajax' => true];
        } else {
            return ['current_typical_works' => $this->typicalWork->getTypicalWork($_SESSION['idCurrentPermission']),
                'current_dates' => $this->date->getDates($_SESSION['idCurrentPermission']),
                'permission' => $this->permission->getPermission($_SESSION['idCurrentPermission'])[0],
                'supervisorOfResponsibleForExecute' => $this->getSupervisor(),
                'responsiblesForPreparation' => $this->employee->getEmployee(2, $_SESSION['idCurrentPermission']),
                'responsiblesForExecute' => $this->employee->getEmployee(3, $_SESSION['idCurrentPermission']),
                'responsiblesForControl' => $this->employee->getEmployee(4, $_SESSION['idCurrentPermission']),
                'roles' => $this->role->getRoles($_COOKIE['user']),
                'protections' => $this->permission->getProtectionsOfPermission($_SESSION['idCurrentPermission']),
                'user_fio' => $this->getUserFio($this->db)];
        }
    }
}