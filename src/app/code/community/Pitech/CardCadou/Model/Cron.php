<?php

/**
 * CardCadou
 *
 * Model Cron
 *
 * @category   Payment
 * @package    CardCadou
 * @subpackage CardCadou_Model
 * @author     Alin Pop <alin.pop@pitechnologies.ro>
 * @author     Izabella Papp <izabella.papp@pitechnologies.ro>
 * @license    https://cardcadou.online/ CardCadou
 * @link       https://cardcadou.online/
 */

class Pitech_CardCadou_Model_Cron
{
    /**
     * Cancel blocked cards when time is expired.
     */
    public function cancelBlockedCards()
    {
        $timeout = Mage::getStoreConfig('payment/cardcadou/timeout', null);
        $transactionModel = Mage::getModel('cardcadou/transaction');
        $_apiCardCadou = Mage::getModel('cardcadou/cardcadou')->cardcadou();
        $transactionCollection = $transactionModel->getCollection()->addFieldToFilter('order_status', array('nlike' => 'ACCEPTED'))
                        ->addFieldToFilter('cancel_status', array('nlike' => 'ACCEPTED'));
        $ordersCollection = Mage::getModel('sales/order');
        $nowDate = new DateTime(date('y-m-d h:m:s'));
        foreach ($transactionCollection as $item) {
            $date = new DateTime($item->getUpdatedAt());
            $min = "+" . $timeout . " minutes";
            $date->modify($min);
            if ($date < $nowDate) {
                $payment = $ordersCollection->load($item->getOrderId(), 'increment_id')->getPayment()->getMethodInstance()->getCode();

                if ($payment == 'cashondelivery') {
                    $_orderType = $_apiCardCadou::TYPE_CONFIRM_RAMBURS;
                } else {
                    $_orderType = $_apiCardCadou::TYPE_CONFIRM_ONLINE;
                }

                $result = $_apiCardCadou->cancelCard($item->getOrderId(), $_orderType);

                if (isset($result['error'])) {
                    Mage::log($result, null, 'cardcadou.log');
                    $transactionData = array(
                        'id' => $item->getId(),
                        'order_status' => $item->getOrderStatus(),
                        'order_deny_code' => $item->getOrderDenyCode(),
                        'confirmation_status' => '',
                        'deny_code' => 0,
                        'cancel_status' => 'API ERROR',
                        'cancel_deny_code' => 0,
                        'updated_at' => now(),
                    );
                } else {
                    $transactionData = array(
                        'id' => $item->getId(),
                        'order_status' => $item->getOrderStatus(),
                        'order_deny_code' => $item->getOrderDenyCode(),
                        'confirmation_status' => '',
                        'deny_code' => 0,
                        'cancel_status' => $result['confirmation_status'],
                        'cancel_deny_code' => $result['confirmation_deny_code'],
                        'uuid' => $result['transaction_uuid'],
                        'updated_at' => now(),
                    );
                }
                $item->setData($transactionData);
                $item->save();
            }
        }
    }
}
