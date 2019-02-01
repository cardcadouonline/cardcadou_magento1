<?php
/**
 * CardCadou Block Admin Transactions
 *
 * Block Admin Transactions
 *
 * @category   Payment
 * @package    CardCadou
 * @subpackage CardCadou_Block
 * @author     Alin Pop <alin.pop@pitechnologies.ro>
 * @author     Izabella Papp <izabella.papp@pitechnologies.ro>
 * @license    https://cardcadou.online/ CardCadou
 * @link       https://cardcadou.online/
 */

class Pitech_CardCadou_Block_Adminhtml_Transaction extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    /**
     * Pitech_CardCadou_Block_Adminhtml_Transaction constructor.
     */
    public function __construct()
    {
        $this->_blockGroup = 'cardcadou/adminhtml_transaction_grid';
        $this->_controller = 'adminhtml_cardcadou';
        $this->_headerText = $this->__('CardCadou');

        parent::__construct();
        $this->_removeButton('add');
    }
}
