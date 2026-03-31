@php use Illuminate\Support\Carbon; @endphp
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en-GB">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <title>Password Reset</title>
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

                                    <p>We have received a request to reset your password for Nation Media Group. If you
                                        did not make this request, please disregard this email.</p>

                                    <p>To reset your password, please click on the following link:</p>

                                    <p style="text-align: center; margin: 1.5rem 0;"><a
                                            href="{{ $endpoint.'?resetAttributeKey='.$token.'&serviceName='.$channel.'&redirect_link='.urlencode($redirect_url)  }}"
                                            style="border: solid 1px #3a499a;color:#ffffff; padding: 7px 15px; background:#3a499a; border-radius:3px; font-size: 16px;">
                                            Click this link to reset</a></p>
                                    <p>Or visit this link if button is unclickable
                                        <strong>{{ $endpoint.'?resetAttributeKey='.$token.'&serviceName='.$channel.'&redirect_link='.urlencode($redirect_url)  }}</strong>
                                    </p>
                                    <p>The link expires at
                                        <strong>{{ Carbon::parse($created_at)->addDay(1)->format('H:ia , d-m-Y') }}</strong></p>

                                    <p>If the link does not work, please copy and paste it into your browser's address
                                        bar.</p>

                                    <p>Once you have accessed the password reset page, please follow the instructions to
                                        reset your password.</p>

                                    <p>If you have any questions or concerns, please do not hesitate to contact our
                                        customer support team.</p>

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
