<?php

/**
 * CardCadou Data Helper
 *
 * Data Helper
 *
 * @category   Payment
 * @package    CardCadou
 * @subpackage CardCadou_Helper
 * @author     Alin Pop <alin.pop@pitechnologies.ro>
 * @author     Izabella Papp <izabella.papp@pitechnologies.ro>
 * @license    https://cardcadou.online/ CardCadou
 * @link       https://cardcadou.online/
 */

class Pitech_CardCadou_Helper_Data extends Mage_Core_Helper_Abstract
{
    CONST XML_PATH_CARDCADOU_IS_ENABLED = 'payment/cardcadou/active';
    CONST XML_PATH_CARDCADOU_PARTNER_CODE = 'payment/cardcadou/partner_code';
    CONST XML_PATH_CARDCADOU_SECRET_KEY = 'payment/cardcadou/secret_key';
    CONST XML_PATH_CARDCADOU_METHOD_NAME = 'payment/cardcadou/method_name';
    CONST XML_PATH_CARDCADOU_METHOD_CALL_TO_ACTION = 'payment/cardcadou/method_call_to_action';
    CONST XML_PATH_CARDCADOU_ACCEPTED_WHEN_ORDER_STATUS = 'payment/cardcadou/accepted_when_order_status';
    CONST XML_PATH_CARDCADOU_CANCELED_WHEN_ORDER_STATUS = 'payment/cardcadou/canceled_when_order_status';
    CONST XML_PATH_CARDCADOU_TIMEOUT = 'payment/cardcadou/timeout';

    /**
     * Check if module is enabled
     *
     * @return int
     */
    public function isEnabled()
    {
        return Mage::getStoreConfig(self::XML_PATH_CARDCADOU_IS_ENABLED, null);
    }

    /**
     * Get the API partner code
     *
     * @return String
     */
    public function getPartnerCode()
    {
        return Mage::getStoreConfig(self::XML_PATH_CARDCADOU_PARTNER_CODE, null);
    }

    /**
     * Get the API secret key
     *
     * @return String
     */
    public function getSecretKey()
    {
        return Mage::getStoreConfig(self::XML_PATH_CARDCADOU_SECRET_KEY, null);
    }

    /**
     * Get the name of the payment method
     *
     * @param integer $store ID of the current store
     *
     * @return String
     */
    public function getMethodName($store = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_CARDCADOU_METHOD_NAME, $store);
    }

    /**
     * Get the text of call to action button
     *
     * @param integer $store ID of the current store
     *
     * @return String
     */
    public function getCallToActionText($store = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_CARDCADOU_METHOD_CALL_TO_ACTION, $store);
    }

    /**
     * Get the order status when the card purchase should be confirmed
     *
     * @return String
     */
    public function getAcceptedStatus()
    {
        return Mage::getStoreConfig(self::XML_PATH_CARDCADOU_ACCEPTED_WHEN_ORDER_STATUS, null);
    }

    /**
     * Get the order status when the card purchase should be canceled
     *
     * @return String
     */
    public function getCanceledStatus()
    {
        return Mage::getStoreConfig(self::XML_PATH_CARDCADOU_CANCELED_WHEN_ORDER_STATUS, null);
    }

    /**
     * Get the timeout for blocked cards
     *
     * @return int
     */
    public function getTimeOut()
    {
        return (int)Mage::getStoreConfig(self::XML_PATH_CARDCADOU_TIMEOUT, null);
    }
}
