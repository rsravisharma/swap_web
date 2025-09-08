<!DOCTYPE html>
<html>
<head>
    <title>Email Verification</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; }
        .form-group { margin-bottom: 15px; }
        .btn { padding: 10px 20px; background: #007bff; color: white; border: none; cursor: pointer; }
        .alert { padding: 10px; margin: 10px 0; border-radius: 4px; }
        .alert-success { background: #d4edda; color: #155724; }
        .alert-danger { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <h2>Email Verification Required</h2>
    
    @if (session('message'))
        <div class="alert alert-success">{{ session('message') }}</div>
    @endif

    <p>Please check your email for a verification OTP or click the verification link.</p>
    
    <div class="form-group">
        <label for="otp">Enter OTP:</label>
        <input type="text" id="otp" maxlength="6" placeholder="Enter 6-digit OTP">
        <button type="button" onclick="verifyOtp()" class="btn">Verify OTP</button>
    </div>

    <form method="POST" action="{{ route('verification.send') }}">
        @csrf
        <button type="submit" class="btn">Resend Verification Email</button>
    </form>

    <div id="result"></div>

    <script>
        async function verifyOtp() {
            const otp = document.getElementById('otp').value;
            const result = document.getElementById('result');
            
            if (otp.length !== 6) {
                result.innerHTML = '<div class="alert alert-danger">Please enter a 6-digit OTP</div>';
                return;
            }

            try {
                const response = await fetch('{{ route("verification.verify-otp") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ otp: otp })
                });

                const data = await response.json();
                
                if (data.success) {
                    result.innerHTML = '<div class="alert alert-success">' + data.message + '</div>';
                    setTimeout(() => {
                        window.location.href = '/dashboard';
                    }, 2000);
                } else {
                    result.innerHTML = '<div class="alert alert-danger">' + data.message + '</div>';
                }
            } catch (error) {
                result.innerHTML = '<div class="alert alert-danger">An error occurred. Please try again.</div>';
            }
        }
    </script>
</body>
</html>
