<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('login') }} - {{ __('app_name') }}</title>
    
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <!-- Poppins Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .login-container {
            width: 100%;
            max-width: 450px;
        }
        
        .login-card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }
        
        .login-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }
        
        .login-icon i {
            font-size: 40px;
            color: white;
        }
        
        .login-title {
            text-align: center;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 8px;
        }
        
        .login-subtitle {
            text-align: center;
            color: #64748b;
            margin-bottom: 30px;
        }
        
        .form-label {
            font-weight: 500;
            color: #475569;
            margin-bottom: 8px;
        }
        
        .input-group-text {
            background: #f8fafc;
            border-right: none;
            color: #94a3b8;
        }
        
        .form-control {
            border-left: none;
            padding: 10px 15px;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.15);
        }
        
        .input-group:focus-within .input-group-text {
            border-color: #667eea;
        }
        
        .login-button {
            width: 100%;
            padding: 12px;
            font-weight: 500;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            margin-top: 10px;
            transition: all 0.3s ease;
        }
        
        .login-button:hover {
            background: linear-gradient(135deg, #5568d3 0%, #65408b 100%);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }
        
        .login-footer {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
            color: #64748b;
        }
        
        .language-dropdown {
            margin-top: 20px;
            text-align: center;
        }
        
        .language-dropdown .dropdown-toggle {
            border-radius: 8px;
        }
        
        .invalid-feedback {
            display: block;
            margin-top: 5px;
            color: #dc3545;
        }
        
        .form-check-label {
            color: #64748b;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-icon">
                <i class="bi bi-truck"></i>
            </div>
            <h3 class="login-title">{{ __('app_name') }}</h3>
            <p class="login-subtitle">{{ __('login') }}</p>

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="mb-3">
                    <label for="email" class="form-label">{{ __('email') }}</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-envelope"></i>
                        </span>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" 
                               id="email" name="email" value="{{ old('email') }}" required autofocus>
                    </div>
                    @error('email')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">{{ __('password') }}</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-lock"></i>
                        </span>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" 
                               id="password" name="password" required>
                    </div>
                    @error('password')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember">
                        <label class="form-check-label" for="remember">
                            {{ __('remember_me') }}
                        </label>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary login-button">
                    <i class="bi bi-box-arrow-in-right me-2"></i> {{ __('login') }}
                </button>
            </form>

            <div class="login-footer">
                <small>Default: admin@logiflow.com / admin123</small>
            </div>

            <div class="language-dropdown">
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-translate"></i> Language
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="{{ route('lang.switch', 'en') }}">English</a></li>
                        <li><a class="dropdown-item" href="{{ route('lang.switch', 'hi') }}">हिंदी</a></li>
                        <li><a class="dropdown-item" href="{{ route('lang.switch', 'es') }}">Español</a></li>
                        <li><a class="dropdown-item" href="{{ route('lang.switch', 'ta') }}">தமிழ்</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5.3 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>