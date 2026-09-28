<a href="{{ route('cart.index') }}" class="nad-cartbtn !gap-2 !px-4" title="السلة — طلباتي" wire:key="header-cart">
    <x-shop-icon name="bag" class="h-4 w-4" />
    <span class="hidden text-[12px] font-extrabold sm:block">طلباتي</span>
    <span class="nad-cbadge {{ $count > 0 ? '' : 'zero' }}">{{ $count }}</span>
</a>
