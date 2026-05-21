<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post-Class Survey</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="icon" type="image/x-icon" href="{{ asset('storage/images/favicon.png') }}" />
    <style>
        body {
            background-color: #f7f7fb;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .login-card {
            background: #fff;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.05);
            text-align: center;
            max-width: 400px;
            width: 100%;
            border-top: 4px solid #FFD121;
        }

        .login-card img {
            width: 150px;
            margin-bottom: 1rem;
        }

        .btn-office365 {
            background-color: #7b3fe4;
            color: #fff;
            font-weight: 500;
        }

        .btn-office365:hover {
            background-color: #692fcf;
            color: #fff;
        }

        .btn-office365 i {
            margin-right: 8px;
        }
    </style>
</head>

<body>

    <div class="login-card">
        <img src="{{ asset('storage/images/logo light.png') }}" alt="App Logo" class="logo" />
        <h5 class="fw-bold text-purple">Welcome to MCU Post-Class Survey</h5>
        <p class="text-muted">Please sign-in to your account</p>

        @if (session('session_expired'))
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <i class="fas fa-clock me-2"></i>
                <strong>Session Expired!</strong> {{ session('session_expired') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                {{ $errors->first('msg') ?? $errors->first() }}
            </div>
        @endif

        <a href="{{ url('/auth/microsoft/redirect') }}" class="btn btn-office365 w-100">
            <i class="fa-brands fa-microsoft"></i> LOGIN WITH OFFICE365
        </a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
