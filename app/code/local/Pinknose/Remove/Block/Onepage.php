<?php
class Pinknose_Remove_Block_Onepage extends Mage_Checkout_Block_Onepage
{
	public function getSteps()
	{
		$steps = array();

		if (!$this->isCustomerLoggedIn()) {
			$steps['login'] = $this->getCheckout()->getStepData('login');
		}

		$stepCodes = array('billing', 'shipping', 'shipping_method', 'review');

		foreach ($stepCodes as $step) {
			$steps[$step] = $this->getCheckout()->getStepData($step);
		}
		return $steps;
	}

	public function getActiveStep()
	{
		return $this->isCustomerLoggedIn() ? 'billing' : 'login';
	}
}