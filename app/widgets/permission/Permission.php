<?php

namespace widgets\permission;

class Permission
{
    protected $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getPermission($permissionId = 0, $number = '', $userId = 0, $search = '', $dateStart = '', $dateEnd = '', $statusId = 0, $numPage = 0):array {
        $query = "SELECT * FROM get_permission(:permission_id, :number, :user_id, :search, :date_start, :date_end, :status_id, :num_page)";
        $stmt = $this->db->prepare($query);
        $stmt->execute(array('permission_id' => $permissionId, 'number' => $number, 'user_id' => $userId, 'search' => $search,
            'date_start' => $dateStart, 'date_end' => $dateEnd, 'status_id' => $statusId, 'num_page' => $numPage));
        $results = $stmt->fetchAll();

        if($results) {
            foreach ($results as &$result) {
                $arrNumbers = explode('/', $result['number']);
                $result['first_number'] = $arrNumbers[0];

                if(isset($arrNumbers[1])) {
                    $result['second_number'] = $arrNumbers[1];
                } else {
                    $result['second_number'] = $arrNumbers[0];
                }
            }
        } else {
            return [];
        }

        return $results;
    }

    public function setPermission($permissionId = 0, $number = '', $description = '', $addition = '', $subdivisionId = 0, $untypicalWork = ''):void {
        $query = "SELECT * FROM add_permission(:permission_id, :number, :description, :addition, :subdivision_id, :untypical_work)";
        $stmt = $this->db->prepare($query);
        $stmt->execute(array('permission_id' => $permissionId, 'number' => $number,
                             'description' => $description, 'addition' => $addition,
                             'subdivision_id' => $subdivisionId, 'untypical_work' => $untypicalWork));
        $_SESSION['idCurrentPermission'] =  $stmt->fetch()['id'];
    }

    public function updatePermission($permissionId, $description, $addition, $number, $subdivisionId, $untypicalWork):void {
        $number = strval($number);
        $query = "SELECT * FROM update_permission(:permission_id, :number, :description, :addition, :subdivision_id, :untypical_work)";
        $stmt = $this->db->prepare($query);
        $stmt->execute(array('permission_id' => $permissionId, 'description' => $description, 'addition' => $addition,
            'number' => $number, 'subdivision_id' => $subdivisionId, 'untypical_work' => $untypicalWork));
    }

    public function recoveryPermission($permissionId, $description, $addition, $number, $subdivisionId, $untypicalWork):void {
        $query = "SELECT * FROM recovery_permission(:permission_id, :number, :description, :addition, :subdivision_id, :untypical_work)";
        $stmt = $this->db->prepare($query);
        $stmt->execute(array('permission_id' => $permissionId, 'description' => $description, 'addition' => $addition,
            'number' => $number, 'subdivision_id' => $subdivisionId, 'untypical_work' => $untypicalWork));
    }

    public function connectUserAndPermission($userId, $permissionId):void {
        $query = "SELECT * FROM connect_user_and_permission(:user_id, :permission_id)";
        $stmt = $this->db->prepare($query);
        $stmt->execute(array('user_id' => $userId, 'permission_id' => $permissionId));
    }

    public function delPermission($permissionId):void {
        $query = "SELECT * FROM del_permission(:permission_id)";
        $stmt = $this->db->prepare($query);
        $stmt->execute(array('permission_id' => $permissionId));
    }

    public function getProtectionsOfPermission($permissionId) {
        $query = "SELECT * FROM get_protections_of_permission(:permission_id)";
        $stmt = $this->db->prepare($query);
        $stmt->execute(array('permission_id' => $permissionId));
        return $stmt->fetchAll();
    }

    public function delMasksByPermissionId($protectionId, $entrance_exit, $type_locations, $locations, $vtor, $permissionId) {
        $query = "SELECT * FROM del_masks_by_permission_id(:protection_id, :entrance_exit, :type_locations, :locations, :vtor , :permission_id)";
        $stmt = $this->db->prepare($query);
        $stmt->execute(array('protection_id' => $protectionId, 'entrance_exit' => $entrance_exit, 'type_locations' => $type_locations, 'locations' => $locations, 'vtor' =>  $vtor,  'permission_id' => $permissionId));
    }
}