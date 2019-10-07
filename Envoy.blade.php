@servers(['web' => ['matt@iera.hillman.me']])

@task('check-bot', ['on' => 'web'])
    journalctl -f -u schwartz.bot
@endtask

@task('restart-schwartz.bot', ['on' => 'web'])
    sudo systemctl restart schwartz.bot
@endtask

@task('restart-schwartz.broadcast', ['on' => 'web'])
    sudo systemctl restart schwartz.broadcast
@endtask

@task('restart-schwartz.horizon', ['on' => 'web'])
    sudo systemctl restart schwartz.horizon
@endtask

@task('restart-stat-calc', ['on' => 'web'])
    sudo systemctl restart stat-calc
@endtask

@story('restart', ['confirm' => true])
    restart-schwartz.bot
    restart-schwartz.broadcast
    restart-schwartz.horizon
    restart-stat-calc
@endstory

@task('update-submodules', ['on' => 'web'])
    pushd /srv/http/schwartz.hillman.me/
    git --git-dir=/var/repo/schwartz.git --work-tree=. submodule update
@endtask

@task('scrape-guild', ['on' => 'web'])
    pushd /srv/http/schwartz.hillman.me/
    php artisan swgoh:guild {{ $code }}
@endtask