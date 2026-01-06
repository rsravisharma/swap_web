<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Message Received - Swap Support</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f4f4f4;">
        <div style="background-color: white; padding: 30px; border-radius: 10px;">
            <h2 style="color: #0284c7; margin-top: 0;">Thank You for Contacting Swap!</h2>
            
            <p>Hi {{ $message->name }},</p>
            
            <p>We have received your message and our support team will get back to you within 24 hours.</p>
            
            <div style="margin: 20px 0; padding: 15px; background-color: #f8f9fa; border-radius: 5px;">
                <p><strong>Your Message Details:</strong></p>
                <p><strong>Subject:</strong> {{ $message->subject_label }}</p>
                <p><strong>Date:</strong> {{ $message->created_at->format('F j, Y \a\t g:i A') }}</p>
            </div>
            
            <div style="margin: 20px 0;">
                <strong>Your Message:</strong>
                <p style="background-color: #f8f9fa; padding: 15px; border-radius: 5px; white-space: pre-wrap;">{{ $message->message }}</p>
            </div>
            
            <p>If you need immediate assistance, you can also reach us at:</p>
            <ul>
                <li>Email: swap.cubebitz@gmail.com</li>
                <li>Phone: +91 1800-XXX-XXXX (Mon-Fri, 9AM-6PM IST)</li>
            </ul>
            
            <p style="margin-top: 30px;">Best regards,<br>The Swap Support Team</p>
            
            <hr style="border: none; border-top: 1px solid #ddd; margin: 30px 0;">
            
            <p style="color: #666; font-size: 12px; text-align: center;">
                This is an automated confirmation email. Please do not reply to this email.
            </p>
        </div>
    </div>
</body>
</html>
