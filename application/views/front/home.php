<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Modern Multi-Tenant Home Page Master View
 * Assembled from modular components in front/partials/
 */

$view_data = $this->data ?? get_defined_vars();

// 1. Header (HTML head, SEO tags, CSS variables, Google Fonts)
$this->load->view('front/partials/header', $view_data);

// 2. Sticky Glassmorphic Navbar
$this->load->view('front/partials/navbar', $view_data);
?>

<main class="grow">
    <?php
    // 3. Hero Carousel & Chairman Profile Card
    $this->load->view('front/partials/hero_slider', $view_data);

    // 4. Citizen Services Grid (Holding Tax, Data Entry, Sonod QR)
    $this->load->view('front/partials/quick_services', $view_data);

    // 5. Leadership Speeches & Union Information (with modals)
    $this->load->view('front/partials/speeches', $view_data);

    // 6. Union Parishad Ward Members Carousel
    $this->load->view('front/partials/members_carousel', $view_data);

    // 7. Administrative Officers & Staff Carousel
    $this->load->view('front/partials/employees_grid', $view_data);
    ?>
</main>

<?php
// 8. Modern Dynamic Footer & Owl Carousel Scripts
$this->load->view('front/partials/footer', $view_data);