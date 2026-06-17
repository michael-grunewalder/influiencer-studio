<div class="flex flex-col gap-4">
    <x-header title="{{__('influencer.ui.overview.label')}}" separator progress-indicator>
        <x-slot:actions>
            <x-button label="Add Influencer" icon="o-plus" class="btn-primary" link="/influencer/create" />
        </x-slot:actions>
    </x-header>
    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4">
        @foreach($influencer as $i)
            <x-card title="{{ $i->name }}">
                <x-slot:figure>
                    <img src="{{$i->avatar}}" />
                </x-slot:figure>
                <x-slot:menu>
                    <x-button icon="o-share" class="btn-circle btn-sm" />
                    <x-icon name="o-heart" class="cursor-pointer" />
                </x-slot:menu>
                <x-slot:actions separator>
                    <x-button label="Use" class="btn-primary" />
                </x-slot:actions>
            </x-card>
        @endforeach
    </div>
</div>
