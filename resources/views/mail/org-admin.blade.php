@php use Illuminate\Support\Carbon; @endphp
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en-GB">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <title>New Corporate Account</title>
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
                                    <p>We hope this email finds you well. We are thrilled to inform you that your account has been successfully created for your nation.Africa subscription. Welcome aboard!</p>

                                    <p>Your credentials have been securely set up, and you can now access your account
                                    using the following link: {{ $product->product_link }} . The email for this account is {{ $user->email }}.
                                        Click this link to set your password {{ $resetlink }}</p>

                                    <p>In order to receive free breaking news alerts, kindly send an SMS with the word ‘PRIME’ in capital letters to 20688.
                                    This account offers you a user-friendly interface that empowers you to your profile. We believe it will significantly enhance your experience with us. Should you encounter any issues during the login process or have any questionsabout using our platform, please do not hesitate to contact our dedicated support team at {{ config('custom.CUSTOMER.CUSTOMERCARE') }}. We are here to
                                    assist you and ensure a smooth onboarding experience.</p>

                                    <p>If you have any feedback or suggestions for us, we'd be delighted to hear from you. Our team is always eager to improve our services based on your feedback or input. Once again, welcome aboard!
                                    </p>
                                    <p>Thank you for choosing to subscribe on Nation.Africa a product of Nation Media Group. We are
                                    excited to have you join our community, and we look forward to offering you content that you love.
                                    </p>
                                    <p>Best regards,<br>
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
                                                <a href="{{ route('user.unsubscribe',$user->id) }}" class="text-muted">Unsubscribe</a>
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

