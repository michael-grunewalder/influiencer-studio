<div class="flex flex-col gap-4">
    <div>For more examples look at <a href="https://mary-ui.com/docs/installation">https://mary-ui.com/docs/installation</a> and
        <a href="https://daisyui.com/components/">https://daisyui.com/components/</a>
    </div>
    <hr />
    <!-- Breadcrumps -->
    <div class="mockup-window bg-base-300 border border-base-300 pb-4" title="Hello World">
        <div class="grid place-content-center h-80">
            <x-header title="Breadcrumps"></x-header>
            <!--------------BREADCRUMPS ---------------------->
            @php
                $breadcrumbs = [
                    ['label' => 'Dashboard'],
                    ['label' => 'Demo'],
                    ['label' => 'Components'],
                ];
            @endphp
            <x-breadcrumbs :items="$breadcrumbs" />
        </div>
    </div>

    <!-- Buttons -->
    <div class="mockup-window bg-base-300 border border-base-300 pb-4">
        <div class="grid place-content-center">

            <x-header title="Buttons"></x-header>
            <div class="flex gap-3">
                <x-button label="Hi!" />
                {{--  COLOR AND STYLE --}}
                <x-button label="Hi!" class="btn-primary" />
                <x-button label="How" class="btn-warning" />
                <x-button label="Are" class="btn-success" />
                <x-button label="You?" class="btn-error btn-sm btn-soft" />

                {{-- SLOT--}}
                <x-button class="btn-primary btn-dash">
                    With default slot 😃
                </x-button>

                {{-- CIRCLE --}}
                <x-button icon="o-user" class="btn-circle" />
                <x-button icon="o-user" class="btn-circle btn-outline" />

                {{-- SQUARE --}}
                <x-button icon="o-user" class="btn-circle btn-ghost" />
                <x-button icon="o-user" class="btn-square" />
            </div>

        </div>
    </div>

    <!-- Badges --->
    <div class="mockup-window bg-base-300 border border-base-300 pb-4">
        <div class="grid place-content-center">

            <x-header title="Badges"></x-header>
            <div>Standalone</div>
            <div class="flex gap-3">
                <x-badge value="Hello" />
                <x-badge value="Hello" class="badge-soft" />
                <x-badge value="Hello" class="badge-primary" />
                <x-badge value="Hello" class="badge-primary badge-soft " />
                <x-badge value="Hello" class="badge-warning" />
                <x-badge value="Hello" class="badge-error badge-dash" />
            </div>
            <div class="mt-8">Combined</div>
            <div class="flex gap-3">
                <x-button>
                    Inbox
                    <x-badge value="+99" class="badge-neutral badge-sm" />
                </x-button>
                <x-button class="indicator">
                    Inbox
                    <x-badge value="7" class="badge-secondary badge-sm indicator-item" />
                </x-button>
            </div>
        </div>
    </div>

    <!-- Stastistic -->
    <div class="mockup-window bg-base-300 border border-base-300 pb-4">
        <div class="grid place-content-center">

            <x-header title="Statistic"></x-header>
            <div class="flex gap-3">
                <x-stat
                    title="Messages"
                    value="44"
                    icon="o-envelope"
                    tooltip="Hello"
                    color="text-primary" />

                <x-stat
                    title="Sales"
                    description="This month"
                    value="22.124"
                    icon="o-arrow-trending-up"
                    tooltip-bottom="There" />

                <x-stat
                    title="Lost"
                    description="This month"
                    value="34"
                    icon="o-arrow-trending-down"
                    tooltip-left="Ops!" />

                <x-stat
                    title="Sales"
                    description="This month"
                    value="22.124"
                    icon="o-arrow-trending-down"
                    class="text-orange-500"
                    color="text-pink-500"
                    tooltip-right="Gosh!" />
            </div>

        </div>
    </div>

    <!-- Cards -->
    <div class="mockup-window bg-base-300 border border-base-300 pb-4">
        <div class="grid place-content-center">

            <x-header title="Cards"></x-header>
            <div class="flex gap-3">
                <x-card title="Your stats" subtitle="Our findings about you" shadow separator>
                    I have title, subtitle and separator.
                </x-card>

                <x-card title="Nice things">
                    I am using slots here.

                    <x-slot:figure>
                        <img src="https://picsum.photos/500/200" />
                    </x-slot:figure>
                    <x-slot:menu>
                        <x-button icon="o-share" class="btn-circle btn-sm" />
                        <x-icon name="o-heart" class="cursor-pointer" />
                    </x-slot:menu>
                    <x-slot:actions separator>
                        <x-button label="Ok" class="btn-primary" />
                    </x-slot:actions>
                </x-card>
            </div>

        </div>
    </div>

    <!-- Carousell -->
    <div class="mockup-window bg-base-300 border border-base-300 pb-4">
            <x-header title="Carousell"></x-header>
                @php
                    $slides = [
                        [
                            'image' => 'https://picsum.photos/800/800?random=1',
                        ],
                        [
                            'image' => 'https://picsum.photos/800/800?random=2',
                        ],
                        [
                            'image' => 'https://picsum.photos/800/800?random=3',
                        ],
                        [
                            'image' => 'https://picsum.photos/800/800?random=4',
                        ],
                    ];
                @endphp
                <h1>Basic</h1>
                <x-carousel :slides="$slides" />
                <h1>No Indicators</h1>
                <x-carousel :slides="$slides" without-indicators />
                <h1>No Arrows</h1>
                <x-carousel :slides="$slides" without-arrows />
                <h1>Autoplay</h1>
                <x-carousel :slides="$slides" autoplay />

    </div>
    <hr />
    <div>For more examples look at <a href="https://mary-ui.com/docs/installation">https://mary-ui.com/docs/installation</a> and
        <a href="https://daisyui.com/components/">https://daisyui.com/components/</a>
    </div>


</div>
