<link rel="stylesheet" href="<?= base_url('assets/css/general-customer/pages/home_style.css'); ?>">

<!-- Welcome Section -->
<section class="dashboard-hero">

<style>
    /* Hero Section */
.dashboard-hero {
   background: 
    linear-gradient(rgba(10, 42, 58, 0.6), rgba(10, 42, 58, 0.6)),
      url("<?php echo base_url('assets/images/img-page/home_bg.png'); ?>");
  background-size: cover;
  background-position: center;
  position: relative; /* important */
  padding: 10%;
  color: #fff;
  text-align: center;
}

</style>
    <h1>Welcome back, <span class="highlight">User!</span></h1>
    <p class="subtle">What would you like to do today?</p>

    <div class="hero-cards">
        <div class="hero-card" id="orderProgressCard">
            <div class="card-header">
                <span>Order Progress</span>
                <span class="arrow">▼</span>
            </div>
            <div class="card-body">
                <p>1 in progress</p>
            </div>
        </div>

        <div class="hero-card">
            <div class="card-header">
                <span>Recent Activity</span>
                <span class="arrow">▼</span>
            </div>
            <div class="card-body">
                <p>Order 20259821030131 in fabrication</p>
            </div>
        </div>

        <div class="hero-card">
            <div class="card-header">
                <span>Appointment</span>
                <span class="arrow">▼</span>
            </div>
            <div class="card-body">
                <p>06/9/2025 - 9:00 AM</p>
            </div>
        </div>
    </div>

    <p class="last-update">Last Update: 5 hours ago</p>
</section>

<!-- Order Progress Section -->
<section class="order-progress">
    <div class="section-header">
        <h2>Order Progress Overview</h2>
        <div class="dropdown">
            <button onclick="toggleDropdown('orderDropdown')" class="dropbtn">Status ▾</button>
            <div id="orderDropdown" class="dropdown-content">
                <a href="#">All Orders</a>
                <a href="#">In Progress</a>
                <a href="#">Completed</a>
                <a href="#">Cancelled</a>
            </div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Product</th>
                <th>Order ID</th>
                <th>Last Update</th>
                <th>Status</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>798 Series Sliding Window</td>
                <td>20259821030131</td>
                <td>May 27, 2025</td>
                <td><span class="status in-progress">In Progress</span></td>
                <td><a href="#">View details</a></td>
            </tr>
            <tr>
                <td>Frameless Store Front Double Swing Door</td>
                <td>2025456286712</td>
                <td>May 21, 2025</td>
                <td><span class="status completed">Completed</span></td>
                <td><a href="#">View details</a></td>
            </tr>
            <tr>
                <td>Aluminum Kitchen Cabinet</td>
                <td>2025657585932</td>
                <td>May 18, 2025</td>
                <td><span class="status completed">Completed</span></td>
                <td><a href="#">View details</a></td>
            </tr>
            <tr>
                <td>Frameless Mirrors</td>
                <td>2025756735254</td>
                <td>May 10, 2025</td>
                <td><span class="status completed">Completed</span></td>
                <td><a href="#">View details</a></td>
            </tr>
        </tbody>
    </table>
</section>

