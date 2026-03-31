@php use Illuminate\Support\Carbon; @endphp
        <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en-GB">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <title>User Verification Email</title>
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
                                        Thank you for creating an account with Nation Media Group! We're thrilled to have you on board and we just need to verify your email address to activate your account and unlock all its features.

                                    </p>
                                   <p>
                                       To complete the verification process:
                                   </p>
                                    <p>
                                        Click on the following link or copy and paste it into your web browser:
                                        <a href="{{ $link.'/account/email-verification' }}?token={{ $user->verification_token }}">{{ $link }}/account/email-verification?token={{ $user->verification_token }}</a>
                                    </p>
                                    <p>
                                        By verifying your email address, you will gain access to a wide range of benefits, including:
                                    </p>
                                    <p>
                                        <ol>
                                            <li>Personalized user experience</li>
                                            <li>Exclusive offers and promotions</li>
                                            <li>Latest updates on new features and enhancements</li>
                                            <li>Seamless communication with our support team</li>
                                        </ol>
                                    </p>

                                    <p>
                                        If you did not create an account on our platform, please ignore this email, and we apologize for any inconvenience caused.
                                    </p>

                                    <p>If you encounter any issues or have any questions regarding the verification process, please feel free to reach out to our customer support team at {{ config('custom.CUSTOMER.CUSTOMERCARE') }}
                                       .
                                    </p>
                                    <p>Thank you once again for choosing Nation Media Group. We look forward to serving you and providing you with a delightful user experience.</p>

                                    <p>Thank you for choosing Nation Media Group.</p>
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
