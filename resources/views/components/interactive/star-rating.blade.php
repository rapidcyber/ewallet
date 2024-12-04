<div x-data="{ rating: 0, ratingOnHover: 0, isOnMouseOverMode: false }" x-modelable="rating" {{ $attributes->merge(['class' => 'flex items-center gap-2']) }}>
    {{-- Star --}}
    <template x-if="isOnMouseOverMode === false">
        <div class="flex gap-2">
            {{-- star#1 --}}    
            <div class="flex">
                <template x-if="rating < 0.5">
                    <x-icon.product.star-left-side fill="#D9D9D9" @mouseover="isOnMouseOverMode=true;"  @click="rating=0.5" class="cursor-pointer" />
                </template>
                <template x-if="rating >= 0.5">
                    <x-icon.product.star-left-side fill="#FAA90C" @mouseover="isOnMouseOverMode=true;" @click="rating=0.5" class="cursor-pointer" />
                </template>
                <template x-if="rating < 1">
                    <x-icon.product.star-right-side fill="#D9D9D9" @mouseover="isOnMouseOverMode=true;" @click="rating=1" class="cursor-pointer" />
                </template>
                <template x-if="rating >= 1">
                    <x-icon.product.star-right-side fill="#FAA90C" @mouseover="isOnMouseOverMode=true;" @click="rating=1" class="cursor-pointer" />
                </template>
            </div>

            {{-- star#2 --}}
            <div class="flex">
                <template x-if="rating < 1.5">
                    <x-icon.product.star-left-side fill="#D9D9D9" @mouseover="isOnMouseOverMode=true;" @click="rating=1.5" class="cursor-pointer" />
                </template>
                <template x-if="rating >= 1.5">
                    <x-icon.product.star-left-side fill="#FAA90C" @mouseover="isOnMouseOverMode=true;" @click="rating=1.5" class="cursor-pointer" />
                </template>
                <template x-if="rating < 2">
                    <x-icon.product.star-right-side fill="#D9D9D9" @mouseover="isOnMouseOverMode=true;" @click="rating=2" class="cursor-pointer" />
                </template>
                <template x-if="rating >= 2">
                    <x-icon.product.star-right-side fill="#FAA90C" @mouseover="isOnMouseOverMode=true;" @click="rating=2" class="cursor-pointer" />
                </template>
            </div>

            {{-- star#3 --}}
            <div class="flex">
                <template x-if="rating < 2.5">
                    <x-icon.product.star-left-side fill="#D9D9D9" @mouseover="isOnMouseOverMode=true;" @click="rating=2.5" class="cursor-pointer" />
                </template>
                <template x-if="rating >= 2.5">
                    <x-icon.product.star-left-side fill="#FAA90C" @mouseover="isOnMouseOverMode=true;" @click="rating=2.5" class="cursor-pointer" />
                </template>
                <template x-if="rating < 3">
                    <x-icon.product.star-right-side fill="#D9D9D9" @mouseover="isOnMouseOverMode=true;" @click="rating=3" class="cursor-pointer" />
                </template>
                <template x-if="rating >= 3">
                    <x-icon.product.star-right-side fill="#FAA90C" @mouseover="isOnMouseOverMode=true;" @click="rating=3" class="cursor-pointer" />
                </template>
            </div>

            {{-- star#4 --}}
            <div class="flex">
                <template x-if="rating < 3.5">
                    <x-icon.product.star-left-side fill="#D9D9D9" @mouseover="isOnMouseOverMode=true;" @click="rating=3.5" class="cursor-pointer" />
                </template>
                <template x-if="rating >= 3.5">
                    <x-icon.product.star-left-side fill="#FAA90C" @mouseover="isOnMouseOverMode=true;" @click="rating=3.5" class="cursor-pointer" />
                </template>
                <template x-if="rating < 4">
                    <x-icon.product.star-right-side fill="#D9D9D9" @mouseover="isOnMouseOverMode=true;" @click="rating=4" class="cursor-pointer" />
                </template>
                <template x-if="rating >= 4">
                    <x-icon.product.star-right-side fill="#FAA90C" @mouseover="isOnMouseOverMode=true;" @click="rating=4" class="cursor-pointer" />
                </template>
            </div>

            {{-- star#5 --}}
            <div class="flex">
                <template x-if="rating < 4.5">
                    <x-icon.product.star-left-side fill="#D9D9D9" @mouseover="isOnMouseOverMode=true;" @click="rating=4.5" class="cursor-pointer" />
                </template>
                <template x-if="rating >= 4.5">
                    <x-icon.product.star-left-side fill="#FAA90C" @mouseover="isOnMouseOverMode=true;" @click="rating=4.5" class="cursor-pointer" />
                </template>
                <template x-if="rating < 5">
                    <x-icon.product.star-right-side fill="#D9D9D9" @mouseover="isOnMouseOverMode=true;" @click="rating=5" class="cursor-pointer" />
                </template>
                <template x-if="rating >= 5">
                    <x-icon.product.star-right-side fill="#FAA90C" @mouseover="isOnMouseOverMode=true;" @click="rating=5" class="cursor-pointer" />
                </template>
            </div>
        </div>
    </template>
    <template x-if="isOnMouseOverMode === true">
        <div class="flex gap-2">
            {{-- star#1 --}}    
            <div class="flex">
                <template x-if="ratingOnHover < 0.5">
                    <x-icon.product.star-left-side fill="#D9D9D9" @mouseover="isOnMouseOverMode=true;ratingOnHover=0.5" @mouseleave="isOnMouseOverMode=false;" @click="rating=0.5" class="cursor-pointer" />
                </template>
                <template x-if="ratingOnHover >= 0.5">
                    <x-icon.product.star-left-side fill="#FAA90C" @mouseover="isOnMouseOverMode=true;ratingOnHover=0.5" @mouseleave="isOnMouseOverMode=false;" @click="rating=0.5" class="cursor-pointer" />
                </template>
                <template x-if="ratingOnHover < 1">
                    <x-icon.product.star-right-side fill="#D9D9D9" @mouseover="isOnMouseOverMode=true;ratingOnHover=1" @mouseleave="isOnMouseOverMode=false;" @click="rating=1" class="cursor-pointer" />
                </template>
                <template x-if="ratingOnHover >= 1">
                    <x-icon.product.star-right-side fill="#FAA90C" @mouseover="isOnMouseOverMode=true;ratingOnHover=1" @mouseleave="isOnMouseOverMode=false;" @click="rating=1" class="cursor-pointer" />
                </template>
            </div>

            {{-- star#2 --}}
            <div class="flex">
                <template x-if="ratingOnHover < 1.5">
                    <x-icon.product.star-left-side fill="#D9D9D9" @mouseover="isOnMouseOverMode=true;ratingOnHover=1.5" @mouseleave="isOnMouseOverMode=false;"
                    @click="rating=1.5" class="cursor-pointer" />
                </template>
                <template x-if="ratingOnHover >= 1.5">
                    <x-icon.product.star-left-side fill="#FAA90C" @mouseover="isOnMouseOverMode=true;ratingOnHover=1.5" @mouseleave="isOnMouseOverMode=false;"
                    @click="rating=1.5" class="cursor-pointer" />
                </template>
                <template x-if="ratingOnHover < 2">
                    <x-icon.product.star-right-side fill="#D9D9D9" @mouseover="isOnMouseOverMode=true;ratingOnHover=2" @mouseleave="isOnMouseOverMode=false;"
                    @click="rating=2" class="cursor-pointer" />
                </template>
                <template x-if="ratingOnHover >= 2">
                    <x-icon.product.star-right-side fill="#FAA90C" @mouseover="isOnMouseOverMode=true;ratingOnHover=2" @mouseleave="isOnMouseOverMode=false;" @click="rating=2" class="cursor-pointer" />
                </template>
            </div>

            {{-- star#3 --}}
            <div class="flex">
                <template x-if="ratingOnHover < 2.5">
                    <x-icon.product.star-left-side fill="#D9D9D9" @mouseover="isOnMouseOverMode=true;ratingOnHover=2.5" @mouseleave="isOnMouseOverMode=false;"
                    @click="rating=2.5" class="cursor-pointer" />
                </template>
                <template x-if="ratingOnHover >= 2.5">
                    <x-icon.product.star-left-side fill="#FAA90C" @mouseover="isOnMouseOverMode=true;ratingOnHover=2.5" @mouseleave="isOnMouseOverMode=false;"
                    @click="rating=2.5" class="cursor-pointer" />
                </template>
                <template x-if="ratingOnHover < 3">
                    <x-icon.product.star-right-side fill="#D9D9D9" @mouseover="isOnMouseOverMode=true;ratingOnHover=3" @mouseleave="isOnMouseOverMode=false;"
                    @click="rating=3" class="cursor-pointer" />
                </template>
                <template x-if="ratingOnHover >= 3">
                    <x-icon.product.star-right-side fill="#FAA90C" @mouseover="isOnMouseOverMode=true;ratingOnHover=3" @mouseleave="isOnMouseOverMode=false;"
                    @click="rating=3" class="cursor-pointer" />
                </template>
            </div>

            {{-- star#4 --}}
            <div class="flex">
                <template x-if="ratingOnHover < 3.5">
                    <x-icon.product.star-left-side fill="#D9D9D9" @mouseover="isOnMouseOverMode=true;ratingOnHover=3.5" @mouseleave="isOnMouseOverMode=false;"
                    @click="rating=3.5" class="cursor-pointer" />
                </template>
                <template x-if="ratingOnHover >= 3.5">
                    <x-icon.product.star-left-side fill="#FAA90C" @mouseover="isOnMouseOverMode=true;ratingOnHover=3.5" @mouseleave="isOnMouseOverMode=false;"
                    @click="rating=3.5" class="cursor-pointer" />
                </template>
                <template x-if="ratingOnHover < 4">
                    <x-icon.product.star-right-side fill="#D9D9D9" @mouseover="isOnMouseOverMode=true;ratingOnHover=4" @mouseleave="isOnMouseOverMode=false;"
                    @click="rating=4" class="cursor-pointer" />
                </template>
                <template x-if="ratingOnHover >= 4">
                    <x-icon.product.star-right-side fill="#FAA90C" @mouseover="isOnMouseOverMode=true;ratingOnHover=4" @mouseleave="isOnMouseOverMode=false;"
                    @click="rating=4" class="cursor-pointer" />
                </template>
            </div>

            {{-- star#5 --}}
            <div class="flex">
                <template x-if="ratingOnHover < 4.5">
                    <x-icon.product.star-left-side fill="#D9D9D9" @mouseover="isOnMouseOverMode=true;ratingOnHover=4.5" @mouseleave="isOnMouseOverMode=false;"
                    @click="rating=4.5" class="cursor-pointer" />
                </template>
                <template x-if="ratingOnHover >= 4.5">
                    <x-icon.product.star-left-side fill="#FAA90C" @mouseover="isOnMouseOverMode=true;ratingOnHover=4.5" @mouseleave="isOnMouseOverMode=false;"
                    @click="rating=4.5" class="cursor-pointer" />
                </template>
                <template x-if="ratingOnHover < 5">
                    <x-icon.product.star-right-side fill="#D9D9D9" @mouseover="isOnMouseOverMode=true;ratingOnHover=5" @mouseleave="isOnMouseOverMode=false;"
                    @click="rating=5" class="cursor-pointer" />
                </template>
                <template x-if="ratingOnHover >= 5">
                    <x-icon.product.star-right-side fill="#FAA90C" @mouseover="isOnMouseOverMode=true;ratingOnHover=5" @mouseleave="isOnMouseOverMode=false;"
                    @click="rating=5" class="cursor-pointer" />
                </template>
            </div>
        </div>
    </template>
    
    {{-- Rate --}}
    <div>
        <p x-text="`(${rating})`" class="text-sm"></p>
    </div>
</div>