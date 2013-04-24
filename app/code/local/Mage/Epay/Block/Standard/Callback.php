<?php
/**
 * Copyright ePay | Dit Online Betalingssystem, (c) 2010.
 * This program is free software. You are allowed to use the software but NOT allowed to modify the software. 
 * It is also not legal to do any changes to the software and distribute it in your own name / brand. 
*/
class Mage_Epay_Block_Standard_Callback extends Mage_Core_Block_Abstract
{
    protected function _toHtml()
    {
        $standard = Mage::getModel('epay/standard');

        $form = new Varien_Data_Form();
        $form->setAction($standard->getEpayUrl())
            ->setId('ePay')
            ->setName('ePay')
            ->setMethod('POST')
            ->setUseContainer(true);
        foreach ($standard->getStandardCheckoutFormFields() as $field=>$value) {
            $form->addField($field, 'hidden', array('name'=>$field, 'value'=>$value));
        }
        $html = '<html><body><script type="text/javascript" src="http://www.epay.dk/js/standardwindow.js"></script> ';
        $html.= $this->__('Du har valgt at betale via Epay | Dit Online Betalingssystem.');
        $html.= $form->toHtml();
        $html.= "Hvis ikke Standard Betalingsvinduet &#229;bner op automatisk, s&#229; klik p&#229; knappen for at aktivere det.<br><br>";
        $html.= "Bem&#230;rk! Hvis I benytter en pop-up stopper, skal I holde CTRL tasten nede, mens I trykker p&#229; knappen.<br><br>";
        $html.= '<input type="button" onclick="javascript:open_ePay_window();" value="&#197;ben betalingsvinduet">';
        $html.= '</body></html>';

        return $html;
    }
}
