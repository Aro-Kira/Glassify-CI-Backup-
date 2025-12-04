<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($title) ? $title : 'sales Panel'; ?></title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- Shared CSS -->
    <link rel="stylesheet" href="<?= base_url('assets/css/include/layout.css'); ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/include/sidebar.css'); ?>">
    <link rel="stylesheet" href="<?php echo base_url('assets/css/include/header_admin.css'); ?>">

    <!-- Page-specific CSS -->
    <?php if (isset($page_css)): ?>
        <link rel="stylesheet" href="<?= base_url('assets/css/' . $page_css); ?>">
    <?php endif; ?>

    <!-- Chart.js (optional) -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>


    <!--  HEADER -->
    <?php $this->load->view('sales_page/includes/header'); ?>
</head>

<body>

    <div class="layout-container" id="layout-container">

        <!-- Sidebar -->
        <?php $this->load->view('sales_page/includes/sidebar'); ?>

        <!-- Main content -->
        <main class="main-content" id="main-content">
            <section class="content-area">
                <?php
                if (isset($content_view)) {
                   $this->load->view($content_view);

                } else {
                    echo "<p>No content view found.</p>";
                }
                ?>
            </section>
        </main>
    </div>

    <!-- Sidebar toggle JS -->
    <script src="<?= base_url('assets/js/includes/sidebar.js'); ?>"></script>

    <!-- Page-specific JS -->
    <?php if (isset($page_js)): ?>
        <script src="<?= base_url('assets/js/' . $page_js); ?>"></script>
    <?php endif; ?>
</body>

</html>