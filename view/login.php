<?= view('inc/header.php') ?>

<div class="text-center">
    <form style="width: 100%; max-width: 330px; padding: 15px; margin: auto;">
        <div class="h1">CRM</div>
        <h1 class="h3 mb-3 font-weight-normal">Please login</h1>
        <div class="form-group">
            <input type="email" class="form-control" placeholder="Email address" required autofocus>
        </div>
        <div class="form-group">
            <input type="password" class="form-control" placeholder="Password" required>
        </div>
        <button type="submit" class="btn btn-lg btn-primary btn-block">Login</button>
    </form>
</div>

<?= view('inc/footer.php') ?>