<!-- Activity Feed -->
<section class="activity-feed">
    <h2>Activity Feed</h2>
    <div class="activity-divider"></div>
    <ul>
        <li>
            <div class="activity-text">
                <strong>Your Order is In Fabrication</strong>
                <p>Your order <span class="order-id">20259821030131</span> is currently in fabrication.</p>
            </div>
            <div class="time-stamp">
                <span>10:30 PM</span><br>
                <span>05/27/2025</span>
            </div>
        </li>

        <li>
            <div class="activity-text">
                <strong>Your Order is Scheduled for an Ocular Visit</strong>
                <p>An ocular visit has been scheduled for your order <span class="order-id">20259821030131</span>.</p>
            </div>
            <div class="time-stamp">
                <span>1:23 PM</span><br>
                <span>05/24/2025</span>
            </div>
        </li>

        <li>
            <div class="activity-text">
                <strong>Your Order is Placed</strong>
                <p>Your order <span class="order-id">20259821030131</span> has been placed successfully.</p>
            </div>
            <div class="time-stamp">
                <span>8:00 AM</span><br>
                <span>05/22/2025</span>
            </div>
        </li>

        <li>
            <div class="activity-text">
                <strong>Your Order is Completed</strong>
                <p>Your order <span class="order-id">2025456286712</span> is completed.</p>
            </div>
            <div class="time-stamp">
                <span>9:30 AM</span><br>
                <span>05/21/2025</span>
            </div>
        </li>
    </ul>
    <div class="read-all">Read all <span class="arrow">▼</span></div>
</section>

<!-- Appointment Section -->
<section class="appointment">
    <div class="section-header">
        <h2>Appointment</h2>
        <div class="dropdown">
            <button onclick="toggleDropdown('appointmentDropdown')" class="dropbtn">Status ▾</button>
            <div id="appointmentDropdown" class="dropdown-content">
                <a href="#">All Status</a>
                <a href="#">Pending</a>
                <a href="#">Confirmed</a>
                <a href="#">Cancelled</a>
            </div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Service</th>
                <th>Date & Time</th>
                <th>Assigned Staff</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>20259821030131</td>
                <td>Installation</td>
                <td>06/9/2025 - 9:00 AM</td>
                <td>Joaquin Santos</td>
                <td><span class="status confirmed">Confirmed</span></td>
            </tr>
            <tr>
                <td>20259851931801</td>
                <td>Ocular Visit</td>
                <td>05/17/2025 - 11:00 AM</td>
                <td>Engr. Cruz</td>
                <td><span class="status pending">Pending</span></td>
            </tr>
            <tr>
                <td>20250189190211</td>
                <td>Consultation</td>
                <td>05/3/2025 - 1:30 PM</td>
                <td>M. Lopez</td>
                <td><span class="status cancelled">Cancelled</span></td>
            </tr>
        </tbody>
    </table>
</section>

<!-- Recommendations -->
<section class="recommendations">
    <h2>You May Also Like</h2>
    <div class="recommendation-grid">
        <div class="recommendation-card">
            <img src="<?= base_url('assets/img/image 1.png'); ?>" alt="Aluminum Kitchen Cabinet">
            <h3>Aluminum Kitchen Cabinet</h3>
            <button>Build and Buy</button>
        </div>

        <div class="recommendation-card">
            <img src="<?= base_url('assets/img/image 2.png'); ?>" alt="Aluminum Kitchen Cabinet">
            <h3>Aluminum Kitchen Cabinet</h3>
            <button>Build and Buy</button>
        </div>

        <div class="recommendation-card">
            <img src="<?= base_url('assets/img/image 3.png'); ?>" alt="Aluminum Kitchen Cabinet">
            <h3>Aluminum Kitchen Cabinet</h3>
            <button>Build and Buy</button>
        </div>

        <div class="recommendation-card">
            <img src="<?= base_url('assets/img/image 4.png'); ?>" alt="Aluminum Kitchen Cabinet">
            <h3>Aluminum Kitchen Cabinet</h3>
            <button>Build and Buy</button>
        </div>
    </div>
</section>

<script>
function toggleDropdown(id) {
    document.getElementById(id).classList.toggle("show");
}

window.onclick = function (e) {
    if (!e.target.matches('.dropbtn')) {
        let dropdowns = document.getElementsByClassName("dropdown-content");
        for (let i = 0; i < dropdowns.length; i++) {
            dropdowns[i].classList.remove("show");
        }
    }
}

document.querySelectorAll(".hero-card").forEach(card => {
    card.querySelector(".card-header").addEventListener("click", () => {
        card.classList.toggle("active");
    });
});
</script>


