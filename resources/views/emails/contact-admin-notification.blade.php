<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>New Contact Message</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f4f4f4;">
        <div style="background-color: white; padding: 30px; border-radius: 10px;">
            <h2 style="color: #0284c7; margin-top: 0;">New Contact Message</h2>
            
            <div style="margin: 20px 0; padding: 15px; background-color: #f8f9fa; border-radius: 5px;">
                <p><strong>From:</strong> {{ $message->name }}</p>
                <p><strong>Email:</strong> {{ $message->email }}</p>
                <p><strong>Subject:</strong> {{ $message->subject_label }}</p>
                <p><strong>Date:</strong> {{ $message->created_at->format('F j, Y \a\t g:i A') }}</p>
            </div>
            
            <div style="margin: 20px 0;">
                <strong>Message:</strong>
                <p style="background-color: #f8f9fa; padding: 15px; border-radius: 5px; white-space: pre-wrap;">{{ $message->message }}</p>
            </div>
            
            @if($message->user)
            <div style="margin: 20px 0; padding: 15px; background-color: #e0f2fe; border-radius: 5px;">
                <p><strong>Registered User ID:</strong> {{ $message->user_id }}</p>
            </div>
            @endif
            
            <p style="color: #666; font-size: 12px;">IP Address: {{ $message->ip_address }}</p>
        </div>
    </div>
</body>
</html>
