<?php
return [

        'token_link'                =>   '/oauth/v1/generate?grant_type=client_credentials',
        'checkout_processlink'      =>   '/mpesa/stkpush/v1/processrequest',
        'checkout_querylink'        =>   '/mpesa/stkpushquery/v1/query',
        'reversal_link'             =>   '/mpesa/reversal/v1/request',
        'balance_link'              =>   '/mpesa/accountbalance/v1/query',
        'c2b_regiterUrl'            =>   '/mpesa/c2b/v2/registerurl',
        'transtat_link'             =>   '/mpesa/transactionstatus/v1/query',
        'b2b_link'                  =>   '/mpesa/b2b/v1/paymentrequest',
        'b2c_link'                  =>   '/mpesa/b2c/v1/paymentrequest',
        'billMOptinLink'            =>   '/v1/billmanager-invoice/optin',
        'billMChangeOptinLink'      =>   '/v1/billmanager-invoice/change-optin-details',
        'billMSingleInvoice'        =>   '/v1/billmanager-invoice/single-invoicing',
        'billMBulkInvoice'          =>   '/v1/billmanager-invoice/bulk-invoicing',
        'billMCancelSingleIn'       =>   '/v1/billmanager-invoice/cancel-single-invoice',
        'qrcode'                    =>   '/mpesa/qrcode/v1/generate'


    ];
