<style>
    .dashboard-title .links {
        text-align: center;
        margin-bottom: 1.75rem;
    }

    .dashboard-title .links>a {
        padding: 0 25px;
        font-size: 12px;
        font-weight: 600;
        letter-spacing: .1rem;
        text-decoration: none;
        text-transform: uppercase;
        color: #fff;
    }

    .dashboard-title h1 {
        font-weight: 200;
        font-size: 2.5rem;
    }

    .dashboard-title .avatar {
        background: #fff;
        border: 2px solid #fff;
        width: 70px;
        height: 70px;
    }
</style>

<div class="dashboard-title card bg-primary">
    <div class="card-body">
        <div class="text-center ">
            <img class="avatar img-circle shadow mt-1" src="{{ admin_asset('@admin/images/logo.png') }}">

            <div class="text-center mb-1">
                <h1 class="mb-2 mt-2 text-white">Appsolutely AIO</h1>
                <div class="links">
                    <a href="{{ \Appsolutely\AIO\Admin::WEBSITE_URL }}" target="_blank">Website</a>
                    <a href="{{ \Appsolutely\AIO\Admin::GITHUB_URL }}" target="_blank">Github</a>
                </div>
            </div>
        </div>
    </div>
</div>
