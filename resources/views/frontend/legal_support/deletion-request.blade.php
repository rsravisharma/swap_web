<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Account Deletion - SWAP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .deletion-header {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
            padding: 3rem 0;
        }
        .form-section {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 2rem;
            margin-bottom: 2rem;
        }
        .warning-box {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        .info-box {
            background-color: #d1ecf1;
            border: 1px solid #bee5eb;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
    </style>
</head>
<body>
    <header class="deletion-header">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center">
                    <h1 class="display-4 fw-bold">
                        <i class="bi bi-person-x me-3"></i>Account Deletion Request
                    </h1>
                    <p class="lead">Request permanent deletion of your SWAP account and associated data</p>
                </div>
            </div>
        </div>
    </header>

    <main class="container my-5">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <div class="warning-box">
                    <h4 class="text-warning mb-3">
                        <i class="bi bi-exclamation-triangle me-2"></i>Important Warning
                    </h4>
                    <p><strong>Account deletion is permanent and cannot be undone.</strong> Once your deletion request is processed:</p>
                    <ul class="mb-0">
                        <li>Your account and profile will be permanently deleted</li>
                        <li>All your product listings will be removed</li>
                        <li>Your transaction history will be deleted</li>
                        <li>You will lose access to all messages and communications</li>
                        <li>This action cannot be reversed</li>
                    </ul>
                </div>

                <div class="info-box">
                    <h4 class="text-info mb-3">
                        <i class="bi bi-info-circle me-2"></i>What Happens Next?
                    </h4>
                    <ol class="mb-0">
                        <li><strong>Verification:</strong> You'll receive an email to verify this deletion request</li>
                        <li><strong>Review:</strong> Our team will review your request (typically within 3-5 business days)</li>
                        <li><strong>Processing:</strong> Once approved, your data will be permanently deleted within 30 days</li>
                        <li><strong>Confirmation:</strong> You'll receive a final confirmation email when deletion is complete</li>
                    </ol>
                </div>

                <div class="form-section">
                    <h3 class="mb-4">
                        <i class="bi bi-clipboard-data me-2"></i>Deletion Request Form
                    </h3>

                    <form method="POST" action="{{ route('deletion.store') }}">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">
                                <i class="bi bi-envelope me-1"></i>Email Address <span class="text-danger">*</span>
                            </label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                   id="email" name="email" value="{{ old('email') }}" required>
                            <div class="form-text">Enter the email address associated with your SWAP account</div>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="name" class="form-label">
                                <i class="bi bi-person me-1"></i>Full Name
                            </label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name') }}">
                            <div class="form-text">Your full name (optional, helps us verify your identity)</div>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label">
                                <i class="bi bi-telephone me-1"></i>Phone Number
                            </label>
                            <input type="tel" class="form-control @error('phone') is-invalid @enderror" 
                                   id="phone" name="phone" value="{{ old('phone') }}">
                            <div class="form-text">Phone number associated with your account (optional)</div>
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="reason" class="form-label">
                                <i class="bi bi-chat-text me-1"></i>Reason for Deletion
                            </label>
                            <textarea class="form-control @error('reason') is-invalid @enderror" 
                                      id="reason" name="reason" rows="4">{{ old('reason') }}</textarea>
                            <div class="form-text">Please let us know why you're deleting your account (optional, helps us improve)</div>
                            @error('reason')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="confirmation" required>
                                <label class="form-check-label" for="confirmation">
                                    <strong>I understand that this action is permanent and cannot be undone. I want to permanently delete my SWAP account and all associated data.</strong>
                                </label>
                            </div>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="/" class="btn btn-outline-secondary me-md-2">
                                <i class="bi bi-arrow-left me-1"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-danger">
                                <i class="bi bi-trash me-1"></i>Submit Deletion Request
                            </button>
                        </div>
                    </form>
                </div>

                <div class="mt-4 text-center">
                    <h5>Check Request Status</h5>
                    <form method="POST" action="{{ route('deletion.status') }}" class="d-inline-flex gap-2">
                        @csrf
                        <input type="email" name="email" class="form-control" placeholder="Enter your email" style="width: 300px;">
                        <button type="submit" class="btn btn-outline-primary">
                            <i class="bi bi-search me-1"></i>Check Status
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center">
                    <p class="mb-0">&copy; 2025 SWAP. All rights reserved.</p>
                    <p class="mb-0 small">
                        <a href="/privacy-policy" class="text-light">Privacy Policy</a> | 
                        <a href="/terms-of-service" class="text-light">Terms of Service</a>
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
