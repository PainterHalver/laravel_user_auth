<div class="flex justify-between items-center bg-gray-100 p-4">
    <div class="flex items-center">
        <a href="{{ route('home') }}" class="text-2xl font-bold">Laravel Auth App</a>
    </div>
    <div class="flex items-center">
        @auth
            <span class="mx-4 text-xl">{{ auth()->user()->username }}</span>
            <form action="{{ route('logout') }}" method="post">
                @csrf
                <button type="submit" class="mx-4 text-xl">Logout</button>
            </form>
        @endauth
        @guest
            <span class="text-xl">You are not logged in</span>
        @endguest
    </div>
</div>
