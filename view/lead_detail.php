<?= view('inc/header.php') ?>
<?= view('inc/navbar.php') ?>

<div class="container">
    <h1>Lead Detail</h1>

    <div>
        Name: <?= "$user->first_name $user->last_name" ?>
    </div>
    <div>
        Website: <?= $user->instances->first()->site->url ?>
    </div>
    <div>
        # of email conversation: <?= $user->service_conversations_count ?>
    </div>
    <div>
        Site Health Score:
    </div>
    <div>
        # of times logged in the last week: <?= $user->access_tokens_count ?>
    </div>
</div>

<?= view('inc/footer.php') ?>
