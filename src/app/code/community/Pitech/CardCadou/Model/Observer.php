<?php

/**
 * CardCadou Observer
 *
 * Model Observer
 *
 * @category   Payment
 * @package    CardCadou
 * @subpackage CardCadou_Model
 * @author     Alin Pop <alin.pop@pitechnologies.ro>
 * @author     Izabella Papp <izabella.papp@pitechnologies.ro>
 * @license    https://cardcadou.online/ CardCadou
 * @link       https://cardcadou.online/
 */

class Pitech_CardCadou_Model_Observer
{
    /**
     * Block CardCadou when is applied in  the checkout.
     * @param Varien_Event_Observer $observer
     */
    public function blockCardCadou(Varien_Event_Observer $observer)
    {
        $_cardNumber = Mage::getSingleton('checkout/session')->getCouponCode();
        if (isset($_cardNumber)) {
            $order = $observer->getEvent()->getOrder();
            $payment = $order->getPayment()->getMethodInstance()->getCode();

            $_amount = round(Mage::getSingleton('checkout/session')->getCouponAmount());
            $_orderRef = $order->getIncrementId();
            $_apiCardCadou = Mage::getModel('cardcadou/cardcadou')->cardcadou();

            if ($order->getGrandTotal() == 0) {
                $_orderType = $_apiCardCadou::TYPE_ORDER_CARDCADOU;
            } elseif ($payment == 'cashondelivery') {
                $_orderType = $_apiCardCadou::TYPE_ORDER_RAMBURS;
            } else {
                $_orderType = $_apiCardCadou::TYPE_ORDER_ONLINE;
            }

            $result = $_apiCardCadou->blockCard($_cardNumber, $_amount, $_orderRef, $_orderType);

            $transactionModel = Mage::getModel('cardcadou/transaction');

            if (isset($result['error'])) {
                $transactionData = array(
                    'card_serie' => $_cardNumber,
                    'amount' => $_amount,
                    'order_id' => $_orderRef,
                    'uuid' => '',
                    'order_status' => 'API ERROR',
                    'order_deny_code' => 0,
                    'confirmation_status' => '',
                    'deny_code' => 0,
                    'cancel_status' => '',
                    'cancel_deny_code' => 0,
                    'api_reference' => '',
                    'updated_at' => now(),
                );
            } else {
                $transactionData = array(
                    'card_serie' => $_cardNumber,
                    'amount' => $_amount,
                    'order_id' => $_orderRef,
                    'api_reference' => $result['order_reference'],
                    'order_status' => $result['order_status'],
                    'order_deny_code' => $result['deny_code'],
                    'confirmation_status' => ($_orderType == $_apiCardCadou::TYPE_ORDER_CARDCADOU) ? 'ACCEPTED' : '',
                    'deny_code' => 0,
                    'cancel_status' => '',
                    'cancel_deny_code' => 0,
                    'uuid' => $result['transaction_uuid'],
                    'updated_at' => now(),
                );
            }
            $transactionModel->setData($transactionData);
            $transactionModel->save();

            Mage::getSingleton('checkout/session')->unsetData('coupon_code');
            Mage::getSingleton('checkout/session')->unsetData('coupon_amount');

            if (isset($result['error']) || ($result['order_status'] == 'REFUSED')) {
                $order->addStatusHistoryComment(Mage::helper('cardcadou')->__('An error occurred while processing CardCadou.'));
                $order->cancel()->save();

                Mage::log($_orderRef, null, 'cardcadou.log');
                Mage::log($result, null, 'cardcadou.log');
                Mage::getSingleton('checkout/session')->addError(Mage::helper('cardcadou')->__('An error occurred while processing CardCadou.'));

                //Mage::app()->getFrontController()->getResponse()->setRedirect(Mage::getUrl('checkout/cart'))->sendResponse();
				exit;
            }
        }
    }

