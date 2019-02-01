<?php
/**
 * CardCadou Admin Controller
 *
 * Admin Controller
 *
 * @category   Payment
 * @package    CardCadou
 * @subpackage CardCadou_Controller
 * @author     Alin Pop <alin.pop@pitechnologies.ro>
 * @author     Izabella Papp <izabella.papp@pitechnologies.ro>
 * @license    https://cardcadou.online/ CardCadou
 * @link       https://cardcadou.online/
 */

class Pitech_CardCadou_Adminhtml_CardcadouController extends Mage_Adminhtml_Controller_Action
{
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('cardcadou/transaction');
    }


    public function indexAction()
    {
        $this->loadLayout();
        $this->_title($this->__('Sales'))->_title($this->__('CardCadou'));
        $this->_setActiveMenu('sales/sales');
        $this->renderLayout();
    }


    public function gridAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('cardcadou/adminhtml_transaction_grid')->toHtml()
        );
    }
}