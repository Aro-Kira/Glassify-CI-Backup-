<link rel="stylesheet" href="<?php echo base_url('assets/css/general-customer/shop/waiting_order_style.css'); ?>">

<div class="breadcrumb-strip">
    <div class="page-title">Products & Services</div>
</div>

<main>
    <section class="status-banner">
        <div class="status-icon">
            <svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" fill="none" stroke-linecap="round"
                stroke-linejoin="round" color="#eeb609">
                <path d="M5 22h14"></path>
                <path d="M5 2h14"></path>
                <path d="M17 22v-4.172a2 2 0 0 0-.586-1.414L12 12l-4.414 4.414A2 2 0 0 0 7 17.828V22"></path>
                <path d="M7 2v4.172a2 2 0 0 0 .586 1.414L12 12l4.414-4.414A2 2 0 0 0 17 6.172V2"></path>
            </svg>
        </div>
        <h1 class="status-title">Waiting for your order approval</h1>
        <p class="status-desc">We've received your order and are currently reviewing the details. We'll notify you soon.</p>
        <?php if (isset($order_id)): ?>
            <p class="order-reference">Order Reference: <strong>GI<?= str_pad($order_id, 3, '0', STR_PAD_LEFT) ?></strong></p>
        <?php endif; ?>
    </section>

    <div class="waiting-actions">
        <a href="<?= base_url('products') ?>" class="btn-primary">Continue Shopping</a>
        <a href="<?= base_url('my_purchases') ?>" class="btn-secondary">View My Purchases</a>
    </div>
</main>

<style>
.status-banner {
    text-align: center;
    padding: 60px 20px;
    background: #f9f9f9;
    border-radius: 12px;
    margin: 40px auto;
    max-width: 600px;
}

.status-icon {
    width: 80px;
    height: 80px;
    margin: 0 auto 20px;
}

.status-icon svg {
    width: 100%;
    height: 100%;
    color: #eeb609;
}

.status-title {
    font-size: 24px;
    font-weight: 600;
    color: #003b4d;
    margin-bottom: 10px;
}

.status-desc {
    font-size: 16px;
    color: #666;
    margin-bottom: 15px;
}

.order-reference {
    font-size: 14px;
    color: #003b4d;
    background: #e8f4f8;
    padding: 10px 20px;
    border-radius: 6px;
    display: inline-block;
    margin-top: 10px;
}

.order-reference strong {
    color: #eeb609;
    font-weight: 700;
}

.waiting-actions {
    text-align: center;
    padding: 20px;
    margin-bottom: 40px;
}

.waiting-actions .btn-primary,
.waiting-actions .btn-secondary {
    display: inline-block;
    padding: 12px 30px;
    margin: 0 10px;
    border-radius: 6px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
}

.waiting-actions .btn-primary {
    background: #003b4d;
    color: #fff;
}

.waiting-actions .btn-primary:hover {
    background: #00567a;
}

.waiting-actions .btn-secondary {
    background: #eeb609;
    color: #003b4d;
}

.waiting-actions .btn-secondary:hover {
    background: #d9a508;
}

.breadcrumb-strip {
    padding: 15px 30px;
    background: #003b4d;
    color: #fff;
}

.page-title {
    font-size: 18px;
    font-weight: 600;
}
</style>
