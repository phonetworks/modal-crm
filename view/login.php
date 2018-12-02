<?= view('inc/header.php') ?>

<div class="text-center">
    <form method="POST" action="<?= url('login') ?>" novalidate style="width: 100%; max-width: 330px; padding: 15px; margin: auto;">
        <div class="h1">CRM</div>
        <h1 class="h3 mb-3 font-weight-normal">Please login</h1>
        <?php if (isset($fail_message)): ?>
            <div class="text-danger"><?= $fail_message ?></div>
        <?php endif ?>
        <div class="form-group">
            <input type="email" name="email" value="<?= isset($body) ? $body['email'] : null ?>" class="form-control" placeholder="Email address" required autofocus>
            <?php if (isset($errors) && $errors->has('email')): ?>
                <div class="text-danger"><?= $errors->first('email') ?></div>
            <?php endif ?>
        </div>
        <div class="form-group">
            <input type="password" name="password" class="form-control" placeholder="Password" required>
            <?php if (isset($errors) && $errors->has('password')): ?>
                <div class="text-danger"><?= $errors->first('password') ?></div>
            <?php endif ?>
        </div>
        <button type="submit" class="btn btn-lg btn-primary btn-block">Login</button>
    </form>
</div>

<?= view('inc/footer.php') ?>