    /**
     * When order status is change the CardCadou confirm or cancel transaction.
     * @param $observer
     */
    public function orderStatusChange($observer)
    {
        $acceptedOrderStatus = Mage::getStoreConfig('payment/cardcadou/accepted_when_order_status', null);
        $canceledOrderStatus = Mage::getStoreConfig('payment/cardcadou/canceled_when_order_status', null);

        $order = $observer->getEvent()->getOrder();
        $payment = $order->getPayment()->getMethodInstance()->getCode();
        $_orderRef = $order->getIncrementId();

        $_apiCardCadou = Mage::getModel('cardcadou/cardcadou')->cardcadou();
        if ($payment == 'cashondelivery') {
            $_orderType = $_apiCardCadou::TYPE_CONFIRM_RAMBURS;
        } else {
            $_orderType = $_apiCardCadou::TYPE_CONFIRM_ONLINE;
        }

        $ccTransaction = Mage::getModel('cardcadou/transaction')->getCollection()->addFieldToFilter('order_id', $_orderRef)->getFirstItem();
        
        if ($ccTransaction->getId()) {
            $_transaction = Mage::getModel('cardcadou/transaction')->load($ccTransaction->getId());
            if ($_transaction->getOrderStatus() == 'REFUSED' || $_transaction->getConfirmationStatus() == 'ACCEPTED' || $_transaction->getCancelStatus() == 'ACCEPTED') {
                return;
            } else {
                $_apiOrderRef = $_transaction->getApiReference();

                $acceptedOrderStatus = explode(',', $acceptedOrderStatus);
                $canceledOrderStatus = explode(',', $canceledOrderStatus);

                if (in_array($order->getStatus(), $acceptedOrderStatus)) {
                    if ($_apiOrderRef) {
                        $result = $_apiCardCadou->confirmCard($_apiOrderRef, $_orderType);

                        if (isset($result['error'])) {
                            Mage::log($result, null, 'cardcadou.log');
                            $transactionData = array(
                                'id' => $_transaction->getId(),
                                'order_status' => $_transaction->getOrderStatus(),
                                'order_deny_code' => $_transaction->getOrderDenyCode(),
                                'confirmation_status' => 'API ERROR',
                                'deny_code' => 0,
                                'cancel_status' => '',
                                'cancel_deny_code' => 0,
                                'updated_at' => now(),
                            );
                        } else {
                            $transactionData = array(
                                'id' => $_transaction->getId(),
                                'order_status' => $_transaction->getOrderStatus(),
                                'order_deny_code' => $_transaction->getOrderDenyCode(),
                                'confirmation_status' => $result['confirmation_status'],
                                'deny_code' => $result['confirmation_deny_code'],
                                'cancel_status' => '',
                                'cancel_deny_code' => 0,
                                'uuid' => $result['transaction_uuid'],
                                'updated_at' => now(),
                            );
                        }

                        $_transaction->setData($transactionData);
                        $_transaction->save();
                    }
                }

                if (in_array($order->getStatus(), $canceledOrderStatus)) {
                    if ($_apiOrderRef) {
                        $result = $_apiCardCadou->cancelCard($_apiOrderRef, $_orderType);
                        
                        if (isset($result['error'])) {
                            Mage::log($result, null, 'cardcadou.log');
                            $transactionData = array(
                                'id' => $_transaction->getId(),
                                'order_status' => $_transaction->getOrderStatus(),
                                'order_deny_code' => $_transaction->getOrderDenyCode(),
                                'confirmation_status' => '',
                                'deny_code' => 0,
                                'cancel_status' => 'API ERROR',
                                'cancel_deny_code' => 0,
                                'updated_at' => now(),
                            );
                        } else {
                            $transactionData = array(
                                'id' => $_transaction->getId(),
                                'order_status' => $_transaction->getOrderStatus(),
                                'order_deny_code' => $_transaction->getOrderDenyCode(),
                                'confirmation_status' => '',
                                'deny_code' => 0,
                                'cancel_status' => $result['confirmation_status'],
                                'cancel_deny_code' => $result['confirmation_deny_code'],
                                'uuid' => $result['transaction_uuid'],
                                'updated_at' => now(),
                            );
                        }

                        $_transaction->setData($transactionData);
                        $_transaction->save();
                    }
                }
            }
        }
    }

