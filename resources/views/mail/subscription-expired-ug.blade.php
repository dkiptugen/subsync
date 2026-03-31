@php use Illuminate\Support\Carbon; @endphp
        <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en-GB">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <title>Subscription Expired</title>
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
                        style="padding: 40px 0 30px 0; border-bottom: 1px solid #ffffff; background:#a92131 !important;">
                       <img src="{{ asset('assets/img/monitor.png') }}"
                            alt="Daily Monitor" style="display: block; height:100px; width:auto;"/>
                    </td>
                </tr>
                <tr>
                    <td bgcolor="#ffffff" style="padding: 40px 30px 40px 30px;">
                        <table border="0" cellpadding="0" cellspacing="0" width="100%"
                               style="border-collapse: collapse;">
                            <tr>
                                <td style="color: #153643; font-family: Arial, sans-serif;">
                                    @php
                                        $name = explode(' ',$user->name);
                                    @endphp
                                    <h1 style="font-size: 18px; margin: 0;">Dear {{ ucfirst($name[0]) }},</h1>
                                </td>
                            </tr>
                            <tr>
                                <td style="color: #153643; font-family: Arial, sans-serif; font-size: 16px; line-height: 24px; padding: 20px 0 30px 0;">
                                   
                                   
<p>Thank you for supporting our journalism.</p>
<p>
    This is a reminder that your subscription for {{ $product->product_name }} has lapsed, and we invite you to continue enjoying exclusive content specially curated for you. Click the link below to renew your subscription.
</p>
 <p style="width:100%; text-align:center;"><a href="{{ $renew_link }}"
                                              style="display: inline-block; padding: 5px 15px; background-color:#a92131; color: #ffffff; text-decoration: none; border-radius: 5px;">Renew Subscription</a></p>


<p>
   For help, check out our FAQ page (add FAQ link - https://www.monitor.co.ug/uganda/frequently-asked-questions-1934580). For assistance, email customercareug@ug.nationmedia.com or call +256312301230

</p>

<p>
 With Appreciation,
<br>
<b>Customer Service Team</b>
</p>


                                   
                                   
                                   
                                   
                                   
                                   
                                   
                                   
                                   
                                

                                    
                                    
                                
                                

                                </td>
                            </tr>

                        </table>
                    </td>
                </tr>
                <tr>
                    <td bgcolor="#a92131" style="padding: 30px 30px;">
                        <table border="0" cellpadding="0" cellspacing="0" width="100%"
                               style="border-collapse: collapse;">
                            <tr>
                                <td style="color: #ffffff; font-family: Arial, sans-serif; font-size: 14px;">
                                    <p style="margin: 0;">&copy; Daily Monitor
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
                    </td>
                </tr>
            </table>

        </td>
    </tr>
</table>
</body>
</html>

