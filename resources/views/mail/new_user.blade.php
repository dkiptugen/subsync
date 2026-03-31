<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
		"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"
		lang="en-GB">
<head>
	<meta http-equiv="Content-Type"
			content="text/html; charset=UTF-8"/>
	<title>New User</title>
	<meta name="viewport"
			content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=no;">
	
	<meta http-equiv="X-UA-Compatible"
			content="IE=9; IE=8; IE=7; IE=EDGE"/>
	
	<style type="text/css">
        a[x-apple-data-detectors] {
            color: inherit !important;
        }
	</style>

</head>
<body style="margin: 0; padding: 0;background: #f0f0f0">
@php use Illuminate\Support\Carbon; @endphp
<table role="presentation"
		border="0"
		cellpadding="0"
		cellspacing="0"
		width="100%">
	<tr>
		<td style="padding: 20px 0 30px 0;">
			
			<table align="center"
					border="0"
					cellpadding="0"
					cellspacing="0"
					width="600"
					style="border-collapse: collapse; border: 1px solid #cccccc;">
				<tr>
					<td align="center"
							bgcolor="#ffffff"
							style="padding: 40px 0 30px 0; border-bottom: 1px solid #dedede; background:#ffffff !important;">
						<img src="https://www.nationmedia.com/annualreport2020/assets/images/logo-blue.png"
								alt="The Nation Media Group Logo"
								style="display: block; height:100px; width:auto;"/>
					</td>
				</tr>
				<tr>
					<td bgcolor="#ffffff"
							style="padding: 40px 30px 40px 30px;">
						<table border="0"
								cellpadding="0"
								cellspacing="0"
								width="100%"
								style="border-collapse: collapse;">
							<tr>
								<td style="color: #153643; font-family: Arial, sans-serif;">
									<h1 style="font-size: 18px; margin: 0;">Dear {{ ucfirst($user->name) }},</h1>
								</td>
							</tr>
							<tr>
								<td style="color: #153643; font-family: Arial, sans-serif; font-size: 16px; line-height: 24px; padding: 20px 0 30px 0;">
									<p> We're pleased to inform you that your subscription to ePaper(or Nation.Africa/Monitor/etc) is now active!</p>
									
									
									<p>Login Credentials:</p>
									<p>Username: {{ $user->email }}</p>
									<p>Password: {{ $password }}</p>
									
									<p>We recommend changing your password after your first login for security reasons.</p>
									
									<p>Enjoy our exclusive online content!</p>
									
									<p>ePaper website link:
										<a href="https://epaper.nation.africa/">https://epaper.nation.africa/</a></p>
									<p>Android app link:
										<a href="https://play.google.com/store/apps/details?id=com.nationmediagroup.android.epaper&pcampaignid=web_share">https://play.google.com/store/apps/details?id=com.nationmediagroup.android.epaper&pcampaignid=web_share</a>
									</p>
									<p>iOS app link:
										<a href="https://apps.apple.com/us/app/nation-epaper/id1617530983">https://apps.apple.com/us/app/nation-epaper/id1617530983</a>
									</p>
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
					<td bgcolor="#3a499a"
							style="padding: 30px 30px;">
						<table border="0"
								cellpadding="0"
								cellspacing="0"
								width="100%"
								style="border-collapse: collapse;">
							<tr>
								<td style="color: #ffffff; font-family: Arial, sans-serif; font-size: 14px;">
									<p style="margin: 0;">&copy; The Nation Media Group
								</td>
								<td align="right">
									<table border="0"
											cellpadding="0"
											cellspacing="0"
											style="border-collapse: collapse;">
										<tr>
											<td>
											
											</td>
											<td style="font-size: 0; line-height: 0;"
													width="20">&nbsp;
											</td>
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
