(function ($) {
    "use strict";

    /* =========================
       COMMON ACTIVE SIDEBAR LOGIC
       ========================= */
    $(function () {

        var currentPage = window.location.pathname.split('/').pop();

        // fix for root index
        if (currentPage === "" || currentPage === "/") {
            currentPage = "index.html";
        }

        // remove old active
        $('.sidebar a').removeClass('active');

        $('.sidebar a[href]').each(function () {

            var href = $(this).attr('href');
            if (!href || href === '#') return;

            var linkPage = href.split('/').pop();

            if (linkPage === currentPage) {
                $(this).addClass('active');

                // if dropdown item → activate parent & open
                if ($(this).hasClass('dropdown-item')) {
                    $(this).closest('.dropdown')
                           .find('.nav-link')
                           .addClass('active');

                    $(this).closest('.dropdown-menu')
                           .addClass('show');

                    $(this).closest('.dropdown')
                           .find('.dropdown-toggle')
                           .attr('aria-expanded', 'true');
                }
            }
        });
    });

    /* =========================
       EXISTING CODE (UNCHANGED)
       ========================= */

    // Spinner
    var spinner = function () {
        setTimeout(function () {
            if ($('#spinner').length > 0) {
                $('#spinner').removeClass('show');
            }
        }, 1);
    };
    spinner();

    // Back to top
    $(window).scroll(function () {
        if ($(this).scrollTop() > 300) {
            $('.back-to-top').fadeIn('slow');
        } else {
            $('.back-to-top').fadeOut('slow');
        }
    });

    $('.back-to-top').click(function () {
        $('html, body').animate({ scrollTop: 0 }, 1500, 'easeInOutExpo');
        return false;
    });

    // Sidebar toggler
    $('.sidebar-toggler').click(function () {
        $('.sidebar, .content').toggleClass("open");
        return false;
    });

    // बाकी chart / carousel / calendar code
    // (यहाँ कोई change नहीं किया गया)

})(jQuery);

// Dark/Light Mode Toggle
document.addEventListener('DOMContentLoaded', function() {
    const themeToggle = document.getElementById('dark-mode-toggle');
    const htmlElement = document.documentElement;
    
    // Check for saved theme or prefers-color-scheme
    const savedTheme = localStorage.getItem('theme') || 'light';
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    
    // Set initial theme
    if (savedTheme === 'dark' || (savedTheme === 'auto' && prefersDark)) {
        htmlElement.setAttribute('data-bs-theme', 'dark');
    } else {
        htmlElement.setAttribute('data-bs-theme', 'light');
    }
    
    // Toggle theme on button click
    themeToggle.addEventListener('click', function() {
        const currentTheme = htmlElement.getAttribute('data-bs-theme');
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        
        htmlElement.setAttribute('data-bs-theme', newTheme);
        localStorage.setItem('theme', newTheme);
        
        // Update icon
        updateThemeIcon(newTheme);
    });
    
    // Set correct icon on page load
    updateThemeIcon(htmlElement.getAttribute('data-bs-theme'));
    
    function updateThemeIcon(theme) {
        const icon = themeToggle.querySelector('i');
        if (icon) {
            if (theme === 'dark') {
                icon.className = 'fas fa-sun';
            } else {
                icon.className = 'fas fa-moon ';
            }
        }
    }
    
    // Listen for system theme changes
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', e => {
        if (localStorage.getItem('theme') === 'auto') {
            htmlElement.setAttribute('data-bs-theme', e.matches ? 'dark' : 'light');
            updateThemeIcon(e.matches ? 'dark' : 'light');
        }
    });
});

