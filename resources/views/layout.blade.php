<!doctype html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Bảng kê chành')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Be Vietnam Pro', sans-serif;
            background: #f4f6f9;
        }

        .app-card {
            border: 0;
            border-radius: 18px;
            box-shadow: 0 10px 30px rgba(0,0,0,.08);
        }

        .brand-title {
            font-weight: 700;
            letter-spacing: .3px;
        }

        .upload-box {
            border: 2px dashed #0d6efd;
            border-radius: 16px;
            background: #f8fbff;
            padding: 35px;
            text-align: center;
            cursor: pointer;
        }

        .truck-card {
            border-radius: 16px;
            border: 0;
            box-shadow: 0 6px 18px rgba(0,0,0,.06);
        }

        .warehouse-item {
            padding: 8px 12px;
            border-radius: 10px;
        }

        .warehouse-item:hover {
            background: #f1f5ff;
        }
    </style>
</head>

<body>

<nav class="navbar bg-white shadow-sm mb-4">
    <div class="container">
        <span class="navbar-brand brand-title">
            <i class="bi bi-box-seam text-primary"></i>
            Bảng kê chành
        </span>
    </div>
</nav>

<main class="container pb-5">
    @yield('content')
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

@yield('script')

</body>
</html>