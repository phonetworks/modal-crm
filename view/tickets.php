<?= view('inc/header.php') ?>
<?= view('inc/navbar.php') ?>

<div class="container">
    <h1>Service Tickets</h1>

    <table class="table table-bordered mt">
        <thead>
            <tr>
                <th>Ticket ID</th>
                <th>Title</th>
                <th>Type</th>
                <th>By</th>
                <th>Assignee</th>
                <th>Open date</th>
                <th>Close date</th>
                <th>Status</th>
                <th>Feedback</th>
            </tr>
        </thead>
            <tbody>
            <?php foreach ($tickets as $ticket): ?>
                <tr>
                    <td><a href="<?= url('service-tickets/' . $ticket->uuid) ?>"><?= $ticket->uuid ?></a></td>
                    <td><?= $ticket->title ?></td>
                    <td><?= $ticketTypeToText($ticket->type) ?></td>
                    <td><?= "{$ticket->byUser->first_name} {$ticket->byUser->last_name}" ?></td>
                    <td><?= $ticket->assigneeUser ? "{$ticket->assigneeUser->first_name} {$ticket->assigneeUser->last_name}" : '' ?></td>
                    <td><?= $ticket->open_date ?></td>
                    <td><?= $ticket->close_date ?></td>
                    <td><?= $ticketStatusToText($ticket->status) ?></td>
                    <td><?= $ticket->feedback ?></td>
                </tr>
            <?php endforeach ?>
            </tbody>
    </table>
</div>

<?= view('inc/footer.php') ?>
