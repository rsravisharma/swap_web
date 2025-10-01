<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deletion Request Status - SWAP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="card">
                    <div class="card-header">
                        <h4><i class="bi bi-clipboard-check me-2"></i>Deletion Request Status</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Email:</strong> {{ $deletionRequest->email }}</p>
                                <p><strong>Submitted:</strong> {{ $deletionRequest->created_at->format('M d, Y h:i A') }}</p>
                                <p><strong>Status:</strong> 
                                    <span class="badge {{ $deletionRequest->status_badge }}">
                                        {{ ucfirst($deletionRequest->status) }}
                                    </span>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Verified:</strong> 
                                    @if($deletionRequest->verified)
                                        <i class="bi bi-check-circle text-success"></i> Yes
                                    @else
                                        <i class="bi bi-x-circle text-danger"></i> No
                                    @endif
                                </p>
                                @if($deletionRequest->processed_at)
                                    <p><strong>Processed:</strong> {{ $deletionRequest->processed_at->format('M d, Y h:i A') }}</p>
                                @endif
                            </div>
                        </div>
                        
                        @if($deletionRequest->admin_notes)
                            <hr>
                            <h6>Admin Notes:</h6>
                            <p class="text-muted">{{ $deletionRequest->admin_notes }}</p>
                        @endif
                    </div>
                </div>
                
                <div class="text-center mt-3">
                    <a href="/deletion-request" class="btn btn-outline-primary">Back to Deletion Request</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
