<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | SUC Accreditation DMS - CICS MarSU</title>
    <link rel="icon" href="{{ asset('images/logos/cics_logo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,600;9..40,700&display=swap" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        :root {
            --ag-primary: #78a22f;
            --ag-primary-dark: #638b24;
            --ag-ink: #243020;
            --ag-muted: #667260;
            --ag-surface: #f5f7f3;
            --ag-line: #e2e8dd;
        }

        body {
            font-family: 'DM Sans', system-ui, sans-serif;
            background: #f1f4ee;
            background-image: linear-gradient(135deg, #eaf0e2 0%, #f7f9f5 56%, #e4ecd9 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }

        .login-card {
            background: #ffffff;
            border: 1px solid var(--ag-line);
            border-radius: 10px;
            box-shadow: 0 18px 48px rgba(45, 64, 38, 0.12);
            overflow: hidden;
            width: 100%;
            max-width: 950px;
        }

        .brand-panel {
            background: linear-gradient(180deg, rgba(40, 57, 30, 0.94) 0%, rgba(31, 45, 24, 0.97) 100%),
                        url('https://images.unsplash.com/photo-1541829070764-84a7d30dd3f3?auto=format&fit=crop&w=800&q=80');
            background-size: cover;
            background-position: center;
            color: #ffffff;
            padding: 3.5rem 2.5rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .btn-apple-green {
            background-color: var(--ag-primary);
            color: #ffffff;
            border: none;
            font-weight: 700;
            padding: 0.8rem 1.5rem;
            border-radius: 7px;
            transition: all 0.2s;
        }

        .btn-apple-green:hover {
            background-color: var(--ag-primary-dark);
            color: #ffffff;
            box-shadow: 0 6px 16px rgba(120, 162, 47, 0.25);
        }
        .form-control:focus, .form-check-input:focus {
            border-color: var(--ag-primary);
            box-shadow: 0 0 0 0.22rem rgba(120, 162, 47, 0.16);
        }
        .institution-logos {
            display: flex;
            gap: 0.55rem;
        }
        .institution-logo {
            height: 52px;
            object-fit: contain;
            width: 52px;
        }
    </style>
</head>
<body>

<div class="login-card">
    <div class="row g-0">
        <!-- Brand Panel -->
        <div class="col-lg-6 brand-panel">
            <div>
                <div class="d-flex align-items-center gap-3 mb-4">
                    <div class="institution-logos" aria-label="CICS and MarSU logos">
                        <img src="{{ asset('images/logos/cics_logo.png') }}" class="institution-logo" alt="College of Information and Computing Sciences logo">
                        <img src="{{ asset('images/logos/MARSU LOGO.png') }}" class="institution-logo" alt="Marinduque State University logo">
                    </div>
                    <div>
                        <h5 class="mb-0 fw-bold">Marinduque State University</h5>
                        <small class="text-light opacity-75">College of Information & Computing Sciences</small>
                    </div>
                </div>
                <br><br>
                <h2 class="fw-bold text-white mb-3">Accreditation Document Management System</h2>
                <p class="text-light opacity-85 fs-6 lh-base">
                    Streamlining SUC Accreditation folder hierarchies, parameter outputs, multi-file PDF repositories, and Accreditor reviews with bank-grade audit logging and security.
                </p>
            </div>
        </div>

        <!-- Form Panel -->
        <div class="col-lg-6 p-4 p-md-5 bg-white d-flex flex-column justify-content-center">
            <div class="mb-4">
                <h3 class="fw-bold mb-1" style="color:var(--ag-ink);">Welcome back</h3>
                <p class="mb-0 fs-7" style="color:var(--ag-muted);">Sign in to access your assigned accreditation areas.</p>
            </div>

            @if($errors->any())
                <div class="alert alert-danger py-2 px-3 fs-7 rounded-3 mb-3">
                    <i class="bi bi-exclamation-circle me-1"></i> {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login.post') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label fw-semibold fs-7 text-secondary">Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope text-muted"></i></span>
                        <input type="email" id="emailInput" name="email" class="form-control bg-light border-start-0 ps-0" placeholder="user@cics.marsu.edu.ph" value="{{ old('email') }}" required autofocus>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold fs-7 text-secondary">Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-lock text-muted"></i></span>
                        <input type="password" id="passwordInput" name="password" class="form-control bg-light border-start-0 ps-0" placeholder="••••••••" required>
                    </div>
                </div>

                <div class="d-flex align-items-center justify-content-between mb-4">
                    <div class="form-check">
                        <input type="checkbox" name="remember" class="form-check-input" id="remember">
                        <label class="form-check-label fs-7 text-secondary" for="remember">Remember me</label>
                    </div>
                </div>

                <button type="submit" class="btn btn-apple-green w-100 mb-3">
                    Sign In to Portal <i class="bi bi-arrow-right-short ms-1 fs-5 align-middle"></i>
                </button>
            </form>

            <!-- Demo Quick Login Helper Cards -->
            <!-- <div class="mt-4 pt-3 border-top">
                <small class="text-muted fw-semibold d-block mb-2 text-uppercase fs-8" style="letter-spacing: 0.5px;">Quick Test Login Credentials (Password: <code>password</code>):</small>
                <div class="d-flex flex-wrap gap-1">
                    <button type="button" onclick="fillCreds('admin@cics.marsu.edu.ph')" class="btn btn-xs btn-outline-primary py-1 px-2 fs-8">Admin</button>
                    <button type="button" onclick="fillCreds('faculty@cics.marsu.edu.ph')" class="btn btn-xs btn-outline-success py-1 px-2 fs-8">Faculty</button>
                    <button type="button" onclick="fillCreds('accreditor@cics.marsu.edu.ph')" class="btn btn-xs btn-outline-warning py-1 px-2 fs-8">Accreditor</button>
                </div>
            </div> -->
        </div>
    </div>
</div>

<script>
    function fillCreds(email) {
        document.getElementById('emailInput').value = email;
        document.getElementById('passwordInput').value = 'password';
        document.querySelector('form').submit();
    }
</script>
</body>
</html>
