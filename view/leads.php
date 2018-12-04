<?= view('inc/header.php') ?>
<?= view('inc/navbar.php') ?>

<div class="container">
    <h1>Leads</h1>

    <div class="form-group">
        <form id="form">
            <input placeholder="Search by name or website" id="search" class="form-control">
        </form>
    </div>

    <table class="table table-bordered mt">
        <thead>
            <tr>
                <th>Name</th>
                <th>Website</th>
                <th># of email converation</th>
                <th>Site Health Score</th>
                <th># of times logged in the last week</th>
            </tr>
        </thead>
        <tbody id="users"></tbody>
    </table>
    <div></div>

</div>

<script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>

<script>
    
(function ($, window) {
    'use strict';

    var $search = $('#search');
    var $form = $('#form');
    var currentPage = 1;
    var lastPage = null;
    $form.submit(function (ev) {
        ev.preventDefault();
        loadData({ append: false });
    });

    function loadData({ append = true } = {}) {

        var search = $search.val();

        var queryParams = {
            search: search,
            page: currentPage,
            limit: 2,
        };
        $.get(<?= json_encode(url('ajax/leads')) ?> + '?' + $.param(queryParams))
        .then(function (res) {
            if (! lastPage) {
                lastPage = res.last_page;
            }
            var users = res.data;
            var content = users.map(user => {
                return `<tr><td>${user.first_name || ''} ${user.last_name || ''}</td><td>${user.instances[0].site.url}</td><td></td><td></td><td>${user.access_tokens_count}</td></tr>`;
            }).join('');
            if (append) {
                $('#users').append(content);
            }
            else {
                $('#users').html(content);
            }
        });
    }
    loadData({append: true});

    $(window).scroll(function () {
        if( $(window).scrollTop() == $(document).height() - $(window).height() ) {
            if (lastPage && currentPage < lastPage) {
                currentPage++;
                loadData();
            }
        }
    });

})(jQuery, window);

</script>

<?= view('inc/footer.php') ?>
