<div class="w-full px-3 sm:px-9">
    <ul class="w-full flex flex-row text-primary-600 overflow-hidden rounded-l-2xl rounded-r-2xl bg-rp-primary-purple-100 text-xs sm:text-sm">
        <li class="{{request()->routeIs('features.remit') ? 'bg-primary-600 text-white font-bold' : ''}} px-2 cursor-pointer flex-1 rounded-2xl py-1 text-center"><a href="{{route('features.remit')}}" class="block">Remit</a></li>
        <li class="{{request()->routeIs('features.explore') ? 'bg-primary-600 text-white font-bold' : ''}} px-2 cursor-pointer flex-1 rounded-2xl py-1 text-center"><a href="{{route('features.explore')}}" class="block">Explore</a></li>
        <li class="{{request()->routeIs('features.payments') ? 'bg-primary-600 text-white font-bold' : '' }} px-2 cursor-pointer flex-1 rounded-2xl py-1 text-center"><a href="{{route('features.payments')}}" class="block">Payments</a></li>
        <li class="{{request()->routeIs('features.assets') ? 'bg-primary-600 text-white font-bold' : '' }} px-2 cursor-pointer flex-1 rounded-2xl py-1 text-center"><a href="{{route('features.assets')}}" class="block">Assets</a></li>
        <li class="{{request()->routeIs('features.yolo') ? 'bg-primary-600 text-white font-bold' : '' }} px-2 cursor-pointer flex-1 rounded-2xl py-1 text-center"><a href="{{route('features.yolo')}}" class="block">Yolo</a></li>
    </ul>
</div>