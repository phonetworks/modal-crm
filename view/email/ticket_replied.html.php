<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width">
    <title>Ticket Replied</title>
</head>
<body>

<div>
    <p>Your ticket has been replied by <?= $this->e($repliedByName) ?> (<?= $this->e($repliedByEmail) ?>).</p>
    <p><a href="<?= $this->e($ticketUrl) ?>">View ticket</a></p>
</div>

</body>
</html>
