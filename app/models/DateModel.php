<?php

namespace models;

use core\DB;
use widgets\date\Date;

class DateModel extends AppModel
{
    protected $date;
    protected $db;

    public function __construct() {
        $this->db = DB::getMainDB();
        $this->date = new Date($this->db);
    }

    public function setDates() {
        $this->date->delDates($_SESSION['idCurrentPermission']);
        $counter = 0;

        while (isset($_POST['date-' . $counter + 1])) {
            $counter++;
            $this->date->setDate($_POST['date-' . $counter], $_POST['from-time-' . $counter], $_POST['to-time-' . $counter], $_SESSION['idCurrentPermission']);
        }

        $this->redirect('permission', 'add');
    }

    public function getIndexVarsToTwig():array {
        return ['dates' => $this->date->getDates($_SESSION['idCurrentPermission']),
            'user_fio' => $this->getUserFio($this->db)];
    }
}