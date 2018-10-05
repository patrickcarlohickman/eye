<div class="bg-white border-t border-b sm:rounded sm:border shadow-inner">
    <div class="flex mb-8 border-b border-grey-light pb-4">
        <div class="flex-none bg-brand bg-circuit bg-md ml-4 w-16 h-16 -mt-6 rounded shadow-lg">
            <div class="h-full flex justify-center items-center">
                @eyewitness_svg('analytics-89', 'svgcolor-white')
            </div>
        </div>
        <h1 class="font-hairline ml-4 mt-2 text-2xl">Overview</h1>
    </div>
    <div class="px-6 pb-4">
        @eyewitness_tutorial('These blue helper boxes will provide info on each page that you visit. Once you are familar with Eyewitness you can disable these helpers in your config file by settings "display_helpers" to false.')
            @if ($statuses->where('healthy', 0)->count())
                <div class="flex items-end justify-center w-full items-center pb-8 border-b">
                    <div class="f-modal-alert pr-8">
                        <div class="f-modal-icon f-modal-warning scaleWarning">
                            <span class="f-modal-body pulseWarningIns"></span>
                            <span class="f-modal-dot pulseWarningIns"></span>
                        </div>
                    </div>
                    <p class="text-2xl text-grey-darker font-medium">You have unhealthy monitors</p>
                </div>
                <div class="border-b mb-4">
                    @foreach($statuses->where('healthy', 0) as $status)
                        <div class="flex py-2">
                            <div class="w-1/2 text-right">
                                <p class="block tracking-wide text-grey text-right mr-1">Unhealthy monitor</p>
                            </div>
                            <div class="w-1/2">
                                <p class="text-grey-darker ml-4">{{ ucfirst(strtolower($status->monitor)) }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="flex items-end justify-center w-full items-center pb-8">
                    <div class="f-modal-alert pr-8">
                        <div class="f-modal-icon f-modal-success animate scaleWarning">
                            <span class="f-modal-line f-modal-tip animateSuccessTip"></span>
                            <span class="f-modal-line f-modal-long animateSuccessLong"></span>
                            <div class="f-modal-placeholder"></div>
                            <div class="f-modal-fix"></div>
                        </div>
                    </div>
                    <p class="text-2xl text-grey-darker font-medium">All monitors are healthy</p>
                </div>
            @endif

        <div class="px-6 pb-4">
            <div class="block md:flex">
                <div class="w-full md:w-1/2">
                    <div class="pl-1 pb-3 pt-3">
                        <div class="flex">
                            <div class="w-1/2 text-right">
                                <p class="block tracking-wide text-grey text-right mr-1">Application name</p>
                            </div>
                            <div class="w-1/2">
                                <p class="text-grey-darker ml-4">{{ $eye->application()->find('name') }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="pl-1 pb-3 pt-3">
                        <div class="flex">
                            <div class="w-1/2 text-right">
                                <p class="block tracking-wide text-grey text-right mr-1">Environment</p>
                            </div>
                            <div class="w-1/2">
                                <p class="text-grey-darker ml-4">{{ ucfirst(strtolower($eye->application()->find('env'))) }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="pl-1 pb-3 pt-3">
                        <div class="flex">
                            <div class="w-1/2 text-right">
                                <p class="block tracking-wide text-grey text-right mr-1">Timezone</p>
                            </div>
                            <div class="w-1/2">
                                <p class="text-grey-darker ml-4">{{ $eye->application()->find('timezone') }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="pl-1 pb-3 pt-3">
                        <div class="flex">
                            <div class="w-1/2 text-right">
                                <p class="block tracking-wide text-grey text-right mr-1">PHP version</p>
                            </div>
                            <div class="w-1/2">
                                <p class="text-grey-darker ml-4">{{ $eye->application()->find('version_php') }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="pl-1 pb-3 pt-3">
                        <div class="flex">
                            <div class="w-1/2 text-right">
                                <p class="block tracking-wide text-grey text-right mr-1">Laravel version</p>
                            </div>
                            <div class="w-1/2">
                                <p class="text-grey-darker ml-4">{{ $eye->application()->find('version_laravel') }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="pl-1 pb-3 pt-3">
                        <div class="flex">
                            <div class="w-1/2 text-right">
                                <p class="block tracking-wide text-grey text-right mr-1">Eyewitness version</p>
                            </div>
                            <div class="w-1/2">
                                <p class="text-grey-darker ml-4">{{ $eye->version() }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="w-full md:w-1/2">
                    <div class="pl-1 pb-3 pt-3">
                        <div class="flex">
                            <div class="w-1/2 text-right">
                                <p class="block tracking-wide text-grey text-right mr-1">Config cache</p>
                            </div>
                            <div class="w-1/2 inline-flex">
                                <p class="ml-4 max-w-full font-mono rounded text-xs text-white p-1 {{ $eye->application()->find('cache_config') ? 'bg-green' : 'bg-red' }}">{{ $eye->application()->find('cache_config') ? 'Cached' : 'Disabled' }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="pl-1 pb-3 pt-3">
                        <div class="flex">
                            <div class="w-1/2 text-right">
                                <p class="block tracking-wide text-grey text-right mr-1">Route cache</p>
                            </div>
                            <div class="w-1/2 inline-flex">
                                <p class="ml-4 max-w-full font-mono rounded text-xs text-white p-1 {{ $eye->application()->find('cache_route') ? 'bg-green' : 'bg-red' }}">{{ $eye->application()->find('cache_route') ? 'Cached' : 'Disabled' }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="pl-1 pb-3 pt-3">
                        <div class="flex">
                            <div class="w-1/2 text-right">
                                <p class="block tracking-wide text-grey text-right mr-1">Debug mode</p>
                            </div>
                            <div class="w-1/2 inline-flex">
                                <p class="ml-4 max-w-full font-mono rounded text-xs text-white p-1 {{ $eye->application()->find('debug') ? 'bg-red' : 'bg-green' }}">{{ $eye->application()->find('debug') ? 'Enabled' : 'Disabled' }}</p>
                            </div>
                        </div>
                    </div>
                    @if ($eye->application()->find('eyewitness_debug'))
                        <div class="pl-1 pb-3 pt-3">
                            <div class="flex">
                                <div class="w-1/2 text-right">
                                    <p class="block tracking-wide text-grey text-right mr-1">Eyewitness debug</p>
                                </div>
                                <div class="w-1/2 inline-flex">
                                    <p class="ml-4 max-w-full font-mono rounded text-xs text-white p-1 bg-red">Enabled</p>
                                </div>
                            </div>
                        </div>
                    @endif
                    <div class="pl-1 pb-3 pt-3">
                        <div class="flex">
                            <div class="w-1/2 text-right">
                                <p class="block tracking-wide text-grey text-right mr-1">Maintenance mode</p>
                            </div>
                            <div class="w-1/2 inline-flex">
                                <p class="ml-4 max-w-full font-mono rounded text-xs text-white p-1 {{ $eye->application()->find('maintenance_mode') ? 'bg-red' : 'bg-green' }}">{{ $eye->application()->find('maintenance_mode') ? 'Down' : 'Up' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
