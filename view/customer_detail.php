<?php $this->layout('layout/main.php', [ 'title' => 'Lead Detail' ]) ?>

<div class="container">
    <h1>Customer Detail</h1>

    <div>
        Name: <?= $this->e("$user->first_name $user->last_name") ?>
    </div>
    <div>
        Website: <?= $this->e($user->instances->first()->site->url) ?>
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
