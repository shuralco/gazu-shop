<div>

    @section('metatags')
        <x-seo-meta
            :title="$title ?? 'Головна сторінка'"
            :description="$desc ?? null"
            :pageType="'website'"
            :language="'uk'"
        />
    @endsection

    <div class="pt-4 md:pt-6">
        @foreach($modules as $module)
            @includeIf('livewire.homepage-modules.' . $module->type, ['module' => $module])
        @endforeach
    </div>

</div>

@if(session('success'))
    @script
    <script>
        toastr.success('{{ session('success') }}')
    </script>
    @endscript
@endif
