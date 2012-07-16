<?php namespace Helium;

/*
 * References.php
 * Copyright: Bryan Healey 2010, 2011 (bryan@bryanhealey.com)
 * License: GNU General Public License (v3)
 * Purpose: Model for References data objects
 */

class References extends Model {
    protected $class = 'References';
    protected $table = 'ref_options';
    protected $primary = 'ref_type_id';

    public $validate = array(
        'ref_type'=>array('filled',true)
    );

    public $schema = array(
        'ref_type_id'=>array('type'=>'int(10)','isNull'=>false,'isPrimary'=>true),
        'ref_type'=>array('type'=>'varchar(100)','isNull'=>true)
    );

    function __construct() {
        // nothing to do here...
    }

    /**
     *
     * function: getReferenceTypeId
     * Used for getting a reference ID by string
     * @access public
     * @param string $type
     * @return [boolean,int]
     */
    function getReferenceTypeId($type) {
        if ($type) {
            $sql = 'SELECT ref_type_id FROM ref_options WHERE ref_type = ?;';
            if ($result = $this->getAll($sql,array($type))) return $result[0]->ref_type_id;
            else {
                $sql = 'INSERT INTO ref_options (ref_type) VALUES (?);';
                $nref = $this->insert($sql,array($type));
                if ($nref) return $nref;
                else return false;
            }
        } else return false;
    }

    /**
     *
     * function: getReferenceById
     * Used for getting a reference by ID
     * @access public
     * @param int $id
     * @return [boolean,string]
     */
    function getReferenceById($id) {
        if ($id) {
            $sql = 'SELECT ref_type FROM ref_options WHERE ref_type_id = ?;';
            if ($result = $this->getAll($sql,array($id))) return $result[0]->ref_type;
            else return false;
        } else return false;
    }
}


