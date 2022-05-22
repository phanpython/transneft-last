<?php

namespace widgets\protection;

use core\DB;

class Protection
{
    protected $db;

    public function __construct($db) {
        $this->db = $db;
    }


    public function setMaskingProtections($protectionId, $entrance_exit, $type_locations, $locations, $vtor, $permissionId) {
        $query = "SELECT * FROM set_masking_protections(:protection_id, :entrance_exit, :type_locations, :locations, :vtor ,  :permission_id)";
        $stmt = $this->db->prepare($query);
        $stmt->execute(array('protection_id' => $protectionId, 'entrance_exit' => $entrance_exit, 'type_locations' => $type_locations, 'locations' => $locations, 'vtor' =>  $vtor,  'permission_id' => $permissionId));
    }
    
    /* ТУ */

    public function getProtectionsTu($tuname):array {
        $query = "SELECT * FROM get_protections_tu(:tuname)";
        $stmt = $this->db->prepare($query);
        $stmt->execute(array('tuname' => $tuname));
        return $stmt->fetchAll();
    }

    public function getProtectionsTuSearch($tuname, $search):array {
        $query = "SELECT * FROM get_protections_tu_search(:tuname, :search)";
        $stmt = $this->db->prepare($query);
        $stmt->execute(array('npsname' => $tuname, 'search' => $search));

        return $stmt->fetchAll();
    }


    /* НПС */ 

    public function getProtectionsNps($npsname, $typeobjectId):array {
        $query = "SELECT * FROM get_protections_nps(:npsname, :typeobject_id)";
        $stmt = $this->db->prepare($query);
        $stmt->execute(array('npsname' => $npsname, 'typeobject_id' => $typeobjectId));
        return $stmt->fetchAll();
    }

    public function getProtectionsNpsSearch($npsname, $search):array {
        $query = "SELECT * FROM get_protections_nps_search(:npsname, :search)";
        $stmt = $this->db->prepare($query);
        $stmt->execute(array('npsname' => $npsname, 'search' => $search));

        return $stmt->fetchAll();
    }


    /* ЛУ */

    public function getProtectionsLu($luname):array {
        $query = "SELECT * FROM get_protections_lu(:luname)";
        $stmt = $this->db->prepare($query);
        $stmt->execute(array('luname' => $luname));
        return $stmt->fetchAll();
    }

    public function getProtectionsLuSearch($luname, $search):array {
        $query = "SELECT * FROM get_protections_lu_search(:luname, :search)";
        $stmt = $this->db->prepare($query);
        $stmt->execute(array('luname' => $luname, 'search' => $search));

        return $stmt->fetchAll();
    }

    /* При вводе типовой работы  в разрешение добавляются защиты */

    public function setMaskingProtectionsFromTypicalWorks($protectionId, $vtorId, $entranceId, $permissionId):array {
        $query = "SELECT * FROM set_masking_protections_from_typical_works(:protection_id, :vtor_id, :entrance_id, :permission_id)";
        $stmt = $this->db->prepare($query);
        $stmt->execute(array('protection_id' => $protectionId, 'vtor_id' => $vtorId, 'entrance_id' => $entranceId, 'permission_id' => $permissionId));

        return $stmt->fetchAll();
    }

    public function getMaskingProtectionsFromTypicalWorks($work, $permissionId):array {
        $query = "SELECT * FROM get_masking_protections_from_typical_works(:work, :permission_id)";
        $stmt = $this->db->prepare($query);
        $stmt->execute(array('work' => $work, 'permission_id' => $permissionId));

        return $stmt->fetchAll();
    }

    /* Вернуть только те типы защит, у которых нет родителя */

    public function getTypesWithoutParent():array {
        $query = "SELECT * FROM get_types_without_parent";
        $stmt = $this->db->query($query);
        
        return $stmt->fetchAll();
    }

    
    public function getAllTypes():array {
        $query = "SELECT * FROM get_all_types";
        $stmt = $this->db->query($query);

        return $stmt->fetchAll();
    }

    public function getTypesChildren($types, $idParent):array {
        $result = [];

        foreach ($types as $type) {
            if($type['parent_id'] === $idParent) {
                $fl = true;

                foreach ($result as $item) {
                    if($item['name'] === $type['name']) {
                        $fl = false;
                    }
                }

                if($fl) {
                    $result[] = $type;
                }
            }
        }

        return $result;
    }

    public function setTypes() {
        $query = "SELECT * FROM get_types";
        $stmt = $this->db->query($query);

        $this->types = $stmt->fetchAll();
    }

    public function getTypes():array {
        return $this->types;
    }
    public function getProtectionsOfPermissionThisStatuses($permissionId) {
        $query = "SELECT * FROM get_protections_of_permission_this_statuses(:permission_id)";
        $stmt = $this->db->prepare($query);
        $stmt->execute(array('permission_id' => $permissionId));
        return $stmt->fetchAll();
    }

    public function addMaskingStatuses($permissionId, $protection, $masking = '', $unmasking = '', $check_masking = '',  $check_unmasking = '') {
        $query = "SELECT * FROM add_masking_statuses( :permission_id, :protection, :masking, :unmasking, :check_masking, :check_unmasking)";
        $stmt = $this->db->prepare($query);
        $stmt->execute(array('permission_id' => $permissionId, 'protection' => $protection,  'masking' => $masking, 'unmasking' => $unmasking, 'check_masking' => $check_masking, 'check_unmasking' => $check_unmasking));

        return $stmt->fetchAll();
    }

    public function getObject($objectId = 0, $parentId = 0) {
        $query = "SELECT * FROM get_object(:object_id, :parent_id)";
        $stmt = $this->db->prepare($query);
        $stmt->execute(array('object_id' => $objectId, 'parent_id' => $parentId));

        return $stmt->fetchAll();
    }
    

}