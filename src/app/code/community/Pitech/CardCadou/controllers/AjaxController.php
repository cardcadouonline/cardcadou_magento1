<?php

/**
 * CardCadou Ajax Controller
 *
 * Ajax Controller
 *
 * @category   Payment
 * @package    CardCadou
 * @subpackage CardCadou_Controller
 * @author     Alin Pop <alin.pop@pitechnologies.ro>
 * @author     Izabella Papp <izabella.papp@pitechnologies.ro>
 * @license    https://cardcadou.online/ CardCadou
 * @link       https://cardcadou.online/
 */

class Pitech_CardCadou_AjaxController extends Mage_Core_Controller_Front_Action
{
    /**
     * Validate CardCadou in checkout.
     */
    public function verifyAction()
    {
        $_cardNumber = $this->getRequest()->getParam('card');

        $result = Mage::getModel('cardcadou/cardcadou')->cardcadou()->verifyCard($_cardNumber);

        if (isset($result['error'])) {
			Mage::log($result, null, 'cardcadou.log');
            $response['status'] = 0;
            $response['error'] = $this->__($result['error']['message']);
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
            return;
        }

        $status = $result['status'];
        $value = $result['value'];

        if ($status == 'VALID') {
            $subtotal = Mage::getModel('checkout/session')->getQuote()->getSubtotal();

            if ($subtotal >= $value) {
                Mage::getSingleton('checkout/session')->setCouponCode($_cardNumber);
                Mage::getSingleton('checkout/session')->setCouponAmount($value);
                $response['status'] = 1;
                $response['message'] = $this->__('The coupon has been successfully applied.');
                $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
            } else {
                $response['status'] = 0;
                $response['error'] = $this->__('The coupon can not be applied. The value of the coupon is greater than the total order amount.');
                $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
            }
        } else {
            Mage::getSingleton('checkout/session')->unsetData('coupon_code');
            Mage::getSingleton('checkout/session')->unsetData('coupon_amount');
            $response['status'] = 0;
            $response['error'] = $this->__('Coupon code is not valid.');
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
        }
    }

    /**
     * Remove CardCadou from the checkout.
     */
    public function removeAction()
    {
        try {
            Mage::getSingleton('checkout/session')->unsetData('coupon_code');
            Mage::getSingleton('checkout/session')->unsetData('coupon_amount');
            $response['status'] = 1;
            $response['message'] = $this->__('The coupon has been successfully removed.');
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
        } catch (Exception $exception) {
            $response['status'] = 0;
            $response['error'] = $this->__('An error occurred while removing the coupon.');
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
        };
    }
}
