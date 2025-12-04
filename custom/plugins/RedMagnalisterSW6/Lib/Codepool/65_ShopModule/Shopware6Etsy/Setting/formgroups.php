<?php

MLSetting::gi()->get('formgroups__orderimport');
MLSetting::gi()->overwrite('formgroups__orderimport__fields__orderimport.paymentmethod__expert', false);
MLSetting::gi()->overwrite('formgroups__orderimport__fields__orderimport.shippingmethod__expert', false);
MLSetting::gi()->add('formgroups__orderimport__fields__orderimport.paymentstatus', array(
    'i18n' => '{#i18n:formfields__orderimport.paymentstatus#}',
    'name' => 'orderimport.paymentstatus',
    'type' => 'select',
), true);