    /**
     * Set CardCadou discount on quote.
     * @param $observer
     */
    public function setDiscount($observer)
    {
        $codeCardCadou = Mage::getSingleton('checkout/session')->getCouponCode();
        $discountAmount = Mage::getSingleton('checkout/session')->getCouponAmount();

        if ($codeCardCadou && $discountAmount) {
            $quote = $observer->getEvent()->getQuote();
            $quoteid = $quote->getId();
            if ($quoteid) {
                if ($discountAmount > 0) {
                    $total = $quote->getBaseSubtotal();
                    $quote->setSubtotal(0);
                    $quote->setBaseSubtotal(0);

                    $quote->setSubtotalWithDiscount(0);
                    $quote->setBaseSubtotalWithDiscount(0);

                    $quote->setGrandTotal(0);
                    $quote->setBaseGrandTotal(0);


                    $canAddItems = $quote->isVirtual() ? ('billing') : ('shipping');
                    foreach ($quote->getAllAddresses() as $address) {
                        $address->setSubtotal(0);
                        $address->setBaseSubtotal(0);

                        $address->setGrandTotal(0);
                        $address->setBaseGrandTotal(0);

                        $address->collectTotals();

                        $quote->setSubtotal((float)$quote->getSubtotal() + $address->getSubtotal());
                        $quote->setBaseSubtotal((float)$quote->getBaseSubtotal() + $address->getBaseSubtotal());

                        $quote->setSubtotalWithDiscount(
                            (float)$quote->getSubtotalWithDiscount() + $address->getSubtotalWithDiscount()
                        );
                        $quote->setBaseSubtotalWithDiscount(
                            (float)$quote->getBaseSubtotalWithDiscount() + $address->getBaseSubtotalWithDiscount()
                        );

                        $quote->setGrandTotal((float)$quote->getGrandTotal() + $address->getGrandTotal());
                        $quote->setBaseGrandTotal((float)$quote->getBaseGrandTotal() + $address->getBaseGrandTotal());

                        $quote->save();

                        $quote->setGrandTotal($quote->getBaseSubtotal() - $discountAmount)
                            ->setBaseGrandTotal($quote->getBaseSubtotal() - $discountAmount)
                            ->setSubtotalWithDiscount($quote->getBaseSubtotal() - $discountAmount)
                            ->setBaseSubtotalWithDiscount($quote->getBaseSubtotal() - $discountAmount)
                            ->save();


                        if ($address->getAddressType() == $canAddItems) {
                            $address->setSubtotalWithDiscount((float)$address->getSubtotalWithDiscount() - $discountAmount);
                            $address->setGrandTotal((float)$address->getGrandTotal() - $discountAmount);
                            $address->setBaseSubtotalWithDiscount((float)$address->getBaseSubtotalWithDiscount() - $discountAmount);
                            $address->setBaseGrandTotal((float)$address->getBaseGrandTotal() - $discountAmount);
                            if ($address->getDiscountDescription()) {
                                $address->setDiscountAmount(-($address->getDiscountAmount() - $discountAmount));
                                $address->setDiscountDescription($address->getDiscountDescription() . ', Card Cadou');
                                $address->setBaseDiscountAmount(-($address->getBaseDiscountAmount() - $discountAmount));
                            } else {
                                $address->setDiscountAmount(-($discountAmount));
                                $address->setDiscountDescription('Card Cadou');
                                $address->setBaseDiscountAmount(-($discountAmount));
                            }
                            $address->save();
                        }
                        $quote->save();
                    }
                }
            }
        }
    }
}
