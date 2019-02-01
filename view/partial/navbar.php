<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <a href="#" class="navbar-brand">CRM</a>
    <button type="button" class="navbar-toggler" data-toggle="collapse" data-target="#navbarSupportedContent">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav mr-auto">
            <li class="nav-item">
                <a href="#" class="nav-link">Home</a>
            </li>
            <li class="nav-item">
                <a href="<?= url('customers/graphjs') ?>" class="nav-link">GraphJS</a>
            </li>
            <li class="nav-item">
                <a href="<?= url('customers/groups') ?>" class="nav-link">Grou.ps</a>
            </li>
            <li class="nav-item">
                <a href="<?= url('service-tickets') ?>" class="nav-link">Service</a>
            </li>
        </ul>
        <form method="post" action="<?= url('logout') ?>">
            <button type="submit" class="btn btn-outline-danger">Logout</button>
        </form>
    </div>
</nav>
