<?php
/**
 * API Client Model
 *
 * REST API Model
 *
 * @category   Payment
 * @package    CardCadou
 * @subpackage CardCadou_Model
 * @author     Alin Pop <alin.pop@pitechnologies.ro>
 * @author     Izabella Papp <izabella.papp@pitechnologies.ro>
 * @license    https://cardcadou.online/ CardCadou
 * @link       https://cardcadou.online/
 */

require_once Mage::getBaseDir('base') . '/lib/CardCadou/Client.php';

class Pitech_CardCadou_Model_Cardcadou
{
    /**
     * @return Client_CardCadou
     */
    public function cardcadou()
    {
        $_partnerCode = Mage::getStoreConfig('payment/cardcadou/partner_code', null);
        $_secretkey = Mage::helper('core')->decrypt(Mage::getStoreConfig('payment/cardcadou/secret_key', null));

        $_restClient = new Client_CardCadou($_partnerCode, $_secretkey, true);

        return $_restClient;
    }
}