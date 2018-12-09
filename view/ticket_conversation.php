<?= view('inc/header.php') ?>
<?= view('inc/navbar.php') ?>

<div class="container">
    <h1>Ticket Conversation</h1>

    <div class="mt-3">
        <div>Ticket ID: <?= $ticket->uuid ?></div>
        <div>Title: <?= $ticket->title ?></div>
        <div>Type: <?= $ticketTypeToText($ticket->type) ?></div>
        <div>By: <?= "{$ticket->byUser->first_name} {$ticket->byUser->last_name}" ?></div>
        <div>Assigned To: <?= $ticket->assigneeUser ? "{$ticket->assigneeUser->first_name} {$ticket->assigneeUser->last_name}" : '<i>Not Assigned</i>' ?></div>
        <div>Status: <?= $ticketStatusToText($ticket->status) ?></div>
    </div>

    <div class="mt-4">
        <form method="post">
            <label for="reply">Reply</label>
            <textarea id="reply" class="form-control"></textarea>
            <div class="clearfix mt-2">
                <button type="submit" class="btn btn-primary float-right">Submit</button>
            </div>
        </form>
    </div>

    <div class="mt-4">
        <?php foreach ($conversations as $conversation): ?>
            <div class="border mb-5">
                <div class="border-bottom p-2">
                    <span class="fas fa-user"></span>
                    <?= "{$conversation->user->first_name} {$conversation->user->last_name}" ?>
                    <span class="float-right">
                        <span class="fas fa-calendar"></span>
                        <?= $conversation->created_at ?>
                    </span>
                </div>
                <div class="p-2"><?= $conversation->text ?></div>
            </div>
        <?php endforeach ?>
    </div>
</div>

<?= view('inc/footer.php') ?>
