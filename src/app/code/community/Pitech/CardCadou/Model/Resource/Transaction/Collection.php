<?php

/**
 * CardCadou Transaction Collection
 *
 * Model Transaction Collection
 *
 * @category   Payment
 * @package    CardCadou
 * @subpackage CardCadou_Model
 * @author     Alin Pop <alin.pop@pitechnologies.ro>
 * @author     Izabella Papp <izabella.papp@pitechnologies.ro>
 * @license    https://cardcadou.online/ CardCadou
 * @link       https://cardcadou.online/
 */

class Pitech_CardCadou_Model_Resource_Transaction_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('cardcadou/transaction');
    }
}