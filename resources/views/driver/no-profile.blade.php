<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NexAuto ELD - Driver Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet" />
</head>
<body class="bg-dark text-white d-flex align-items-center justify-content-center vh-100">
    <div class="text-center p-4" style="max-width: 480px;">
        <div class="mb-3 text-warning">
            <i class="ri-user-unfollow-line" style="font-size: 64px;"></i>
        </div>
        <h3 class="fw-bold mb-2">No Driver Profile Linked</h3>
        <p class="text-secondary mb-4">
            Your login account is not linked to an active driver record in the fleet system. Please contact your fleet administrator to configure your driver profile and waybill assignment.
        </p>
        <div class="d-flex justify-content-center gap-2">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-outline-light rounded-pill px-4">
                    <i class="ri-logout-box-line me-1"></i> Sign Out
                </button>
            </form>
            @can('view_driver')
                <a href="{{ route('driver.index') }}" class="btn btn-primary rounded-pill px-4">
                    <i class="ri-dashboard-line me-1"></i> Fleet Management
                </a>
            @endcan
        </div>
    </div>
</body>
</html>

