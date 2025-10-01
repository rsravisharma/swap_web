<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deletion Request Verification - SWAP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-6 mx-auto">
                <div class="card text-center">
                    <div class="card-body p-5">
                        @if($status === 'verified')
                            <i class="bi bi-check-circle text-success" style="font-size: 4rem;"></i>
                            <h3 class="mt-3 text-success">Verification Successful</h3>
                            <p class="text-muted">Your deletion request has been verified and will be processed by our team within 3-5 business days.</p>
                        @elseif($status === 'already_verified')
                            <i class="bi bi-info-circle text-info" style="font-size: 4rem;"></i>
                            <h3 class="mt-3 text-info">Already Verified</h3>
                            <p class="text-muted">This deletion request has already been verified and is being processed.</p>
                        @else
                            <i class="bi bi-x-circle text-danger" style="font-size: 4rem;"></i>
                            <h3 class="mt-3 text-danger">Invalid Verification Link</h3>
                            <p class="text-muted">This verification link is invalid or has expired.</p>
                        @endif
                        
                        <a href="/" class="btn btn-primary mt-3">Return to Home</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
