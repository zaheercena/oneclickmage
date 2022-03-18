<img src="https://qisstpay.com/images/qisstpayLogoHd.png?2c17eccafe68477653388f509c7037bf" alt="Qisstpay.com" width="380"/>

## Qisstpay.com Oneclick Extension

## Installation
The suggested way to install the Qisstpay.com OneClick extension is to Download and place Extension inside app->code like following:

```bash
Download and add extension in your magento app->code and Extension Structure is Qisst->Oneclick directory
bin/magento setup:upgrade && bin/magento setup:di:compile && bin/magento cache:clean

Once installed what we have to do is just enable Module in:
**Configurations->Sales->PaymentMethods->Qisstpay**
once enabled option is "YES" then it comes to environment
Which Environment you are using it for, 
SANDBOX or PRODUCTION/LIVE
select Live to "YES/NO" and that's it
