<!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Heebo:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Icon Font Stylesheet -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <!-- Libraries Stylesheet -->
    <link href="{{ asset('assets/lib/owlcarousel/assets/owl.carousel.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/lib/tempusdominus/css/tempusdominus-bootstrap-4.min.css') }}" rel="stylesheet" />
    

    <!-- Customized Bootstrap Stylesheet -->
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet">

    <!-- Template Stylesheet -->
    <link href="{{ asset('assets/css/style.css') }}" rel="stylesheet">

    <style>
        .sidebar-toggle .toggle-icon { transition: transform 0.3s ease; }
        .sidebar-toggle .toggle-icon.rotated { transform: rotate(180deg); }
        .sidebar .navbar .navbar-nav .nav-link.active {
            color: #ffffff !important;
            background: #1a3a5c !important;
            border-color: #1a3a5c !important;
        }
        .sidebar .navbar .navbar-nav .nav-link.active i {
            background: #1a3a5c !important;
            color: #ffffff !important;
        }
        .sidebar .navbar .navbar-nav .nav-link:hover {
            color: #1a3a5c !important;
            background: #ffffff !important;
            border-color: #1a3a5c !important;
        }
        .sidebar .navbar .navbar-nav .nav-link:hover i {
            background: #ffffff !important;
            color: #1a3a5c !important;
        }

        /* Form Field Typography & Sizing Enhancements */
        .form-control,
        .form-select,
        input.form-control,
        select.form-select,
        textarea.form-control {
            font-size: 0.95rem !important;
            color: #212529 !important;
        }

        .form-label,
        label.form-label {
            font-size: 0.875rem !important;
            font-weight: 500;
            color: #495057;
        }

        .form-floating > label {
            font-size: 0.875rem !important;
        }

        .select2-container .select2-selection--single .select2-selection__rendered {
            font-size: 0.95rem !important;
        }

        .select2-results__option {
            font-size: 0.925rem !important;
        }

        /* Unified Modal & Select2 Stacking Hierarchy */
        .modal-backdrop {
            z-index: 1050 !important;
        }
        .modal {
            z-index: 1055 !important;
        }
        .modal-dialog {
            position: relative;
            z-index: 1056 !important;
        }
        .modal-content {
            background-color: #ffffff;
            pointer-events: auto;
        }
        .select2-dropdown,
        .select2-container--open {
            z-index: 1065 !important;
        }
    </style>