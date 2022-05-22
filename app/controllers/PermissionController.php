<?php

namespace controllers;

use core\Twig;
use models\PermissionModel;

class PermissionController extends AppController
{
    private $model;

    public function indexAction() {
        $this->checkAuthorization();
        $this->setMeta('Разрешения');
        $this->model = new PermissionModel();

        if(isset($_POST['del-permission'])) {
            $this->model->delPermission($_POST['id']);
        }elseif(isset($_POST['edit-permission'])) {
            $this->model->editPermission($_POST['id']);
        } elseif(isset($_POST['create-permission-by'])) {
            $this->model->createPermissionById($_POST['id']);
        } elseif(isset($_POST['cancel-agreement-permission'])) {
            $this->model->addStatusOfPermission($_POST['id'], 1);
        } elseif(isset($_POST['agreement-permission'])) {
            $this->model->addStatusOfPermission($_POST['id'], 2);
        } elseif(isset($_POST['cancel-apply-permission'])) {
            $this->model->addStatusOfPermission($_POST['id'], 2, $_POST['comment'], $_POST['date'], $_POST['time']);
        } elseif(isset($_POST['apply-permission'])) {
            $this->model->addStatusOfPermission($_POST['id'], 3, $_POST['comment'], $_POST['date'], $_POST['time']);
        } elseif(isset($_POST['activemasking-permission'])) {
            $this->model->addStatusOfPermission($_POST['id'], 7);
        } elseif(isset($_POST['masking-submit'])) {
            if($_POST['masking-submit'] == 'masking'){
                $this->model->addStatusOfPermission($_POST['id'], 8);
            } elseif($_POST['masking-submit'] == 'check_masking'){
                $this->model->addStatusOfPermission($_POST['id'], 9);
            } elseif($_POST['masking-submit'] == 'unmasking'){
                $this->model->addStatusOfPermission($_POST['id'], 11);
            } elseif($_POST['masking-submit'] == 'check_unmasking'){
                $this->model->addStatusOfPermission($_POST['id'], 12);
            }
        } elseif(isset($_POST['open-permission'])) {
            $this->model->addStatusOfPermission($_POST['id'], 4, $_POST['comment'], $_POST['date'], $_POST['time']);
        } elseif(isset($_POST['pause-permission'])) {
            $this->model->addStatusOfPermission($_POST['id'], 5, $_POST['comment'], $_POST['date'], $_POST['time']);
        } elseif(isset($_POST['close-permission'])) {;
            $this->model->addStatusOfPermission($_POST['id'], 6, $_POST['comment'], $_POST['date'], $_POST['time']);
        } elseif(isset($_POST['activeunmasking-permission'])) {
            $this->model->addStatusOfPermission($_POST['id'], 10);
        } elseif(isset($_POST['complete-permission'])) {
            $this->model->addStatusOfPermission($_POST['id'], 13);
        }  elseif(isset($_POST['recovery-permission'])) {
            $this->model->addStatusOfPermission($_POST['id']);
        } elseif(isset($_POST['filter'])) {
            $this->model->setSessionsForFilter();
        } elseif(isset($_POST['search_info'])) {
            $this->model->searchPermissions();
        } elseif(isset($_POST['archive-permissions'])) {
            $this->model->setSessionArchive();
        } elseif(isset($_POST['operative-permissions'])) {
            $this->model->unsetSessionArchive();
        } elseif(isset($_GET['num_page'])) {
            $this->model->setNumPageToSession($_GET['num_page']);
        }

        $this->setIndexVarsToTwig();
    }

    public function addAction() {
        $this->checkAuthorization();
        $this->setMeta('Добавить разрешение');
        $this->model = new PermissionModel();

        if (isset($_REQUEST["id_type_work"])) {
            $this->model->delTypicalWork($_REQUEST["id_type_work"]);
        } elseif(isset($_REQUEST['id_responsible'])) {
            $this->model->delResponsible($_REQUEST['id_responsible'], $_REQUEST['id_type_person']);
        } elseif(isset($_POST['update-permission'])) {
            $this->model->updatePermission($_POST['description'], $_POST['addition']);
        } elseif(isset($_POST['create-permission'])) {
            $this->model->createPermission();
        } elseif(isset($_POST['edit-number'])) {
            $this->model->updateNumber($_POST['first_number'], $_POST['second_number']);
        } elseif(isset($_POST['filter'])) {
            $this->model->setSessionsForFilter();
        } elseif(isset($_POST['search_info'])) {
            $this->model->searchPermissions();
        } elseif(isset($_REQUEST['id_responsible_for_preparation'])) {
            $this->model->delEmployee($_REQUEST["id_responsible_for_preparation"], $_SESSION['idCurrentPermission'], 2);
        }

        $this->setAddVarsToTwig();
    }

    public function setAddVarsToTwig() {
        $arr = $this->model->getAddVarsToTwig();
        Twig::addVarsToArrayOfRender($arr);
    }

    public function setIndexVarsToTwig(){
        $arr = $this->model->getIndexVarsToTwig();
        Twig::addVarsToArrayOfRender($arr);
    }
}