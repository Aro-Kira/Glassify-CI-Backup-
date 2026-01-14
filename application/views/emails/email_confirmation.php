<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Confirmation - Glassify</title>
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f4f4f4;">
    <table role="presentation" style="width: 100%; border-collapse: collapse; background-color: #f4f4f4;">
        <tr>
            <td style="padding: 20px 0;">
                <table role="presentation" style="width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="padding: 30px; text-align: center; background-color: #083c5d; border-radius: 8px 8px 0 0;">
                            <h1 style="margin: 0; color: #ffffff; font-size: 28px; font-weight: bold;">Glassify</h1>
                        </td>
                    </tr>
                    
                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px 30px;">
                            <h2 style="margin: 0 0 20px 0; color: #083c5d; font-size: 24px;">Confirm Your Email Address</h2>
                            
                            <p style="margin: 0 0 20px 0; color: #333333; font-size: 16px; line-height: 1.6;">
                                Hello <?php echo htmlspecialchars($first_name); ?>,
                            </p>
                            
                            <p style="margin: 0 0 20px 0; color: #333333; font-size: 16px; line-height: 1.6;">
                                Thank you for registering with Glassify! Please confirm your email address by clicking the button below to activate your account.
                            </p>
                            
                            <!-- Confirmation Button -->
                            <table role="presentation" style="width: 100%; margin: 30px 0;">
                                <tr>
                                    <td style="text-align: center;">
                                        <a href="<?php echo $confirmation_link; ?>" style="display: inline-block; padding: 14px 40px; background-color: #083c5d; color: #ffffff; text-decoration: none; border-radius: 5px; font-size: 16px; font-weight: bold;">Confirm My Email</a>
                                    </td>
                                </tr>
                            </table>
                            
                            <p style="margin: 20px 0; color: #666666; font-size: 14px; line-height: 1.6;">
                                Or copy and paste this link into your browser:
                            </p>
                            
                            <p style="margin: 0 0 20px 0; padding: 15px; background-color: #f8f9fa; border-left: 4px solid #083c5d; word-break: break-all; color: #333333; font-size: 14px;">
                                <?php echo htmlspecialchars($confirmation_link); ?>
                            </p>
                            
                            <div style="background-color: #d4edda; border-left: 4px solid #28a745; padding: 15px; margin: 20px 0; border-radius: 4px;">
                                <p style="margin: 0; color: #155724; font-size: 14px; line-height: 1.6;">
                                    <strong>✓ Important:</strong> After confirming your email, you'll be able to log in to your Glassify account and start ordering!
                                </p>
                            </div>
                            
                            <p style="margin: 20px 0 0 0; color: #666666; font-size: 14px; line-height: 1.6;">
                                If the button doesn't work, you can also:
                            </p>
                            
                            <ol style="margin: 10px 0 20px 0; padding-left: 20px; color: #333333; font-size: 14px; line-height: 1.8;">
                                <li>Copy the link above</li>
                                <li>Open a new browser window or tab</li>
                                <li>Paste the link into the address bar</li>
                                <li>Press Enter to confirm your email</li>
                            </ol>
                            
                            <hr style="border: none; border-top: 1px solid #e0e0e0; margin: 30px 0;">
                            
                            <p style="margin: 0; color: #999999; font-size: 12px; line-height: 1.6;">
                                This is an automated email. Please do not reply to this message. If you have any questions or concerns, please contact our support team.
                            </p>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="padding: 20px 30px; background-color: #f8f9fa; border-radius: 0 0 8px 8px; text-align: center;">
                            <p style="margin: 0 0 10px 0; color: #666666; font-size: 12px;">
                                © <?php echo date('Y'); ?> Glassify. All rights reserved.
                            </p>
                            <p style="margin: 0; color: #999999; font-size: 11px;">
                                This email was sent to <?php echo htmlspecialchars($user_email); ?>
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
