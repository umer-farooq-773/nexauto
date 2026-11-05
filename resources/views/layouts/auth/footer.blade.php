
<div class="page-wrapper">

    <!-- Footer -->
    <footer class="footer-main">
        <div class="container-fluid px-4">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center py-3">
                <div class="text-center text-md-start">
                    <span class="text-muted small">
                        &copy; {{ date('Y') }} <span class="fw-bold text-primary">{{ config('app.name') }}</span>.
                        All Rights Reserved.
                        <span class="d-none d-md-inline">
                    </span>
                </div>
                <div class="d-flex gap-3 mt-2 mt-md-0">
                    <a href="#" class="text-muted small text-decoration-none hover-link">Privacy Policy</a>
                    <span class="text-muted small">|</span>
                    <a href="#" class="text-muted small text-decoration-none hover-link">Terms of Service</a>
                    <span class="text-muted small">|</span>
                    <a href="#" class="text-muted small text-decoration-none hover-link">Cookies</a>
                </div>
            </div>
        </div>
    </footer>
</div>

<style>
    .footer-main {
        flex-shrink: 0;
        background: linear-gradient(180deg, #ffffff 0%, #ffffff 100%);
        border-top: 1px solid rgba(0,0,0,0.05);
        box-shadow: 0 -4px 20px rgba(0,0,0,0.03);
        padding: 4px;
        width: 100%;
        margin-top: 20px !important; /* This pushes footer to bottom */
    }
    .hover-link {
        position: relative;
        transition: all 0.3s ease;
    }

    html,
    body {
        height: 100%;
    }

    .content {
        min-height: 100vh;
        display: flex;
        flex-direction: column;
    }

    .content > .container-fluid {
        flex: 1;
    }

    .hover-link:hover {
        color: #4361ee !important;
        transform: translateX(3px);
    }

    .content-wrapper {
    padding-bottom: 80px;
}
    .hover-link::after {
        content: '';
        position: absolute;
        bottom: -2px;
        left: 0;
        width: 0;
        height: 1px;
        background: #4361ee;
        transition: width 0.3s ease;
    }

    .hover-link:hover::after {
        width: 100%;
    }

    @media (max-width: 768px) {
        .footer-main .row > div {
            margin-bottom: 1.5rem;
        }

        .footer-main .row > div:last-child {
            margin-bottom: 0;
        }
    }
</style>

@push('auth_scripts')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
@endpush
