<section class="article-list py-5">
    <div class="article-list__wrapper container-xl">
        <div class="article-list__breadcrumb mb-3">
            {{ Breadcrumbs::render('page', $page) }}
        </div>
    </div>
    @if ($page['nested'])
        @include('livewire.article-list.detail')
    @else
        @include('livewire.article-list.list')
    @endif
</section>
