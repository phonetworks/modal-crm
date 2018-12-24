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
                <th># of email conversation</th>
                <th>Site Health Score</th>
                <th># of times logged in the last week</th>
            </tr>
        </thead>
        <tbody id="users"></tbody>
    </table>
    <div></div>

</div>

<div id="email-modal" class="modal fade" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Send Email</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="form-group">
                        <label class="col-form-label">Title</label>
                        <input type="text" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="col-form-label">Content</label>
                        <textarea class="form-control"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary">Send</button>
            </div>
        </div>
    </div>
</div>

<script>
    
(function ($, window) {
    'use strict';

    var baseUrl = <?= json_encode(url()) ?>;
    var $search = $('#search');
    var $form = $('#form');
    var currentPage = 1;
    var lastPage = null;
    $form.submit(function (ev) {
        ev.preventDefault();
        loadData({ append: false, page: 1 });
    });

    function loadData({ append = true, page = null } = {}) {

        var search = $search.val();

        currentPage = page ? page : currentPage;

        var queryParams = {
            search: search,
            page: currentPage,
            limit: 20,
        };
        $.get(<?= json_encode(url('ajax/leads')) ?> + '?' + $.param(queryParams))
        .then(function (res) {
            if (! lastPage) {
                lastPage = res.last_page;
            }
            var users = res.data;
            var $content = users.map(user => {
                var $tr = $(`
<tr>
    <td>
        <button href="#" class="btn-email btn btn-link float-right"><span class="fas fa-envelope"></span></button>
        <a href="${baseUrl}/leads/${user.id}">${user.first_name || ''} ${user.last_name || ''}</a>
    </td>
    <td>${(user.instances[0] && user.instances[0].site) ? user.instances[0].site.url : ''}</td>
    <td>${user.service_conversations_count}</td>
    <td></td><td>${user.access_tokens_count}</td>
</tr>
`);
                $tr.find('.btn-email').on('click', function (ev) {
                    ev.preventDefault();
                    $('#email-modal').modal();
                });
                return $tr;
            });
            if (append) {
                $('#users').append($content);
            }
            else {
                $('#users').html($content);
            }

            // Load more content if not reached bottom of the page
            if (hasNextPage()
                && ($('html').height() - $(window).scrollTop() < $(window).height())) {
                currentPage++;
                loadData();
            }
        }).fail(function (err) {
            console.log(err);
            alert('Error occurred');
        });
    }
    loadData({append: true});

    function hasNextPage() {
        return lastPage && currentPage < lastPage;
    }

    $(window).scroll(function () {
        if( $(window).scrollTop() == $(document).height() - $(window).height() ) {
            if (hasNextPage()) {
                currentPage++;
                loadData();
            }
        }
    });

})(jQuery, window);

</script>

<?= view('inc/footer.php') ?>
