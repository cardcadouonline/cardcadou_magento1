<?php
/**
 * CardCadou Block Admin Transactions Grid
 *
 * Block Admin Transactions Grid
 *
 * @category   Payment
 * @package    CardCadou
 * @subpackage CardCadou_Block
 * @author     Alin Pop <alin.pop@pitechnologies.ro>
 * @author     Izabella Papp <izabella.papp@pitechnologies.ro>
 * @license    https://cardcadou.online/ CardCadou
 * @link       https://cardcadou.online/
 */

class Pitech_CardCadou_Block_Adminhtml_Transaction_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Pitech_CardCadou_Block_Adminhtml_Transaction_Grid constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setDefaultSort('id');
        $this->setId('cardcadou_grid');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    /**
     * @return string
     */
    protected function _getCollectionClass()
    {
        return 'cardcadou/transaction_collection';
    }

    /**
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel($this->_getCollectionClass());
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumn('id', array(
            'header' => $this->__('ID'),
            'index' => 'id'
        ));

        $this->addColumn('order_id', array(
            'header' => $this->__('Order Id'),
            'index' => 'order_id'
        ));

        $this->addColumn('card_serie', array(
            'header' => $this->__('Card Series'),
            'index' => 'card_serie'
        ));

        $this->addColumn('amount', array(
            'header' => $this->__('Amount'),
            'type' => 'currency',
            'index' => 'amount'
        ));

        $this->addColumn('api_reference', array(
            'header' => $this->__('API Reference'),
            'index' => 'api_reference'
        ));

        $this->addColumn('order_status', array(
            'header' => $this->__('Order Status'),
            'index' => 'order_status'
        ));

        $this->addColumn('order_deny_code', array(
            'header' => $this->__('Order Deny Code'),
            'index' => 'order_deny_code',
            'type' => 'options',
            'options' => Mage::getModel('cardcadou/cardcadou')->cardcadou()->getOrderErrors()
        ));

        $this->addColumn('confirmation_status', array(
            'header' => $this->__('Confirmation Status'),
            'index' => 'confirmation_status'
        ));

        $this->addColumn('deny_code', array(
            'header' => $this->__('Deny Code'),
            'index' => 'deny_code',
            'type' => 'options',
            'options' => Mage::getModel('cardcadou/cardcadou')->cardcadou()->getConfirmErrors()
        ));

        $this->addColumn('cancel_status', array(
            'header' => $this->__('Cancel Status'),
            'index' => 'cancel_status'
        ));

        $this->addColumn('cancel_deny_code', array(
            'header' => $this->__('Cancel Deny Code'),
            'index' => 'cancel_deny_code',
            'type' => 'options',
            'options' => Mage::getModel('cardcadou/cardcadou')->cardcadou()->getConfirmErrors()
        ));

        $this->addColumn('uuid', array(
            'header' => $this->__('Last UUID'),
            'index' => 'uuid'
        ));

        $this->addColumn('updated_at', array(
            'header' => $this->__('Updated At'),
            'type' => 'datetime',
            'index' => 'updated_at'
        ));

        return parent::_prepareColumns();
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current' => true));
    }
}
