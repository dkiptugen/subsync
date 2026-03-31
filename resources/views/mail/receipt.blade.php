@php use Illuminate\Support\Carbon; @endphp
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en-GB">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <title>Successful Payment</title>
    <meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=no;">

    <meta http-equiv="X-UA-Compatible" content="IE=9; IE=8; IE=7; IE=EDGE"/>

    <style type="text/css">
        a[x-apple-data-detectors] {
            color: inherit !important;
        }
    </style>

</head>
<body style="margin: 0; padding: 0;background: #f0f0f0">
<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
    <tr>
        <td style="padding: 20px 0 30px 0;">

            <table align="center" border="0" cellpadding="0" cellspacing="0" width="600"
                   style="border-collapse: collapse; border: 1px solid #cccccc;">
                <tr>
                    <td align="center" bgcolor="#ffffff"
                        style="padding: 40px 0 30px 0; border-bottom: 1px solid #dedede; background:#ffffff !important;">
                        <img src="https://www.nationmedia.com/annualreport2020/assets/images/logo-blue.png"
                             alt="The Nation Media Group Logo" style="display: block; height:100px; width:auto;"/>
                    </td>
                </tr>
                <tr>
                    <td bgcolor="#ffffff" style="padding: 40px 30px 40px 30px;">
                        <table border="0" cellpadding="0" cellspacing="0" width="100%"
                               style="border-collapse: collapse;">
                            <tr>
                                <td style="color: #153643; font-family: Arial, sans-serif;">
                                    <h1 style="font-size: 18px; margin: 0;">Dear {{ ucfirst($user->name) }},</h1>
                                </td>
                            </tr>
                            <tr>
                                <td style="color: #153643; font-family: Arial, sans-serif; font-size: 16px; line-height: 24px; padding: 20px 0 30px 0;">
                                    <p>
                                        We hope this email finds you well. We are writing to inform you that your recent
                                        payment has been successfully processed and credited to your account. We greatly
                                        appreciate your prompt action in completing the payment
                                        for {{ $product->product_name }} on {{ Carbon\Carbon::parse($transaction->transaction_date) }}.

                                    </p>
                                    <p>
                                        Transaction Details:
                                    </p>

                                    <p>Payment Date: {{ $transaction->transaction_date }}</p>
                                    <p>Payment Amount: {{ $transaction->amount }}</p>
                                    <p> Invoice/Order Number: {{ $transaction->identifier }}</p>
                                    <p> Payment Method: {{ optional($transaction->payment_method)->name }}</p>

                                    <p>Rest assured, your payment has been successfully applied to your account, and any
                                        outstanding balance has been cleared. You can now enjoy uninterrupted access
                                        to {{ $product->product_name }}
                                        and all its associated benefits.
                                    </p>
                                    <p>If you have any questions regarding your payment or if you need further
                                        assistance, please don't hesitate to contact our customer support team
                                        at {{ config('custom.CUSTOMER.CUSTOMERCARE') }}
                                        . We're here to help you.
                                    </p>
                                    <p>Thank you for choosing Nation media Group for your {{ $product->product_name }}
                                        needs. We greatly value your business and look forward to serving you in the
                                        future.</p>


                                    <p>

                                        Best regards,
                                        <br>
                                        The Nation Media Group Team

                                    </p>
                                </td>
                            </tr>

                        </table>
                    </td>
                </tr>
                <tr>
                    <td bgcolor="#3a499a" style="padding: 30px 30px;">
                        <table border="0" cellpadding="0" cellspacing="0" width="100%"
                               style="border-collapse: collapse;">
                            <tr>
                                <td style="color: #ffffff; font-family: Arial, sans-serif; font-size: 14px;">
                                    <p style="margin: 0;">&copy; The Nation Media Group
                                </td>
                                <td align="right">
                                    <table border="0" cellpadding="0" cellspacing="0"
                                           style="border-collapse: collapse;">
                                        <tr>
                                            <td>

                                            </td>
                                            <td style="font-size: 0; line-height: 0;" width="20">&nbsp;</td>
                                            <td>
                                                <a href="{{ route('user.unsubscribe',$user->id) }}"
                                                   style="color:#ededed; text-decoration:none;">Unsubscribe</a>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
            </table>
        </td>
    </tr>
</table>

</td>
</tr>
</table>
</body>
</html>
