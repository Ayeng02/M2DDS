<nav class="navbar navbar-expand-lg navbar-light">
    <div class="container-fluid">
        <a class="navbar-brand dropdown-toggle d-flex align-items-center" href="#" id="navbarProfile" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <!-- Dynamic image -->
            <img src="../<?php echo htmlspecialchars($emp_img); ?>" alt="Profile" class="rounded-circle me-2" style="width: 40px; height: 40px;">

            <!-- Name and Role -->
            <div class="d-flex flex-column">
                <span class="text-white"><?php echo htmlspecialchars($emp_fullname); ?></span>
                <small class="text-center" style="color: #A72828 ; font-weight: 500; font-size:15px;"> <i class="bi bi-pc-display-horizontal"></i> Cashier</small>
            </div>
        </a>


        <ul class="dropdown-menu" aria-labelledby="navbarProfile" style="margin-left: 5px;">
            <li>
                <hr class="dropdown-divider">
            </li>
            <li><a class="dropdown-item" href="#" id="logoutBtn"> <i class="bi bi-box-arrow-right"></i> Logout</a></li>
        </ul>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mx-auto">
                <li class="nav-item">
                    <a class="nav-link act1" href="../cashier/cashier.php"><i class="bi bi-shop" style="margin-right: 5px;"></i> POS</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link act2" href="../cashier/productMonitoring.php"><i class="bi bi-box" style="margin-right: 5px;"></i> Product Monitoring</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link act3" href="../cashier/cashierSales.php"><i class="bi bi-graph-up" style="margin-right: 5px;"></i> Sales</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link act4" href="../cashier/dtr.php"><i class="bi bi-calendar" style="margin-right: 5px;"></i> DTR</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link act5" href="../cashier/report.php"><i class="bi bi-file-earmark-text" style="margin-right: 5px;"></i> Report</a>
                </li>
            </ul>
        </div>
    </div>
</nav>